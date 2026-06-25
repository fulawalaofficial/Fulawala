<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoojaPacket extends Model
{
    protected $fillable = [
        'packet_name',
        'image',
        'description',
        'included_flowers',
        'mrp_price',
        'sale_price',
        'monthly_price',
        'weekly_price',
        'daily_quantity',
        'package_type',
        'duration_months',
        'status',
    ];

    protected $casts = [
        'included_flowers' => 'array',
        'mrp_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'weekly_price' => 'decimal:2',
        'duration_months' => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'packet_id');
    }

    public function getPackageTypeLabelAttribute()
    {
        switch ($this->package_type) {
            case 'Monthly':
                return 'Monthly';
            case 'Three Month':
                return 'Three Month';
            case 'Six Month':
                return 'Six Month';
            case 'One Year':
                return 'One Year';
            default:
                return $this->package_type ?: 'Monthly';
        }
    }

    public function getFlowerItemsAttribute()
    {
        $items = $this->included_flowers ?? [];

        if (!is_array($items)) {
            return [];
        }

        return collect($items)->map(function ($item) {
            if (is_string($item)) {
                return [
                    'flower_id' => null,
                    'flower_name' => $item,
                    'unit' => '',
                    'quantity' => '',
                    'price' => 0,
                    'mrp_price' => 0,
                    'sale_price' => 0,
                ];
            }

            return [
                'flower_id' => $item['flower_id'] ?? null,
                'flower_name' => $item['flower_name'] ?? '',
                'unit' => $item['unit'] ?? '',
                'quantity' => $item['quantity'] ?? '',
                'price' => $item['price'] ?? 0,
                'mrp_price' => $item['mrp_price'] ?? 0,
                'sale_price' => $item['sale_price'] ?? 0,
            ];
        })->values()->all();
    }

    public function getFlowerSummaryAttribute()
    {
        return collect($this->flower_items)->map(function ($item) {
            return trim(($item['flower_name'] ?? '') . ' - ' . ($item['quantity'] ?? '') . ' ' . ($item['unit'] ?? ''));
        })->filter()->implode(', ');
    }
}