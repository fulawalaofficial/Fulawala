<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\HasApiTokens;
use Throwable;

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
            'password' => 'hashed',
        ];
    }

    /**
     * Generate a reliable URL for the profile photo.
     *
     * The URL points to a Laravel route instead of directly accessing
     * public/uploads. This avoids shared-hosting public-path problems.
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $photoPath = $this->profile_photo;

            if (!$photoPath) {
                return null;
            }

            /*
             * Extract only the filename, whether the database contains:
             *
             * uploads/profile-photos/file.jpg
             * profile-photos/file.jpg
             * storage/profile-photos/file.jpg
             * https://domain.com/uploads/profile-photos/file.jpg
             */
            $parsedPath = parse_url($photoPath, PHP_URL_PATH);
            $filename = basename($parsedPath ?: $photoPath);

            if (
                !$filename ||
                in_array($filename, ['.', '..'], true)
            ) {
                return null;
            }

            try {
                if (Route::has('profile.photo.file')) {
                    $url = route('profile.photo.file', [
                        'filename' => $filename,
                    ]);
                } else {
                    $url = url(
                        '/api/profile/photo-file/' .
                        rawurlencode($filename)
                    );
                }
            } catch (Throwable) {
                $url = url(
                    '/api/profile/photo-file/' .
                    rawurlencode($filename)
                );
            }

            /*
             * Avoid HTTP image URLs in the production mobile application.
             */
            if (app()->environment('production')) {
                $url = preg_replace(
                    '/^http:\/\//i',
                    'https://',
                    $url
                ) ?: $url;
            }

            /*
             * Cache-busting value changes whenever a new photo path is saved.
             */
            $version = substr(
                md5($photoPath),
                0,
                12
            );

            return $url . '?v=' . $version;
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