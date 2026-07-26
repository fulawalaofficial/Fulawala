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
     * Include these calculated fields in API JSON.
     */
    protected $appends = [
        'image_url',
        'package_type_label',
        'flower_items',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(
            Subscription::class,
            'packet_id'
        );
    }

    /**
     * Return the complete package image URL.
     *
     * New database format:
     * pooja-packets/photo.jpg
     *
     * Old supported formats:
     * uploads/pooja-packets/photo.jpg
     * storage/pooja-packets/photo.jpg
     * public/storage/pooja-packets/photo.jpg
     * storage/app/public/pooja-packets/photo.jpg
     * https://fulawala.com/storage/pooja-packets/photo.jpg
     */
    public function getImageUrlAttribute(): ?string
    {
        $rawImage = $this->getRawOriginal('image');

        if (blank($rawImage)) {
            return null;
        }

        $rawImage = trim(
            str_replace('\\', '/', (string) $rawImage)
        );

        /*
         * Image already has a complete URL.
         */
        if (Str::startsWith($rawImage, ['http://', 'https://'])) {
            return $this->forceCorrectScheme($rawImage);
        }

        $path = $this->normaliseImagePath($rawImage);

        if (blank($path)) {
            return null;
        }

        /*
         * Support old files stored directly inside public/uploads.
         */
        if (
            Str::startsWith($path, 'uploads/') &&
            is_file(public_path($path))
        ) {
            return $this->makeAbsoluteUrl($path);
        }

        /*
         * Support files stored inside storage/app/public.
         */
        if (Storage::disk('public')->exists($path)) {
            return $this->makeAbsoluteUrl(
                'storage/' . ltrim($path, '/')
            );
        }

        /*
         * Support any other file stored directly in public folder.
         */
        if (is_file(public_path($path))) {
            return $this->makeAbsoluteUrl($path);
        }

        /*
         * File does not exist.
         */
        return null;
    }

    /**
     * Return the normalized file path for deleting an image.
     */
    public function imageStoragePath(): ?string
    {
        $rawImage = $this->getRawOriginal('image');

        if (blank($rawImage)) {
            return null;
        }

        $rawImage = trim(
            str_replace('\\', '/', (string) $rawImage)
        );

        if (Str::startsWith($rawImage, ['http://', 'https://'])) {
            $urlPath = parse_url($rawImage, PHP_URL_PATH);

            if (!$urlPath) {
                return null;
            }

            return $this->normaliseImagePath($urlPath);
        }

        return $this->normaliseImagePath($rawImage);
    }

    /**
     * Normalize different old image path formats.
     */
    private function normaliseImagePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        $path = ltrim($path, '/');

        /*
         * Handle a complete absolute server path.
         *
         * Example:
         * /home/user/project/storage/app/public/pooja-packets/image.jpg
         */
        $storageMarker = 'storage/app/public/';
        $storagePosition = strpos($path, $storageMarker);

        if ($storagePosition !== false) {
            $path = substr(
                $path,
                $storagePosition + strlen($storageMarker)
            );
        }

        $prefixes = [
            'public/storage/',
            'storage/app/public/',
            'app/public/',
            'storage/',
            'public/',
        ];

        foreach ($prefixes as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                $path = Str::after($path, $prefix);
                break;
            }
        }

        return ltrim($path, '/');
    }

    /**
     * Create an absolute URL using APP_URL.
     */
    private function makeAbsoluteUrl(string $path): string
    {
        $baseUrl = rtrim(
            (string) config('app.url'),
            '/'
        );

        if ($baseUrl === '') {
            $baseUrl = rtrim(url('/'), '/');
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Convert old HTTP image URL to HTTPS when the application uses HTTPS.
     */
    private function forceCorrectScheme(string $url): string
    {
        $appUrl = (string) config('app.url');

        if (
            Str::startsWith($appUrl, 'https://') &&
            Str::startsWith($url, 'http://')
        ) {
            return Str::replaceFirst(
                'http://',
                'https://',
                $url
            );
        }

        return $url;
    }

    public function getPackageTypeLabelAttribute(): string
    {
        if (blank($this->package_type)) {
            return 'Monthly';
        }

        return Str::title((string) $this->package_type);
    }

    public function getFlowerItemsAttribute(): array
    {
        $items = $this->included_flowers;

        if (!is_array($items)) {
            return [];
        }

        $finalItems = [];

        foreach ($items as $item) {
            /*
             * Support old JSON format where only flower names were saved.
             */
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

            if (!is_array($item)) {
                continue;
            }

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

        return $finalItems;
    }
}