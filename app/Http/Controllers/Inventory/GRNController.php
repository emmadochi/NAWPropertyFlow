<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\GoodsReceivedNote;
use App\Models\Inventory\PurchaseOrder;
use App\Services\Inventory\ProcurementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GRNController extends Controller
{
    public function __construct(
        protected ProcurementService $procurementService
    ) {}

    public function index(Request $request)
    {
        $query = GoodsReceivedNote::with(['purchaseOrder.supplier', 'site.project', 'receiver', 'items.material']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ref_number', 'like', "%{$search}%")
                  ->orWhere('waybill_number', 'like', "%{$search}%")
                  ->orWhere('driver_name', 'like', "%{$search}%")
                  ->orWhere('vehicle_plate', 'like', "%{$search}%");
            });
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        $notes = $query->latest()->paginate(15);

        return view('inventory.grn.index', compact('notes'));
    }

    public function create(Request $request)
    {
        $purchaseOrders = PurchaseOrder::whereIn('status', ['approved', 'partially_delivered'])
            ->with(['supplier', 'site.project', 'items.material'])
            ->latest()
            ->get();

        $selectedPo = $request->filled('purchase_order_id')
            ? PurchaseOrder::with(['supplier', 'site.project', 'items.material'])->find($request->purchase_order_id)
            : null;

        return view('inventory.grn.create', compact('purchaseOrders', 'selectedPo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required|date_format:H:i',
            'waybill_number' => 'nullable|string|max:100',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:30',
            'vehicle_plate' => 'nullable|string|max:30',
            'delivery_gps_lat' => 'nullable|numeric|between:-90,90',
            'delivery_gps_lng' => 'nullable|numeric|between:-180,180',
            'remarks' => 'nullable|string',
            'photo_evidence' => 'nullable|array',
            'photo_evidence.*' => 'image|max:5120', // 5MB max
            'items' => 'required|array|min:1',
            'items.*.po_item_id' => 'nullable|exists:purchase_order_items,id',
            'items.*.material_id' => 'required|exists:material_catalogue,id',
            'items.*.qty_received' => 'required|numeric|min:0',
            'items.*.qty_rejected' => 'nullable|numeric|min:0',
            'items.*.rejection_reason' => 'nullable|string',
            'items.*.batch_number' => 'nullable|string|max:100',
            'items.*.manufacture_date' => 'nullable|date',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        // Handle photo upload
        $photoPaths = [];
        if ($request->hasFile('photo_evidence')) {
            foreach ($request->file('photo_evidence') as $photo) {
                $path = $photo->store('inventory/grn_evidence', 'public');
                $photoPaths[] = $path;
            }
        }
        $validated['photo_evidence_paths'] = $photoPaths;

        $grn = $this->procurementService->receiveGoods($validated, Auth::id());

        return redirect()->route('inventory.grn.show', $grn)
            ->with('success', "Goods Received Note {$grn->ref_number} recorded. Site stock credited successfully.");
    }

    public function show(GoodsReceivedNote $grn)
    {
        $grn->load(['purchaseOrder.supplier', 'site.project', 'receiver', 'items.material']);
        return view('inventory.grn.show', compact('grn'));
    }
}
