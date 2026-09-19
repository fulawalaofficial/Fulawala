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
     * Create event booking from Event Master + Event Plan + saved address.
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

        $userId = (int) $request->user()->id;

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
            ? (float) $data['budget']
            : (float) ($eventPlan?->price ?? $eventMaster->starting_price ?? 0);

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

                // Snapshots keep old/admin records readable if master data changes later.
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
                'eventMaster',
                'eventPlan',
                'address',
            ]),
        ], 201);
    }

    /**
     * Return user quotations with SERVER-COMPUTED payment state.
     *
     * The app never decides whether a payment is really paid.
     * Only verified Payment rows are used.
     */
    public function myQuotations(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $quotations = Quotation::query()
            ->with([
                'booking.eventMaster',
                'booking.eventPlan',
                'booking.address',
            ])
            ->whereHas(
                'booking',
                fn ($query) => $query->where('user_id', $userId)
            )
            ->latest()
            ->get();

        $data = $quotations
            ->map(fn (Quotation $quotation) => array_merge(
                $quotation->toArray(),
                $this->buildPaymentSummary($quotation, $userId)
            ))
            ->values();

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Shared payment summary used by quotation listing.
     */
    private function buildPaymentSummary(
        Quotation $quotation,
        int $userId
    ): array {
        $booking = $quotation->booking;

        if (!$booking) {
            return [
                'total_amount' => 0,
                'advance_required_amount' => 0,
                'advance_paid_amount' => 0,
                'balance_paid_amount' => 0,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'is_advance_paid' => false,
                'is_fully_paid' => false,
                'can_pay_advance' => false,
                'can_pay_balance' => false,
                'payment_status' => 'Pending',
            ];
        }

        $total = max(
            0,
            round(
                (float) (
                    $quotation->total_amount
                    ?? $quotation->amount
                    ?? 0
                ),
                2
            )
        );

        $advanceRequired = min(
            $total,
            max(
                0,
                round(
                    (float) ($quotation->advance_amount ?? 0),
                    2
                )
            )
        );

        $payments = Payment::query()
            ->where('user_id', $userId)
            ->where('reference_id', $booking->id)
            ->whereIn('payment_type', [
                'event_advance',
                'event_balance',
                'event_full',
            ])
            ->where('payment_status', 'Paid')
            ->get();

        $advancePaid = (float) $payments
            ->where('payment_type', 'event_advance')
            ->sum('amount');

        $balancePaid = (float) $payments
            ->whereIn('payment_type', ['event_balance', 'event_full'])
            ->sum('amount');

        $paidAmount = min(
            $total,
            max(0, $advancePaid + $balancePaid)
        );

        $remainingAmount = max(
            0,
            round($total - $paidAmount, 2)
        );

        $isAdvancePaid = $advanceRequired <= 0
            ? true
            : ($advancePaid + 0.01 >= $advanceRequired);

        $isFullyPaid = $total > 0
            && $remainingAmount <= 0.01;

        $quotationStatus = strtolower(
            trim((string) $quotation->quotation_status)
        );

        $canStartPayment = in_array(
            $quotationStatus,
            [
                'sent',
                'quotation sent',
                'accepted',
                'approved',
            ],
            true
        );

        return [
            'total_amount' => $total,
            'advance_required_amount' => $advanceRequired,
            'advance_paid_amount' => round($advancePaid, 2),
            'balance_paid_amount' => round($balancePaid, 2),
            'paid_amount' => round($paidAmount, 2),
            'remaining_amount' => $remainingAmount,

            'is_advance_paid' => $isAdvancePaid,
            'is_fully_paid' => $isFullyPaid,

            'can_pay_advance' => $canStartPayment
                && !$isAdvancePaid
                && $advanceRequired > 0,

            'can_pay_balance' => $canStartPayment
                && $isAdvancePaid
                && !$isFullyPaid
                && $remainingAmount > 0,

            'payment_status' => $isFullyPaid
                ? 'Fully Paid'
                : ($isAdvancePaid && $paidAmount > 0
                    ? 'Advance Paid'
                    : 'Pending'),
        ];
    }
}
