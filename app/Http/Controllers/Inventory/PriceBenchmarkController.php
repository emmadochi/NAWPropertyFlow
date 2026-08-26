<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\MaterialCatalogue;
use App\Models\Inventory\PriceBenchmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PriceBenchmarkController extends Controller
{
    public function index(Request $request)
    {
        $materials = MaterialCatalogue::where('is_active', true)->with('priceBenchmarks')->orderBy('name')->get();
        $benchmarks = PriceBenchmark::with(['material', 'enteredBy'])
            ->latest('recorded_date')
            ->paginate(25);

        // Build price matrix per material per city
        $cities = [
            'lagos' => 'Lagos Market',
            'abuja' => 'Abuja / FCT Market',
            'port_harcourt' => 'Port Harcourt / South-South',
            'ibadan' => 'Ibadan / South-West',
            'kano' => 'Kano / North',
            'enugu' => 'Enugu / South-East',
            'other' => 'Other / Regional',
        ];

        return view('inventory.benchmarks.index', compact('materials', 'benchmarks', 'cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:material_catalogue,id',
            'city' => 'required|in:lagos,abuja,port_harcourt,ibadan,kano,enugu,other',
            'city_name_custom' => 'nullable|string|max:100',
            'unit_price' => 'required|numeric|min:0',
            'recorded_date' => 'required|date',
            'source_market_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['entered_by_user_id'] = Auth::id();

        PriceBenchmark::create($validated);

        return redirect()->route('inventory.benchmarks.index')
            ->with('success', 'Market price index entry recorded successfully.');
    }

    public function destroy(PriceBenchmark $benchmark)
    {
        $benchmark->delete();

        return redirect()->route('inventory.benchmarks.index')
            ->with('success', 'Price benchmark removed.');
    }
}
