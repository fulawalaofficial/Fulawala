<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_type',
        'name',
        'number',
        'address',
        'city',
        'state',
        'pincode',
        'landmark',
        'is_default',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_default' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $appends = [
        'full_address',
        'has_coordinates',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eventBookings(): HasMany
    {
        return $this->hasMany(EventBooking::class);
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address,
            $this->landmark,
            $this->city,
            $this->state,
            $this->pincode,
        ])
            ->filter(fn ($value) => filled($value))
            ->unique(fn ($value) => strtolower(trim((string) $value)))
            ->implode(', ');
    }

    public function getHasCoordinatesAttribute(): bool
    {
        return $this->hasCoordinates();
    }

    public function hasCoordinates(): bool
    {
        if ($this->latitude === null || $this->longitude === null) {
            return false;
        }

        return !(
            (float) $this->latitude === 0.0
            && (float) $this->longitude === 0.0
        );
    }
}
