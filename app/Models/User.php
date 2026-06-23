<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'mobile', 'email', 'password', 'role', 'status'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function addresses() { return $this->hasMany(Address::class); }
    public function subscriptions() { return $this->hasMany(Subscription::class); }
    public function customOrders() { return $this->hasMany(CustomOrder::class); }
    public function eventBookings() { return $this->hasMany(EventBooking::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function isAdmin(): bool { return $this->role === 'admin'; }
}
