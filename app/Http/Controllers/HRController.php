<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SalesTarget;
use App\Models\LeaveRequest;
use App\Models\Branch;
use App\Services\PerformanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HRController extends Controller
{
    public function __construct(private PerformanceService $performanceService) {}

    /* ─── Leaderboard ─────────────────────────────────────────────── */

    public function leaderboard(Request $request)
    {
        $month    = (int) $request->get('month', now()->month);
        $year     = (int) $request->get('year', now()->year);
        $branchId = $request->get('branch_id');

        $leaderboard = $this->performanceService->leaderboard($month, $year, $branchId ?: null);
        $branches    = Branch::orderBy('name')->get();

        return view('hr.leaderboard', compact('leaderboard', 'month', 'year', 'branches', 'branchId'));
    }

    /* ─── Sales Targets ───────────────────────────────────────────── */

    public function targets(Request $request)
    {
        $month    = (int) $request->get('month', now()->month);
        $year     = (int) $request->get('year', now()->year);

        $staff  = User::where('role', 'sales_executive')->orderBy('name')->get();
        $targets = SalesTarget::where('target_month', $month)
            ->where('target_year', $year)
            ->get()
            ->keyBy('user_id');

        return view('hr.targets', compact('staff', 'targets', 'month', 'year'));
    }

    public function storeTarget(Request $request)
    {
        $data = $request->validate([
            'user_id'        => 'required|exists:users,id',
            'target_month'   => 'required|integer|min:1|max:12',
            'target_year'    => 'required|integer|min:2020|max:2050',
            'leads_target'   => 'required|integer|min:0',
            'sales_target'   => 'required|integer|min:0',
            'revenue_target' => 'required|numeric|min:0',
        ]);

        SalesTarget::updateOrCreate(
            ['user_id' => $data['user_id'], 'target_month' => $data['target_month'], 'target_year' => $data['target_year']],
            $data
        );

        return back()->with('success', 'Sales target saved successfully.');
    }

    /* ─── Leave Requests ──────────────────────────────────────────── */

    public function leaveIndex(Request $request)
    {
        $user    = Auth::user();
        $isAdmin = in_array($user->role, ['super_admin', 'company_admin', 'hr', 'sales_manager']);

        $query = LeaveRequest::with('user', 'reviewer');

        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->latest()->paginate(20);

        return view('hr.leave.index', compact('leaves', 'isAdmin'));
    }

    public function leaveCreate()
    {
        return view('hr.leave.create', ['types' => LeaveRequest::TYPES]);
    }

    public function leaveStore(Request $request)
    {
        $data = $request->validate([
            'leave_type' => 'required|in:' . implode(',', array_keys(LeaveRequest::TYPES)),
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string|max:1000',
        ]);

        $start = Carbon::parse($data['start_date']);
        $end   = Carbon::parse($data['end_date']);
        $days  = $start->diffInWeekdays($end) + 1;

        LeaveRequest::create(array_merge($data, [
            'user_id'       => Auth::id(),
            'days_requested' => $days,
            'status'         => 'pending',
        ]));

        return redirect()->route('hr.leave.index')->with('success', 'Leave request submitted successfully.');
    }

    public function leaveReview(Request $request, LeaveRequest $leave)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['super_admin', 'company_admin', 'hr', 'sales_manager'])) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'status'       => 'required|in:approved,rejected',
            'review_notes' => 'nullable|string|max:500',
        ]);

        $leave->update(array_merge($data, [
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]));

        return back()->with('success', 'Leave request ' . $data['status'] . '.');
    }
}
