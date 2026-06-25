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
        'monthly_price',
        'weekly_price',
        'daily_quantity',
        'package_type',
        'duration_months',
        'status',
    ];

    protected $casts = [
        'included_flowers' => 'array',
        'monthly_price' => 'decimal:2',
        'weekly_price' => 'decimal:2',
        'duration_months' => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'packet_id');
    }

    public function getDurationLabelAttribute(): string
    {
        return match ((int) $this->duration_months) {
            1 => 'One Month',
            2 => 'Two Months',
            3 => 'Three Months',
            6 => 'Six Months',
            12 => 'One Year',
            default => $this->duration_months . ' Months',
        };
    }

    public function getFlowerItemsAttribute(): array
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
                    'mrp_price' => 0,
                    'sale_price' => 0,
                ];
            }

            return [
                'flower_id' => $item['flower_id'] ?? null,
                'flower_name' => $item['flower_name'] ?? '',
                'unit' => $item['unit'] ?? '',
                'quantity' => $item['quantity'] ?? '',
                'mrp_price' => $item['mrp_price'] ?? 0,
                'sale_price' => $item['sale_price'] ?? 0,
            ];
        })->values()->all();
    }

    public function getItemsTotalMrpAttribute(): float
    {
        return collect($this->flower_items)->sum(function ($item) {
            return (float) ($item['quantity'] ?? 0) * (float) ($item['mrp_price'] ?? 0);
        });
    }

    public function getItemsTotalSaleAttribute(): float
    {
        return collect($this->flower_items)->sum(function ($item) {
            return (float) ($item['quantity'] ?? 0) * (float) ($item['sale_price'] ?? 0);
        });
    }
}