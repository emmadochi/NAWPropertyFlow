<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $settings = \App\Models\CompanySetting::first();
        
        if (!$settings || !$settings->hasFeature($feature)) {
            // Check if request expects JSON
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Your current package does not include access to this feature. Please upgrade your plan.'], 403);
            }
            
            abort(403, 'Your current package does not include access to this feature. Please upgrade your plan.');
        }

        return $next($request);
    }
}
