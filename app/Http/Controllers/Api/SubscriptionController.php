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
use Throwable;

class SubscriptionController extends Controller
{
    public function store(Request $request, RazorpayService $razorpay)
    {
        $data = $request->validate([
            'packet_id' => ['required', 'exists:pooja_packets,id'],
            'duration' => ['required', 'integer', 'in:1,3,6,12'],
            'start_date' => ['required', 'date'],
            'address' => ['nullable', 'string'],
            'address_id' => ['nullable', 'exists:addresses,id'],
        ]);

        try {
            return DB::transaction(function () use ($request, $data, $razorpay) {
                $packet = PoojaPacket::findOrFail($data['packet_id']);

                $start = Carbon::parse($data['start_date']);
                $end = $start->copy()->addMonths((int) $data['duration'])->subDay();
                $amount = (float) $packet->monthly_price * (int) $data['duration'];

                $addressId = $data['address_id'] ?? null;

                if ($addressId) {
                    $address = Address::where('id', $addressId)
                        ->where('user_id', $request->user()->id)
                        ->firstOrFail();

                    $addressId = $address->id;
                } else {
                    $address = Address::create([
                        'user_id' => $request->user()->id,
                        'address' => $data['address'] ?? 'Default address',
                        'city' => '',
                        'state' => '',
                        'pincode' => '',
                        'is_default' => false,
                    ]);

                    $addressId = $address->id;
                }

                $subscription = Subscription::create([
                    'user_id' => $request->user()->id,
                    'packet_id' => $packet->id,
                    'address_id' => $addressId,
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'duration' => $data['duration'],
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
                'error' => config('app.debug') ? $e->getMessage() : null,
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