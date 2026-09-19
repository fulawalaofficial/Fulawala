<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\EventBooking;
use App\Models\EventMaster;
use App\Models\EventPlan;
use App\Models\Payment;
use App\Models\Quotation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventBookingController extends Controller
{
    /**
     * Create an event booking from a selected Event Master + saved address.
     *
     * event_type and venue_address are stored as snapshots so the admin
     * booking page keeps working even if the master event/address changes later.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event_master_id' => ['required', 'integer', 'exists:event_masters,id'],
            'event_plan_id' => ['nullable', 'integer', 'exists:event_plans,id'],
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'event_time' => ['required', 'date_format:H:i'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'requirement' => ['required', 'string', 'max:5000'],
            'reference_image' => ['nullable', 'string', 'max:1000'],
            'special_instructions' => ['nullable', 'string', 'max:5000'],
        ]);

        $userId = $request->user()->id;

        $eventMaster = EventMaster::query()
            ->whereKey($data['event_master_id'])
            ->where('status', 'active')
            ->firstOrFail();

        $address = Address::query()
            ->whereKey($data['address_id'])
            ->where('user_id', $userId)
            ->first();

        if (!$address) {
            throw ValidationException::withMessages([
                'address_id' => ['The selected address does not belong to your account.'],
            ]);
        }

        $eventPlan = null;

        if (!empty($data['event_plan_id'])) {
            $eventPlan = EventPlan::query()
                ->whereKey($data['event_plan_id'])
                ->where('event_master_id', $eventMaster->id)
                ->where('status', 'active')
                ->first();

            if (!$eventPlan) {
                throw ValidationException::withMessages([
                    'event_plan_id' => ['The selected plan is not available for this event.'],
                ]);
            }
        }

        $budget = array_key_exists('budget', $data) && $data['budget'] !== null
            ? $data['budget']
            : ($eventPlan?->price ?? $eventMaster->starting_price);

        $booking = DB::transaction(function () use (
            $data,
            $userId,
            $eventMaster,
            $eventPlan,
            $address,
            $budget
        ) {
            return EventBooking::create([
                'user_id' => $userId,
                'event_master_id' => $eventMaster->id,
                'event_plan_id' => $eventPlan?->id,
                'address_id' => $address->id,

                // Keep current admin Event Booking page compatible.
                'event_type' => $eventMaster->name,
                'event_date' => $data['event_date'],
                'event_time' => $data['event_time'],
                'venue_address' => $address->full_address,
                'budget' => $budget,
                'requirement' => $data['requirement'],
                'reference_image' => $data['reference_image'] ?? null,
                'special_instructions' => $data['special_instructions'] ?? null,
                'booking_status' => 'Request Submitted',
            ]);
        });

        return response()->json([
            'message' => 'Event booking submitted successfully.',
            'data' => $booking->load([
                'eventMaster.photos',
                'eventMaster.videos',
                'eventPlan',
                'address',
            ]),
        ], 201);
    }

    public function myQuotations(Request $request): JsonResponse
    {
        $quotations = Quotation::with([
                'booking.eventMaster',
                'booking.eventPlan',
                'booking.address',
            ])
            ->whereHas('booking', fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest()
            ->get();

        return response()->json($quotations);
    }

    /**
     * Existing quotation-accept flow kept compatible with your current project.
     * If you already use a live Razorpay order/verification flow, call that flow
     * instead of creating the mock payment record below.
     */
    public function acceptQuotation(Request $request, Quotation $quotation): JsonResponse
    {
        $quotation->loadMissing('booking');

        abort_unless(
            $quotation->booking
            && $quotation->booking->user_id === $request->user()->id,
            403
        );

        DB::transaction(function () use ($request, $quotation): void {
            $quotation->update([
                'quotation_status' => 'Accepted',
            ]);

            $quotation->booking->update([
                'booking_status' => 'Confirmed',
            ]);

            Payment::firstOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'payment_type' => 'event_advance',
                    'reference_id' => $quotation->booking_id,
                ],
                [
                    'amount' => $quotation->advance_amount,
                    'razorpay_order_id' => 'mock_order_' . uniqid(),
                    'razorpay_payment_id' => 'mock_payment_' . uniqid(),
                    'payment_status' => 'Paid',
                ]
            );
        });

        return response()->json(
            $quotation->fresh()->load('booking')
        );
    }
}
