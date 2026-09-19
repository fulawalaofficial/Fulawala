<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EventMedia extends Model
{
    protected $fillable = [
        'event_master_id',
        'media_type',
        'path',
        'external_url',
        'title',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'media_url',
    ];

    public function eventMaster(): BelongsTo
    {
        return $this->belongsTo(EventMaster::class);
    }

    public function getMediaUrlAttribute(): ?string
    {
        if ($this->external_url) {
            return $this->external_url;
        }

        if (!$this->path) {
            return null;
        }

        if (Str::startsWith($this->path, ['http://', 'https://'])) {
            return $this->path;
        }

        return asset('storage/' . ltrim($this->path, '/'));
    }
}
