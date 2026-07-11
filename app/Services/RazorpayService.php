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
        $keyId = trim(
            (string) config(
                'services.razorpay.key_id'
            )
        );

        $keySecret = trim(
            (string) config(
                'services.razorpay.key_secret'
            )
        );

        $currency = trim(
            (string) config(
                'services.razorpay.currency',
                'INR'
            )
        );

        if (
            $keyId === '' ||
            $keySecret === ''
        ) {
            throw new RuntimeException(
                'Razorpay key ID or key secret is missing. Check the .env file.'
            );
        }

        $this->keyId = $keyId;

        $this->currency = $currency !== ''
            ? strtoupper($currency)
            : 'INR';

        $this->api = new Api(
            $keyId,
            $keySecret
        );
    }

    /**
     * Return public Razorpay Key ID.
     */
    public function getKeyId(): string
    {
        return $this->keyId;
    }

    /**
     * Return configured currency.
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Convert rupees into paise.
     *
     * ₹100 becomes 10000 paise.
     */
    public function amountToPaise(
        float|int|string $amount
    ): int {
        $numericAmount = (float) $amount;

        if ($numericAmount <= 0) {
            throw new RuntimeException(
                'Payment amount must be greater than zero.'
            );
        }

        return (int) round(
            $numericAmount * 100
        );
    }

    /**
     * Create a Razorpay order.
     */
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
            $formattedNotes[(string) $key] =
                (string) $value;
        }

        $order = $this->api->order->create([
            'receipt' => $receipt,
            'amount' =>
                $this->amountToPaise($amount),

            'currency' => $this->currency,
            'notes' => $formattedNotes,
        ]);

        return $order->toArray();
    }

    /**
     * Verify Razorpay Checkout signature.
     *
     * Razorpay throws an exception when the signature
     * is invalid.
     */
    public function verifySignature(
        string $orderId,
        string $paymentId,
        string $signature
    ): bool {
        $this->api
            ->utility
            ->verifyPaymentSignature([
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ]);

        return true;
    }

    /**
     * Fetch a Razorpay payment.
     */
    public function fetchPayment(
        string $paymentId
    ): array {
        $payment = $this->api
            ->payment
            ->fetch($paymentId);

        return $payment->toArray();
    }

    /**
     * Capture the payment only when its current status
     * is authorized.
     */
    public function capturePaymentIfNeeded(
        string $paymentId,
        int $amountInPaise
    ): array {
        if ($amountInPaise <= 0) {
            throw new RuntimeException(
                'Capture amount must be greater than zero.'
            );
        }

        $payment = $this->api
            ->payment
            ->fetch($paymentId);

        $paymentData = $payment->toArray();

        $status = $paymentData['status'] ?? null;

        /*
         * Auto-captured payment.
         */
        if ($status === 'captured') {
            return $paymentData;
        }

        /*
         * Capture only an authorized payment.
         */
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