<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPlan extends Model
{
    protected $fillable = [
        'event_master_id',
        'name',
        'price',
        'description',
        'features',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'sort_order' => 'integer',
    ];

    public function eventMaster(): BelongsTo
    {
        return $this->belongsTo(EventMaster::class);
    }
}
