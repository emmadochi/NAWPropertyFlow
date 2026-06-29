<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\InspectionScheduledMail;
use Carbon\Carbon;

class InspectionController extends Controller
{
    protected $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Display listing of inspections.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Inspection::with(['lead', 'property', 'assignedOfficer']);

        if ($user->role === 'sales_executive') {
            $query->where('assigned_to', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_filter')) {
            if ($request->date_filter === 'today') {
                $query->whereDate('inspection_date', Carbon::today());
            } elseif ($request->date_filter === 'upcoming') {
                $query->where('inspection_date', '>', Carbon::now());
            } elseif ($request->date_filter === 'past') {
                $query->where('inspection_date', '<', Carbon::now());
            }
        }

        $inspections = $query->orderBy('inspection_date', 'asc')->paginate(15)->withQueryString();
        
        $leads = Lead::orderBy('full_name', 'asc');
        if ($user->role === 'sales_executive') {
            $leads->where('assigned_to', $user->id);
        }
        $leads = $leads->get();

        $properties = Property::orderBy('name', 'asc')->get();
        $officers = User::whereIn('role', ['sales_executive', 'sales_manager'])->orderBy('name', 'asc')->get();

        return view('inspections.index', compact('inspections', 'leads', 'properties', 'officers'));
    }

    /**
     * Store new scheduled inspection.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'property_id' => 'required|exists:properties,id',
            'assigned_to' => 'nullable|exists:users,id',
            'inspection_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $lead = Lead::findOrFail($request->lead_id);
        $property = Property::findOrFail($request->property_id);

        $validated['assigned_to'] = $request->assigned_to ?? $lead->assigned_to ?? Auth::id();
        $validated['status'] = 'Scheduled';

        $inspection = Inspection::create($validated);

        // Update Lead status to Inspection Scheduled automatically
        $this->leadService->updateStatus($lead, 'Inspection Scheduled');

        // Log Activity
        $formattedDate = Carbon::parse($inspection->inspection_date)->format('d M Y, h:i A');
        $this->leadService->logActivity(
            $lead->id,
            Auth::id(),
            'Inspection Scheduled',
            "Site inspection scheduled for '{$property->name}' on {$formattedDate}."
        );

        // Send email to client
        if ($lead->email) {
            try {
                Mail::to($lead->email)->send(new InspectionScheduledMail($inspection));
            } catch (\Exception $e) {
                // Ignore or log
            }
        }

        try {
            app(\App\Services\DripService::class)->triggerFor($lead, 'inspection_booked');
        } catch (\Exception $e) {}

        return back()->with('success', 'Inspection scheduled successfully.');
    }

    /**
     * Update inspection details or status.
     */
    public function update(Request $request, Inspection $inspection)
    {
        $user = Auth::user();
        if ($user->role === 'sales_executive' && $inspection->assigned_to !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:Scheduled,Completed,Cancelled,Rescheduled',
            'inspection_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $originalStatus = $inspection->status;
        $originalDate = $inspection->inspection_date;

        $inspection->update($validated);

        // Log Activity
        $lead = $inspection->lead;
        if ($originalStatus !== $inspection->status) {
            $this->leadService->logActivity(
                $lead->id,
                Auth::id(),
                'Updated',
                "Inspection status changed from '{$originalStatus}' to '{$inspection->status}'."
            );

            // If inspection is completed, update lead stage to Follow Up or Negotiation (we default to Negotiation for interest)
            if ($inspection->status === 'Completed') {
                event(new \App\Events\InspectionCompleted($inspection));
                if ($lead->status === 'Inspection Scheduled') {
                    $this->leadService->updateStatus($lead, 'Negotiation');
                }
            }
        }

        if ($request->filled('inspection_date') && Carbon::parse($originalDate)->ne(Carbon::parse($inspection->inspection_date))) {
            $formattedDate = Carbon::parse($inspection->inspection_date)->format('d M Y, h:i A');
            $this->leadService->logActivity(
                $lead->id,
                Auth::id(),
                'Updated',
                "Inspection rescheduled to {$formattedDate}."
            );
        }

        return back()->with('success', 'Inspection updated successfully.');
    }

    /**
     * Remove / cancel inspection.
     */
    public function destroy(Inspection $inspection)
    {
        $lead = $inspection->lead;
        $inspection->delete();

        $this->leadService->logActivity(
            $lead->id,
            Auth::id(),
            'Updated',
            "A scheduled inspection was removed."
        );

        return back()->with('success', 'Inspection deleted successfully.');
    }
}
