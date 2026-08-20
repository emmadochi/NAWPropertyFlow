<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FollowUp;
use App\Models\Inspection;
use App\Models\Lead;
use App\Models\PaymentMilestone;
use App\Models\LeaveApplication;
use App\Models\StaffSubmission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Dedicated Notification Center View.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $officerId = $user->role === 'sales_executive' ? $user->id : null;
        $category = $request->get('category', 'all');

        $allNotifications = $this->compileAllAlerts($user, 50);

        if ($category !== 'all') {
            $allNotifications = array_filter($allNotifications, function($n) use ($category) {
                return $n['category'] === $category;
            });
        }

        $unreadCount = count($allNotifications);

        return view('notifications.index', compact('allNotifications', 'unreadCount', 'category'));
    }

    /**
     * API JSON endpoint for Top Bar dropdown & Sidebar live badge counters.
     */
    public function getAlerts()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'unread_count' => 0, 
                'alerts' => [], 
                'badges' => [
                    'leads' => 0, 
                    'milestones' => 0, 
                    'inspections' => 0, 
                    'hr' => 0
                ]
            ]);
        }

        $alerts = $this->compileAllAlerts($user, 8);
        $badges = $this->computeSidebarBadges($user);

        return response()->json([
            'unread_count' => count($alerts),
            'alerts' => $alerts,
            'badges' => $badges,
        ]);
    }

    /**
     * Core Aggregator: Compiles alerts across Payments, Leads, Inspections, Follow-ups, and Approvals.
     */
    protected function compileAllAlerts($user, $limit = 10)
    {
        $officerId = $user->role === 'sales_executive' ? $user->id : null;
        $isAdmin = in_array($user->role, ['super_admin', 'company_admin']);
        $alerts = [];

        // 1. Proof of Payment (POP) Uploaded / Pending Audit (High Priority for Admins)
        if ($isAdmin) {
            $popMilestones = PaymentMilestone::whereNotNull('proof_of_payment')
                ->whereNull('verified_at')
                ->with(['paymentPlan.sale.lead', 'paymentPlan.sale.property'])
                ->orderBy('pop_submitted_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($popMilestones as $pop) {
                $leadName = $pop->paymentPlan?->sale?->lead?->full_name ?? 'Client';
                $propName = $pop->paymentPlan?->sale?->property?->name ?? 'Estate Unit';
                $alerts[] = [
                    'id' => 'pop-' . $pop->id,
                    'type' => 'payment',
                    'category' => 'payments',
                    'icon' => '💳',
                    'title' => 'Proof of Payment Uploaded',
                    'description' => "{$leadName} uploaded POP (₦" . number_format($pop->amount_paid, 2) . ") for '{$pop->label}' in {$propName}. Awaiting verification.",
                    'time' => $pop->pop_submitted_at ? $pop->pop_submitted_at->diffForHumans() : 'Recently',
                    'url' => route('payments.milestones', $pop->payment_plan_id)
                ];
            }
        }

        // 2. Overdue & Pending Follow-Ups
        $followUpQuery = FollowUp::where('status', 'Pending');
        if ($officerId) {
            $followUpQuery->whereHas('lead', function($q) use ($officerId) {
                $q->where('assigned_to', $officerId);
            });
        } else {
            $followUpQuery->whereHas('lead');
        }
        $followUps = $followUpQuery->with('lead')
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        foreach ($followUps as $f) {
            $alerts[] = [
                'id' => 'followup-' . $f->id,
                'type' => 'followup',
                'category' => 'leads',
                'icon' => '📞',
                'title' => 'Follow-up Reminder',
                'description' => ($f->type ?: 'Call') . ' with ' . ($f->lead?->full_name ?? 'Lead') . ' is due (' . ($f->due_date ? $f->due_date->diffForHumans() : 'Today') . ')',
                'time' => $f->due_date ? $f->due_date->diffForHumans() : 'Due',
                'url' => route('leads.show', $f->lead_id)
            ];
        }

        // 3. Scheduled Inspections (Next 48 Hours)
        $inspectionQuery = Inspection::where('status', 'Scheduled')
            ->where('inspection_date', '>=', Carbon::now()->subHours(2))
            ->where('inspection_date', '<=', Carbon::now()->addHours(48))
            ->whereHas('lead');
        if ($officerId) {
            $inspectionQuery->where('assigned_to', $officerId);
        }
        $inspections = $inspectionQuery->with(['lead', 'property'])
            ->orderBy('inspection_date', 'asc')
            ->limit(5)
            ->get();

        foreach ($inspections as $i) {
            $alerts[] = [
                'id' => 'inspection-' . $i->id,
                'type' => 'inspection',
                'category' => 'inspections',
                'icon' => '🏡',
                'title' => 'Site Tour Scheduled',
                'description' => 'Tour at ' . ($i->property?->name ?? 'Estate') . ' with ' . ($i->lead?->full_name ?? 'Prospect'),
                'time' => $i->inspection_date ? $i->inspection_date->diffForHumans() : 'Upcoming',
                'url' => route('inspections.index')
            ];
        }

        // 4. New Assigned Leads (Within last 48 hours)
        $leadQuery = Lead::where('status', 'New')
            ->where('created_at', '>=', Carbon::now()->subHours(48));
        if ($officerId) {
            $leadQuery->where('assigned_to', $officerId);
        }
        $leads = $leadQuery->orderBy('created_at', 'desc')->limit(5)->get();

        foreach ($leads as $l) {
            $alerts[] = [
                'id' => 'lead-' . $l->id,
                'type' => 'lead',
                'category' => 'leads',
                'icon' => '🆕',
                'title' => 'New Prospect Captured',
                'description' => $l->full_name . ' (' . ($l->property_interest ?: 'General Inquiries') . ') is awaiting follow-up.',
                'time' => $l->created_at->diffForHumans(),
                'url' => route('leads.show', $l->id)
            ];
        }

        // 5. HR Staff Leave Requests (For Admin/HR)
        if ($isAdmin || $user->role === 'hr_manager') {
            $pendingLeaves = LeaveApplication::where('status', 'Pending')
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            foreach ($pendingLeaves as $leave) {
                $alerts[] = [
                    'id' => 'leave-' . $leave->id,
                    'type' => 'approval',
                    'category' => 'hr',
                    'icon' => '📝',
                    'title' => 'Leave Application Pending',
                    'description' => ($leave->user?->name ?? 'Staff') . " requested {$leave->type} ({$leave->days_count} days).",
                    'time' => $leave->created_at->diffForHumans(),
                    'url' => route('hr.leaves.index')
                ];
            }
        }

        return array_slice($alerts, 0, $limit);
    }

    /**
     * Computes live numerical badge counts for specific Sidebar links.
     */
    protected function computeSidebarBadges($user)
    {
        $officerId = $user->role === 'sales_executive' ? $user->id : null;
        $isAdmin = in_array($user->role, ['super_admin', 'company_admin']);

        // Leads badge: New leads count
        $leadsQuery = Lead::where('status', 'New');
        if ($officerId) {
            $leadsQuery->where('assigned_to', $officerId);
        }
        $newLeadsCount = $leadsQuery->count();

        // Milestone badge: Payments requiring admin audit or pending
        $milestonesCount = 0;
        if ($isAdmin) {
            $milestonesCount = PaymentMilestone::whereNotNull('proof_of_payment')
                ->whereNull('verified_at')
                ->count();
        }

        // Inspections badge: Scheduled in next 48 hrs
        $inspQuery = Inspection::where('status', 'Scheduled')
            ->where('inspection_date', '>=', Carbon::now()->subHours(2))
            ->where('inspection_date', '<=', Carbon::now()->addHours(48));
        if ($officerId) {
            $inspQuery->where('assigned_to', $officerId);
        }
        $inspectionsCount = $inspQuery->count();

        // HR badge: Pending leave requests
        $hrCount = 0;
        if ($isAdmin || $user->role === 'hr_manager') {
            $hrCount = LeaveApplication::where('status', 'Pending')->count();
        }

        return [
            'leads' => $newLeadsCount,
            'milestones' => $milestonesCount,
            'inspections' => $inspectionsCount,
            'hr' => $hrCount,
        ];
    }
}

