<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionDelivery extends Model
{
    protected $fillable = [
        'subscription_id',
        'delivery_date',
        'fixed_delivery_time',
        'delivery_boy_id',
        'delivery_status',
        'failed_reason',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function deliveryBoy()
    {
        return $this->belongsTo(Staff::class, 'delivery_boy_id');
    }
}