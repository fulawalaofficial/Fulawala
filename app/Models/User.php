<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /**
     * Fields that may be mass assigned.
     */
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * Fields hidden from JSON/API responses.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Model casts.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Mobile devices logged in by this customer.
     */
    public function devices(): HasMany
    {
        return $this->hasMany(
            UserDevice::class,
            'user_id'
        );
    }

    /**
     * Saved customer addresses.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(
            Address::class,
            'user_id'
        );
    }

    /**
     * Customer subscriptions.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(
            Subscription::class,
            'user_id'
        );
    }

    /**
     * Customer customized orders.
     */
    public function customOrders(): HasMany
    {
        return $this->hasMany(
            CustomOrder::class,
            'user_id'
        );
    }

    /**
     * Customer event bookings.
     */
    public function eventBookings(): HasMany
    {
        return $this->hasMany(
            EventBooking::class,
            'user_id'
        );
    }

    /**
     * Customer payment records.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(
            Payment::class,
            'user_id'
        );
    }
}
