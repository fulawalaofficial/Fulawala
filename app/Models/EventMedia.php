<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
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
        'source_type',
        'embed_url',
        'thumbnail_url',
        'is_direct_video',
    ];

    public function eventMaster(): BelongsTo
    {
        return $this->belongsTo(EventMaster::class);
    }

    public function getMediaUrlAttribute(): ?string
    {
        if ($this->external_url) {
            return trim((string) $this->external_url);
        }

        if (!$this->path) {
            return null;
        }

        if (Str::startsWith($this->path, ['http://', 'https://'])) {
            return $this->path;
        }

        $storageUrl = Storage::disk('public')->url(ltrim($this->path, '/'));

        if (Str::startsWith($storageUrl, ['http://', 'https://'])) {
            return $storageUrl;
        }

        return asset(ltrim($storageUrl, '/'));
    }

    public function getSourceTypeAttribute(): string
    {
        if ($this->path) {
            return 'upload';
        }

        $url = trim((string) $this->external_url);

        if ($url === '') {
            return 'unknown';
        }

        if ($this->youtubeId($url)) {
            return 'youtube';
        }

        if ($this->vimeoId($url)) {
            return 'vimeo';
        }

        if ($this->isDirectVideoUrl($url)) {
            return 'direct_url';
        }

        return 'external_url';
    }

    public function getEmbedUrlAttribute(): ?string
    {
        if (!$this->external_url) {
            return null;
        }

        $url = trim((string) $this->external_url);

        if ($youtubeId = $this->youtubeId($url)) {
            return 'https://www.youtube.com/embed/' . rawurlencode($youtubeId);
        }

        if ($vimeoId = $this->vimeoId($url)) {
            return 'https://player.vimeo.com/video/' . rawurlencode($vimeoId);
        }

        return null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->external_url) {
            return null;
        }

        if ($youtubeId = $this->youtubeId((string) $this->external_url)) {
            return 'https://img.youtube.com/vi/' . rawurlencode($youtubeId) . '/hqdefault.jpg';
        }

        return null;
    }

    public function getIsDirectVideoAttribute(): bool
    {
        if ($this->path && $this->media_type === 'video') {
            return true;
        }

        return $this->external_url
            ? $this->isDirectVideoUrl((string) $this->external_url)
            : false;
    }

    private function youtubeId(string $url): ?string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host) ?: '';
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($host === 'youtu.be') {
            $id = explode('/', $path)[0] ?? null;
            return $this->validVideoId($id) ? $id : null;
        }

        if (!in_array($host, ['youtube.com', 'm.youtube.com', 'music.youtube.com'], true)) {
            return null;
        }

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        if (!empty($query['v']) && $this->validVideoId((string) $query['v'])) {
            return (string) $query['v'];
        }

        $segments = array_values(array_filter(explode('/', $path)));

        if (count($segments) >= 2 && in_array($segments[0], ['embed', 'shorts', 'live'], true)) {
            return $this->validVideoId($segments[1]) ? $segments[1] : null;
        }

        return null;
    }

    private function vimeoId(string $url): ?string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host) ?: '';

        if (!in_array($host, ['vimeo.com', 'player.vimeo.com'], true)) {
            return null;
        }

        $segments = array_values(array_filter(explode('/', trim((string) parse_url($url, PHP_URL_PATH), '/'))));

        foreach (array_reverse($segments) as $segment) {
            if (ctype_digit($segment)) {
                return $segment;
            }
        }

        return null;
    }

    private function isDirectVideoUrl(string $url): bool
    {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        return Str::endsWith($path, ['.mp4', '.webm', '.mov', '.m4v', '.ogg']);
    }

    private function validVideoId(?string $value): bool
    {
        if (!$value) {
            return false;
        }

        return (bool) preg_match('/^[A-Za-z0-9_-]{6,30}$/', $value);
    }
}
