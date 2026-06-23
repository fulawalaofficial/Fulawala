<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::with(['user','packet','address'])->latest()->paginate(30);
        return view('admin.subscriptions.index', compact('subscriptions'));
    }
}
