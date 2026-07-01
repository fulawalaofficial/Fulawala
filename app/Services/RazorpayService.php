<?php

namespace App\Services;

use Razorpay\Api\Api;

class RazorpayService
{
    public function api(): Api
    {
        $keyId = config('services.razorpay.key_id');
        $keySecret = config('services.razorpay.key_secret');

        if (!$keyId || !$keySecret) {
            abort(500, 'Razorpay API keys are not configured in .env file.');
        }

        return new Api($keyId, $keySecret);
    }

    public function amountToPaise($amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    public function createOrder($amount, string $receipt, array $notes = [])
    {
        return $this->api()->order->create([
            'receipt' => substr($receipt, 0, 40),
            'amount' => $this->amountToPaise($amount),
            'currency' => config('services.razorpay.currency', 'INR'),
            'notes' => $notes,
        ]);
    }

    public function verifySignature(string $orderId, string $paymentId, string $signature): void
    {
        $this->api()->utility->verifyPaymentSignature([
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ]);
    }

    public function capturePaymentIfNeeded(string $paymentId, int $amountInPaise)
    {
        $payment = $this->api()->payment->fetch($paymentId);

        if (($payment['status'] ?? null) === 'authorized') {
            $payment = $payment->capture([
                'amount' => $amountInPaise,
                'currency' => config('services.razorpay.currency', 'INR'),
            ]);
        }

        return $payment;
    }
}