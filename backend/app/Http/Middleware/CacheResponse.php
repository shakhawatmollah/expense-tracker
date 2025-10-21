<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, int $minutes = 5): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Generate cache key based on URL and user
        $key = $this->getCacheKey($request);

        // Check if cached response exists
        if (Cache::has($key)) {
            return response()->json(Cache::get($key))
                ->header('X-Cache', 'HIT');
        }

        // Get fresh response
        $response = $next($request);

        // Cache successful JSON responses
        if ($response->isSuccessful() && $response->headers->get('Content-Type') === 'application/json') {
            $content = json_decode($response->getContent(), true);
            Cache::put($key, $content, now()->addMinutes($minutes));
            
            return response()->json($content)
                ->header('X-Cache', 'MISS');
        }

        return $response;
    }

    /**
     * Generate cache key for the request
     */
    protected function getCacheKey(Request $request): string
    {
        $userId = $request->user()?->id ?? 'guest';
        $url = $request->fullUrl();
        
        return 'api_cache:' . md5($userId . $url);
    }
}