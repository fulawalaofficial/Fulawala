<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionDelivery;

class SubscriptionDeliveryController extends Controller
{
    public function index()
    {
        $deliveries = SubscriptionDelivery::with(['subscription.packet','subscription.user','deliveryBoy'])->latest()->paginate(30);
        return view('admin.daily-deliveries.index', compact('deliveries'));
    }
}
