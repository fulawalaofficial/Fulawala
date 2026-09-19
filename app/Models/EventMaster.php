<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventMaster extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'cover_image',
        'starting_price',
        'status',
        'sort_order',
    ];

    protected $appends = [
        'cover_image_url',
    ];

    protected $casts = [
        'starting_price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (EventMaster $event): void {
            if (!$event->slug) {
                $event->slug = static::uniqueSlug($event->name);
            }
        });

        static::updating(function (EventMaster $event): void {
            if (!$event->slug) {
                $event->slug = static::uniqueSlug($event->name, $event->id);
                return;
            }

            if ($event->isDirty('name') && !$event->isDirty('slug')) {
                $event->slug = static::uniqueSlug($event->name, $event->id);
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function media(): HasMany
    {
        return $this->hasMany(EventMedia::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(EventMedia::class)
            ->where('media_type', 'photo')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(EventMedia::class)
            ->where('media_type', 'video')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function plans(): HasMany
    {
        return $this->hasMany(EventPlan::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function activePlans(): HasMany
    {
        return $this->hasMany(EventPlan::class)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(EventBooking::class);
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }

        if (Str::startsWith($this->cover_image, ['http://', 'https://'])) {
            return $this->cover_image;
        }

        $storageUrl = Storage::disk('public')->url(ltrim($this->cover_image, '/'));

        if (Str::startsWith($storageUrl, ['http://', 'https://'])) {
            return $storageUrl;
        }

        return asset(ltrim($storageUrl, '/'));
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'event';
        $slug = $base;
        $counter = 2;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
