<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventorySite;
use App\Models\Inventory\MaterialCatalogue;
use App\Models\Inventory\MaterialIssueVoucher;
use App\Models\Inventory\WasteLog;
use App\Services\Inventory\ProcurementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WasteLogController extends Controller
{
    public function __construct(
        protected ProcurementService $procurementService
    ) {}

    public function index(Request $request)
    {
        $query = WasteLog::with(['site.project', 'material', 'logger', 'issueVoucher']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('activity_name', 'like', "%{$search}%")
                  ->orWhere('responsible_team', 'like', "%{$search}%");
            });
        }

        if ($request->filled('waste_type')) {
            $query->where('waste_type', $request->waste_type);
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        $logs = $query->latest()->paginate(20);
        $sites = InventorySite::where('is_active', true)->orderBy('name')->get();

        return view('inventory.waste.index', compact('logs', 'sites'));
    }

    public function create(Request $request)
    {
        $sites = InventorySite::where('is_active', true)->with('stock.material')->orderBy('name')->get();
        $materials = MaterialCatalogue::where('is_active', true)->orderBy('name')->get();
        $selectedMiv = $request->filled('miv_id') ? MaterialIssueVoucher::find($request->miv_id) : null;

        return view('inventory.waste.create', compact('sites', 'materials', 'selectedMiv'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:inventory_sites,id',
            'material_id' => 'required|exists:material_catalogue,id',
            'miv_id' => 'nullable|exists:material_issue_vouchers,id',
            'qty' => 'required|numeric|min:0.001',
            'waste_type' => 'required|in:avoidable,unavoidable,loss,theft_suspected',
            'activity_name' => 'nullable|string|max:255',
            'responsible_team' => 'nullable|string|max:255',
            'description' => 'required|string',
            'weather_condition' => 'nullable|string|max:100',
            'insurance_claim_raised' => 'boolean',
            'deduct_from_stock' => 'boolean',
            'photos' => 'nullable|array',
            'photos.*' => 'image|max:5120',
        ]);

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('inventory/waste_photos', 'public');
                $photoPaths[] = $path;
            }
        }
        $validated['photo_paths'] = $photoPaths;

        $waste = $this->procurementService->logWaste($validated, Auth::id());

        return redirect()->route('inventory.waste.index')
            ->with('success', "Waste log recorded successfully for {$waste->material->name}.");
    }
}
