<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Payment;
use App\Models\PoojaPacket;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'packet_id' => ['required','exists:pooja_packets,id'],
            'duration' => ['required','integer','in:1,3,6,12'],
            'start_date' => ['required','date'],
            'address' => ['nullable','string'],
            'address_id' => ['nullable','exists:addresses,id'],
        ]);

        $packet = PoojaPacket::findOrFail($data['packet_id']);
        $start = Carbon::parse($data['start_date']);
        $end = $start->copy()->addMonths((int) $data['duration'])->subDay();
        $amount = (float) $packet->monthly_price * (int) $data['duration'];

        $addressId = $data['address_id'] ?? null;
        if (!$addressId) {
            $address = Address::create([
                'user_id' => $request->user()->id,
                'address' => $data['address'] ?? 'Default address',
                'city' => '', 'state' => '', 'pincode' => '',
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
            'payment_status' => 'Paid',
            'subscription_status' => 'Active',
        ]);

        Payment::create([
            'user_id' => $request->user()->id,
            'payment_type' => 'subscription',
            'reference_id' => $subscription->id,
            'amount' => $amount,
            'razorpay_order_id' => 'mock_order_'.uniqid(),
            'razorpay_payment_id' => 'mock_payment_'.uniqid(),
            'payment_status' => 'Paid',
        ]);

        return response()->json($subscription->load(['packet','address']), 201);
    }

    public function mySubscriptions(Request $request)
    {
        return response()->json($request->user()->subscriptions()->with(['packet','address'])->latest()->get());
    }
}
