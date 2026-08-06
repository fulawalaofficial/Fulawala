<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id',
        'sanctum_token_id',
        'device_id',
        'fcm_token',
        'fcm_token_hash',
        'device_name',
        'device_model',
        'platform',
        'os_version',
        'app_version',
        'timezone',
        'locale',
        'last_ip_address',
        'user_agent',
        'notifications_enabled',
        'is_active',
        'logged_in_at',
        'last_seen_at',
        'logged_out_at',
    ];

    /*
     * Do not return sensitive notification and token information
     * in normal API responses.
     */
    protected $hidden = [
        'sanctum_token_id',
        'fcm_token',
        'fcm_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'notifications_enabled' => 'boolean',
            'is_active' => 'boolean',
            'logged_in_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'logged_out_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Only devices that can receive push notifications.
     */
    public function scopePushEnabled(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('notifications_enabled', true)
            ->whereNotNull('fcm_token');
    }
}