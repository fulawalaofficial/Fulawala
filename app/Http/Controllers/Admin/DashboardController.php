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
        $cards = [
            'Today Custom Orders' => CustomOrder::whereDate('created_at', Carbon::today())->count(),
            'Active Subscriptions' => Subscription::where('subscription_status', 'Active')->count(),
            'Pending Events' => EventBooking::whereIn('booking_status', ['Request Submitted','Under Review'])->count(),
            'Today Deliveries' => SubscriptionDelivery::whereDate('delivery_date', Carbon::today())->count(),
            'Revenue' => '₹'.number_format(Payment::where('payment_status', 'Paid')->sum('amount'), 2),
            'Customers' => User::where('role', 'customer')->count(),
        ];

        return view('admin.dashboard', compact('cards'));
    }
}
