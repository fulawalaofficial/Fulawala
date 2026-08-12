<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * A clean address string for mobile/admin display.
     */
    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->number,
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

    /**
     * True only when a usable GPS pair is stored.
     */
    public function getHasCoordinatesAttribute(): bool
    {
        return $this->hasCoordinates();
    }

    public function hasCoordinates(): bool
    {
        if ($this->latitude === null || $this->longitude === null) {
            return false;
        }

        /*
         * 0,0 is commonly sent before GPS is resolved.
         * Do not treat it as a real delivery point.
         */
        return !(
            (float) $this->latitude === 0.0
            && (float) $this->longitude === 0.0
        );
    }
}
