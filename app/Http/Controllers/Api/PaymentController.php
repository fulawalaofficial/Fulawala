<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class PaymentController extends Controller
{
    public function createOrder(Request $request, RazorpayService $razorpay)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_type' => ['required', 'string', 'max:50'],
            'reference_id' => ['nullable', 'integer'],
        ]);

        try {
            $receipt = 'pay_' . $request->user()->id . '_' . now()->format('ymdHis') . '_' . Str::random(5);

            $order = $razorpay->createOrder(
                $data['amount'],
                $receipt,
                [
                    'user_id' => (string) $request->user()->id,
                    'payment_type' => $data['payment_type'],
                    'reference_id' => (string) ($data['reference_id'] ?? ''),
                ]
            );

            $payment = Payment::create([
                'user_id' => $request->user()->id,
                'payment_type' => $data['payment_type'],
                'reference_id' => $data['reference_id'] ?? null,
                'amount' => $data['amount'],
                'razorpay_order_id' => $order['id'],
                'payment_status' => 'Pending',
            ]);

            return response()->json([
                'message' => 'Razorpay order created successfully.',
                'payment_id' => $payment->id,
                'razorpay_order_id' => $order['id'],
                'order_id' => $order['id'],
                'amount' => $order['amount'],
                'amount_rupees' => $payment->amount,
                'currency' => $order['currency'],
                'key_id' => config('services.razorpay.key_id'),
                'name' => config('app.name', 'Fulawala'),
                'description' => ucfirst(str_replace('_', ' ', $data['payment_type'])) . ' payment',
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Unable to create Razorpay order.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    public function verify(Request $request, RazorpayService $razorpay)
    {
        $data = $request->validate([
            'payment_id' => ['required', 'exists:payments,id'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $payment = Payment::where('id', $data['payment_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($payment->payment_status === 'Paid') {
            return response()->json([
                'message' => 'Payment already verified.',
                'payment' => $payment,
            ]);
        }

        if ($payment->razorpay_order_id !== $data['razorpay_order_id']) {
            return response()->json([
                'message' => 'Invalid Razorpay order ID.',
            ], 422);
        }

        try {
            $razorpay->verifySignature(
                $data['razorpay_order_id'],
                $data['razorpay_payment_id'],
                $data['razorpay_signature']
            );

            $razorpayPayment = $razorpay->capturePaymentIfNeeded(
                $data['razorpay_payment_id'],
                $razorpay->amountToPaise($payment->amount)
            );

            if (($razorpayPayment['status'] ?? null) !== 'captured') {
                return response()->json([
                    'message' => 'Payment is not captured yet.',
                    'razorpay_status' => $razorpayPayment['status'] ?? null,
                ], 422);
            }

            DB::transaction(function () use ($payment, $data) {
                $payment->update([
                    'razorpay_payment_id' => $data['razorpay_payment_id'],
                    'razorpay_order_id' => $data['razorpay_order_id'],
                    'razorpay_signature' => $data['razorpay_signature'],
                    'payment_status' => 'Paid',
                ]);

                if ($payment->payment_type === 'subscription' && $payment->reference_id) {
                    Subscription::where('id', $payment->reference_id)
                        ->where('user_id', $payment->user_id)
                        ->update([
                            'payment_status' => 'Paid',
                            'subscription_status' => 'Active',
                        ]);
                }
            });

            return response()->json([
                'message' => 'Payment verified successfully.',
                'payment' => $payment->fresh(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Payment verification failed.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    public function history(Request $request)
    {
        return response()->json(
            $request->user()->payments()->latest()->get()
        );
    }
}