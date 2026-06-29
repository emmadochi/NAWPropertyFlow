<?php

namespace App\Services;

use App\Models\User;
use App\Models\SalesTarget;
use App\Models\Sale;
use App\Models\Lead;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PerformanceService
{
    /**
     * Get leaderboard for a given month/year, scoped optionally to a branch.
     */
    public function leaderboard(int $month, int $year, ?int $branchId = null): Collection
    {
        $query = User::where('role', 'sales_executive')
            ->withCount([
                'leads as leads_count' => fn ($q) => $q
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year),
                'sales as sales_count' => fn ($q) => $q
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year),
            ])
            ->withSum([
                'sales as revenue_total' => fn ($q) => $q
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year),
            ], 'deal_value');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get()->map(function (User $user) use ($month, $year) {
            $target = SalesTarget::where('user_id', $user->id)
                ->where('target_month', $month)
                ->where('target_year', $year)
                ->first();

            $user->target            = $target;
            $user->leads_pct         = $target && $target->leads_target > 0
                ? round(($user->leads_count / $target->leads_target) * 100)
                : null;
            $user->sales_pct         = $target && $target->sales_target > 0
                ? round(($user->sales_count / $target->sales_target) * 100)
                : null;
            $user->revenue_pct       = $target && $target->revenue_target > 0
                ? round(($user->revenue_total / $target->revenue_target) * 100)
                : null;

            return $user;
        })->sortByDesc('revenue_total')->values();
    }

    /**
     * Compute individual performance stats for a user over a date range.
     */
    public function userStats(User $user, Carbon $from, Carbon $to): array
    {
        $leads  = Lead::where('assigned_to', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $sales  = Sale::where('sales_officer_id', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $revenue = Sale::where('sales_officer_id', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->sum('deal_value');

        $conversionRate = $leads > 0 ? round(($sales / $leads) * 100, 1) : 0;

        return compact('leads', 'sales', 'revenue', 'conversionRate');
    }

    /**
     * Build monthly performance digest data for all sales executives.
     */
    public function monthlyDigest(int $month, int $year): array
    {
        $leaderboard = $this->leaderboard($month, $year);
        $topPerformer = $leaderboard->first();
        $totalRevenue = $leaderboard->sum('revenue_total');
        $totalSales   = $leaderboard->sum('sales_count');

        return compact('leaderboard', 'topPerformer', 'totalRevenue', 'totalSales', 'month', 'year');
    }
}
