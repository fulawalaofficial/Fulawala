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
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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
            ->unique(fn ($value) => mb_strtolower(trim((string) $value)))
            ->implode(', ');
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null
            && $this->longitude !== null;
    }
}
