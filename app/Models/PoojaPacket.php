<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    /**
     * Automatically append these values to API JSON responses.
     */
    protected $appends = [
        'image_url',
        'package_type_label',
        'flower_items',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'packet_id');
    }

    /**
     * Return a browser-ready image URL for the admin panel and mobile API.
     *
     * Supported values in the database:
     * - pooja-packets/example.jpg
     * - storage/pooja-packets/example.jpg
     * - public/pooja-packets/example.jpg
     * - storage/app/public/pooja-packets/example.jpg
     * - uploads/pooja-packets/example.jpg
     * - https://fulawala.com/...
     */
    public function getImageUrlAttribute(): ?string
    {
        $image = $this->attributes['image'] ?? null;

        if (!is_string($image) || trim($image) === '') {
            return null;
        }

        $image = str_replace('\\', '/', trim($image));

        // The database already contains a complete external URL.
        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        $image = ltrim($image, '/');

        // Normalize paths accidentally saved with filesystem prefixes.
        foreach ([
            'storage/app/public/',
            'app/public/',
            'public/storage/',
            'public/',
        ] as $prefix) {
            if (Str::startsWith($image, $prefix)) {
                $image = Str::after($image, $prefix);
                break;
            }
        }

        // Old images may be stored directly inside public/uploads.
        if (Str::startsWith($image, 'uploads/')) {
            return url('/' . $image);
        }

        // A value already containing storage/ should not receive it twice.
        if (Str::startsWith($image, 'storage/')) {
            return url('/' . $image);
        }

        // New images are stored on Laravel's public disk.
        $generatedUrl = Storage::disk('public')->url($image);

        if (Str::startsWith($generatedUrl, ['http://', 'https://'])) {
            return $generatedUrl;
        }

        return url('/' . ltrim($generatedUrl, '/'));
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

        $finalItems = [];

        foreach ($items as $item) {
            if (is_string($item)) {
                $finalItems[] = [
                    'flower_id' => null,
                    'flower_name' => $item,
                    'unit' => '',
                    'quantity' => '',
                    'price' => 0,
                    'mrp_price' => 0,
                    'sale_price' => 0,
                    'line_mrp_total' => 0,
                    'line_sale_total' => 0,
                ];

                continue;
            }

            if (is_array($item)) {
                $finalItems[] = [
                    'flower_id' => $item['flower_id'] ?? null,
                    'flower_name' => $item['flower_name'] ?? '',
                    'unit' => $item['unit'] ?? '',
                    'quantity' => $item['quantity'] ?? '',
                    'price' => $item['price'] ?? 0,
                    'mrp_price' => $item['mrp_price'] ?? 0,
                    'sale_price' => $item['sale_price'] ?? 0,
                    'line_mrp_total' => $item['line_mrp_total'] ?? 0,
                    'line_sale_total' => $item['line_sale_total'] ?? 0,
                ];
            }
        }

        return $finalItems;
    }
}
