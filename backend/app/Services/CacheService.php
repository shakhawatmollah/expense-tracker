<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    protected const DEFAULT_TTL = 3600; // 1 hour
    protected const SHORT_TTL = 300;    // 5 minutes
    protected const LONG_TTL = 86400;   // 24 hours

    /**
     * Get cached data or execute callback and cache result
     */
    public function remember(string $key, callable $callback, int $ttl = self::DEFAULT_TTL): mixed
    {
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (\Exception $e) {
            Log::warning("Cache error for key {$key}: " . $e->getMessage());

            // Fallback to direct execution if cache fails
            return $callback();
        }
    }

    /**
     * Cache dashboard data
     */
    public function cacheDashboardData(int $userId, callable $callback): array
    {
        $key = "dashboard.user.{$userId}";

        return $this->remember($key, $callback, self::SHORT_TTL);
    }

    /**
     * Cache monthly summary
     */
    public function cacheMonthlySummary(int $userId, int $year, int $month, callable $callback): array
    {
        $key = "monthly_summary.user.{$userId}.{$year}.{$month}";

        return $this->remember($key, $callback, self::DEFAULT_TTL);
    }

    /**
     * Cache category breakdown
     */
    public function cacheCategoryBreakdown(int $userId, ?string $startDate, ?string $endDate, callable $callback): array
    {
        $key = "category_breakdown.user.{$userId}." . md5($startDate . $endDate);

        return $this->remember($key, $callback, self::DEFAULT_TTL);
    }

    /**
     * Cache analytics data
     */
    public function cacheAnalytics(int $userId, string $type, callable $callback): array
    {
        $key = "analytics.{$type}.user.{$userId}";

        return $this->remember($key, $callback, self::LONG_TTL);
    }

    /**
     * Cache budget summaries
     */
    public function cacheBudgetSummary(int $userId, callable $callback): array
    {
        $key = "budget_summary.user.{$userId}";

        return $this->remember($key, $callback, self::SHORT_TTL);
    }

    /**
     * Invalidate user-specific cache
     */
    public function invalidateUserCache(int $userId): void
    {
        $patterns = [
            "dashboard.user.{$userId}",
            "monthly_summary.user.{$userId}.*",
            "category_breakdown.user.{$userId}.*",
            "analytics.*.user.{$userId}",
            "budget_summary.user.{$userId}",
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($pattern, '*')) {
                // For patterns with wildcards, we need to use cache tags or manual tracking
                $this->invalidatePattern($pattern);
            } else {
                Cache::forget($pattern);
            }
        }
    }

    /**
     * Invalidate cache when expenses change
     */
    public function invalidateExpenseCache(int $userId): void
    {
        Cache::forget("dashboard.user.{$userId}");
        Cache::forget("budget_summary.user.{$userId}");

        // Clear current month summary
        $currentYear = now()->year;
        $currentMonth = now()->month;
        Cache::forget("monthly_summary.user.{$userId}.{$currentYear}.{$currentMonth}");
    }

    /**
     * Invalidate cache when budgets change
     */
    public function invalidateBudgetCache(int $userId): void
    {
        Cache::forget("budget_summary.user.{$userId}");
        Cache::forget("dashboard.user.{$userId}");
    }

    /**
     * Get cache key for user-specific data
     */
    public function getUserCacheKey(int $userId, string $type, ...$params): string
    {
        $suffix = empty($params) ? '' : '.' . implode('.', $params);

        return "{$type}.user.{$userId}{$suffix}";
    }

    /**
     * Simple pattern-based cache invalidation
     */
    protected function invalidatePattern(string $pattern): void
    {
        // This is a simplified implementation
        // In production, consider using Redis with pattern matching
        // or implementing cache tagging
        try {
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                $redis = Cache::getStore()->getRedis();
                $keys = $redis->keys(str_replace('*', '*', $pattern));
                if (! empty($keys)) {
                    $redis->del($keys);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Pattern cache invalidation failed: ' . $e->getMessage());
        }
    }

    /**
     * Warm up frequently accessed cache
     */
    public function warmupUserCache(int $userId): void
    {
        try {
            // Warm up dashboard cache
            app(\App\Services\DashboardService::class)->getDashboardOverview($userId);

            // Warm up current month summary
            $currentYear = now()->year;
            $currentMonth = now()->month;
            app(\App\Services\DashboardService::class)->getMonthlySummary($userId, $currentYear, $currentMonth);

        } catch (\Exception $e) {
            Log::warning("Cache warmup failed for user {$userId}: " . $e->getMessage());
        }
    }
}
