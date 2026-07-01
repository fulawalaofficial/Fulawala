<?php

namespace App\Services;

use Razorpay\Api\Api;
use RuntimeException;

class RazorpayService
{
    protected Api $api;

    public function __construct()
    {
        $keyId = config('services.razorpay.key_id');
        $keySecret = config('services.razorpay.key_secret');

        if (!$keyId || !$keySecret) {
            throw new RuntimeException('Razorpay key_id or key_secret is missing. Check .env file.');
        }

        $this->api = new Api($keyId, $keySecret);
    }

    public function amountToPaise(float|int|string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    public function createOrder(float|int|string $amount, string $receipt, array $notes = []): array
    {
        $order = $this->api->order->create([
            'receipt' => $receipt,
            'amount' => $this->amountToPaise($amount),
            'currency' => config('services.razorpay.currency', 'INR'),
            'notes' => $notes,
        ]);

        return $order->toArray();
    }

    public function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        $this->api->utility->verifyPaymentSignature([
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ]);

        return true;
    }

    public function capturePaymentIfNeeded(string $paymentId, int $amountInPaise): array
    {
        $payment = $this->api->payment->fetch($paymentId);

        if (($payment['status'] ?? null) === 'captured') {
            return $payment->toArray();
        }

        $capturedPayment = $payment->capture([
            'amount' => $amountInPaise,
            'currency' => config('services.razorpay.currency', 'INR'),
        ]);

        return $capturedPayment->toArray();
    }
}