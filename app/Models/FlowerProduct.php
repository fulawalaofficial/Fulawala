<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FlowerProduct extends Model
{
    protected $fillable = [
        'flower_name',
        'image',
        'category',
        'price',
        'unit',
        'stock_status',
        'description',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Always include a browser/app-ready absolute URL in API JSON.
     */
    protected $appends = [
        'image_url',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function getImageUrlAttribute(): ?string
    {
        $image = trim((string) $this->image);

        if ($image === '') {
            return null;
        }

        // Already a full external/public URL.
        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        $path = str_replace('\\', '/', $image);
        $path = ltrim($path, '/');

        // Normalize common values that may already be stored in the DB.
        if (Str::startsWith($path, 'storage/app/public/')) {
            $path = Str::after($path, 'storage/app/public/');
        }

        if (Str::startsWith($path, 'public/')) {
            $path = Str::after($path, 'public/');
        }

        // If DB already stores storage/... return that public URL.
        if (Str::startsWith($path, 'storage/')) {
            return asset($path);
        }

        // Some older uploads may have been placed directly under public/.
        if (file_exists(public_path($path))) {
            return asset($path);
        }

        // Standard Laravel public disk location:
        // storage/app/public/{path} -> public/storage/{path}
        return asset('storage/' . $path);
    }
}
