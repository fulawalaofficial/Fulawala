<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventBooking;
use App\Models\Quotation;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));
        $status = $request->get('status', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo = $request->get('date_to', '');

        $query = Quotation::with(['booking.user']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (is_numeric($search)) {
                    $q->orWhere('id', $search)
                        ->orWhere('booking_id', $search);
                }

                $q->orWhere('quotation_status', 'like', "%{$search}%")
                    ->orWhere('decoration_details', 'like', "%{$search}%")
                    ->orWhere('terms', 'like', "%{$search}%")
                    ->orWhereHas('booking', function ($bookingQuery) use ($search) {
                        $bookingQuery->where('event_type', 'like', "%{$search}%")
                            ->orWhere('venue_address', 'like', "%{$search}%");
                    })
                    ->orWhereHas('booking.user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        if ($status !== '') {
            $query->where('quotation_status', $status);
        }

        if ($dateFrom !== '') {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $quotations = $query->latest()->paginate(20)->withQueryString();

        $bookings = EventBooking::with('user')
            ->doesntHave('quotation')
            ->latest()
            ->get();

        $stats = [
            'total' => Quotation::count(),
            'sent' => Quotation::whereRaw('LOWER(quotation_status) LIKE ?', ['%sent%'])->count(),
            'accepted' => Quotation::whereRaw('LOWER(quotation_status) LIKE ?', ['%accepted%'])->count(),
            'rejected' => Quotation::whereRaw('LOWER(quotation_status) LIKE ?', ['%rejected%'])->count(),
            'total_amount' => Quotation::sum('total_amount'),
            'advance_amount' => Quotation::sum('advance_amount'),
            'balance_amount' => Quotation::sum('balance_amount'),
        ];

        $statusOptions = collect([
            'Sent',
            'Accepted',
            'Rejected',
            'Paid',
            'Cancelled',
        ])->merge(
            Quotation::whereNotNull('quotation_status')
                ->where('quotation_status', '!=', '')
                ->distinct()
                ->pluck('quotation_status')
        )->unique()->values();

        $filters = [
            'search' => $search,
            'status' => $status,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        return view('admin.quotations.index', compact(
            'quotations',
            'bookings',
            'stats',
            'statusOptions',
            'filters'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => ['required', 'exists:event_bookings,id', 'unique:quotations,booking_id'],
            'decoration_details' => ['required', 'string'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'advance_amount' => ['required', 'numeric', 'min:0', 'lte:total_amount'],
            'terms' => ['nullable', 'string'],
        ]);

        $data['balance_amount'] = $data['total_amount'] - $data['advance_amount'];
        $data['quotation_status'] = 'Sent';

        $quote = Quotation::create($data);

        $quote->booking?->update([
            'booking_status' => 'Quotation Sent',
        ]);

        return back()->with('success', 'Quotation sent successfully.');
    }

    public function update(Request $request, Quotation $quotation)
    {
        $data = $request->validate([
            'decoration_details' => ['required', 'string'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'advance_amount' => ['required', 'numeric', 'min:0', 'lte:total_amount'],
            'terms' => ['nullable', 'string'],
            'quotation_status' => ['required', 'string', 'max:50'],
        ]);

        $data['balance_amount'] = $data['total_amount'] - $data['advance_amount'];

        $quotation->update($data);

        if ($quotation->booking) {
            if ($data['quotation_status'] === 'Accepted') {
                $quotation->booking->update(['booking_status' => 'Accepted']);
            } elseif ($data['quotation_status'] === 'Rejected') {
                $quotation->booking->update(['booking_status' => 'Rejected']);
            } elseif ($data['quotation_status'] === 'Sent') {
                $quotation->booking->update(['booking_status' => 'Quotation Sent']);
            }
        }

        return back()->with('success', 'Quotation updated successfully.');
    }
}