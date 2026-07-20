<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomOrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'flower_product_id',
        'quantity',
        'unit',
        'price',
        'total',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'flower_product_id' => 'integer',
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(
            CustomOrder::class,
            'order_id'
        );
    }

    public function flower(): BelongsTo
    {
        return $this->belongsTo(
            FlowerProduct::class,
            'flower_product_id'
        );
    }
}