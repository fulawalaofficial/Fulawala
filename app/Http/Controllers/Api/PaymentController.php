<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function createOrder(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required','numeric','min:1'],
            'payment_type' => ['required','string'],
            'reference_id' => ['nullable','integer'],
        ]);

        $amountInPaise = (int) round($data['amount'] * 100);
        $razorpayOrderId = 'mock_order_'.uniqid();

        // Add real Razorpay SDK call here when RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET are configured.

        $payment = Payment::create([
            'user_id' => $request->user()->id,
            'payment_type' => $data['payment_type'],
            'reference_id' => $data['reference_id'] ?? null,
            'amount' => $data['amount'],
            'razorpay_order_id' => $razorpayOrderId,
            'payment_status' => 'Pending',
        ]);

        return response()->json([
            'payment_id' => $payment->id,
            'razorpay_order_id' => $razorpayOrderId,
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'key' => env('RAZORPAY_KEY_ID', 'rzp_test_mock_key'),
        ]);
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'payment_id' => ['required','exists:payments,id'],
            'razorpay_payment_id' => ['required','string'],
            'razorpay_order_id' => ['required','string'],
            'razorpay_signature' => ['nullable','string'],
        ]);

        $payment = Payment::findOrFail($data['payment_id']);
        $payment->update([
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'razorpay_order_id' => $data['razorpay_order_id'],
            'razorpay_signature' => $data['razorpay_signature'] ?? 'mock_signature',
            'payment_status' => 'Paid',
        ]);

        return response()->json(['message' => 'Payment verified', 'payment' => $payment]);
    }

    public function history(Request $request)
    {
        return response()->json($request->user()->payments()->latest()->get());
    }
}
