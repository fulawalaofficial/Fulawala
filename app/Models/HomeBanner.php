<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeBanner extends Model
{
    // Keys must be valid routes in the React Native app's authenticated navigator.
    public const TARGETS = [
        'Flowers' => 'Fresh Flowers',
        'EventBooking' => 'Event Booking',
        'PoojaPackets' => 'Subscription Plans',
        'MyOrders' => 'My Orders',
        'MySubscriptions' => 'My Subscriptions',
        'Quotations' => 'My Quotations',
        'Cart' => 'My Cart',
        'Profile' => 'Profile',
    ];

    protected $fillable = [
        'title',
        'image_path',
        'target_screen',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? asset('storage/' . ltrim($this->image_path, '/'))
            : null;
    }
}
