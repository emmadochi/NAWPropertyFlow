<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Sale;
use App\Models\Campaign;
use App\Models\ProjectMilestone;
use App\Models\Inspection;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\FollowUp;
use App\Models\Department;
use App\Models\DepartmentTarget;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DepartmentReportController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        // Date boundaries
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Fetch targets grouped by department_id
        $targets = DepartmentTarget::where('target_month', $month)
            ->where('target_year', $year)
            ->get()
            ->groupBy('department_id');

        // Compile reports dynamically
        $reports = [];
        $activeDepartments = Department::where('is_active', true)->with('metrics')->get();

        foreach ($activeDepartments as $dept) {
            $metricsData = [];
            foreach ($dept->metrics as $metric) {
                if (!$metric->is_active) {
                    continue;
                }

                $targetObj = $targets->get($dept->id)?->firstWhere('metric', $metric->key);
                $targetVal = $targetObj ? (float)$targetObj->target_value : null;

                // Determine actual value
                if ($metric->type === 'system') {
                    if ($metric->key === 'revenue') {
                        $actual = (float) Sale::where('status', 'Closed Won')
                            ->whereBetween('deal_closed_at', [$startDate, $endDate])
                            ->sum('deal_value');
                    } else if ($metric->key === 'deals_closed') {
                        $actual = (int) Sale::where('status', 'Closed Won')
                            ->whereBetween('deal_closed_at', [$startDate, $endDate])
                            ->count();
                    } else if ($metric->key === 'leads_contacted') {
                        $actual = (int) FollowUp::where('status', 'Completed')
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->count();
                    } else if ($metric->key === 'campaigns_sent') {
                        $actual = (int) Campaign::whereBetween('created_at', [$startDate, $endDate])->count();
                    } else if ($metric->key === 'leads_generated') {
                        $actual = (int) Lead::whereBetween('created_at', [$startDate, $endDate])->count();
                    } else if ($metric->key === 'milestones_completed') {
                        $actual = (int) ProjectMilestone::where('status', 'completed')
                            ->whereBetween('completed_date', [$startDate, $endDate])
                            ->count();
                    } else if ($metric->key === 'inspections_conducted') {
                        $actual = (int) Inspection::where('status', 'Completed')
                            ->whereBetween('inspection_date', [$startDate, $endDate])
                            ->count();
                    } else if ($metric->key === 'leaves_processed') {
                        $actual = (int) LeaveRequest::whereIn('status', ['approved', 'rejected'])
                            ->whereBetween('reviewed_at', [$startDate, $endDate])
                            ->count();
                    } else if ($metric->key === 'users_created') {
                        $actual = (int) User::whereBetween('created_at', [$startDate, $endDate])->count();
                    } else {
                        $actual = 0;
                    }
                } else {
                    // Manual metric: fetch from target's actual_value
                    $actual = $targetObj ? (float)$targetObj->actual_value : 0;
                }

                $metricsData[$metric->key] = [
                    'label' => $metric->label,
                    'actual' => $actual,
                    'target' => $targetVal,
                    'unit' => $metric->unit,
                ];
            }

            $reports[$dept->name] = [
                'icon' => $dept->icon ?? '🏢',
                'metrics' => $metricsData
            ];
        }

        return view('reports.departments', compact('reports', 'month', 'year'));
    }
}
