<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScopeBranchData
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Allow admins to switch active branch via query parameter
            if ($user->isSuperAdmin() || $user->isCompanyAdmin()) {
                if ($request->has('switch_branch_id')) {
                    $branchId = $request->query('switch_branch_id');
                    
                    if ($branchId === 'all') {
                        session(['selected_branch_id' => 'all']);
                    } else {
                        session(['selected_branch_id' => (int) $branchId]);
                    }
                    
                    // Redirect to the same URL without the query parameter to keep URL clean
                    return redirect($request->url());
                }
            }
        }

        return $next($request);
    }
}
