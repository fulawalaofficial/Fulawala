<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'booking_id',
        'decoration_details',
        'total_amount',
        'advance_amount',
        'balance_amount',
        'terms',
        'quotation_status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(EventBooking::class, 'booking_id');
    }
}