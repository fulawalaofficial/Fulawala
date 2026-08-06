<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'password',
        'role',
        'status',
        'profile_photo',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Generate a reliable URL for the profile photo.
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $photoPath = $this->profile_photo;

            if (!$photoPath) {
                return null;
            }

            $parsedPath = parse_url(
                $photoPath,
                PHP_URL_PATH
            );

            $filename = basename(
                $parsedPath ?: $photoPath
            );

            if (
                !$filename ||
                in_array($filename, ['.', '..'], true)
            ) {
                return null;
            }

            $url = url(
                '/api/profile-images/' .
                rawurlencode($filename)
            );

            if (app()->environment('production')) {
                $url = preg_replace(
                    '/^http:\/\//i',
                    'https://',
                    $url
                ) ?: $url;
            }

            return $url . '?v=' . substr(
                md5($photoPath),
                0,
                12
            );
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

    /**
     * All phones/devices used by this customer.
     */
    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    /**
     * Devices that can currently receive notifications.
     */
    public function notificationDevices(): HasMany
    {
        return $this->devices()
            ->where('is_active', true)
            ->where('notifications_enabled', true)
            ->whereNotNull('fcm_token');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}