<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Lead;
use App\Models\Property;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InspectionController extends Controller
{
    protected $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Display a listing of inspections.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Inspection::with(['lead', 'property']);

        if ($user->role === 'sales_executive') {
            $query->where('assigned_to', $user->id);
        }

        $inspections = $query->orderBy('inspection_date', 'asc')->paginate(15);

        return response()->json($inspections);
    }

    /**
     * Schedule a new site inspection.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'property_id' => 'required|exists:properties,id',
            'inspection_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $lead = Lead::findOrFail($request->lead_id);
        $property = Property::findOrFail($request->property_id);

        // Security check
        if (Auth::user()->role === 'sales_executive' && $lead->assigned_to !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized Access'], 403);
        }

        $validated['assigned_to'] = $lead->assigned_to ?? Auth::id();
        $validated['status'] = 'Scheduled';

        $inspection = Inspection::create($validated);

        // Update Lead Status
        $this->leadService->updateStatus($lead, 'Inspection Scheduled', Auth::id());

        // Log Timeline
        $formattedDate = Carbon::parse($inspection->inspection_date)->format('d M Y, h:i A');
        $this->leadService->logActivity(
            $lead->id,
            Auth::id(),
            'Inspection Scheduled',
            "Site inspection scheduled for '{$property->name}' on {$formattedDate}."
        );

        return response()->json([
            'message' => 'Inspection scheduled successfully',
            'inspection' => $inspection
        ], 201);
    }
}
