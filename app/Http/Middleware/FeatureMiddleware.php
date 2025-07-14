<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\FeatureService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class FeatureMiddleware
{
    protected $featureService;

    public function __construct(FeatureService $featureService)
    {
        $this->featureService = $featureService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $feature
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $feature)
    {
        // Always allow access for super admin (admin_role_id = 1)
        if (auth('admin')->check() && auth('admin')->user()->admin_role_id == 1) {
            return $next($request);
        }

        // Check if the feature is enabled
        if (!$this->featureService->isEnabled($feature)) {
            // For AJAX requests, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Feature not available',
                    'message' => 'This feature is currently disabled.'
                ], 403);
            }

            // For regular requests, show error and redirect back
            Toastr::error('This feature is currently disabled.');
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
