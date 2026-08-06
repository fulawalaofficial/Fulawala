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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Full printable address used by delivery screens and map fallbacks.
     */
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
            ->implode(', ');
    }

    /**
     * Determine whether both GPS coordinates are available.
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null
            && $this->longitude !== null;
    }
}
