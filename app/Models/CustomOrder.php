<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomOrder extends Model
{
    /**
     * Available custom-order statuses.
     */
    public const ORDER_STATUSES = [
        'Order Placed',
        'Confirmed',
        'Preparing',
        'Ready for Delivery',
        'Out for Delivery',
        'Delivered',
        'Cancelled',
    ];

    protected $fillable = [
        'user_id',
        'address_id',
        'delivery_date',
        'delivery_slot',
        'subtotal',
        'delivery_charge',
        'total_amount',
        'payment_status',
        'order_status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'address_id' => 'integer',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(
            Address::class,
            'address_id'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            CustomOrderItem::class,
            'order_id'
        );
    }
}