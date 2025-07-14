<?php

namespace App\Http\Middleware;

use App\Services\CacheService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CacheApiResponse
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $ttl  Cache TTL in seconds (default: 300 = 5 minutes)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, int $ttl = 300)
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Skip caching for authenticated admin/branch requests
        if ($request->is('admin/*') || $request->is('branch/*')) {
            return $next($request);
        }

        // Generate cache key based on route and parameters
        $cacheKey = $this->generateCacheKey($request);
        
        // Try to get cached response
        $cachedResponse = $this->cacheService->getCachedApiResponse($request->route()->getName() ?? $request->path(), $request->all());
        
        if ($cachedResponse !== null) {
            Log::debug('API response served from cache', [
                'endpoint' => $request->path(),
                'cache_key' => $cacheKey
            ]);
            
            return response()->json($cachedResponse)
                ->header('X-Cache-Status', 'HIT')
                ->header('X-Cache-Key', $cacheKey);
        }

        // Process request
        $response = $next($request);

        // Cache successful JSON responses
        if ($response instanceof JsonResponse && $response->getStatusCode() === 200) {
            $responseData = json_decode($response->getContent(), true);
            
            // Only cache if response contains data
            if (!empty($responseData)) {
                $this->cacheService->cacheApiResponse(
                    $request->route()->getName() ?? $request->path(),
                    $request->all(),
                    $responseData,
                    $ttl
                );
                
                Log::debug('API response cached', [
                    'endpoint' => $request->path(),
                    'cache_key' => $cacheKey,
                    'ttl' => $ttl
                ]);
            }
        }

        return $response->header('X-Cache-Status', 'MISS')
                       ->header('X-Cache-Key', $cacheKey);
    }

    /**
     * Generate cache key for the request
     *
     * @param Request $request
     * @return string
     */
    private function generateCacheKey(Request $request): string
    {
        $key = $request->path();
        
        // Include query parameters in cache key
        if (!empty($request->query())) {
            $key .= '?' . http_build_query($request->query());
        }
        
        // Include user ID for user-specific endpoints
        if ($request->user()) {
            $key .= ':user:' . $request->user()->id;
        }
        
        return md5($key);
    }
}
