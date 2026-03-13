<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class ShortUrl extends Model
{
    protected $fillable = [
        'short_code',
        'original_url',
        'expires_at',
        'click_count',
        'last_access_at',
    ];

    protected function casts(): array
    {
        return [
            'click_count' => 'integer',
            'expires_at' => 'datetime',
            'last_access_at' => 'datetime',
        ];
    }

    public static function cacheKey(string $shortCode): string
    {
        return "short_url:{$shortCode}";
    }

    public function toCachePayload(): array
    {
        return [
            'id' => $this->id,
            'original_url' => $this->original_url,
            'expires_at' => $this->expires_at?->toIso8601String(),
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public static function expiresAtFromCache(array $payload): ?Carbon
    {
        if (! isset($payload['expires_at']) || $payload['expires_at'] === null) {
            return null;
        }

        return Carbon::parse($payload['expires_at']);
    }
}
