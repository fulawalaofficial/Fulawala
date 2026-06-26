<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use App\Models\EventBooking;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        $paidPayments = Payment::query()
            ->where('payment_status', 'Paid')
            ->whereBetween('created_at', [$from, $to]);

        $totalRevenue = (clone $paidPayments)->sum('amount');
        $subscriptionRevenue = (clone $paidPayments)->where('payment_type', 'subscription')->sum('amount');
        $customOrderRevenue = (clone $paidPayments)->where('payment_type', 'custom_order')->sum('amount');
        $eventRevenue = (clone $paidPayments)->whereIn('payment_type', ['event_booking', 'quotation', 'event'])->sum('amount');

        $ordersCount = CustomOrder::whereBetween('created_at', [$from, $to])->count();
        $subscriptionsCount = Subscription::whereBetween('created_at', [$from, $to])->count();
        $customersCount = User::where('role', 'customer')->count();
        $eventBookingsCount = EventBooking::whereBetween('created_at', [$from, $to])->count();

        $pendingOrders = CustomOrder::whereRaw('LOWER(order_status) LIKE ?', ['%pending%'])->count();
        $activeSubscriptions = Subscription::whereRaw('LOWER(subscription_status) LIKE ?', ['%active%'])->count();

        $averageOrderValue = $ordersCount > 0
            ? CustomOrder::whereBetween('created_at', [$from, $to])->sum('total_amount') / $ordersCount
            : 0;

        $lastSixMonths = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $lastSixMonths->push([
                'key' => $month->format('Y-m'),
                'label' => $month->format('M Y'),
                'start' => $month->copy()->startOfMonth(),
                'end' => $month->copy()->endOfMonth(),
            ]);
        }

        $monthlyRevenue = $lastSixMonths->map(function ($month) {
            return [
                'label' => $month['label'],
                'value' => (float) Payment::where('payment_status', 'Paid')
                    ->whereBetween('created_at', [$month['start'], $month['end']])
                    ->sum('amount'),
            ];
        });

        $orderStatusData = CustomOrder::selectRaw('order_status, COUNT(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        $subscriptionStatusData = Subscription::selectRaw('subscription_status, COUNT(*) as total')
            ->groupBy('subscription_status')
            ->pluck('total', 'subscription_status');

        $recentPayments = Payment::latest()
            ->take(8)
            ->get();

        $report = [
            'total_revenue' => $totalRevenue,
            'subscription_revenue' => $subscriptionRevenue,
            'custom_order_revenue' => $customOrderRevenue,
            'event_revenue' => $eventRevenue,
            'orders' => $ordersCount,
            'subscriptions' => $subscriptionsCount,
            'customers' => $customersCount,
            'event_bookings' => $eventBookingsCount,
            'pending_orders' => $pendingOrders,
            'active_subscriptions' => $activeSubscriptions,
            'average_order_value' => $averageOrderValue,
        ];

        $chartData = [
            'monthlyLabels' => $monthlyRevenue->pluck('label')->values(),
            'monthlyValues' => $monthlyRevenue->pluck('value')->values(),

            'revenueLabels' => ['Subscriptions', 'Custom Orders', 'Events'],
            'revenueValues' => [
                (float) $subscriptionRevenue,
                (float) $customOrderRevenue,
                (float) $eventRevenue,
            ],

            'orderStatusLabels' => $orderStatusData->keys()->map(fn ($item) => $item ?: 'Unknown')->values(),
            'orderStatusValues' => $orderStatusData->values(),

            'subscriptionStatusLabels' => $subscriptionStatusData->keys()->map(fn ($item) => $item ?: 'Unknown')->values(),
            'subscriptionStatusValues' => $subscriptionStatusData->values(),
        ];

        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        return view('admin.reports.index', compact(
            'report',
            'chartData',
            'recentPayments',
            'filters'
        ));
    }
}