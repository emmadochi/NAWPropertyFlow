<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FollowUpController extends Controller
{
    protected $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Display listing of follow-ups.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Base query
        $query = FollowUp::with('lead');

        if ($user->role === 'sales_executive') {
            $query->whereHas('lead', function($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
        }

        // Apply filters
        $dueToday = (clone $query)->dueToday()->orderBy('due_date', 'asc')->get();
        $dueTomorrow = (clone $query)->dueTomorrow()->orderBy('due_date', 'asc')->get();
        $overdue = (clone $query)->overdue()->orderBy('due_date', 'asc')->get();
        $completed = (clone $query)->completed()->orderBy('updated_at', 'desc')->limit(20)->get();
        $allTasks = (clone $query)->orderBy('due_date', 'asc')->get(); // For calendar view

        // Get list of leads for modal selection
        $leadQuery = Lead::orderBy('full_name', 'asc');
        if ($user->role === 'sales_executive') {
            $leadQuery->where('assigned_to', $user->id);
        }
        $leads = $leadQuery->get();

        return view('follow_ups.index', compact('dueToday', 'dueTomorrow', 'overdue', 'completed', 'leads', 'allTasks'));
    }

    /**
     * Store new scheduled follow-up.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'type' => 'required|string|in:Call,Meeting,Note',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $lead = Lead::findOrFail($request->lead_id);
        $validated['status'] = 'Pending';

        $followUp = FollowUp::create($validated);

        // Update lead status to Follow Up automatically if it was New/Contacted
        if (in_array($lead->status, ['New', 'Contacted'])) {
            $this->leadService->updateStatus($lead, 'Follow Up');
        }

        // Log Activity
        $formattedDate = Carbon::parse($followUp->due_date)->format('d M Y, h:i A');
        $this->leadService->logActivity(
            $lead->id,
            Auth::id(),
            'Follow-up Logged',
            "Scheduled a follow-up {$followUp->type} for {$formattedDate}."
        );

        return back()->with('success', 'Follow-up scheduled successfully.');
    }

    /**
     * Mark follow-up as completed.
     */
    public function update(Request $request, FollowUp $followUp)
    {
        $user = Auth::user();
        if ($user->role === 'sales_executive' && $followUp->lead->assigned_to !== $user->id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|string|in:Pending,Completed',
            'notes' => 'nullable|string',
        ]);

        $followUp->status = $request->status;
        if ($request->filled('notes')) {
            $followUp->notes = $followUp->notes . "\n\n[Completion Notes]: " . $request->notes;
        }
        $followUp->save();

        // Log Activity
        $lead = $followUp->lead;
        $this->leadService->logActivity(
            $lead->id,
            Auth::id(),
            'Follow-up Logged',
            "Follow-up {$followUp->type} marked as COMPLETED. " . ($request->notes ? "Notes: {$request->notes}" : "")
        );

        return back()->with('success', 'Follow-up updated successfully.');
    }

    /**
     * Cancel/delete follow-up.
     */
    public function destroy(FollowUp $followUp)
    {
        $lead = $followUp->lead;
        $followUp->delete();

        $this->leadService->logActivity(
            $lead->id,
            Auth::id(),
            'Updated',
            "A follow-up task was removed."
        );

        return back()->with('success', 'Follow-up deleted successfully.');
    }
}
