<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use App\Models\Payment;
use App\Models\Subscription;

class ReportController extends Controller
{
    public function index()
    {
        $report = [
            'total_revenue' => Payment::where('payment_status','Paid')->sum('amount'),
            'subscription_revenue' => Payment::where('payment_type','subscription')->where('payment_status','Paid')->sum('amount'),
            'custom_order_revenue' => Payment::where('payment_type','custom_order')->where('payment_status','Paid')->sum('amount'),
            'orders' => CustomOrder::count(),
            'subscriptions' => Subscription::count(),
        ];
        return view('admin.reports.index', compact('report'));
    }
}
