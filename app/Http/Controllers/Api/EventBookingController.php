<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventBooking;
use App\Models\Payment;
use App\Models\Quotation;
use Illuminate\Http\Request;

class EventBookingController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'event_type' => ['required','string','max:100'],
            'event_date' => ['required','date'],
            'event_time' => ['required','string','max:50'],
            'venue_address' => ['required','string'],
            'budget' => ['nullable','numeric'],
            'requirement' => ['required','string'],
            'reference_image' => ['nullable','string'],
            'special_instructions' => ['nullable','string'],
        ]);

        $booking = EventBooking::create([
            ...$data,
            'user_id' => $request->user()->id,
            'booking_status' => 'Request Submitted',
        ]);

        return response()->json($booking, 201);
    }

    public function myQuotations(Request $request)
    {
        $quotations = Quotation::with('booking')
            ->whereHas('booking', fn($q) => $q->where('user_id', $request->user()->id))
            ->latest()
            ->get();

        return response()->json($quotations);
    }

    public function acceptQuotation(Request $request, Quotation $quotation)
    {
        abort_unless($quotation->booking->user_id === $request->user()->id, 403);

        $quotation->update(['quotation_status' => 'Accepted']);
        $quotation->booking->update(['booking_status' => 'Confirmed']);

        Payment::create([
            'user_id' => $request->user()->id,
            'payment_type' => 'event_advance',
            'reference_id' => $quotation->booking_id,
            'amount' => $quotation->advance_amount,
            'razorpay_order_id' => 'mock_order_'.uniqid(),
            'razorpay_payment_id' => 'mock_payment_'.uniqid(),
            'payment_status' => 'Paid',
        ]);

        return response()->json($quotation->load('booking'));
    }
}
