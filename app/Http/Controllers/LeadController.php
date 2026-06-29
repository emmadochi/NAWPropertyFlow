<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
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
     * Display a listing of the leads.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Lead::with(['propertyInterest', 'assignedOfficer']);

        // 1. Role-based scoping: Sales Executives only see their own assigned leads
        if ($user->role === 'sales_executive') {
            $query->where('assigned_to', $user->id);
        }

        // 2. Apply Filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('status')) {
            $query->ofStatus($request->status);
        }
        if ($request->filled('assigned_to')) {
            $query->ofOfficer($request->assigned_to);
        }
        if ($request->filled('source')) {
            $query->where('lead_source', $request->source);
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Data for dropdowns in modals & filters
        $properties = Property::orderBy('name', 'asc')->get();
        
        $officersQuery = User::whereIn('role', ['sales_executive', 'sales_manager']);
        if ($user->role === 'sales_manager' || $user->role === 'sales_executive') {
            $officersQuery->where('branch_id', $user->branch_id);
        } else {
            $selectedBranchId = session('selected_branch_id', 'all');
            if ($selectedBranchId !== 'all') {
                $officersQuery->where('branch_id', $selectedBranchId);
            }
        }
        $officers = $officersQuery->orderBy('name', 'asc')->get();
        $branches = \App\Models\Branch::orderBy('name', 'asc')->get();

        return view('leads.index', compact('leads', 'properties', 'officers', 'branches'));
    }

    /**
     * Store a newly created lead.
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
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        if (!in_array(Auth::user()->role, ['super_admin', 'company_admin'])) {
            $validated['branch_id'] = Auth::user()->branch_id;
        } else {
            if (empty($validated['branch_id']) && session()->has('selected_branch_id') && session('selected_branch_id') !== 'all') {
                $validated['branch_id'] = session('selected_branch_id');
            }
        }

        $this->leadService->createLead($validated);

        return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
    }

    /**
     * Display the specified lead's profile.
     */
    public function show(Lead $lead)
    {
        $user = Auth::user();

        // Authorize check: Sales Executive can only view their own assigned leads
        if ($user->role === 'sales_executive' && $lead->assigned_to !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $lead->load([
            'propertyInterest', 
            'assignedOfficer', 
            'activities.user', 
            'followUps' => fn($q) => $q->orderBy('due_date', 'desc'), 
            'inspections.property', 
            'inspections.assignedOfficer',
            'documents.uploader',
            'sales'
        ]);

        // Compile a unified activity timeline
        $timeline = collect();

        // 1. LeadActivities (Status changes, updates, notes, etc.)
        foreach ($lead->activities as $activity) {
            $timeline->push([
                'type' => 'activity',
                'activity_type' => $activity->activity_type,
                'description' => $activity->description,
                'created_at' => $activity->created_at,
                'user' => $activity->user ? $activity->user->name : 'System',
                'icon' => $activity->activity_type === 'Created' ? '🆕' : ($activity->activity_type === 'Note' ? '📝' : '⚙️'),
                'color' => $activity->activity_type === 'Created' ? 'bg-orange-500' : ($activity->activity_type === 'Note' ? 'bg-amber-500' : 'bg-slate-400')
            ]);
        }

        // 2. Follow-ups
        foreach ($lead->followUps as $followUp) {
            $timeline->push([
                'type' => 'followup',
                'activity_type' => 'Follow-up Scheduled',
                'description' => "Scheduled a {$followUp->type} follow-up: \"{$followUp->notes}\" (Due: " . ($followUp->due_date ? $followUp->due_date->format('M d, Y h:i A') : 'N/A') . ", Status: {$followUp->status})",
                'created_at' => $followUp->created_at,
                'user' => $lead->assignedOfficer ? $lead->assignedOfficer->name : 'System',
                'icon' => $followUp->type === 'Call' ? '📞' : '🔄',
                'color' => $followUp->status === 'Completed' ? 'bg-emerald-500' : 'bg-amber-500'
            ]);
        }

        // 3. Inspections
        foreach ($lead->inspections as $ins) {
            $timeline->push([
                'type' => 'inspection',
                'activity_type' => 'Inspection Booked',
                'description' => "Scheduled inspection for " . ($ins->property ? $ins->property->name : 'Property') . ": \"{$ins->notes}\" (Date: " . ($ins->inspection_date ? $ins->inspection_date->format('M d, Y h:i A') : 'N/A') . ", Status: {$ins->status})",
                'created_at' => $ins->created_at,
                'user' => $ins->assignedOfficer ? $ins->assignedOfficer->name : 'System',
                'icon' => '🏡',
                'color' => 'bg-purple-500'
            ]);
        }

        // 4. Documents
        foreach ($lead->documents as $doc) {
            $timeline->push([
                'type' => 'document',
                'activity_type' => 'Document Uploaded',
                'description' => "Uploaded KYC file: \"{$doc->name}\"",
                'created_at' => $doc->created_at,
                'user' => $doc->uploader ? $doc->uploader->name : 'System',
                'icon' => '📄',
                'color' => 'bg-blue-500'
            ]);
        }

        // 5. Sales
        foreach ($lead->sales as $sale) {
            $timeline->push([
                'type' => 'sale',
                'activity_type' => 'Sale Logged',
                'description' => "Closed Sale: Unit {$sale->unit_name} at {$sale->property_name} for ₦" . number_format($sale->sale_price, 2),
                'created_at' => $sale->created_at,
                'user' => $lead->assignedOfficer ? $lead->assignedOfficer->name : 'System',
                'icon' => '✅',
                'color' => 'bg-emerald-500'
            ]);
        }

        $timeline = $timeline->sortByDesc('created_at');

        $properties = Property::with(['units' => function($q) use ($lead) {
            $q->where('status', 'available')
              ->orWhere(function($sq) use ($lead) {
                  $sq->where('status', 'reserved')
                     ->where('reserved_by_lead_id', $lead->id);
              });
        }])->orderBy('name', 'asc')->get();
        $officersQuery = User::whereIn('role', ['sales_executive', 'sales_manager']);
        if ($user->role === 'sales_manager' || $user->role === 'sales_executive') {
            $officersQuery->where('branch_id', $user->branch_id);
        } else {
            $selectedBranchId = session('selected_branch_id', 'all');
            if ($selectedBranchId !== 'all') {
                $officersQuery->where('branch_id', $selectedBranchId);
            }
        }
        $officers = $officersQuery->orderBy('name', 'asc')->get();
        $branches = \App\Models\Branch::orderBy('name', 'asc')->get();

        return view('leads.show', compact('lead', 'properties', 'officers', 'branches', 'timeline'));
    }

    /**
     * AJAX: Store a new Quick Note for this lead.
     */
    public function storeNote(Request $request, Lead $lead)
    {
        $request->validate([
            'note' => 'required|string',
        ]);

        $activity = app(\App\Services\LeadService::class)->logActivity(
            $lead->id,
            Auth::id(),
            'Note',
            $request->note
        );

        return response()->json([
            'success' => true,
            'activity' => [
                'activity_type' => 'Note',
                'description' => $request->note,
                'created_at' => $activity->created_at->format('M d, Y h:i A'),
                'user' => Auth::user()->name,
                'icon' => '📝',
                'color' => 'bg-amber-500'
            ]
        ]);
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $user = Auth::user();

        if ($user->role === 'sales_executive' && $lead->assigned_to !== $user->id) {
            abort(403, 'Unauthorized action.');
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
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        if (!in_array($user->role, ['super_admin', 'company_admin'])) {
            unset($validated['branch_id']);
        }

        $this->leadService->updateLead($lead, $validated);

        return back()->with('success', 'Lead updated successfully.');
    }

    /**
     * Assign lead directly to a Sales Officer.
     */
    public function assign(Request $request, Lead $lead)
    {
        // Only managers or admin can reassign
        if (Auth::user()->role === 'sales_executive') {
            abort(403, 'Unauthorized. Sales Executives cannot reassign leads.');
        }

        $request->validate([
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $this->leadService->updateLead($lead, [
            'assigned_to' => $request->assigned_to
        ]);

        return back()->with('success', 'Lead assigned successfully.');
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy(Lead $lead)
    {
        // Only super admin or company admin can delete leads
        if (!in_array(Auth::user()->role, ['super_admin', 'company_admin'])) {
            abort(403, 'Unauthorized action. Contact administrator to delete leads.');
        }

        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }

    /**
     * Download sample leads import template.
     */
    public function importTemplate()
    {
        $headers = [
            'Content-type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=leads_import_template.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $columns = ['full_name', 'phone_number', 'whatsapp_number', 'email', 'budget_range', 'preferred_location', 'lead_source', 'notes', 'status'];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, [
                'John Doe',
                '+2348012345678',
                '+2348012345678',
                'johndoe@example.com',
                '₦10,000,000 - ₦20,000,000',
                'Lekki Phase 1',
                'Website',
                'Interested in 3-bedroom terraces.',
                'New'
            ]);
            fclose($file);
        };

        return new \Symfony\Component\HttpFoundation\StreamedResponse($callback, 200, $headers);
    }

    /**
     * Import multiple leads via CSV upload.
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return back()->withErrors(['error' => 'Unable to read the uploaded CSV file.']);
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return back()->withErrors(['error' => 'Empty CSV file uploaded.']);
        }

        $headers = array_map(function($h) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
        }, $headers);

        if (!in_array('full_name', $headers) || !in_array('phone_number', $headers)) {
            fclose($handle);
            return back()->withErrors(['error' => 'CSV file must contain both full_name and phone_number columns.']);
        }

        $importedCount = 0;
        $rowNum = 1;
        $errors = [];

        $user = Auth::user();
        $branchId = !in_array($user->role, ['super_admin', 'company_admin']) 
            ? $user->branch_id 
            : (session('selected_branch_id') !== 'all' ? session('selected_branch_id') : null);

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) !== count($headers)) {
                $errors[] = "Row {$rowNum}: Column count mismatch.";
                continue;
            }

            $data = array_combine($headers, $row);
            if (empty(trim($data['full_name'] ?? '')) && empty(trim($data['phone_number'] ?? ''))) {
                continue;
            }

            if (empty(trim($data['full_name'] ?? ''))) {
                $errors[] = "Row {$rowNum}: full_name is required.";
                continue;
            }
            if (empty(trim($data['phone_number'] ?? ''))) {
                $errors[] = "Row {$rowNum}: phone_number is required.";
                continue;
            }

            $leadData = [
                'full_name' => trim($data['full_name']),
                'phone_number' => trim($data['phone_number']),
                'whatsapp_number' => isset($data['whatsapp_number']) ? trim($data['whatsapp_number']) : null,
                'email' => (!empty($data['email']) && filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) ? trim($data['email']) : null,
                'budget_range' => !empty($data['budget_range']) ? trim($data['budget_range']) : 'N/A',
                'preferred_location' => isset($data['preferred_location']) ? trim($data['preferred_location']) : null,
                'lead_source' => !empty($data['lead_source']) ? trim($data['lead_source']) : 'CSV Import',
                'status' => !empty($data['status']) ? trim($data['status']) : 'New',
                'notes' => isset($data['notes']) ? trim($data['notes']) : null,
                'branch_id' => $branchId,
            ];

            try {
                $this->leadService->createLead($leadData);
                $importedCount++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum}: Failed to import (" . $e->getMessage() . ").";
            }
        }

        fclose($handle);

        if (count($errors) > 0) {
            $msg = "Imported {$importedCount} leads successfully. Errors: " . implode(' ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $msg .= " and " . (count($errors) - 5) . " more errors.";
            }
            return redirect()->route('leads.index')->with('warning', $msg);
        }

        return redirect()->route('leads.index')->with('success', "Successfully imported {$importedCount} leads.");
    }

    /**
     * AJAX: Update Lead Status via Kanban Drag & Drop
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:New,Contacted,Follow Up,Inspection Scheduled,Negotiation,Payment Processing,Closed Won,Closed Lost',
        ]);

        $user = Auth::user();

        // Authorize: Sales Exec can only move their own leads
        if ($user->role === 'sales_executive' && $lead->assigned_to !== $user->id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $oldStatus = $lead->status;
        $lead->status = $validated['status'];
        $lead->save();

        // Log status change
        \App\Models\ActivityLog::create([
            'description' => "Lead status changed from '{$oldStatus}' to '{$validated['status']}'",
            'causer_id' => $user->id,
            'subject_id' => $lead->id,
            'subject_type' => Lead::class,
        ]);

        return response()->json([
            'success' => true,
            'lead_id' => $lead->id,
            'new_status' => $lead->status,
        ]);
    }
}
