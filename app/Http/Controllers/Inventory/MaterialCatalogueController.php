<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\MaterialCatalogue;
use Illuminate\Http\Request;

class MaterialCatalogueController extends Controller
{
    public function index(Request $request)
    {
        $query = MaterialCatalogue::withCount('siteStocks');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $materials = $query->orderBy('name')->paginate(20);
        $categories = [
            'cement' => 'Cement & Binders',
            'steel' => 'Steel & Reinforcements',
            'aggregate' => 'Aggregates & Sand',
            'timber' => 'Timber & Formwork',
            'block' => 'Blocks & Bricks',
            'finishing' => 'Finishing & Tiles',
            'plumbing' => 'Plumbing & Pipes',
            'electrical' => 'Electrical & Conduits',
            'equipment_consumable' => 'Equipment Consumables (Diesel/Oil)',
            'other' => 'Other Materials',
        ];

        return view('inventory.catalogue.index', compact('materials', 'categories'));
    }

    public function create()
    {
        $categories = [
            'cement' => 'Cement & Binders',
            'steel' => 'Steel & Reinforcements',
            'aggregate' => 'Aggregates & Sand',
            'timber' => 'Timber & Formwork',
            'block' => 'Blocks & Bricks',
            'finishing' => 'Finishing & Tiles',
            'plumbing' => 'Plumbing & Pipes',
            'electrical' => 'Electrical & Conduits',
            'equipment_consumable' => 'Equipment Consumables (Diesel/Oil)',
            'other' => 'Other Materials',
        ];

        return view('inventory.catalogue.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30|unique:material_catalogue,code',
            'category' => 'required|in:cement,steel,aggregate,timber,block,finishing,plumbing,electrical,equipment_consumable,other',
            'unit_of_measure' => 'required|string|max:30',
            'standard_unit_cost' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'safety_stock_level' => 'required|numeric|min:0',
            'shelf_life_days' => 'nullable|integer|min:1',
            'is_trackable_by_batch' => 'boolean',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_trackable_by_batch'] = $request->boolean('is_trackable_by_batch');
        $validated['is_active'] = $request->boolean('is_active', true);

        MaterialCatalogue::create($validated);

        return redirect()->route('inventory.catalogue.index')
            ->with('success', "Material '{$validated['name']}' added to catalogue.");
    }

    public function edit(MaterialCatalogue $catalogue)
    {
        $material = $catalogue;
        $categories = [
            'cement' => 'Cement & Binders',
            'steel' => 'Steel & Reinforcements',
            'aggregate' => 'Aggregates & Sand',
            'timber' => 'Timber & Formwork',
            'block' => 'Blocks & Bricks',
            'finishing' => 'Finishing & Tiles',
            'plumbing' => 'Plumbing & Pipes',
            'electrical' => 'Electrical & Conduits',
            'equipment_consumable' => 'Equipment Consumables (Diesel/Oil)',
            'other' => 'Other Materials',
        ];

        return view('inventory.catalogue.edit', compact('material', 'categories'));
    }

    public function update(Request $request, MaterialCatalogue $catalogue)
    {
        $material = $catalogue;
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30|unique:material_catalogue,code,' . $material->id,
            'category' => 'required|in:cement,steel,aggregate,timber,block,finishing,plumbing,electrical,equipment_consumable,other',
            'unit_of_measure' => 'required|string|max:30',
            'standard_unit_cost' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'safety_stock_level' => 'required|numeric|min:0',
            'shelf_life_days' => 'nullable|integer|min:1',
            'is_trackable_by_batch' => 'boolean',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_trackable_by_batch'] = $request->boolean('is_trackable_by_batch');
        $validated['is_active'] = $request->boolean('is_active', true);

        $material->update($validated);

        return redirect()->route('inventory.catalogue.index')
            ->with('success', "Material '{$material->name}' updated successfully.");
    }

    public function destroy(MaterialCatalogue $catalogue)
    {
        $material = $catalogue;
        if ($material->siteStocks()->where('qty_on_hand', '>', 0)->exists()) {
            return back()->with('error', 'Cannot delete material that has existing stock in warehouses.');
        }

        $material->delete();

        return redirect()->route('inventory.catalogue.index')
            ->with('success', 'Material removed from catalogue.');
    }

    public function apiSearch(Request $request)
    {
        $query = MaterialCatalogue::where('is_active', true);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $materials = $query->take(20)->get(['id', 'name', 'code', 'unit_of_measure', 'standard_unit_cost', 'category']);

        return response()->json($materials);
    }
}
