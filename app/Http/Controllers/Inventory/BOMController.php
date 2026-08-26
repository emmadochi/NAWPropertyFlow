<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\BomTemplate;
use App\Models\Inventory\MaterialCatalogue;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BOMController extends Controller
{
    public function index(Request $request)
    {
        $query = BomTemplate::with(['material', 'project', 'setBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('activity_name', 'like', "%{$search}%")
                  ->orWhereHas('material', function ($mq) use ($search) {
                      $mq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('project_id')) {
            if ($request->project_id === 'global') {
                $query->whereNull('project_id');
            } else {
                $query->where('project_id', $request->project_id);
            }
        }

        $boms = $query->orderBy('activity_name')->paginate(20);
        $projects = Project::orderBy('name')->get();

        return view('inventory.bom.index', compact('boms', 'projects'));
    }

    public function create()
    {
        $materials = MaterialCatalogue::where('is_active', true)->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        return view('inventory.bom.create', compact('materials', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'material_id' => 'required|exists:material_catalogue,id',
            'activity_name' => 'required|string|max:255',
            'qty_per_unit' => 'required|numeric|min:0.0001',
            'unit_of_work' => 'required|string|max:30',
            'allowable_variance_pct' => 'required|numeric|min:0|max:100',
        ]);

        $validated['set_by_user_id'] = Auth::id();

        // Check unique constraint
        $exists = BomTemplate::where('material_id', $validated['material_id'])
            ->where('activity_name', $validated['activity_name'])
            ->where('project_id', $validated['project_id'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'A BOM benchmark for this material and activity already exists for this project scope.');
        }

        BomTemplate::create($validated);

        return redirect()->route('inventory.bom.index')
            ->with('success', 'BOM consumption benchmark defined successfully.');
    }

    public function edit(BomTemplate $bom)
    {
        $materials = MaterialCatalogue::where('is_active', true)->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        return view('inventory.bom.edit', compact('bom', 'materials', 'projects'));
    }

    public function update(Request $request, BomTemplate $bom)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'material_id' => 'required|exists:material_catalogue,id',
            'activity_name' => 'required|string|max:255',
            'qty_per_unit' => 'required|numeric|min:0.0001',
            'unit_of_work' => 'required|string|max:30',
            'allowable_variance_pct' => 'required|numeric|min:0|max:100',
        ]);

        $exists = BomTemplate::where('material_id', $validated['material_id'])
            ->where('activity_name', $validated['activity_name'])
            ->where('project_id', $validated['project_id'])
            ->where('id', '!=', $bom->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Another BOM rule matches this combination.');
        }

        $bom->update($validated);

        return redirect()->route('inventory.bom.index')
            ->with('success', 'BOM benchmark updated successfully.');
    }

    public function destroy(BomTemplate $bom)
    {
        $bom->delete();

        return redirect()->route('inventory.bom.index')
            ->with('success', 'BOM template removed.');
    }

    public function suggestQty(Request $request)
    {
        $request->validate([
            'activity_name' => 'required|string',
            'work_quantity' => 'required|numeric|min:0.01',
            'project_id' => 'nullable|integer',
        ]);

        $query = BomTemplate::with('material')
            ->where('activity_name', $request->activity_name);

        if ($request->filled('project_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('project_id', $request->project_id)
                  ->orWhereNull('project_id');
            });
        } else {
            $query->whereNull('project_id');
        }

        $boms = $query->get();

        $suggestions = $boms->map(function ($bom) use ($request) {
            $expectedQty = $bom->qty_per_unit * $request->work_quantity;
            return [
                'material_id' => $bom->material_id,
                'material_name' => $bom->material->name,
                'material_code' => $bom->material->code,
                'unit_of_measure' => $bom->material->unit_of_measure,
                'standard_unit_cost' => $bom->material->standard_unit_cost,
                'qty_per_unit' => $bom->qty_per_unit,
                'unit_of_work' => $bom->unit_of_work,
                'expected_qty' => round($expectedQty, 3),
                'allowable_variance_pct' => $bom->allowable_variance_pct,
                'estimated_total_cost' => round($expectedQty * $bom->material->standard_unit_cost, 2),
            ];
        });

        return response()->json([
            'activity_name' => $request->activity_name,
            'work_quantity' => $request->work_quantity,
            'materials' => $suggestions,
        ]);
    }
}
