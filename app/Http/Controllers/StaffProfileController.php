<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StaffCertification;
use App\Models\DisciplinaryRecord;
use App\Models\PerformanceReview;
use App\Services\PerformanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffProfileController extends Controller
{
    public function __construct(private PerformanceService $performanceService) {}

    /* ─── Staff Profile Overview ──────────────────────────────────── */

    public function show(User $user)
    {
        $from  = now()->startOfMonth();
        $to    = now()->endOfMonth();

        $stats         = $this->performanceService->userStats($user, $from, $to);
        $certifications = StaffCertification::where('user_id', $user->id)->latest()->get();
        $disciplinary  = DisciplinaryRecord::where('user_id', $user->id)->latest()->get();
        $reviews       = PerformanceReview::where('user_id', $user->id)->latest()->get();
        $onboardingTasks = \App\Models\OnboardingTask::where('user_id', $user->id)->orderBy('id')->get();

        return view('hr.staff.show', compact('user', 'stats', 'certifications', 'disciplinary', 'reviews', 'onboardingTasks'));
    }

    /* ─── Certifications ──────────────────────────────────────────── */

    public function storeCertification(Request $request, User $user)
    {
        $data = $request->validate([
            'title'              => 'required|string|max:200',
            'issuing_body'       => 'nullable|string|max:200',
            'issued_date'        => 'nullable|date',
            'expiry_date'        => 'nullable|date|after_or_equal:issued_date',
            'certificate_number' => 'nullable|string|max:100',
            'notes'              => 'nullable|string|max:1000',
        ]);

        StaffCertification::create(array_merge($data, ['user_id' => $user->id]));

        return back()->with('success', 'Certification added.');
    }

    public function destroyCertification(StaffCertification $certification)
    {
        $certification->delete();
        return back()->with('success', 'Certification removed.');
    }

    /* ─── Disciplinary Records ────────────────────────────────────── */

    public function storeDisciplinary(Request $request, User $user)
    {
        $data = $request->validate([
            'incident_type' => 'required|in:' . implode(',', array_keys(DisciplinaryRecord::TYPES)),
            'incident_date' => 'required|date',
            'description'   => 'required|string',
            'action_taken'  => 'required|string',
        ]);

        DisciplinaryRecord::create(array_merge($data, [
            'user_id'   => $user->id,
            'issued_by' => Auth::id(),
            'status'    => 'open',
        ]));

        return back()->with('success', 'Disciplinary record added.');
    }

    /* ─── Performance Reviews ─────────────────────────────────────── */

    public function storeReview(Request $request, User $user)
    {
        $data = $request->validate([
            'review_period'          => 'required|string|max:50',
            'score'                  => 'nullable|integer|min:0|max:100',
            'rating'                 => 'nullable|in:' . implode(',', array_keys(PerformanceReview::RATINGS)),
            'strengths'              => 'nullable|string',
            'areas_for_improvement'  => 'nullable|string',
            'goals_next_period'      => 'nullable|string',
            'manager_comments'       => 'nullable|string',
        ]);

        PerformanceReview::create(array_merge($data, [
            'user_id'     => $user->id,
            'reviewed_by' => Auth::id(),
            'status'      => 'submitted',
        ]));

        return back()->with('success', 'Performance review submitted.');
    }

    /* ─── Onboarding Tasks ────────────────────────────────────────── */

    public function storeOnboardingTask(Request $request, User $user)
    {
        $data = $request->validate([
            'task_name' => 'required|string|max:255',
            'due_date'  => 'nullable|date',
        ]);

        \App\Models\OnboardingTask::create(array_merge($data, [
            'user_id' => $user->id,
            'assigned_by' => Auth::id() ?? 1,
            'is_completed' => false,
        ]));

        return back()->with('success', 'Onboarding task added.');
    }

    public function toggleOnboardingTask(Request $request, \App\Models\OnboardingTask $task)
    {
        $user = Auth::user();
        if ($task->user_id !== $user->id && !in_array($user->role, ['super_admin', 'company_admin', 'hr', 'sales_manager'])) {
            abort(403, 'Unauthorized.');
        }

        $task->update([
            'is_completed' => !$task->is_completed,
            'completed_at' => !$task->is_completed ? now() : null,
        ]);

        return back()->with('success', 'Onboarding task updated.');
    }

    public function destroyOnboardingTask(\App\Models\OnboardingTask $task)
    {
        $task->delete();
        return back()->with('success', 'Onboarding task removed.');
    }
}
