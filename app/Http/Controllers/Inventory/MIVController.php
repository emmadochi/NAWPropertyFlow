<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventorySite;
use App\Models\Inventory\MaterialCatalogue;
use App\Models\Inventory\MaterialIssueVoucher;
use App\Models\User;
use App\Services\Inventory\ProcurementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MIVController extends Controller
{
    public function __construct(
        protected ProcurementService $procurementService
    ) {}

    public function index(Request $request)
    {
        $query = MaterialIssueVoucher::with(['site.project', 'issuer', 'receiver', 'items.material']);

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

        $vouchers = $query->latest()->paginate(15);

        return view('inventory.miv.index', compact('vouchers'));
    }

    public function create(Request $request)
    {
        $sites = InventorySite::where('is_active', true)
            ->with(['project', 'stock.material'])
            ->orderBy('name')
            ->get();

        $selectedSite = $request->filled('site_id')
            ? InventorySite::with(['stock.material', 'stock.batches'])->find($request->site_id)
            : ($sites->first() ?? null);

        // Site engineers and foremen who can receive materials
        $receivers = User::where('status', 'active')->orderBy('name')->get();

        return view('inventory.miv.create', compact('sites', 'selectedSite', 'receivers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:inventory_sites,id',
            'received_by_user_id' => 'required|exists:users,id',
            'activity_name' => 'required|string|max:255',
            'work_quantity' => 'nullable|numeric|min:0.01',
            'work_unit' => 'nullable|string|max:30',
            'foreman_signature_data' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:material_catalogue,id',
            'items.*.stock_batch_id' => 'nullable|exists:stock_batches,id',
            'items.*.qty_issued' => 'required|numeric|min:0.001',
        ]);

        $miv = $this->procurementService->issueMaterial($validated, Auth::id());

        return redirect()->route('inventory.miv.show', $miv)
            ->with('success', "Material Issue Voucher {$miv->ref_number} processed. Site stock debited.");
    }

    public function show(MaterialIssueVoucher $miv)
    {
        $miv->load(['site.project', 'issuer', 'receiver', 'items.material', 'items.batch', 'wasteLogs']);
        return view('inventory.miv.show', compact('miv'));
    }
}
