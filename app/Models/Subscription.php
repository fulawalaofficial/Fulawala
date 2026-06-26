<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'packet_id',
        'address_id',
        'start_date',
        'end_date',
        'duration',
        'amount',
        'payment_status',
        'subscription_status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function packet()
    {
        return $this->belongsTo(PoojaPacket::class, 'packet_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function deliveries()
    {
        return $this->hasMany(SubscriptionDelivery::class);
    }

    public static function generateTodayDeliveries(): int
    {
        $today = Carbon::today();
        $fixedTime = AppSetting::valueFor('default_morning_delivery_time', '06:00 - 08:00');

        $count = 0;

        static::with(['user', 'packet'])
            ->where('subscription_status', 'Active')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->chunk(100, function ($subs) use ($today, $fixedTime, &$count) {
                foreach ($subs as $sub) {
                    $delivery = SubscriptionDelivery::firstOrCreate([
                        'subscription_id' => $sub->id,
                        'delivery_date' => $today->toDateString(),
                    ], [
                        'fixed_delivery_time' => $fixedTime,
                        'delivery_status' => 'Pending',
                    ]);

                    if ($delivery->wasRecentlyCreated) {
                        $count++;
                    }
                }
            });

        return $count;
    }
}