<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     * Checks if the authenticated user has at least one of the specified permissions.
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 1. Super Admin always bypasses
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // 2. Check each permission passed in arguments
        $hasAnyPermission = false;
        foreach ($permissions as $permGroup) {
            $slugs = explode(',', $permGroup);
            foreach ($slugs as $slug) {
                $slug = trim($slug);
                if ($user->hasPermission($slug)) {
                    $hasAnyPermission = true;
                    break 2;
                }
            }
        }

        if (!$hasAnyPermission) {
            abort(403, 'Unauthorized action. You do not have the required permissions to access this resource.');
        }

        return $next($request);
    }
}
