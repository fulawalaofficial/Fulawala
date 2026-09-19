<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventBooking;
use App\Models\EventMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EventBookingController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));
        $status = $request->get('status', '');
        $eventType = $request->get('event_type', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo = $request->get('date_to', '');

        $query = EventBooking::with(['user', 'quotation', 'eventMaster']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }

                $q->orWhere('event_type', 'like', "%{$search}%")
                    ->orWhere('venue_address', 'like', "%{$search}%")
                    ->orWhere('requirement', 'like', "%{$search}%")
                    ->orWhere('special_instructions', 'like', "%{$search}%")
                    ->orWhere('booking_status', 'like', "%{$search}%")
                    ->orWhereHas('eventMaster', function ($eventQuery) use ($search) {
                        $eventQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        if ($status !== '') {
            $query->where('booking_status', $status);
        }

        if ($eventType !== '') {
            $query->where(function ($q) use ($eventType) {
                $q->where('event_type', $eventType)
                    ->orWhereHas('eventMaster', fn ($master) => $master->where('name', $eventType));
            });
        }

        if ($dateFrom !== '') {
            $query->whereDate('event_date', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('event_date', '<=', $dateTo);
        }

        $bookings = $query->latest()->paginate(20)->withQueryString();

        $today = Carbon::today();

        $stats = [
            'total' => EventBooking::count(),
            'today' => EventBooking::whereDate('event_date', $today)->count(),
            'pending' => EventBooking::whereRaw('LOWER(booking_status) LIKE ?', ['%pending%'])->count(),
            'confirmed' => EventBooking::whereRaw('LOWER(booking_status) LIKE ?', ['%confirmed%'])->count(),
            'completed' => EventBooking::whereRaw('LOWER(booking_status) LIKE ?', ['%completed%'])->count(),
            'total_budget' => EventBooking::sum('budget'),
        ];

        $statusOptions = collect([
            'Pending',
            'Confirmed',
            'Quotation Sent',
            'Accepted',
            'In Progress',
            'Completed',
            'Cancelled',
        ])->merge(
            EventBooking::whereNotNull('booking_status')
                ->where('booking_status', '!=', '')
                ->distinct()
                ->pluck('booking_status')
        )->unique()->values();

        // Master event names appear first, then any legacy event_type values already stored.
        $eventTypeOptions = EventMaster::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->merge(
                EventBooking::whereNotNull('event_type')
                    ->where('event_type', '!=', '')
                    ->distinct()
                    ->orderBy('event_type')
                    ->pluck('event_type')
            )
            ->unique()
            ->values();

        $filters = [
            'search' => $search,
            'status' => $status,
            'event_type' => $eventType,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        return view('admin.event-bookings.index', compact(
            'bookings',
            'stats',
            'statusOptions',
            'eventTypeOptions',
            'filters'
        ));
    }

    public function updateStatus(Request $request, EventBooking $eventBooking)
    {
        $data = $request->validate([
            'booking_status' => ['required', 'string', 'max:50'],
        ]);

        $eventBooking->update($data);

        return back()->with('success', 'Event booking status updated successfully.');
    }
}
