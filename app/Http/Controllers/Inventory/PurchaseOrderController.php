<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\CompanyInventorySetting;
use App\Models\Inventory\InventorySite;
use App\Models\Inventory\MaterialCatalogue;
use App\Models\Inventory\MaterialRequisition;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\Supplier;
use App\Services\Inventory\ProcurementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected ProcurementService $procurementService
    ) {}

    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'site.project', 'creator', 'approver', 'items.material']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ref_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchaseOrders = $query->latest()->paginate(15);
        $suppliers = Supplier::where('is_active', true)->where('is_blacklisted', false)->orderBy('name')->get();

        return view('inventory.purchase-orders.index', compact('purchaseOrders', 'suppliers'));
    }

    public function create(Request $request)
    {
        $sites = InventorySite::where('is_active', true)->with('project')->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->where('is_blacklisted', false)->orderBy('name')->get();
        $materials = MaterialCatalogue::where('is_active', true)->orderBy('name')->get();
        $requisition = $request->filled('requisition_id')
            ? MaterialRequisition::with('items.material', 'site')->find($request->requisition_id)
            : null;

        return view('inventory.purchase-orders.create', compact('sites', 'suppliers', 'materials', 'requisition'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:inventory_sites,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'requisition_id' => 'nullable|exists:material_requisitions,id',
            'expected_delivery_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'tax_amount' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'terms_and_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:material_catalogue,id',
            'items.*.qty_ordered' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $po = $this->procurementService->createPurchaseOrder($validated, Auth::id());

        return redirect()->route('inventory.purchase-orders.show', $po)
            ->with('success', "Purchase Order {$po->ref_number} generated with Tier {$po->approval_tier} authorization requirement.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'site.project',
            'creator',
            'approver',
            'items.material',
            'goodsReceivedNotes.receiver',
            'invoice'
        ]);

        $settings = CompanyInventorySetting::current();

        return view('inventory.purchase-orders.show', compact('purchaseOrder', 'settings'));
    }

    public function approve(Request $request, PurchaseOrder $purchaseOrder)
    {
        $user = Auth::user();
        $tier = $purchaseOrder->approval_tier;

        // Check tier permission
        if ($tier === 'tier1' && !$user->hasPermission('inventory.approve_po_tier1') && !$user->isCompanyAdmin()) {
            abort(403, 'Unauthorized. Requires Tier 1 approval authority.');
        }

        if ($tier === 'tier2' && !$user->hasPermission('inventory.approve_po_tier2') && !$user->isCompanyAdmin()) {
            abort(403, 'Unauthorized. Requires Tier 2 executive approval authority.');
        }

        if ($tier === 'tier3' && !$user->hasPermission('inventory.approve_po_tier3') && !$user->isCompanyAdmin()) {
            abort(403, 'Unauthorized. Requires Tier 3 Board / MD approval authority.');
        }

        $this->procurementService->approvePurchaseOrder($purchaseOrder, $user->id);

        return redirect()->route('inventory.purchase-orders.show', $purchaseOrder)
            ->with('success', "Purchase Order {$purchaseOrder->ref_number} approved.");
    }

    public function reject(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $purchaseOrder->update([
            'status' => 'rejected',
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('inventory.purchase-orders.show', $purchaseOrder)
            ->with('success', "Purchase Order {$purchaseOrder->ref_number} rejected.");
    }
}
