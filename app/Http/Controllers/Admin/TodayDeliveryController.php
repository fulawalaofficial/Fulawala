<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\CustomOrder;
use App\Models\SubscriptionDelivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TodayDeliveryController extends Controller
{
    /**
     * Combined delivery operations page.
     *
     * It shows custom flower orders and subscription deliveries separately,
     * while keeping both lists on one operational screen.
     */
    public function index(Request $request): View
    {
        $selectedDate = $this->resolveDate($request->input('date'));
        $search = trim((string) $request->input('search', ''));
        $customStatus = trim((string) $request->input('custom_status', ''));
        $subscriptionStatus = trim((string) $request->input('subscription_status', ''));

        $customBaseQuery = CustomOrder::query()
            ->with([
                'user:id,name,mobile,email,profile_photo',
                'address',
                'items.flower:id,name,image,unit',
            ])
            ->whereDate('delivery_date', $selectedDate);

        $subscriptionBaseQuery = SubscriptionDelivery::query()
            ->with([
                'subscription.user:id,name,mobile,email,profile_photo',
                'subscription.address',
                'subscription.packet:id,packet_name,image,daily_quantity',
                'deliveryBoy:id,name,phone,status',
            ])
            ->whereDate('delivery_date', $selectedDate);

        $stats = [
            'custom_total' => (clone $customBaseQuery)->count(),
            'custom_delivered' => (clone $customBaseQuery)
                ->where('order_status', 'Delivered')
                ->count(),
            'subscription_total' => (clone $subscriptionBaseQuery)->count(),
            'subscription_delivered' => (clone $subscriptionBaseQuery)
                ->where('delivery_status', 'Delivered')
                ->count(),
        ];

        $customOrdersQuery = clone $customBaseQuery;

        if ($customStatus !== '') {
            $customOrdersQuery->where('order_status', $customStatus);
        }

        if ($search !== '') {
            $customOrdersQuery->where(function ($query) use ($search): void {
                $query
                    ->where('id', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search): void {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('address', function ($addressQuery) use ($search): void {
                        $addressQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('number', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%")
                            ->orWhere('pincode', 'like', "%{$search}%");
                    });
            });
        }

        $subscriptionDeliveriesQuery = clone $subscriptionBaseQuery;

        if ($subscriptionStatus !== '') {
            $subscriptionDeliveriesQuery->where('delivery_status', $subscriptionStatus);
        }

        if ($search !== '') {
            $subscriptionDeliveriesQuery->where(function ($query) use ($search): void {
                $query
                    ->where('id', $search)
                    ->orWhereHas('subscription.user', function ($userQuery) use ($search): void {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('subscription.address', function ($addressQuery) use ($search): void {
                        $addressQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('number', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%")
                            ->orWhere('pincode', 'like', "%{$search}%");
                    })
                    ->orWhereHas('subscription.packet', function ($packetQuery) use ($search): void {
                        $packetQuery->where('packet_name', 'like', "%{$search}%");
                    });
            });
        }

        $customOrders = $customOrdersQuery
            ->orderByRaw("CASE order_status WHEN 'Out for Delivery' THEN 1 WHEN 'Ready for Delivery' THEN 2 WHEN 'Preparing' THEN 3 WHEN 'Confirmed' THEN 4 WHEN 'Order Placed' THEN 5 WHEN 'Delivered' THEN 6 WHEN 'Cancelled' THEN 7 ELSE 8 END")
            ->orderBy('delivery_slot')
            ->orderBy('id')
            ->paginate(15, ['*'], 'custom_page')
            ->withQueryString();

        $subscriptionDeliveries = $subscriptionDeliveriesQuery
            ->orderByRaw("CASE delivery_status WHEN 'Out for Delivery' THEN 1 WHEN 'Pending' THEN 2 WHEN 'Failed' THEN 3 WHEN 'Delivered' THEN 4 WHEN 'Cancelled' THEN 5 ELSE 6 END")
            ->orderBy('fixed_delivery_time')
            ->orderBy('id')
            ->paginate(15, ['*'], 'subscription_page')
            ->withQueryString();

        $customStatuses = CustomOrder::ORDER_STATUSES;
        $subscriptionStatuses = [
            'Pending',
            'Out for Delivery',
            'Delivered',
            'Failed',
            'Cancelled',
        ];

        return view('admin.today-delivery.index', compact(
            'selectedDate',
            'search',
            'customStatus',
            'subscriptionStatus',
            'customOrders',
            'subscriptionDeliveries',
            'customStatuses',
            'subscriptionStatuses',
            'stats'
        ));
    }

    /**
     * Save browser-geocoded coordinates so the next map load is immediate.
     */
    public function saveCoordinates(Request $request, Address $address): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $address->update([
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Customer location saved.',
            'latitude' => (float) $address->latitude,
            'longitude' => (float) $address->longitude,
        ]);
    }

    private function resolveDate(mixed $date): string
    {
        try {
            return $date
                ? Carbon::parse((string) $date)->toDateString()
                : today()->toDateString();
        } catch (\Throwable) {
            return today()->toDateString();
        }
    }
}
