<?php

namespace App\Services;

use Razorpay\Api\Api;
use RuntimeException;

class RazorpayService
{
    protected Api $api;
    protected string $keyId;
    protected string $currency;

    public function __construct()
    {
        $keyId = trim((string) config('services.razorpay.key_id'));
        $keySecret = trim((string) config('services.razorpay.key_secret'));
        $currency = trim((string) config('services.razorpay.currency', 'INR'));

        if ($keyId === '' || $keySecret === '') {
            throw new RuntimeException(
                'Razorpay key ID or key secret is missing. Check the .env file.'
            );
        }

        $this->keyId = $keyId;
        $this->currency = $currency !== '' ? strtoupper($currency) : 'INR';
        $this->api = new Api($keyId, $keySecret);
    }

    public function getKeyId(): string
    {
        return $this->keyId;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function amountToPaise(float|int|string $amount): int
    {
        $numericAmount = (float) $amount;

        if ($numericAmount <= 0) {
            throw new RuntimeException(
                'Payment amount must be greater than zero.'
            );
        }

        return (int) round($numericAmount * 100);
    }

    public function createOrder(
        float|int|string $amount,
        string $receipt,
        array $notes = []
    ): array {
        $receipt = trim($receipt);

        if ($receipt === '') {
            throw new RuntimeException(
                'Razorpay receipt cannot be empty.'
            );
        }

        $formattedNotes = [];

        foreach ($notes as $key => $value) {
            $formattedNotes[(string) $key] = (string) $value;
        }

        $order = $this->api->order->create([
            'receipt' => $receipt,
            'amount' => $this->amountToPaise($amount),
            'currency' => $this->currency,
            'notes' => $formattedNotes,
        ]);

        return $order->toArray();
    }

    public function verifySignature(
        string $orderId,
        string $paymentId,
        string $signature
    ): bool {
        $this->api->utility->verifyPaymentSignature([
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ]);

        return true;
    }

    public function verifyWebhookSignature(
        string $rawBody,
        string $signature,
        string $secret
    ): bool {
        if ($rawBody === '' || $signature === '' || $secret === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $signature);
    }

    public function fetchPayment(string $paymentId): array
    {
        $payment = $this->api->payment->fetch($paymentId);

        return $payment->toArray();
    }

    public function capturePaymentIfNeeded(
        string $paymentId,
        int $amountInPaise
    ): array {
        if ($amountInPaise <= 0) {
            throw new RuntimeException(
                'Capture amount must be greater than zero.'
            );
        }

        $payment = $this->api->payment->fetch($paymentId);
        $paymentData = $payment->toArray();
        $status = $paymentData['status'] ?? null;

        if ($status === 'captured') {
            return $paymentData;
        }

        if ($status !== 'authorized') {
            return $paymentData;
        }

        $capturedPayment = $payment->capture([
            'amount' => $amountInPaise,
            'currency' => $this->currency,
        ]);

        return $capturedPayment->toArray();
    }
}
