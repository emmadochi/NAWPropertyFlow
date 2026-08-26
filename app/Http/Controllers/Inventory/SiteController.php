<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventorySite;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $query = InventorySite::with(['project', 'creator'])
            ->withCount(['stock', 'requisitions', 'purchaseOrders']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $sites = $query->latest()->paginate(15);
        $projects = Project::orderBy('name')->get();

        return view('inventory.sites.index', compact('sites', 'projects'));
    }

    public function create()
    {
        $projects = Project::orderBy('name')->get();
        return view('inventory.sites.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30|unique:inventory_sites,code',
            'address' => 'nullable|string',
            'gps_lat' => 'nullable|numeric|between:-90,90',
            'gps_lng' => 'nullable|numeric|between:-180,180',
            'geofence_radius_meters' => 'required|integer|min:10|max:5000',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->is_active : true;

        $site = InventorySite::create($validated);

        return redirect()->route('inventory.sites.show', $site)
            ->with('success', "Site '{$site->name}' created successfully.");
    }

    public function show(InventorySite $site)
    {
        $site->load([
            'project',
            'creator',
            'stock.material',
            'stock.batches' => function ($q) {
                $q->where('qty_remaining', '>', 0)->latest();
            },
            'requisitions' => function ($q) {
                $q->with('requester')->latest()->take(5);
            },
            'goodsReceivedNotes' => function ($q) {
                $q->with('receiver')->latest()->take(5);
            },
        ]);

        return view('inventory.sites.show', compact('site'));
    }

    public function edit(InventorySite $site)
    {
        $projects = Project::orderBy('name')->get();
        return view('inventory.sites.edit', compact('site', 'projects'));
    }

    public function update(Request $request, InventorySite $site)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30|unique:inventory_sites,code,' . $site->id,
            'address' => 'nullable|string',
            'gps_lat' => 'nullable|numeric|between:-90,90',
            'gps_lng' => 'nullable|numeric|between:-180,180',
            'geofence_radius_meters' => 'required|integer|min:10|max:5000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $site->update($validated);

        return redirect()->route('inventory.sites.show', $site)
            ->with('success', "Site '{$site->name}' updated successfully.");
    }

    public function destroy(InventorySite $site)
    {
        if ($site->stock()->where('qty_on_hand', '>', 0)->exists()) {
            return back()->with('error', 'Cannot delete site with active stock balance. Deactivate it instead.');
        }

        $site->delete();

        return redirect()->route('inventory.sites.index')
            ->with('success', 'Site deleted successfully.');
    }
}
