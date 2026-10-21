<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PoojaPacket extends Model
{
    protected $fillable = [
        'packet_name', 'image', 'description', 'included_flowers',
        'mrp_price', 'sale_price', 'monthly_price', 'weekly_price',
        'daily_quantity', 'package_type', 'duration_months', 'status',
    ];

    protected $casts = [
        'included_flowers' => 'array',
        'mrp_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'weekly_price' => 'decimal:2',
        'duration_months' => 'integer',
    ];

    protected $appends = ['image_url', 'package_type_label', 'flower_items'];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'packet_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        $image = trim(str_replace('\\', '/', (string) ($this->attributes['image'] ?? '')));
        if ($image === '') {
            return null;
        }
        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        $path = ltrim($image, '/');
        foreach (['storage/app/public/', 'app/public/', 'public/storage/', 'public/'] as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                $path = Str::after($path, $prefix);
                break;
            }
        }

        if (Str::startsWith($path, 'storage/')) {
            return asset($path);
        }
        // Support older files directly under public/uploads/pooja-packets.
        if (Str::startsWith($path, 'uploads/') || is_file(public_path($path))) {
            return asset($path);
        }

        $url = Storage::disk('public')->url($path);
        return Str::startsWith($url, ['http://', 'https://'])
            ? $url
            : url('/' . ltrim($url, '/'));
    }

    public function getPackageTypeLabelAttribute(): string
    {
        return $this->package_type ?: 'Monthly';
    }

    public function getFlowerItemsAttribute(): array
    {
        $items = $this->included_flowers;
        if (!is_array($items)) {
            return [];
        }

        $result = [];
        foreach ($items as $item) {
            if (is_string($item)) {
                $item = ['flower_name' => $item];
            }
            if (!is_array($item)) {
                continue;
            }

            $result[] = [
                'flower_id' => $item['flower_id'] ?? null,
                'flower_name' => $item['flower_name'] ?? '',
                'unit' => $item['unit'] ?? '',
                'quantity' => $item['quantity'] ?? '',
                'price' => $item['price'] ?? 0,
                'mrp_price' => $item['mrp_price'] ?? ($item['price'] ?? 0),
                'sale_price' => $item['sale_price'] ?? ($item['price'] ?? 0),
                'line_mrp_total' => $item['line_mrp_total'] ?? 0,
                'line_sale_total' => $item['line_sale_total'] ?? 0,
            ];
        }

        return $result;
    }
}
