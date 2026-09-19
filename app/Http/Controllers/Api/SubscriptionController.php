<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Payment;
use App\Models\PoojaPacket;
use App\Models\Subscription;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class SubscriptionController extends Controller
{
    public function store(Request $request, RazorpayService $razorpay)
    {
        $data = $request->validate([
            'packet_id' => [
                'required',
                'integer',
                'exists:pooja_packets,id',
            ],
            'duration' => [
                'required',
                'integer',
                Rule::in([1, 3, 6, 12]),
            ],
            'start_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'address_id' => [
                'required',
                'integer',
                'exists:addresses,id',
            ],
        ]);

        $user = $request->user();

        $addressOwnedByUser = Address::query()
            ->whereKey($data['address_id'])
            ->where('user_id', $user->id)
            ->exists();

        if (!$addressOwnedByUser) {
            throw ValidationException::withMessages([
                'address_id' => [
                    'The selected delivery address does not belong to this account.',
                ],
            ]);
        }

        $packet = PoojaPacket::query()->findOrFail($data['packet_id']);

        if ((float) $packet->monthly_price <= 0) {
            throw ValidationException::withMessages([
                'packet_id' => [
                    'The selected packet does not have a valid monthly price.',
                ],
            ]);
        }

        $duration = (int) $data['duration'];
        $start = Carbon::createFromFormat('Y-m-d', $data['start_date'])->startOfDay();
        $end = $start->copy()->addMonthsNoOverflow($duration)->subDay();
        $amount = round((float) $packet->monthly_price * $duration, 2);

        try {
            return DB::transaction(function () use (
                $user,
                $packet,
                $duration,
                $data,
                $start,
                $end,
                $amount,
                $razorpay
            ) {
                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'packet_id' => $packet->id,
                    'address_id' => $data['address_id'],
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'duration' => $duration,
                    'amount' => $amount,
                    'payment_status' => 'Pending',
                    'subscription_status' => 'Pending',
                ]);

                $receipt = 'sub_' .
                    $subscription->id . '_' .
                    now()->format('ymdHis');

                $order = $razorpay->createOrder(
                    $amount,
                    $receipt,
                    [
                        'user_id' => (string) $user->id,
                        'payment_type' => 'subscription',
                        'subscription_id' => (string) $subscription->id,
                        'address_id' => (string) $data['address_id'],
                    ]
                );

                $payment = Payment::create([
                    'user_id' => $user->id,
                    'payment_type' => 'subscription',
                    'reference_id' => $subscription->id,
                    'amount' => $amount,
                    'razorpay_order_id' => $order['id'],
                    'razorpay_payment_id' => null,
                    'razorpay_signature' => null,
                    'payment_status' => 'Pending',
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Subscription created. Complete Razorpay payment.',
                    'subscription' => $subscription->load(['packet', 'address']),
                    'payment' => [
                        'payment_id' => $payment->id,
                        'razorpay_order_id' => $order['id'],
                        'order_id' => $order['id'],

                        // Razorpay Checkout amount is ALWAYS paise.
                        'amount' => (int) $order['amount'],
                        'amount_in_paise' => (int) $order['amount'],
                        'amount_rupees' => $amount,

                        'currency' => $order['currency'],
                        'key_id' => $razorpay->getKeyId(),
                        'name' => config('app.name', 'Fulawala'),
                        'description' => 'Pooja packet subscription payment',
                        'prefill' => [
                            'name' => $user->name ?? '',
                            'email' => $user->email ?? '',
                            'contact' => $user->mobile ?? '',
                        ],
                        'notes' => [
                            'payment_type' => 'subscription',
                            'subscription_id' => (string) $subscription->id,
                            'payment_id' => (string) $payment->id,
                        ],
                    ],
                ], 201);
            });
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to create subscription payment order.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 422);
        }
    }

    public function mySubscriptions(Request $request)
    {
        return response()->json([
            'status' => true,
            'data' => $request->user()
                ->subscriptions()
                ->with(['packet', 'address'])
                ->latest()
                ->get(),
        ]);
    }
}
