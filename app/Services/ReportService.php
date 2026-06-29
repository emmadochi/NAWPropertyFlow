<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Property;
use App\Models\Inspection;
use App\Models\FollowUp;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Gather dashboard metrics and charts datasets.
     */
    public function getDashboardData(?int $officerId = null, ?string $startDate = null, ?string $endDate = null, ?string $branchId = null): array
    {
        $isMediaManager = \Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->isMediaManager();

        if ($isMediaManager) {
            $campaignQuery = \App\Models\Campaign::query();
            
            $totalCampaigns = (clone $campaignQuery)->count();
            $totalEmailsSent = (clone $campaignQuery)->sum('sent_count');
            
            $totalOpens = (clone $campaignQuery)->sum('opened_count');
            $avgOpenRate = $totalEmailsSent > 0 ? round(($totalOpens / $totalEmailsSent) * 100, 1) : 0;
            
            $totalClicks = (clone $campaignQuery)->sum('clicked_count');
            $avgClickRate = $totalEmailsSent > 0 ? round(($totalClicks / $totalEmailsSent) * 100, 1) : 0;
            
            $recentCampaigns = (clone $campaignQuery)->orderBy('created_at', 'desc')->limit(5)->get();

            $mediaTargets = \App\Models\DepartmentTarget::whereHas('department', function($q) {
                $q->where('name', 'like', '%Media%')->orWhere('name', 'like', '%Marketing%');
            })->where('target_month', Carbon::now()->month)
              ->where('target_year', Carbon::now()->year)
              ->get();

            $sourcePerformance = Lead::selectRaw('lead_source, count(id) as count')
                ->groupBy('lead_source')
                ->orderBy('count', 'desc')
                ->get();
                
            return [
                'is_media_dashboard' => true,
                'metrics' => [
                    'total_campaigns' => $totalCampaigns,
                    'total_emails_sent' => $totalEmailsSent,
                    'avg_open_rate' => $avgOpenRate,
                    'avg_click_rate' => $avgClickRate,
                ],
                'source_performance' => $sourcePerformance,
                'recent_campaigns' => $recentCampaigns,
                'media_targets' => $mediaTargets,
            ];
        }

        $leadQuery = Lead::query();
        $inspectionQuery = Inspection::whereHas('lead');
        $followUpQuery = FollowUp::whereHas('lead');
        $saleQuery = Sale::whereHas('lead');

        // If an officer is logged in and is NOT an admin/manager, filter data to their assigned leads
        if ($officerId) {
            $leadQuery->where('assigned_to', $officerId);
            $inspectionQuery->where('assigned_to', $officerId);
            $followUpQuery->whereHas('lead', function($q) use ($officerId) {
                $q->where('assigned_to', $officerId);
            });
            $saleQuery->where('sales_officer_id', $officerId);
        }

        // Apply Branch Filters
        if ($branchId && $branchId !== 'all') {
            $leadQuery->where('branch_id', $branchId);
            $inspectionQuery->whereHas('lead', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
            $followUpQuery->whereHas('lead', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
            $saleQuery->whereHas('lead', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        // Apply Date Filters
        if ($startDate && $endDate) {
            $leadQuery->whereBetween('created_at', [$startDate, $endDate]);
            $inspectionQuery->whereBetween('inspection_date', [$startDate, $endDate]);
            $followUpQuery->whereBetween('due_date', [$startDate, $endDate]);
            $saleQuery->whereBetween('deal_closed_at', [$startDate, $endDate]);
        }

        // 1. KPI Counters
        $totalLeads = (clone $leadQuery)->count();
        $newLeads = (clone $leadQuery)->where('status', 'New')->count();
        
        $followUpsDue = (clone $followUpQuery)->where('status', 'Pending')
            ->whereDate('due_date', '<=', Carbon::tomorrow())
            ->count();

        $scheduledInspections = (clone $inspectionQuery)->where('status', 'Scheduled')->count();
        $closedDeals = (clone $saleQuery)->where('status', 'Closed Won')->count();
        $totalRevenue = (clone $saleQuery)->where('status', 'Closed Won')->sum('deal_value');

        // 2. Leads by Month (last 6 months)
        $leadsByMonth = (clone $leadQuery)
            ->selectRaw('count(id) as count, DATE_FORMAT(created_at, "%b %Y") as month_name, YEAR(created_at) as yr, MONTH(created_at) as mo')
            ->where('created_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('yr', 'mo', 'month_name')
            ->orderBy('yr', 'asc')
            ->orderBy('mo', 'asc')
            ->get();

        // 3. Sales by Month (last 6 months)
        $salesByMonth = (clone $saleQuery)
            ->selectRaw('sum(deal_value) as total, count(id) as count, DATE_FORMAT(deal_closed_at, "%b %Y") as month_name, YEAR(deal_closed_at) as yr, MONTH(deal_closed_at) as mo')
            ->where('deal_closed_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->where('status', 'Closed Won')
            ->groupBy('yr', 'mo', 'month_name')
            ->orderBy('yr', 'asc')
            ->orderBy('mo', 'asc')
            ->get();

        // 4. Lead Source Performance
        $sourcePerformance = (clone $leadQuery)
            ->selectRaw('lead_source, count(id) as count')
            ->groupBy('lead_source')
            ->orderBy('count', 'desc')
            ->get();

        // 5. Pending Follow-Ups List (Due Today, Tomorrow, Overdue)
        $pendingFollowUps = (clone $followUpQuery)->where('status', 'Pending')
            ->with(['lead'])
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        // 6. Recent Inspections
        $upcomingInspections = (clone $inspectionQuery)->where('status', 'Scheduled')
            ->with(['lead', 'property'])
            ->orderBy('inspection_date', 'asc')
            ->limit(5)
            ->get();

        return [
            'metrics' => [
                'total_leads' => $totalLeads,
                'new_leads' => $newLeads,
                'follow_ups_due' => $followUpsDue,
                'scheduled_inspections' => $scheduledInspections,
                'closed_deals' => $closedDeals,
                'total_revenue' => $totalRevenue,
                'conversion_rate' => $totalLeads > 0 ? round(($closedDeals / $totalLeads) * 100, 1) : 0,
            ],
            'leads_by_month' => $leadsByMonth,
            'sales_by_month' => $salesByMonth,
            'source_performance' => $sourcePerformance,
            'pending_follow_ups' => $pendingFollowUps,
            'upcoming_inspections' => $upcomingInspections,
        ];
    }

    /**
     * Get Sales Officer Performance Leaderboard.
     */
    public function getLeaderboard(): array
    {
        $query = User::whereIn('role', ['sales_executive', 'sales_manager']);

        if (\Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user->isSuperAdmin() || $user->isCompanyAdmin()) {
                if (session()->has('selected_branch_id') && session('selected_branch_id') !== 'all') {
                    $query->where('branch_id', session('selected_branch_id'));
                }
            } else {
                $query->where('branch_id', $user->branch_id);
            }
        }

        return $query->withCount(['sales as closed_deals' => function($q) {
                $q->where('status', 'Closed Won');
            }])
            ->withSum(['sales as total_revenue' => function($q) {
                $q->where('status', 'Closed Won');
            }], 'deal_value')
            ->orderBy('total_revenue', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Reports: Leads by Source details.
     */
    public function getLeadsBySourceReport(?string $startDate = null, ?string $endDate = null, ?string $branchId = null)
    {
        $query = Lead::query();

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->selectRaw('
            lead_source,
            count(id) as total_leads,
            sum(case when status in ("New", "Contacted", "Follow Up", "Inspection") then 1 else 0 end) as active_leads,
            sum(case when status = "Lost" then 1 else 0 end) as lost_leads,
            sum(case when status = "Closed Won" then 1 else 0 end) as won_leads
        ')
        ->groupBy('lead_source')
        ->orderBy('total_leads', 'desc')
        ->get()
        ->map(function($row) {
            $row->conversion_rate = $row->total_leads > 0 
                ? round(($row->won_leads / $row->total_leads) * 100, 1) 
                : 0;
            return $row;
        });
    }

    /**
     * Reports: Sales by Agent details.
     */
    public function getSalesByAgentReport(?string $startDate = null, ?string $endDate = null, ?string $branchId = null)
    {
        $query = User::whereIn('role', ['sales_executive', 'sales_manager']);

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        return $query->withCount(['sales as deals_closed' => function($q) use ($startDate, $endDate) {
            $q->where('status', 'Closed Won');
            if ($startDate && $endDate) {
                $q->whereBetween('deal_closed_at', [$startDate, $endDate]);
            }
        }])
        ->withSum(['sales as gross_revenue' => function($q) use ($startDate, $endDate) {
            $q->where('status', 'Closed Won');
            if ($startDate && $endDate) {
                $q->whereBetween('deal_closed_at', [$startDate, $endDate]);
            }
        }], 'deal_value')
        ->orderBy('gross_revenue', 'desc')
        ->get()
        ->map(function($user) {
            $user->avg_deal_value = $user->deals_closed > 0 
                ? round($user->gross_revenue / $user->deals_closed, 2) 
                : 0;
            return $user;
        });
    }

    /**
     * Reports: Follow-Up compliance by agent.
     */
    public function getFollowUpComplianceReport(?string $startDate = null, ?string $endDate = null, ?string $branchId = null)
    {
        $query = FollowUp::join('leads', 'follow_ups.lead_id', '=', 'leads.id')
            ->join('users', 'leads.assigned_to', '=', 'users.id')
            ->selectRaw('
                users.id as user_id,
                users.name as agent_name,
                count(follow_ups.id) as total,
                sum(case when follow_ups.status = "Completed" then 1 else 0 end) as completed,
                sum(case when follow_ups.status = "Pending" and follow_ups.due_date < NOW() then 1 else 0 end) as overdue
            ');

        if ($branchId && $branchId !== 'all') {
            $query->where('users.branch_id', $branchId);
        }
        if ($startDate && $endDate) {
            $query->whereBetween('follow_ups.due_date', [$startDate, $endDate]);
        }

        return $query->groupBy('users.id', 'users.name')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function($row) {
                $row->compliance_rate = $row->total > 0 
                    ? round(($row->completed / $row->total) * 100, 1) 
                    : 0;
                return $row;
            });
    }

    /**
     * Reports: Branch performance comparison table.
     */
    public function getBranchComparisonReport(?string $startDate = null, ?string $endDate = null)
    {
        return \App\Models\Branch::withCount([
            'leads as total_leads' => function($q) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                }
            },
            'leads as closed_deals' => function($q) use ($startDate, $endDate) {
                $q->where('status', 'Closed Won');
                if ($startDate && $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                }
            }
        ])->get()->map(function($branch) use ($startDate, $endDate) {
            $branch->gross_revenue = Sale::whereHas('lead', function($q) use ($branch, $startDate, $endDate) {
                $q->where('branch_id', $branch->id);
                if ($startDate && $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                }
            })->where('status', 'Closed Won')->sum('deal_value') ?? 0;

            $branch->conversion_rate = $branch->total_leads > 0 
                ? round(($branch->closed_deals / $branch->total_leads) * 100, 1) 
                : 0;

            // Get top agent name by closed sales revenue in this branch
            $topAgent = User::where('branch_id', $branch->id)
                ->withSum(['sales as total_sales' => function($q) use ($startDate, $endDate) {
                    $q->where('status', 'Closed Won');
                    if ($startDate && $endDate) {
                        $q->whereBetween('deal_closed_at', [$startDate, $endDate]);
                    }
                }], 'deal_value')
                ->orderBy('total_sales', 'desc')
                ->first();

            $branch->top_agent = $topAgent && $topAgent->total_sales > 0 
                ? "{$topAgent->name} (₦" . number_format($topAgent->total_sales) . ")" 
                : 'None';

            return $branch;
        });
    }
}
