<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Inspection;
use App\Models\FollowUp;
use App\Models\Sale;
use App\Models\PaymentMilestone;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RetailPerformanceController extends Controller
{
    /**
     * Display the Retail Team Weekly & Monthly Performance Scorecard.
     */
    public function index(Request $request)
    {
        $periodType = $request->get('period_type', 'weekly');
        $year = (int) $request->get('year', now()->year);
        $week = (int) $request->get('week', now()->weekOfYear);
        $month = (int) $request->get('month', now()->month);
        $branchId = $request->get('branch_id');

        // Determine date boundaries
        if ($periodType === 'monthly') {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            $periodLabel = $startDate->format('F Y');
        } else {
            $startDate = Carbon::now()->setISODate($year, $week)->startOfWeek();
            $endDate = Carbon::now()->setISODate($year, $week)->endOfWeek();
            $periodLabel = "Week {$week} ({$startDate->format('d M')} - {$endDate->format('d M, Y')})";
        }

        $currentUser = Auth::user();
        $isExecutive = in_array($currentUser->role, ['sales_executive', 'sales_agent', 'marketer']);

        // Query sales consultants / retail marketers
        $consultantsQuery = User::where(function($q) {
            $q->whereIn('role', ['sales_executive', 'sales_agent', 'sales_manager', 'marketer'])
              ->orWhere('job_title', 'like', '%Sales%')
              ->orWhere('job_title', 'like', '%Marketer%')
              ->orWhere('job_title', 'like', '%Consultant%');
        })->where(function($q) {
            $q->where('status', 'active')->orWhereNull('status');
        });

        if ($branchId) {
            $consultantsQuery->where('branch_id', $branchId);
        } elseif ($currentUser->branch_id && $currentUser->role !== 'super_admin' && $currentUser->role !== 'company_admin') {
            $consultantsQuery->where('branch_id', $currentUser->branch_id);
        }

        $consultants = $consultantsQuery->orderBy('name', 'asc')->get();

        // Compute metrics per consultant
        $scorecard = [];
        $aggregates = [
            'leads_captured'        => 0,
            'calls_logged'          => 0,
            'whatsapp_logged'       => 0,
            'inspections_scheduled' => 0,
            'inspections_completed' => 0,
            'office_visits'         => 0,
            'new_sales_count'       => 0,
            'new_sales_value'       => 0,
            'milestone_collections' => 0,
            'expected_collections'  => 0,
            'daily_leads'           => ['Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0, 'Fri' => 0, 'Sat' => 0, 'Sun' => 0],
        ];

        foreach ($consultants as $consultant) {
            $leadsCaptured = Lead::where('assigned_to', $consultant->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $callsLogged = LeadActivity::where('user_id', $consultant->id)
                ->where(function($q) {
                    $q->where('activity_type', 'like', '%Call%')
                      ->orWhere('description', 'like', '%Call%');
                })
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $whatsappLogged = LeadActivity::where('user_id', $consultant->id)
                ->where(function($q) {
                    $q->where('activity_type', 'like', '%WhatsApp%')
                      ->orWhere('description', 'like', '%WhatsApp%');
                })
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $inspectionsSched = Inspection::where('assigned_to', $consultant->id)
                ->whereBetween('inspection_date', [$startDate, $endDate])
                ->count();

            $inspectionsComp = Inspection::where('assigned_to', $consultant->id)
                ->where('status', 'Completed')
                ->whereBetween('inspection_date', [$startDate, $endDate])
                ->count();

            $officeVisits = LeadActivity::where('user_id', $consultant->id)
                ->where(function($q) {
                    $q->where('activity_type', 'like', '%Office%')
                      ->orWhere('description', 'like', '%Office Visit%');
                })
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $newSalesCount = Sale::where('sales_officer_id', $consultant->id)
                ->where('status', 'Closed Won')
                ->whereBetween('deal_closed_at', [$startDate, $endDate])
                ->count();

            $newSalesValue = (float) Sale::where('sales_officer_id', $consultant->id)
                ->where('status', 'Closed Won')
                ->whereBetween('deal_closed_at', [$startDate, $endDate])
                ->sum('deal_value');

            // Recurring installment top-ups collected
            $milestoneCollections = (float) PaymentMilestone::whereHas('paymentPlan.sale', function($q) use ($consultant) {
                $q->where('sales_officer_id', $consultant->id);
            })->where('status', 'Paid')
              ->whereBetween('paid_at', [$startDate, $endDate])
              ->sum('amount');

            // Expected installment inflows due in this period
            $expectedCollections = (float) PaymentMilestone::whereHas('paymentPlan.sale', function($q) use ($consultant) {
                $q->where('sales_officer_id', $consultant->id);
            })->whereIn('status', ['Pending', 'Overdue'])
              ->whereBetween('due_date', [$startDate, $endDate])
              ->sum('amount');

            $outreachLocations = Lead::where('assigned_to', $consultant->id)
                ->whereNotNull('outreach_location')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->pluck('outreach_location')
                ->unique()
                ->values()
                ->all();

            // Daily Leads Ingestion Breakdown (Mon - Sun)
            $dailyLeadCounts = [
                'Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0, 'Fri' => 0, 'Sat' => 0, 'Sun' => 0
            ];
            $leadDayData = Lead::where('assigned_to', $consultant->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE_FORMAT(created_at, "%a") as day_name, COUNT(*) as cnt')
                ->groupBy('day_name')
                ->pluck('cnt', 'day_name')
                ->toArray();

            foreach ($leadDayData as $dName => $cnt) {
                if (isset($dailyLeadCounts[$dName])) {
                    $dailyLeadCounts[$dName] = (int) $cnt;
                }
            }

            $row = [
                'user'                  => $consultant,
                'leads_captured'        => $leadsCaptured,
                'daily_lead_counts'     => $dailyLeadCounts,
                'calls_logged'          => $callsLogged,
                'whatsapp_logged'       => $whatsappLogged,
                'inspections_scheduled' => $inspectionsSched,
                'inspections_completed' => $inspectionsComp,
                'office_visits'         => $officeVisits,
                'new_sales_count'       => $newSalesCount,
                'new_sales_value'       => $newSalesValue,
                'milestone_collections' => $milestoneCollections,
                'expected_collections'  => $expectedCollections,
                'total_revenue'         => $newSalesValue + $milestoneCollections,
                'outreach_locations'    => $outreachLocations,
            ];

            $scorecard[] = $row;

            // Update team aggregates
            $aggregates['leads_captured']        += $leadsCaptured;
            $aggregates['calls_logged']          += $callsLogged;
            $aggregates['whatsapp_logged']       += $whatsappLogged;
            $aggregates['inspections_scheduled'] += $inspectionsSched;
            $aggregates['inspections_completed'] += $inspectionsComp;
            $aggregates['office_visits']         += $officeVisits;
            $aggregates['new_sales_count']       += $newSalesCount;
            $aggregates['new_sales_value']       += $newSalesValue;
            $aggregates['milestone_collections'] += $milestoneCollections;
            $aggregates['expected_collections']  += $expectedCollections;

            foreach ($dailyLeadCounts as $dName => $cnt) {
                $aggregates['daily_leads'][$dName] = ($aggregates['daily_leads'][$dName] ?? 0) + $cnt;
            }
        }

        // Sort by total revenue or leads captured
        usort($scorecard, function($a, $b) {
            return ($b['total_revenue'] <=> $a['total_revenue']) ?: ($b['leads_captured'] <=> $a['leads_captured']);
        });

        $branches = Branch::orderBy('name', 'asc')->get();

        return view('reports.retail_weekly', compact(
            'scorecard',
            'aggregates',
            'periodType',
            'year',
            'week',
            'month',
            'periodLabel',
            'startDate',
            'endDate',
            'branches',
            'branchId',
            'isExecutive',
            'currentUser'
        ));
    }

    /**
     * Export the Retail Performance Scorecard to CSV.
     */
    public function export(Request $request)
    {
        $periodType = $request->get('period_type', 'weekly');
        $year = (int) $request->get('year', now()->year);
        $week = (int) $request->get('week', now()->weekOfYear);
        $month = (int) $request->get('month', now()->month);
        $branchId = $request->get('branch_id');

        if ($periodType === 'monthly') {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            $fileName = "retail_performance_{$year}_month_{$month}.csv";
        } else {
            $startDate = Carbon::now()->setISODate($year, $week)->startOfWeek();
            $endDate = Carbon::now()->setISODate($year, $week)->endOfWeek();
            $fileName = "retail_performance_{$year}_week_{$week}.csv";
        }

        $headers = [
            'Content-type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $columns = [
            'Sales Consultant',
            'Branch',
            'Outreach Location(s)',
            'Leads Captured',
            'Mon Leads',
            'Tue Leads',
            'Wed Leads',
            'Thu Leads',
            'Fri Leads',
            'Sat Leads',
            'Sun Leads',
            'Calls Logged',
            'WhatsApp Chats',
            'Site Inspections (Completed)',
            'Site Inspections (Scheduled)',
            'Office Visits',
            'New Sales (Deals)',
            'New Sales Value (₦)',
            'Milestone Collections/Top-ups (₦)',
            'Expected Collections (₦)',
            'Total Cash Inflow (₦)',
        ];

        $callback = function() use ($columns, $startDate, $endDate, $branchId) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $consultantsQuery = User::where(function($q) {
                $q->whereIn('role', ['sales_executive', 'sales_agent', 'sales_manager', 'marketer'])
                  ->orWhere('job_title', 'like', '%Sales%');
            });
            if ($branchId) {
                $consultantsQuery->where('branch_id', $branchId);
            }
            $consultants = $consultantsQuery->orderBy('name', 'asc')->get();

            foreach ($consultants as $consultant) {
                $leadsCaptured = Lead::where('assigned_to', $consultant->id)
                    ->whereBetween('created_at', [$startDate, $endDate])->count();

                $dailyLeadCounts = [
                    'Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0, 'Fri' => 0, 'Sat' => 0, 'Sun' => 0
                ];
                $leadDayData = Lead::where('assigned_to', $consultant->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->selectRaw('DATE_FORMAT(created_at, "%a") as day_name, COUNT(*) as cnt')
                    ->groupBy('day_name')
                    ->pluck('cnt', 'day_name')
                    ->toArray();

                foreach ($leadDayData as $dName => $cnt) {
                    if (isset($dailyLeadCounts[$dName])) {
                        $dailyLeadCounts[$dName] = (int) $cnt;
                    }
                }

                $callsLogged = LeadActivity::where('user_id', $consultant->id)
                    ->where(function($q) { $q->where('activity_type', 'like', '%Call%')->orWhere('description', 'like', '%Call%'); })
                    ->whereBetween('created_at', [$startDate, $endDate])->count();

                $whatsappLogged = LeadActivity::where('user_id', $consultant->id)
                    ->where(function($q) { $q->where('activity_type', 'like', '%WhatsApp%')->orWhere('description', 'like', '%WhatsApp%'); })
                    ->whereBetween('created_at', [$startDate, $endDate])->count();

                $inspectionsComp = Inspection::where('assigned_to', $consultant->id)
                    ->where('status', 'Completed')
                    ->whereBetween('inspection_date', [$startDate, $endDate])->count();

                $inspectionsSched = Inspection::where('assigned_to', $consultant->id)
                    ->whereBetween('inspection_date', [$startDate, $endDate])->count();

                $officeVisits = LeadActivity::where('user_id', $consultant->id)
                    ->where(function($q) { $q->where('activity_type', 'like', '%Office%')->orWhere('description', 'like', '%Office Visit%'); })
                    ->whereBetween('created_at', [$startDate, $endDate])->count();

                $newSalesCount = Sale::where('sales_officer_id', $consultant->id)
                    ->where('status', 'Closed Won')
                    ->whereBetween('deal_closed_at', [$startDate, $endDate])->count();

                $newSalesValue = (float) Sale::where('sales_officer_id', $consultant->id)
                    ->where('status', 'Closed Won')
                    ->whereBetween('deal_closed_at', [$startDate, $endDate])->sum('deal_value');

                $milestoneCollections = (float) PaymentMilestone::whereHas('paymentPlan.sale', function($q) use ($consultant) {
                    $q->where('sales_officer_id', $consultant->id);
                })->where('status', 'Paid')->whereBetween('paid_at', [$startDate, $endDate])->sum('amount');

                $expectedCollections = (float) PaymentMilestone::whereHas('paymentPlan.sale', function($q) use ($consultant) {
                    $q->where('sales_officer_id', $consultant->id);
                })->whereIn('status', ['Pending', 'Overdue'])->whereBetween('due_date', [$startDate, $endDate])->sum('amount');

                $outreachLocations = Lead::where('assigned_to', $consultant->id)
                    ->whereNotNull('outreach_location')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->pluck('outreach_location')->unique()->implode(', ');

                fputcsv($file, [
                    $consultant->name,
                    $consultant->branch ? $consultant->branch->name : 'Main',
                    $outreachLocations ?: 'N/A',
                    $leadsCaptured,
                    $dailyLeadCounts['Mon'],
                    $dailyLeadCounts['Tue'],
                    $dailyLeadCounts['Wed'],
                    $dailyLeadCounts['Thu'],
                    $dailyLeadCounts['Fri'],
                    $dailyLeadCounts['Sat'],
                    $dailyLeadCounts['Sun'],
                    $callsLogged,
                    $whatsappLogged,
                    $inspectionsComp,
                    $inspectionsSched,
                    $officeVisits,
                    $newSalesCount,
                    $newSalesValue,
                    $milestoneCollections,
                    $expectedCollections,
                    $newSalesValue + $milestoneCollections,
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
