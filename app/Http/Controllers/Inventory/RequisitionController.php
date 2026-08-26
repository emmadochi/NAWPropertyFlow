<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventorySite;
use App\Models\Inventory\MaterialCatalogue;
use App\Models\Inventory\MaterialRequisition;
use App\Services\Inventory\ProcurementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequisitionController extends Controller
{
    public function __construct(
        protected ProcurementService $procurementService
    ) {}

    public function index(Request $request)
    {
        $query = MaterialRequisition::with(['site', 'project', 'requester', 'approver', 'items.material']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ref_number', 'like', "%{$search}%")
                  ->orWhere('activity_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requisitions = $query->latest()->paginate(15);
        $sites = InventorySite::where('is_active', true)->orderBy('name')->get();

        return view('inventory.requisitions.index', compact('requisitions', 'sites'));
    }

    public function create(Request $request)
    {
        $sites = InventorySite::where('is_active', true)->with('project')->orderBy('name')->get();
        $materials = MaterialCatalogue::where('is_active', true)->orderBy('name')->get();
        $selectedSite = $request->filled('site_id') ? InventorySite::find($request->site_id) : null;

        return view('inventory.requisitions.create', compact('sites', 'materials', 'selectedSite'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:inventory_sites,id',
            'activity_name' => 'required|string|max:255',
            'work_quantity' => 'required|numeric|min:0.01',
            'required_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:material_catalogue,id',
            'items.*.qty_requested' => 'required|numeric|min:0.001',
        ]);

        $requisition = $this->procurementService->createRequisition($validated, Auth::id());

        return redirect()->route('inventory.requisitions.show', $requisition)
            ->with('success', "Material Requisition {$requisition->ref_number} submitted successfully.");
    }

    public function show(MaterialRequisition $requisition)
    {
        $requisition->load(['site.project', 'requester', 'approver', 'items.material', 'purchaseOrder']);
        return view('inventory.requisitions.show', compact('requisition'));
    }

    public function approve(Request $request, MaterialRequisition $requisition)
    {
        $validated = $request->validate([
            'approved_items' => 'nullable|array',
            'approved_items.*' => 'numeric|min:0',
        ]);

        $this->procurementService->approveRequisition($requisition, Auth::id(), $validated['approved_items'] ?? null);

        return redirect()->route('inventory.requisitions.show', $requisition)
            ->with('success', "Material Requisition {$requisition->ref_number} approved.");
    }

    public function reject(Request $request, MaterialRequisition $requisition)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $requisition->update([
            'status' => 'rejected',
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('inventory.requisitions.show', $requisition)
            ->with('success', "Material Requisition {$requisition->ref_number} rejected.");
    }
}
