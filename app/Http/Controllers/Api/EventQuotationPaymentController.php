<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Quotation;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class EventQuotationPaymentController extends Controller
{
    /**
     * Create Razorpay order for:
     * - advance
     * - remaining/balance
     *
     * IMPORTANT:
     * Amount is calculated on Laravel. The mobile app cannot choose the amount.
     */
    public function createOrder(
        Request $request,
        Quotation $quotation,
        RazorpayService $razorpay
    ): JsonResponse {
        $data = $request->validate([
            'payment_stage' => [
                'required',
                Rule::in(['advance', 'balance']),
            ],
        ]);

        $quotation->loadMissing([
            'booking',
            'booking.user',
        ]);

        $booking = $quotation->booking;
        $user = $request->user();

        abort_unless(
            $booking
            && (int) $booking->user_id === (int) $user->id,
            403
        );

        $summary = $this->paymentSummary(
            $quotation,
            (int) $user->id
        );

        $stage = $data['payment_stage'];

        if ($stage === 'advance') {
            if (!$summary['can_pay_advance']) {
                return response()->json([
                    'message' => $summary['is_advance_paid']
                        ? 'Advance payment is already completed.'
                        : 'Advance payment is not currently available for this quotation.',
                ], 422);
            }

            $amount = $summary['advance_required_amount'];
            $paymentType = 'event_advance';
        } else {
            if (!$summary['can_pay_balance']) {
                return response()->json([
                    'message' => $summary['is_fully_paid']
                        ? 'This quotation is already fully paid.'
                        : 'Please complete the required advance payment first.',
                ], 422);
            }

            $amount = $summary['remaining_amount'];
            $paymentType = 'event_balance';
        }

        if ($amount <= 0) {
            return response()->json([
                'message' => 'There is no payable amount for this stage.',
            ], 422);
        }

        /*
         * Invalidate older unverified local attempts of the same stage.
         * Paid rows are never touched.
         */
        Payment::query()
            ->where('user_id', $user->id)
            ->where('reference_id', $booking->id)
            ->where('payment_type', $paymentType)
            ->where('payment_status', 'Pending')
            ->update([
                'payment_status' => 'Failed',
            ]);

        $receipt = sprintf(
            'evt_q%d_%s_%d',
            $quotation->id,
            $stage,
            now()->timestamp
        );

        try {
            $order = $razorpay->createOrder(
                $amount,
                $receipt,
                [
                    'quotation_id' => $quotation->id,
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'payment_stage' => $stage,
                ]
            );
        } catch (Throwable $e) {
            Log::error('Event quotation Razorpay order creation failed.', [
                'quotation_id' => $quotation->id,
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'stage' => $stage,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to create Razorpay order. Please try again.',
            ], 502);
        }

        $razorpayOrderId = (string) ($order['id'] ?? '');

        if ($razorpayOrderId === '') {
            return response()->json([
                'message' => 'Razorpay did not return a valid order ID.',
            ], 502);
        }

        $localPayment = Payment::create([
            'user_id' => $user->id,
            'payment_type' => $paymentType,
            'reference_id' => $booking->id,
            'amount' => $amount,
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_payment_id' => null,
            'payment_status' => 'Pending',
        ]);

        return response()->json([
            'message' => 'Razorpay order created successfully.',

            'payment_id' => $localPayment->id,

            'checkout' => [
                'key_id' => $razorpay->getKeyId(),
                'razorpay_order_id' => $razorpayOrderId,
                'amount_in_paise' => $razorpay->amountToPaise($amount),
                'amount' => $amount,
                'currency' => $razorpay->getCurrency(),

                'name' => 'Fulawala',

                'description' => $stage === 'advance'
                    ? "Advance payment for Event Quotation #{$quotation->id}"
                    : "Remaining payment for Event Quotation #{$quotation->id}",

                'prefill' => [
                    'name' => $user->name ?? '',
                    'email' => $user->email ?? '',
                    'contact' => $user->mobile ?? '',
                ],

                'notes' => [
                    'quotation_id' => (string) $quotation->id,
                    'booking_id' => (string) $booking->id,
                    'payment_stage' => $stage,
                    'local_payment_id' => (string) $localPayment->id,
                ],
            ],
        ]);
    }

    /**
     * Verify Razorpay callback on Laravel.
     *
     * We verify:
     * 1. local payment belongs to authenticated user/booking
     * 2. order ID
     * 3. Razorpay signature
     * 4. remote payment's order ID
     * 5. remote payment amount
     * 6. remote payment currency
     * 7. captured state
     *
     * Only then is payment_status changed to Paid.
     */
    public function verify(
        Request $request,
        Quotation $quotation,
        RazorpayService $razorpay
    ): JsonResponse {
        $data = $request->validate([
            'payment_id' => ['required', 'integer'],
            'payment_stage' => [
                'required',
                Rule::in(['advance', 'balance']),
            ],
            'razorpay_payment_id' => ['required', 'string', 'max:255'],
            'razorpay_order_id' => ['required', 'string', 'max:255'],
            'razorpay_signature' => ['required', 'string', 'max:500'],
        ]);

        $quotation->loadMissing('booking');

        $booking = $quotation->booking;
        $user = $request->user();

        abort_unless(
            $booking
            && (int) $booking->user_id === (int) $user->id,
            403
        );

        $expectedType = $data['payment_stage'] === 'advance'
            ? 'event_advance'
            : 'event_balance';

        $localPayment = Payment::query()
            ->whereKey($data['payment_id'])
            ->where('user_id', $user->id)
            ->where('reference_id', $booking->id)
            ->where('payment_type', $expectedType)
            ->firstOrFail();

        /*
         * Idempotent response if already verified.
         */
        if ($localPayment->payment_status === 'Paid') {
            return response()->json([
                'status' => true,
                'message' => 'Payment is already verified.',
                'quotation' => $this->paymentSummary(
                    $quotation,
                    (int) $user->id
                ),
            ]);
        }

        if (
            !hash_equals(
                (string) $localPayment->razorpay_order_id,
                (string) $data['razorpay_order_id']
            )
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Razorpay order ID does not match this payment.',
            ], 422);
        }

        /*
         * Prevent one Razorpay payment ID from being reused.
         */
        $paymentAlreadyUsed = Payment::query()
            ->where('razorpay_payment_id', $data['razorpay_payment_id'])
            ->where('id', '!=', $localPayment->id)
            ->where('payment_status', 'Paid')
            ->exists();

        if ($paymentAlreadyUsed) {
            return response()->json([
                'status' => false,
                'message' => 'This Razorpay payment has already been used.',
            ], 422);
        }

        /*
         * Verify checkout signature.
         */
        try {
            $razorpay->verifySignature(
                $data['razorpay_order_id'],
                $data['razorpay_payment_id'],
                $data['razorpay_signature']
            );
        } catch (Throwable $e) {
            $localPayment->update([
                'payment_status' => 'Failed',
            ]);

            Log::warning('Event quotation Razorpay signature verification failed.', [
                'quotation_id' => $quotation->id,
                'local_payment_id' => $localPayment->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Razorpay signature verification failed.',
            ], 422);
        }

        $expectedAmountInPaise = $razorpay->amountToPaise(
            $localPayment->amount
        );

        /*
         * Fetch payment directly from Razorpay and validate it.
         */
        try {
            $remotePayment = $razorpay->fetchPayment(
                $data['razorpay_payment_id']
            );

            if (
                (string) ($remotePayment['order_id'] ?? '')
                !== (string) $data['razorpay_order_id']
            ) {
                throw new \RuntimeException(
                    'Razorpay payment belongs to another order.'
                );
            }

            if (
                (int) ($remotePayment['amount'] ?? 0)
                !== $expectedAmountInPaise
            ) {
                throw new \RuntimeException(
                    'Razorpay payment amount does not match.'
                );
            }

            if (
                strtoupper((string) ($remotePayment['currency'] ?? ''))
                !== strtoupper($razorpay->getCurrency())
            ) {
                throw new \RuntimeException(
                    'Razorpay payment currency does not match.'
                );
            }

            /*
             * If the account created an authorized payment instead of auto capture,
             * capture the exact server-approved amount.
             */
            $remotePayment = $razorpay->capturePaymentIfNeeded(
                $data['razorpay_payment_id'],
                $expectedAmountInPaise
            );

            if ((string) ($remotePayment['status'] ?? '') !== 'captured') {
                throw new \RuntimeException(
                    'Razorpay payment is not captured.'
                );
            }
        } catch (Throwable $e) {
            Log::error('Event quotation Razorpay remote verification failed.', [
                'quotation_id' => $quotation->id,
                'local_payment_id' => $localPayment->id,
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'error' => $e->getMessage(),
            ]);

            /*
             * Do not mark Failed here because a temporary Razorpay/network problem
             * can be reconciled through retry/webhook later.
             */
            return response()->json([
                'status' => false,
                'message' => 'Payment could not be confirmed with Razorpay. Please refresh and try again.',
            ], 422);
        }

        DB::transaction(function () use (
            $localPayment,
            $data,
            $quotation,
            $booking
        ): void {
            $payment = Payment::query()
                ->lockForUpdate()
                ->findOrFail($localPayment->id);

            if ($payment->payment_status !== 'Paid') {
                $payment->update([
                    'razorpay_payment_id' => $data['razorpay_payment_id'],
                    'payment_status' => 'Paid',
                ]);
            }

            /*
             * Advance payment accepts quotation and confirms event booking.
             */
            if ($data['payment_stage'] === 'advance') {
                $quotation->update([
                    'quotation_status' => 'Accepted',
                ]);

                if ($booking->booking_status !== 'Cancelled') {
                    $booking->update([
                        'booking_status' => 'Confirmed',
                    ]);
                }
            }
        });

        $quotation->refresh();
        $quotation->loadMissing('booking');

        $summary = $this->paymentSummary(
            $quotation,
            (int) $user->id
        );

        return response()->json([
            'status' => true,

            'message' => $summary['is_fully_paid']
                ? 'Full event payment verified successfully.'
                : 'Advance payment verified successfully.',

            'quotation' => $summary,
        ]);
    }

    /**
     * Server-side payment state for one quotation.
     */
    private function paymentSummary(
        Quotation $quotation,
        int $userId
    ): array {
        $booking = $quotation->booking;

        $total = max(
            0,
            round(
                (float) (
                    $quotation->total_amount
                    ?? $quotation->amount
                    ?? 0
                ),
                2
            )
        );

        $advanceRequired = min(
            $total,
            max(
                0,
                round(
                    (float) ($quotation->advance_amount ?? 0),
                    2
                )
            )
        );

        $payments = Payment::query()
            ->where('user_id', $userId)
            ->where('reference_id', $booking->id)
            ->whereIn('payment_type', [
                'event_advance',
                'event_balance',
                'event_full',
            ])
            ->where('payment_status', 'Paid')
            ->get();

        $advancePaid = (float) $payments
            ->where('payment_type', 'event_advance')
            ->sum('amount');

        $balancePaid = (float) $payments
            ->whereIn('payment_type', ['event_balance', 'event_full'])
            ->sum('amount');

        $paidAmount = min(
            $total,
            max(0, $advancePaid + $balancePaid)
        );

        $remainingAmount = max(
            0,
            round($total - $paidAmount, 2)
        );

        $isAdvancePaid = $advanceRequired <= 0
            ? true
            : ($advancePaid + 0.01 >= $advanceRequired);

        $isFullyPaid = $total > 0
            && $remainingAmount <= 0.01;

        $quotationStatus = strtolower(
            trim((string) $quotation->quotation_status)
        );

        $canStartPayment = in_array(
            $quotationStatus,
            [
                'sent',
                'quotation sent',
                'accepted',
                'approved',
            ],
            true
        );

        return [
            'quotation_id' => $quotation->id,
            'total_amount' => $total,
            'advance_required_amount' => $advanceRequired,
            'advance_paid_amount' => round($advancePaid, 2),
            'balance_paid_amount' => round($balancePaid, 2),
            'paid_amount' => round($paidAmount, 2),
            'remaining_amount' => $remainingAmount,

            'is_advance_paid' => $isAdvancePaid,
            'is_fully_paid' => $isFullyPaid,

            'can_pay_advance' => $canStartPayment
                && !$isAdvancePaid
                && $advanceRequired > 0,

            'can_pay_balance' => $canStartPayment
                && $isAdvancePaid
                && !$isFullyPaid
                && $remainingAmount > 0,

            'payment_status' => $isFullyPaid
                ? 'Fully Paid'
                : ($isAdvancePaid && $paidAmount > 0
                    ? 'Advance Paid'
                    : 'Pending'),
        ];
    }
}
