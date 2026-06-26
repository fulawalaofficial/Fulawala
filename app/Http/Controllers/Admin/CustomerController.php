<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use App\Models\EventBooking;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));
        $status = $request->get('status', '');

        $query = User::query()
            ->where('role', 'customer')
            ->select('users.*')
            ->selectSub(function ($q) {
                $q->from('custom_orders')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('custom_orders.user_id', 'users.id');
            }, 'custom_orders_count')
            ->selectSub(function ($q) {
                $q->from('custom_orders')
                    ->selectRaw('COALESCE(SUM(total_amount), 0)')
                    ->whereColumn('custom_orders.user_id', 'users.id');
            }, 'custom_orders_total')
            ->selectSub(function ($q) {
                $q->from('subscriptions')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('subscriptions.user_id', 'users.id');
            }, 'subscriptions_count')
            ->selectSub(function ($q) {
                $q->from('event_bookings')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('event_bookings.user_id', 'users.id');
            }, 'event_bookings_count');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }

                $q->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $customers = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => User::where('role', 'customer')->count(),
            'active' => User::where('role', 'customer')->where('status', 'Active')->count(),
            'inactive' => User::where('role', 'customer')->where('status', 'Inactive')->count(),
            'orders' => CustomOrder::count(),
            'order_revenue' => CustomOrder::sum('total_amount'),
            'subscriptions' => class_exists(Subscription::class) ? Subscription::count() : 0,
            'event_bookings' => class_exists(EventBooking::class) ? EventBooking::count() : 0,
        ];

        $statuses = collect([
            'Active',
            'Inactive',
        ])->merge(
            User::where('role', 'customer')
                ->whereNotNull('status')
                ->where('status', '!=', '')
                ->distinct()
                ->pluck('status')
        )->unique()->values();

        $filters = [
            'search' => $search,
            'status' => $status,
        ];

        return view('admin.customers.index', compact(
            'customers',
            'stats',
            'statuses',
            'filters'
        ));
    }

    public function updateStatus(Request $request, User $customer)
    {
        abort_if($customer->role !== 'customer', 404);

        $data = $request->validate([
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        $customer->update($data);

        return back()->with('success', 'Customer status updated successfully.');
    }
}