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
        // 1. Super Admins always have access to all platform features
        $user = auth()->user();
        if ($user && ($user->isSuperAdmin() || $user->role === 'super_admin')) {
            return $next($request);
        }

        $settings = \App\Models\CompanySetting::getCached() ?? \App\Models\CompanySetting::first();
        
        // 2. Safe check on company feature access
        $hasAccess = $settings ? $settings->hasFeature($feature) : in_array($feature, ['crm', 'marketing', 'payment_plans']);

        if (!$hasAccess) {
            // Check if request expects JSON
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Your current package does not include access to this feature. Please upgrade your plan.'], 403);
            }
            
            abort(403, 'Your current package does not include access to this feature. Please upgrade your plan.');
        }

        return $next($request);
    }
}
