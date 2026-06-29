<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    protected $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Display a listing of leads.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Lead::with(['propertyInterest', 'assignedOfficer']);

        if ($user->role === 'sales_executive') {
            $query->where('assigned_to', $user->id);
        }

        if ($request->filled('status')) {
            $query->ofStatus($request->status);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($leads);
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:30',
            'whatsapp_number' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'budget_range' => 'required|string|max:100',
            'property_interest_id' => 'nullable|exists:properties,id',
            'preferred_location' => 'nullable|string|max:255',
            'lead_source' => 'required|string|max:100',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $lead = $this->leadService->createLead($validated, Auth::id());

        return response()->json([
            'message' => 'Lead created successfully',
            'lead' => $lead
        ], 201);
    }

    /**
     * Display the specified lead.
     */
    public function show(Lead $lead)
    {
        $user = Auth::user();
        if ($user->role === 'sales_executive' && $lead->assigned_to !== $user->id) {
            return response()->json(['message' => 'Unauthorized Access'], 403);
        }

        $lead->load(['propertyInterest', 'assignedOfficer', 'activities.user', 'followUps', 'inspections', 'documents']);

        return response()->json($lead);
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $user = Auth::user();
        if ($user->role === 'sales_executive' && $lead->assigned_to !== $user->id) {
            return response()->json(['message' => 'Unauthorized Access'], 403);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:30',
            'whatsapp_number' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'budget_range' => 'required|string|max:100',
            'property_interest_id' => 'nullable|exists:properties,id',
            'preferred_location' => 'nullable|string|max:255',
            'lead_source' => 'required|string|max:100',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $lead = $this->leadService->updateLead($lead, $validated, Auth::id());

        return response()->json([
            'message' => 'Lead updated successfully',
            'lead' => $lead
        ]);
    }
}
