<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FlowerProduct extends Model
{
    protected $fillable = [
        'flower_name', 'image', 'category', 'price', 'unit',
        'stock_status', 'description', 'status',
    ];

    protected $casts = ['price' => 'decimal:2'];
    protected $appends = ['image_url'];

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
        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        $path = ltrim(str_replace('\\', '/', $image), '/');
        foreach (['storage/app/public/', 'app/public/', 'public/storage/', 'public/'] as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                $path = Str::after($path, $prefix);
                break;
            }
        }

        if (Str::startsWith($path, 'storage/')) {
            return asset($path);
        }
        if (is_file(public_path($path))) {
            return asset($path);
        }

        return asset('storage/' . $path);
    }
}
