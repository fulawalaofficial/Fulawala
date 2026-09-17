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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class, 'user_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'user_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'user_id');
    }

    public function customOrders(): HasMany
    {
        return $this->hasMany(CustomOrder::class, 'user_id');
    }

    public function eventBookings(): HasMany
    {
        return $this->hasMany(EventBooking::class, 'user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id');
    }
}
