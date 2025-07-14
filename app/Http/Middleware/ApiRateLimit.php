<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\ApiResponseService;

class ApiRateLimit
{
    protected $apiResponseService;

    public function __construct(ApiResponseService $apiResponseService)
    {
        $this->apiResponseService = $apiResponseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $maxAttempts  Maximum attempts per window
     * @param  int  $decayMinutes  Time window in minutes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);
        $maxAttempts = $this->resolveMaxAttempts($request, $maxAttempts);
        
        if ($this->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildRateLimitResponse($key, $maxAttempts, $decayMinutes);
        }

        $this->hit($key, $decayMinutes);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Resolve request signature for rate limiting
     *
     * @param Request $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $user = $request->user();
        
        if ($user) {
            return 'api_rate_limit:user:' . $user->id;
        }

        // For guest users, use IP address
        return 'api_rate_limit:ip:' . $request->ip();
    }

    /**
     * Resolve max attempts based on user type
     *
     * @param Request $request
     * @param int $default
     * @return int
     */
    protected function resolveMaxAttempts(Request $request, int $default): int
    {
        $user = $request->user();
        
        if ($user) {
            // Authenticated users get higher limits
            return $default * 2;
        }

        // Guest users get default limit
        return $default;
    }

    /**
     * Determine if the given key has been "accessed" too many times
     *
     * @param string $key
     * @param int $maxAttempts
     * @return bool
     */
    protected function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return $this->attempts($key) >= $maxAttempts;
    }

    /**
     * Get the number of attempts for the given key
     *
     * @param string $key
     * @return int
     */
    protected function attempts(string $key): int
    {
        return Cache::get($key, 0);
    }

    /**
     * Increment the counter for a given key for a given decay time
     *
     * @param string $key
     * @param int $decayMinutes
     * @return int
     */
    protected function hit(string $key, int $decayMinutes): int
    {
        $current = Cache::get($key, 0);
        $new = $current + 1;
        
        Cache::put($key, $new, $decayMinutes * 60);
        
        return $new;
    }

    /**
     * Calculate the number of remaining attempts
     *
     * @param string $key
     * @param int $maxAttempts
     * @return int
     */
    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        return max(0, $maxAttempts - $this->attempts($key));
    }

    /**
     * Create a rate limit response
     *
     * @param string $key
     * @param int $maxAttempts
     * @param int $decayMinutes
     * @return \Illuminate\Http\JsonResponse
     */
    protected function buildRateLimitResponse(string $key, int $maxAttempts, int $decayMinutes)
    {
        $retryAfter = $this->getTimeUntilNextRetry($key, $decayMinutes);
        
        Log::warning('Rate limit exceeded', [
            'key' => $key,
            'max_attempts' => $maxAttempts,
            'retry_after' => $retryAfter
        ]);

        return $this->apiResponseService->error(
            'Too many requests. Please try again later.',
            429,
            [
                'code' => 'rate_limit_exceeded',
                'retry_after' => $retryAfter
            ]
        )->header('Retry-After', $retryAfter)
         ->header('X-RateLimit-Limit', $maxAttempts)
         ->header('X-RateLimit-Remaining', 0);
    }

    /**
     * Get the time until the next retry
     *
     * @param string $key
     * @param int $decayMinutes
     * @return int
     */
    protected function getTimeUntilNextRetry(string $key, int $decayMinutes): int
    {
        // For simplicity, return the decay time in seconds
        return $decayMinutes * 60;
    }

    /**
     * Add rate limit headers to the response
     *
     * @param mixed $response
     * @param int $maxAttempts
     * @param int $remainingAttempts
     * @return mixed
     */
    protected function addHeaders($response, int $maxAttempts, int $remainingAttempts)
    {
        return $response->header('X-RateLimit-Limit', $maxAttempts)
                       ->header('X-RateLimit-Remaining', $remainingAttempts);
    }
}
