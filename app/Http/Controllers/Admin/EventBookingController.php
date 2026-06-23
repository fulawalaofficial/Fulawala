<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventBooking;

class EventBookingController extends Controller
{
    public function index()
    {
        $bookings = EventBooking::with(['user','quotation'])->latest()->paginate(30);
        return view('admin.event-bookings.index', compact('bookings'));
    }
}
