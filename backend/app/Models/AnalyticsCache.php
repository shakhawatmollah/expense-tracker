<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsCache extends Model
{
    use HasFactory;

    protected $table = 'analytics_cache';

    protected $fillable = [
        'user_id',
        'cache_key',
        'cached_data',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'cached_data' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the cache entry
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for valid (non-expired) cache entries
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope for specific cache type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('cache_type', $type);
    }

    /**
     * Scope for specific cache key
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('cache_key', $key);
    }

    /**
     * Check if cache entry is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * Get cache entry by user and key
     */
    public static function getCache(int $userId, string $key)
    {
        return static::where('user_id', $userId)
            ->byKey($key)
            ->valid()
            ->first();
    }

    /**
     * Set cache entry
     */
    public static function setCache(int $userId, string $key, array $data, int $ttlMinutes = 60)
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'cache_key' => $key,
            ],
            [
                'cached_data' => $data,
                'expires_at' => now()->addMinutes($ttlMinutes),
            ]
        );
    }

    /**
     * Clear expired cache entries
     */
    public static function clearExpired()
    {
        return static::where('expires_at', '<', now())->delete();
    }
}
