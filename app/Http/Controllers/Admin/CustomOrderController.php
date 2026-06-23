<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;

class CustomOrderController extends Controller
{
    public function index()
    {
        $orders = CustomOrder::with(['user','address','items.flower'])->latest()->paginate(30);
        return view('admin.custom-orders.index', compact('orders'));
    }
}
