<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Subscription;
use App\Models\SubscriptionDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SubscriptionDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));
        $status = $request->get('status', '');
        $deliveryBoyId = $request->get('delivery_boy_id', '');
        $dateFrom = $request->get('date_from', '');
        $dateTo = $request->get('date_to', '');

        $query = SubscriptionDelivery::with([
            'subscription.packet',
            'subscription.user',
            'deliveryBoy',
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }

                $q->orWhere('fixed_delivery_time', 'like', "%{$search}%")
                    ->orWhere('delivery_status', 'like', "%{$search}%")
                    ->orWhere('failed_reason', 'like', "%{$search}%")
                    ->orWhereHas('subscription.packet', function ($packetQuery) use ($search) {
                        $packetQuery->where('packet_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('subscription.user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    })
                    ->orWhereHas('deliveryBoy', function ($staffQuery) use ($search) {
                        $staffQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($status !== '') {
            $query->where('delivery_status', $status);
        }

        if ($deliveryBoyId !== '') {
            $query->where('delivery_boy_id', $deliveryBoyId);
        }

        if ($dateFrom !== '') {
            $query->whereDate('delivery_date', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('delivery_date', '<=', $dateTo);
        }

        $deliveries = $query->latest()->paginate(20)->withQueryString();

        $today = Carbon::today();

        $stats = [
            'total' => SubscriptionDelivery::count(),
            'today' => SubscriptionDelivery::whereDate('delivery_date', $today)->count(),
            'pending' => SubscriptionDelivery::whereRaw('LOWER(delivery_status) LIKE ?', ['%pending%'])->count(),
            'delivered' => SubscriptionDelivery::whereRaw('LOWER(delivery_status) LIKE ?', ['%delivered%'])->count(),
            'failed' => SubscriptionDelivery::whereRaw('LOWER(delivery_status) LIKE ?', ['%failed%'])->count(),
        ];

        $statusOptions = collect([
            'Pending',
            'Assigned',
            'Out for Delivery',
            'Delivered',
            'Failed',
            'Cancelled',
        ])->merge(
            SubscriptionDelivery::whereNotNull('delivery_status')
                ->where('delivery_status', '!=', '')
                ->distinct()
                ->pluck('delivery_status')
        )->unique()->values();

        $deliveryBoys = Staff::query()
            ->orderBy('name')
            ->get();

        $filters = [
            'search' => $search,
            'status' => $status,
            'delivery_boy_id' => $deliveryBoyId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        return view('admin.daily-deliveries.index', compact(
            'deliveries',
            'stats',
            'statusOptions',
            'deliveryBoys',
            'filters'
        ));
    }

    public function updateStatus(Request $request, SubscriptionDelivery $delivery)
    {
        $data = $request->validate([
            'delivery_status' => ['required', 'string', 'max:50'],
            'delivery_boy_id' => ['nullable', 'exists:staff,id'],
            'failed_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['delivery_status'] !== 'Failed') {
            $data['failed_reason'] = null;
        }

        $delivery->update($data);

        return back()->with('success', 'Delivery updated successfully.');
    }

    public function generateToday()
    {
        $count = Subscription::generateTodayDeliveries();

        return back()->with('success', "{$count} deliveries generated for today.");
    }
}