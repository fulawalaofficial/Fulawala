<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventBooking;
use App\Models\Quotation;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index() { return view('admin.quotations.index', ['quotations' => Quotation::with('booking.user')->latest()->paginate(30), 'bookings' => EventBooking::doesntHave('quotation')->latest()->get()]); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => ['required','exists:event_bookings,id'],
            'decoration_details' => ['required','string'],
            'total_amount' => ['required','numeric'],
            'advance_amount' => ['required','numeric'],
            'terms' => ['nullable','string'],
        ]);
        $data['balance_amount'] = $data['total_amount'] - $data['advance_amount'];
        $data['quotation_status'] = 'Sent';
        $quote = Quotation::create($data);
        $quote->booking->update(['booking_status' => 'Quotation Sent']);
        return back()->with('success','Quotation sent.');
    }
}
