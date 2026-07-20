<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'password',
        'role',
        'status',
        'profile_photo',
    ];

    /**
     * Attributes hidden from JSON responses.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attributes automatically included in JSON responses.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Generate the full profile-photo URL.
     *
     * New value example:
     * uploads/profile-photos/user-1-uuid.png
     *
     * Old value example:
     * profile-photos/user-1-uuid.png
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $photoPath = $this->profile_photo;

            if (!$photoPath) {
                return null;
            }

            if (filter_var($photoPath, FILTER_VALIDATE_URL)) {
                return $photoPath;
            }

            $normalizedPath = ltrim(
                str_replace('\\', '/', $photoPath),
                '/'
            );

            if (Str::startsWith($normalizedPath, 'uploads/')) {
                return asset($normalizedPath);
            }

            if (Str::startsWith($normalizedPath, 'storage/')) {
                return asset($normalizedPath);
            }

            // Backward compatibility for the former public-disk path.
            return asset('storage/' . $normalizedPath);
        });
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function customOrders(): HasMany
    {
        return $this->hasMany(CustomOrder::class);
    }

    public function eventBookings(): HasMany
    {
        return $this->hasMany(EventBooking::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
