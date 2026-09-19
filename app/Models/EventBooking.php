<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EventBooking extends Model
{
    protected $fillable = [
        'user_id',
        'event_master_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quotation(): HasOne
    {
        return $this->hasOne(Quotation::class, 'booking_id');
    }

    public function eventMaster(): BelongsTo
    {
        return $this->belongsTo(EventMaster::class);
    }
}
