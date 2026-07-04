<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PoojaPacket;
use App\Models\Subscription;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class SubscriptionController extends Controller
{
    public function store(Request $request, RazorpayService $razorpay)
    {
        try {
            $packetId = $request->packet_id;
            $duration = (int) $request->duration;
            $startDate = $request->start_date;
            $addressId = $request->address_id;

            $packet = PoojaPacket::findOrFail($packetId);

            if (!$packet->monthly_price || $packet->monthly_price <= 0) {
                return response()->json([
                    'message' => 'Invalid packet monthly price.',
                ], 422);
            }

            $start = Carbon::parse($startDate);
            $end = $start->copy()->addMonths($duration)->subDay();

            $amount = (float) $packet->monthly_price * $duration;

            return DB::transaction(function () use (
                $request,
                $packet,
                $duration,
                $addressId,
                $start,
                $end,
                $amount,
                $razorpay
            ) {
                $subscription = Subscription::create([
                    'user_id' => $request->user()->id,
                    'packet_id' => $packet->id,
                    'address_id' => $addressId,
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'duration' => $duration,
                    'amount' => $amount,
                    'payment_status' => 'Pending',
                    'subscription_status' => 'Pending',
                ]);

                $receipt = 'sub_' . $subscription->id . '_' . now()->format('ymdHis');

                $order = $razorpay->createOrder(
                    $amount,
                    $receipt,
                    [
                        'user_id' => (string) $request->user()->id,
                        'payment_type' => 'subscription',
                        'subscription_id' => (string) $subscription->id,
                        'address_id' => (string) $addressId,
                    ]
                );

                $payment = Payment::create([
                    'user_id' => $request->user()->id,
                    'payment_type' => 'subscription',
                    'reference_id' => $subscription->id,
                    'amount' => $amount,
                    'razorpay_order_id' => $order['id'],
                    'payment_status' => 'Pending',
                ]);

                return response()->json([
                    'message' => 'Subscription created. Complete Razorpay payment.',
                    'subscription' => $subscription->load(['packet', 'address']),
                    'payment' => [
                        'payment_id' => $payment->id,
                        'razorpay_order_id' => $order['id'],
                        'order_id' => $order['id'],
                        'amount' => $order['amount'],
                        'amount_rupees' => $amount,
                        'currency' => $order['currency'],
                        'key_id' => config('services.razorpay.key_id'),
                        'name' => config('app.name', 'Fulawala'),
                        'description' => 'Pooja packet subscription payment',
                    ],
                ], 201);
            });
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Unable to create subscription payment order.',
                'error' => $e->getMessage(),
                'file' => config('app.debug') ? $e->getFile() : null,
                'line' => config('app.debug') ? $e->getLine() : null,
            ], 422);
        }
    }

    public function mySubscriptions(Request $request)
    {
        return response()->json(
            $request->user()
                ->subscriptions()
                ->with(['packet', 'address'])
                ->latest()
                ->get()
        );
    }
}