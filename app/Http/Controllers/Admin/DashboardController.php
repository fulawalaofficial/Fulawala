<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use App\Models\EventBooking;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionDelivery;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $totalRevenue = Payment::where('payment_status', 'Paid')->sum('amount');

        $cards = [
            [
                'label' => 'Today Custom Orders',
                'value' => CustomOrder::whereDate('created_at', $today)->count(),
                'icon' => '🛒',
                'description' => 'New flower orders today',
                'color' => 'orange',
            ],
            [
                'label' => 'Active Subscriptions',
                'value' => Subscription::where('subscription_status', 'Active')->count(),
                'icon' => '📅',
                'description' => 'Running pooja packet plans',
                'color' => 'emerald',
            ],
            [
                'label' => 'Pending Events',
                'value' => EventBooking::whereIn('booking_status', ['Request Submitted', 'Under Review'])->count(),
                'icon' => '🎉',
                'description' => 'Events waiting for review',
                'color' => 'purple',
            ],
            [
                'label' => 'Today Deliveries',
                'value' => SubscriptionDelivery::whereDate('delivery_date', $today)->count(),
                'icon' => '🚚',
                'description' => 'Deliveries scheduled today',
                'color' => 'blue',
            ],
            [
                'label' => 'Revenue',
                'value' => '₹' . number_format($totalRevenue, 2),
                'icon' => '💰',
                'description' => 'Total paid payment amount',
                'color' => 'amber',
            ],
            [
                'label' => 'Customers',
                'value' => User::where('role', 'customer')->count(),
                'icon' => '👥',
                'description' => 'Registered customers',
                'color' => 'pink',
            ],
        ];

        $quickActions = [
            [
                'title' => 'Custom Orders',
                'description' => 'Manage flower orders',
                'icon' => '🌼',
                'url' => url('/admin/custom-orders'),
            ],
            [
                'title' => 'Subscriptions',
                'description' => 'Manage pooja packets',
                'icon' => '📦',
                'url' => url('/admin/subscriptions'),
            ],
            [
                'title' => 'Event Bookings',
                'description' => 'Review event requests',
                'icon' => '🎊',
                'url' => url('/admin/event-bookings'),
            ],
            [
                'title' => 'Payments',
                'description' => 'Check payment records',
                'icon' => '💳',
                'url' => url('/admin/payments'),
            ],
        ];

        $summary = [
            'totalOrders' => CustomOrder::count(),
            'totalEvents' => EventBooking::count(),
            'totalPayments' => Payment::count(),
            'paidPayments' => Payment::where('payment_status', 'Paid')->count(),
        ];

        return view('admin.dashboard', compact('cards', 'quickActions', 'summary'));
    }
}