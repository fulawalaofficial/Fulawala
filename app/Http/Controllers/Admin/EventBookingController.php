<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventBooking;
use App\Models\EventMaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventBookingController extends Controller
{
    /**
     * Keep the admin workflow consistent and predictable.
     */
    private const BOOKING_STATUSES = [
        'Request Submitted',
        'Pending',
        'Quotation Sent',
        'Accepted',
        'Confirmed',
        'In Progress',
        'Completed',
        'Cancelled',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $status = trim((string) $request->get('status', ''));
        $eventType = trim((string) $request->get('event_type', ''));
        $dateFrom = trim((string) $request->get('date_from', ''));
        $dateTo = trim((string) $request->get('date_to', ''));

        $query = EventBooking::query()
            ->with([
                'user',
                'quotation',
                'eventMaster',
                'eventPlan',
                'address',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }

                $q->orWhere('event_type', 'like', "%{$search}%")
                    ->orWhere('venue_address', 'like', "%{$search}%")
                    ->orWhere('requirement', 'like', "%{$search}%")
                    ->orWhere('special_instructions', 'like', "%{$search}%")
                    ->orWhere('booking_status', 'like', "%{$search}%")
                    ->orWhereHas('eventMaster', function ($eventQuery) use ($search): void {
                        $eventQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('short_description', 'like', "%{$search}%");
                    })
                    ->orWhereHas('eventPlan', function ($planQuery) use ($search): void {
                        $planQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('address', function ($addressQuery) use ($search): void {
                        $addressQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('number', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%")
                            ->orWhere('pincode', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($search): void {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */
        if ($status !== '') {
            $query->where('booking_status', $status);
        }

        if ($eventType !== '') {
            $query->where(function ($q) use ($eventType): void {
                $q->where('event_type', $eventType)
                    ->orWhereHas(
                        'eventMaster',
                        fn ($master) => $master->where('name', $eventType)
                    );
            });
        }

        if ($dateFrom !== '') {
            $query->whereDate('event_date', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('event_date', '<=', $dateTo);
        }

        /*
        |--------------------------------------------------------------------------
        | Results
        |--------------------------------------------------------------------------
        */
        $bookings = $query
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Dashboard Stats
        |--------------------------------------------------------------------------
        */
        $today = Carbon::today();

        $stats = [
            'total' => EventBooking::count(),

            'new_requests' => EventBooking::query()
                ->whereIn('booking_status', ['Request Submitted', 'Pending'])
                ->count(),

            'today' => EventBooking::query()
                ->whereDate('event_date', $today)
                ->count(),

            'upcoming' => EventBooking::query()
                ->whereDate('event_date', '>', $today)
                ->whereNotIn('booking_status', ['Completed', 'Cancelled'])
                ->count(),

            'confirmed' => EventBooking::query()
                ->whereIn('booking_status', ['Accepted', 'Confirmed'])
                ->count(),

            'completed' => EventBooking::query()
                ->where('booking_status', 'Completed')
                ->count(),

            'total_budget' => (float) EventBooking::sum('budget'),
        ];

        /*
        |--------------------------------------------------------------------------
        | Filter Options
        |--------------------------------------------------------------------------
        */
        $statusOptions = collect(self::BOOKING_STATUSES)
            ->merge(
                EventBooking::query()
                    ->whereNotNull('booking_status')
                    ->where('booking_status', '!=', '')
                    ->distinct()
                    ->pluck('booking_status')
            )
            ->filter()
            ->unique()
            ->values();

        $eventTypeOptions = EventMaster::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->merge(
                EventBooking::query()
                    ->whereNotNull('event_type')
                    ->where('event_type', '!=', '')
                    ->distinct()
                    ->orderBy('event_type')
                    ->pluck('event_type')
            )
            ->filter()
            ->unique()
            ->values();

        $filters = [
            'search' => $search,
            'status' => $status,
            'event_type' => $eventType,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $activeFilterCount = collect($filters)
            ->filter(fn ($value) => filled($value))
            ->count();

        return view('admin.event-bookings.index', compact(
            'bookings',
            'stats',
            'statusOptions',
            'eventTypeOptions',
            'filters',
            'activeFilterCount'
        ));
    }

    public function updateStatus(
        Request $request,
        EventBooking $eventBooking
    ): RedirectResponse {
        $data = $request->validate([
            'booking_status' => [
                'required',
                'string',
                'max:50',
                Rule::in(self::BOOKING_STATUSES),
            ],
        ]);

        $eventBooking->update($data);

        return back()->with(
            'success',
            "Booking #{$eventBooking->id} status changed to {$data['booking_status']}."
        );
    }
}
