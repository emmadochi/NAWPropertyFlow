<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentMetric;
use App\Models\DepartmentTarget;
use App\Models\StaffMetricSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffSubmissionController extends Controller
{
    /**
     * Display a listing of submissions for the authenticated staff member.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        // Fetch user's department and its active manual metrics
        $department = $user->departmentRelation;
        $manualMetrics = collect();
        if ($department) {
            $manualMetrics = $department->metrics()
                ->where('is_active', true)
                ->where('type', 'manual')
                ->get();
        }

        // Fetch user's submissions for this month/year
        $submissions = StaffMetricSubmission::where('user_id', $user->id)
            ->where('submission_month', $month)
            ->where('submission_year', $year)
            ->with('metric')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('hr.submissions.index', compact('submissions', 'manualMetrics', 'department', 'month', 'year'));
    }

    /**
     * Store a new pending KPI submission from a staff member.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->department_id) {
            return back()->withErrors(['error' => 'You must be assigned to a department to log KPI metrics.']);
        }

        $validated = $request->validate([
            'department_metric_id' => 'required|exists:department_metrics,id',
            'value'                => 'required|numeric|min:0',
            'submission_month'     => 'required|integer|min:1|max:12',
            'submission_year'      => 'required|integer|min:2020|max:2050',
            'notes'                => 'nullable|string',
        ]);

        // Ensure the metric belongs to the user's department and is manual
        $metric = DepartmentMetric::where('id', $validated['department_metric_id'])
            ->where('department_id', $user->department_id)
            ->where('type', 'manual')
            ->firstOrFail();

        StaffMetricSubmission::create([
            'user_id'              => $user->id,
            'department_id'        => $user->department_id,
            'department_metric_id' => $metric->id,
            'value'                => $validated['value'],
            'submission_month'     => $validated['submission_month'],
            'submission_year'      => $validated['submission_year'],
            'status'               => 'pending',
            'notes'                => $validated['notes'],
        ]);

        return redirect()->route('hr.submissions.index')->with('success', 'KPI submission logged successfully and is pending HOD approval.');
    }

    /**
     * Show HOD approval queue.
     */
    public function hodIndex(Request $request)
    {
        $user = Auth::user();
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        // Admins can see all submissions; HODs can only see their department's submissions
        if ($user->hasRole(['super_admin', 'company_admin', 'hr'])) {
            $departments = Department::where('is_active', true)->get();
            $deptIds = $departments->pluck('id')->toArray();
        } else {
            // Find departments managed by this HOD
            $departments = Department::where('hod_id', $user->id)->where('is_active', true)->get();
            $deptIds = $departments->pluck('id')->toArray();

            if (empty($deptIds)) {
                abort(403, 'You are not assigned as Head of Department for any active business unit.');
            }
        }

        $submissions = StaffMetricSubmission::whereIn('department_id', $deptIds)
            ->where('submission_month', $month)
            ->where('submission_year', $year)
            ->with(['user', 'department', 'metric'])
            ->orderBy('status', 'asc') // pending first
            ->orderBy('created_at', 'desc')
            ->get();

        return view('hr.submissions.hod-review', compact('submissions', 'departments', 'month', 'year'));
    }

    /**
     * Approve a pending submission.
     */
    public function approve(StaffMetricSubmission $submission)
    {
        $user = Auth::user();

        // Check permission (Admin or department's HOD)
        if (!$user->hasRole(['super_admin', 'company_admin', 'hr']) && $submission->department->hod_id !== $user->id) {
            abort(403, 'Unauthorized approval action.');
        }

        $submission->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Aggregate and update the actual value in department targets
        $this->updateDepartmentActual(
            $submission->department_id,
            $submission->metric->key,
            $submission->submission_month,
            $submission->submission_year
        );

        return back()->with('success', 'Submission approved successfully and metrics aggregated.');
    }

    /**
     * Reject a pending submission.
     */
    public function reject(Request $request, StaffMetricSubmission $submission)
    {
        $user = Auth::user();

        // Check permission (Admin or department's HOD)
        if (!$user->hasRole(['super_admin', 'company_admin', 'hr']) && $submission->department->hod_id !== $user->id) {
            abort(403, 'Unauthorized rejection action.');
        }

        $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        $submission->update([
            'status' => 'rejected',
            'notes' => $request->notes,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Re-aggregate (in case it was previously approved and now rejected)
        $this->updateDepartmentActual(
            $submission->department_id,
            $submission->metric->key,
            $submission->submission_month,
            $submission->submission_year
        );

        return back()->with('success', 'Submission has been rejected with feedback notes.');
    }

    /**
     * Helper to sum all approved submissions and update department_targets
     */
    protected function updateDepartmentActual($departmentId, $metricKey, $month, $year)
    {
        $metric = DepartmentMetric::where('department_id', $departmentId)->where('key', $metricKey)->first();
        if (!$metric) return;

        // Sum all approved submissions
        $sum = StaffMetricSubmission::where('department_id', $departmentId)
            ->where('department_metric_id', $metric->id)
            ->where('submission_month', $month)
            ->where('submission_year', $year)
            ->where('status', 'approved')
            ->sum('value');

        $dept = Department::findOrFail($departmentId);

        $target = DepartmentTarget::where([
            'department_id' => $departmentId,
            'target_month'  => $month,
            'target_year'   => $year,
            'metric'        => $metricKey
        ])->first();

        $targetValue = $target ? $target->target_value : 0;

        // Update or create department target actual_value
        DepartmentTarget::updateOrCreate(
            [
                'department_id' => $departmentId,
                'target_month'  => $month,
                'target_year'   => $year,
                'metric'        => $metricKey
            ],
            [
                'department'    => $dept->name, // legacy fallback
                'actual_value'  => $sum,
                'target_value'  => $targetValue,
            ]
        );
    }
}
