<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventBooking extends Model
{
    protected $fillable = [
        'user_id',
        'event_type',
        'event_date',
        'event_time',
        'venue_address',
        'budget',
        'requirement',
        'reference_image',
        'special_instructions',
        'booking_status',
    ];

    protected $casts = [
        'event_date' => 'date',
        'budget' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quotation()
    {
        return $this->hasOne(Quotation::class, 'booking_id');
    }
}