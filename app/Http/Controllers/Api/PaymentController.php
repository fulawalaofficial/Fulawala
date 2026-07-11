<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PaymentController extends Controller
{
    /**
     * Create Razorpay order for a custom order or subscription.
     *
     * Do not accept the payable amount from the application.
     * The amount is read from the database.
     */
    public function createOrder(
        Request $request,
        RazorpayService $razorpay
    ) {
        $data = $request->validate([
            'payment_type' => [
                'required',
                'string',
                Rule::in([
                    'custom_order',
                    'subscription',
                ]),
            ],

            'reference_id' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $user = $request->user();

        /*
         * Read the amount from the related database record.
         */
        $payable = $this->resolvePayable(
            $user->id,
            $data['payment_type'],
            $data['reference_id']
        );

        /*
         * Prevent duplicate Razorpay orders when the user
         * presses the payment button repeatedly.
         */
        $existingPayment = Payment::where(
            'user_id',
            $user->id
        )
            ->where(
                'payment_type',
                $data['payment_type']
            )
            ->where(
                'reference_id',
                $data['reference_id']
            )
            ->where(
                'payment_status',
                'Pending'
            )
            ->whereNotNull('razorpay_order_id')
            ->latest()
            ->first();

        if (
            $existingPayment &&
            round((float) $existingPayment->amount, 2) ===
            round((float) $payable['amount'], 2)
        ) {
            return response()->json(
                $this->buildCheckoutResponse(
                    $existingPayment,
                    $razorpay,
                    $payable['description'],
                    $user,
                    'Existing Razorpay order returned.'
                )
            );
        }

        try {
            $prefix = $data['payment_type'] === 'custom_order'
                ? 'co'
                : 'sub';

            $receipt = $prefix . '_' .
                $data['reference_id'] . '_' .
                now()->format('ymdHis') . '_' .
                Str::lower(Str::random(5));

            $razorpayOrder = $razorpay->createOrder(
                $payable['amount'],
                $receipt,
                [
                    'user_id' => (string) $user->id,
                    'payment_type' => $data['payment_type'],
                    'reference_id' =>
                        (string) $data['reference_id'],
                ]
            );

            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_type' => $data['payment_type'],
                'reference_id' => $data['reference_id'],
                'amount' => $payable['amount'],
                'razorpay_order_id' =>
                    $razorpayOrder['id'],

                'razorpay_payment_id' => null,
                'razorpay_signature' => null,
                'payment_status' => 'Pending',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Razorpay order created successfully.',

                /*
                 * Local payments-table ID.
                 */
                'payment_id' => $payment->id,

                /*
                 * Razorpay order ID.
                 */
                'razorpay_order_id' =>
                    $razorpayOrder['id'],

                'order_id' =>
                    $razorpayOrder['id'],

                /*
                 * Razorpay amount is in paise.
                 */
                'amount' =>
                    (int) $razorpayOrder['amount'],

                'amount_rupees' =>
                    $payment->amount,

                'currency' =>
                    $razorpayOrder['currency'],

                /*
                 * Only the public key ID is returned.
                 */
                'key_id' =>
                    $razorpay->getKeyId(),

                'name' =>
                    config('app.name', 'Fulawala'),

                'description' =>
                    $payable['description'],

                'prefill' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'contact' => $user->mobile,
                ],

                'notes' => [
                    'payment_type' =>
                        $data['payment_type'],

                    'reference_id' =>
                        (string) $data['reference_id'],
                ],
            ], 201);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to create Razorpay order.',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 422);
        }
    }

    /**
     * Verify Razorpay payment and update the related record.
     */
    public function verify(
        Request $request,
        RazorpayService $razorpay
    ) {
        $data = $request->validate([
            /*
             * Local ID from payments table.
             */
            'payment_id' => [
                'required',
                'integer',
                'exists:payments,id',
            ],

            'razorpay_payment_id' => [
                'required',
                'string',
                'max:100',
            ],

            'razorpay_order_id' => [
                'required',
                'string',
                'max:100',
            ],

            'razorpay_signature' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $payment = Payment::where(
            'id',
            $data['payment_id']
        )
            ->where(
                'user_id',
                $request->user()->id
            )
            ->firstOrFail();

        /*
         * Return success if this request has already
         * been verified.
         */
        if ($payment->payment_status === 'Paid') {
            return response()->json([
                'status' => true,
                'message' => 'Payment already verified.',
                'payment' => $payment,
            ]);
        }

        if (!$payment->razorpay_order_id) {
            return response()->json([
                'status' => false,
                'message' => 'Razorpay order ID is missing.',
            ], 422);
        }

        /*
         * Compare the returned order ID with the order ID
         * stored by the server.
         */
        if (
            !hash_equals(
                (string) $payment->razorpay_order_id,
                (string) $data['razorpay_order_id']
            )
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Razorpay order ID.',
            ], 422);
        }

        /*
         * Prevent the same Razorpay payment from being
         * applied to more than one local payment record.
         */
        $duplicatePayment = Payment::where(
            'razorpay_payment_id',
            $data['razorpay_payment_id']
        )
            ->where('id', '!=', $payment->id)
            ->exists();

        if ($duplicatePayment) {
            return response()->json([
                'status' => false,
                'message' => 'This Razorpay payment has already been used.',
            ], 422);
        }

        try {
            /*
             * Verify Razorpay Checkout signature using the
             * server-stored Razorpay order ID.
             */
            $razorpay->verifySignature(
                $payment->razorpay_order_id,
                $data['razorpay_payment_id'],
                $data['razorpay_signature']
            );

            /*
             * Fetch payment and capture it when its status
             * is still authorized.
             */
            $razorpayPayment = $razorpay
                ->capturePaymentIfNeeded(
                    $data['razorpay_payment_id'],
                    $razorpay->amountToPaise(
                        $payment->amount
                    )
                );

            $razorpayStatus =
                $razorpayPayment['status'] ?? null;

            $razorpayOrderId =
                $razorpayPayment['order_id'] ?? null;

            $razorpayAmount = (int) (
                $razorpayPayment['amount'] ?? 0
            );

            $razorpayCurrency =
                $razorpayPayment['currency'] ?? null;

            $expectedAmount = $razorpay->amountToPaise(
                $payment->amount
            );

            /*
             * Confirm payment belongs to this order.
             */
            if (
                $razorpayOrderId !==
                $payment->razorpay_order_id
            ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Razorpay payment does not belong to this order.',
                ], 422);
            }

            /*
             * Confirm the actual Razorpay amount matches
             * the amount stored in our database.
             */
            if ($razorpayAmount !== $expectedAmount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment amount does not match the order amount.',
                    'expected_amount' => $expectedAmount,
                    'received_amount' => $razorpayAmount,
                ], 422);
            }

            if (
                strtoupper((string) $razorpayCurrency) !==
                $razorpay->getCurrency()
            ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Razorpay payment currency.',
                ], 422);
            }

            if ($razorpayStatus !== 'captured') {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment is not captured yet.',
                    'razorpay_status' =>
                        $razorpayStatus,
                ], 422);
            }

            DB::transaction(function () use (
                $payment,
                $data
            ) {
                /*
                 * Lock payment row to prevent duplicate
                 * verification requests.
                 */
                $lockedPayment = Payment::where(
                    'id',
                    $payment->id
                )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $lockedPayment->payment_status ===
                    'Paid'
                ) {
                    return;
                }

                $lockedPayment->update([
                    'razorpay_payment_id' =>
                        $data['razorpay_payment_id'],

                    'razorpay_order_id' =>
                        $data['razorpay_order_id'],

                    'razorpay_signature' =>
                        $data['razorpay_signature'],

                    'payment_status' => 'Paid',
                ]);

                /*
                 * Update custom order after payment.
                 */
                if (
                    $lockedPayment->payment_type ===
                    'custom_order' &&
                    $lockedPayment->reference_id
                ) {
                    $updated = CustomOrder::where(
                        'id',
                        $lockedPayment->reference_id
                    )
                        ->where(
                            'user_id',
                            $lockedPayment->user_id
                        )
                        ->update([
                            'payment_status' => 'Paid',
                            'order_status' => 'Order Placed',
                        ]);

                    if (!$updated) {
                        throw new RuntimeException(
                            'Custom order could not be updated.'
                        );
                    }
                }

                /*
                 * Update subscription after payment.
                 */
                if (
                    $lockedPayment->payment_type ===
                    'subscription' &&
                    $lockedPayment->reference_id
                ) {
                    $updated = Subscription::where(
                        'id',
                        $lockedPayment->reference_id
                    )
                        ->where(
                            'user_id',
                            $lockedPayment->user_id
                        )
                        ->update([
                            'payment_status' => 'Paid',
                            'subscription_status' =>
                                'Active',
                        ]);

                    if (!$updated) {
                        throw new RuntimeException(
                            'Subscription could not be updated.'
                        );
                    }
                }
            });

            $freshPayment = $payment->fresh();

            return response()->json([
                'status' => true,
                'message' => 'Payment verified successfully.',
                'payment' => $freshPayment,

                'custom_order' =>
                    $freshPayment->payment_type ===
                    'custom_order'
                        ? CustomOrder::with([
                            'items.flower',
                            'address',
                        ])->find(
                            $freshPayment->reference_id
                        )
                        : null,

                'subscription' =>
                    $freshPayment->payment_type ===
                    'subscription'
                        ? Subscription::with('packet')
                            ->find(
                                $freshPayment->reference_id
                            )
                        : null,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Payment verification failed.',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 422);
        }
    }

    /**
     * Return logged-in user's payment history.
     */
    public function history(Request $request)
    {
        $payments = $request->user()
            ->payments()
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Find payable record and return its server-side amount.
     */
    private function resolvePayable(
        int $userId,
        string $paymentType,
        int $referenceId
    ): array {
        if ($paymentType === 'custom_order') {
            $order = CustomOrder::where(
                'id',
                $referenceId
            )
                ->where('user_id', $userId)
                ->firstOrFail();

            if ($order->payment_status === 'Paid') {
                throw ValidationException::withMessages([
                    'reference_id' => [
                        'This custom order is already paid.',
                    ],
                ]);
            }

            if ((float) $order->total_amount <= 0) {
                throw ValidationException::withMessages([
                    'reference_id' => [
                        'The custom order amount is invalid.',
                    ],
                ]);
            }

            return [
                'amount' => $order->total_amount,
                'description' =>
                    'Custom flower order payment',
            ];
        }

        if ($paymentType === 'subscription') {
            $subscription = Subscription::where(
                'id',
                $referenceId
            )
                ->where('user_id', $userId)
                ->firstOrFail();

            if (
                $subscription->payment_status ===
                'Paid'
            ) {
                throw ValidationException::withMessages([
                    'reference_id' => [
                        'This subscription is already paid.',
                    ],
                ]);
            }

            if ((float) $subscription->amount <= 0) {
                throw ValidationException::withMessages([
                    'reference_id' => [
                        'The subscription amount is invalid.',
                    ],
                ]);
            }

            return [
                'amount' => $subscription->amount,
                'description' =>
                    'Flower subscription payment',
            ];
        }

        throw ValidationException::withMessages([
            'payment_type' => [
                'Unsupported payment type.',
            ],
        ]);
    }

    /**
     * Build Razorpay Checkout response for an existing
     * pending payment.
     */
    private function buildCheckoutResponse(
        Payment $payment,
        RazorpayService $razorpay,
        string $description,
        $user,
        string $message
    ): array {
        return [
            'status' => true,
            'message' => $message,
            'payment_id' => $payment->id,

            'razorpay_order_id' =>
                $payment->razorpay_order_id,

            'order_id' =>
                $payment->razorpay_order_id,

            'amount' =>
                $razorpay->amountToPaise(
                    $payment->amount
                ),

            'amount_rupees' =>
                $payment->amount,

            'currency' =>
                $razorpay->getCurrency(),

            'key_id' =>
                $razorpay->getKeyId(),

            'name' =>
                config('app.name', 'Fulawala'),

            'description' =>
                $description,

            'prefill' => [
                'name' => $user->name,
                'email' => $user->email,
                'contact' => $user->mobile,
            ],
        ];
    }
}