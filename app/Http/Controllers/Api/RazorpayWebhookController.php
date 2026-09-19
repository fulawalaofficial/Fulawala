<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class RazorpayWebhookController extends Controller
{
    public function handle(
        Request $request,
        RazorpayService $razorpay
    ) {
        $rawBody = $request->getContent();
        $receivedSignature = trim(
            (string) $request->header('X-Razorpay-Signature')
        );
        $webhookSecret = trim(
            (string) config('services.razorpay.webhook_secret')
        );

        if (
            !$razorpay->verifyWebhookSignature(
                $rawBody,
                $receivedSignature,
                $webhookSecret
            )
        ) {
            Log::warning('Invalid Razorpay webhook signature.');

            return response()->json([
                'status' => false,
                'message' => 'Invalid webhook signature.',
            ], 401);
        }

        $payload = json_decode($rawBody, true);

        if (!is_array($payload)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid webhook payload.',
            ], 400);
        }

        $event = (string) ($payload['event'] ?? '');

        try {
            switch ($event) {
                case 'payment.captured':
                    $this->handleCapturedPayment(
                        $payload,
                        $razorpay
                    );
                    break;

                case 'payment.failed':
                    $this->handleFailedPayment($payload);
                    break;

                case 'order.paid':
                case 'payment.authorized':
                    /*
                     * Kept for logging/observability.
                     * payment.captured is the event that finalizes the
                     * local record, while mobile verification can also
                     * finalize it immediately after checkout.
                     */
                    break;
            }

            Log::info('Razorpay webhook processed.', [
                'event' => $event,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Webhook processed.',
            ]);
        } catch (Throwable $e) {
            report($e);

            Log::error('Razorpay webhook processing failed.', [
                'event' => $event,
                'message' => $e->getMessage(),
            ]);

            /*
             * Return 500 so Razorpay can retry the webhook.
             */
            return response()->json([
                'status' => false,
                'message' => 'Webhook processing failed.',
            ], 500);
        }
    }

    private function handleCapturedPayment(
        array $payload,
        RazorpayService $razorpay
    ): void {
        $entity = $payload['payload']['payment']['entity'] ?? null;

        if (!is_array($entity)) {
            throw new RuntimeException(
                'Razorpay payment entity is missing.'
            );
        }

        $razorpayPaymentId = (string) ($entity['id'] ?? '');
        $razorpayOrderId = (string) ($entity['order_id'] ?? '');
        $status = (string) ($entity['status'] ?? '');
        $amount = (int) ($entity['amount'] ?? 0);
        $currency = strtoupper(
            (string) ($entity['currency'] ?? '')
        );

        if (
            $razorpayPaymentId === '' ||
            $razorpayOrderId === '' ||
            $status !== 'captured'
        ) {
            throw new RuntimeException(
                'Incomplete captured payment webhook.'
            );
        }

        $payment = Payment::query()
            ->where('razorpay_order_id', $razorpayOrderId)
            ->first();

        if (!$payment) {
            /*
             * The event is valid, but may refer to a different integration.
             * Do not fail the whole webhook queue for an unknown order.
             */
            Log::notice('Razorpay captured payment has no local order.', [
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
            ]);

            return;
        }

        $expectedAmount = $razorpay->amountToPaise($payment->amount);

        if ($amount !== $expectedAmount) {
            throw new RuntimeException(
                'Webhook payment amount does not match local payment.'
            );
        }

        if ($currency !== $razorpay->getCurrency()) {
            throw new RuntimeException(
                'Webhook payment currency does not match local payment.'
            );
        }

        $duplicate = Payment::query()
            ->where('razorpay_payment_id', $razorpayPaymentId)
            ->where('id', '!=', $payment->id)
            ->exists();

        if ($duplicate) {
            throw new RuntimeException(
                'Razorpay payment ID is already linked to another payment.'
            );
        }

        DB::transaction(function () use (
            $payment,
            $razorpayPaymentId
        ) {
            $locked = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->payment_status === 'Paid') {
                return;
            }

            $locked->update([
                'razorpay_payment_id' => $razorpayPaymentId,
                'payment_status' => 'Paid',
            ]);

            $this->activateRelatedRecord($locked);
        });
    }

    private function handleFailedPayment(array $payload): void
    {
        $entity = $payload['payload']['payment']['entity'] ?? null;

        if (!is_array($entity)) {
            return;
        }

        $razorpayPaymentId = (string) ($entity['id'] ?? '');
        $razorpayOrderId = (string) ($entity['order_id'] ?? '');

        if ($razorpayOrderId === '') {
            return;
        }

        DB::transaction(function () use (
            $razorpayOrderId,
            $razorpayPaymentId
        ) {
            $payment = Payment::query()
                ->where('razorpay_order_id', $razorpayOrderId)
                ->lockForUpdate()
                ->first();

            if (!$payment || $payment->payment_status === 'Paid') {
                return;
            }

            $payment->update([
                'razorpay_payment_id' =>
                    $razorpayPaymentId !== ''
                        ? $razorpayPaymentId
                        : $payment->razorpay_payment_id,
                'payment_status' => 'Failed',
            ]);
        });
    }

    private function activateRelatedRecord(Payment $payment): void
    {
        if (
            $payment->payment_type === 'subscription' &&
            $payment->reference_id
        ) {
            $updated = Subscription::query()
                ->whereKey($payment->reference_id)
                ->where('user_id', $payment->user_id)
                ->update([
                    'payment_status' => 'Paid',
                    'subscription_status' => 'Active',
                ]);

            if (!$updated) {
                throw new RuntimeException(
                    'Subscription could not be activated.'
                );
            }
        }

        if (
            $payment->payment_type === 'custom_order' &&
            $payment->reference_id
        ) {
            $updated = CustomOrder::query()
                ->whereKey($payment->reference_id)
                ->where('user_id', $payment->user_id)
                ->update([
                    'payment_status' => 'Paid',
                    'order_status' => 'Order Placed',
                ]);

            if (!$updated) {
                throw new RuntimeException(
                    'Custom order could not be activated.'
                );
            }
        }
    }
}
