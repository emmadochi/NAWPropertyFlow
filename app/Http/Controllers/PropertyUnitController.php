<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Lead;
use Illuminate\Http\Request;

class PropertyUnitController extends Controller
{
    public function index(Property $property)
    {
        $units = $property->units()
            ->with('reservedByLead')
            ->orderBy('unit_number')
            ->paginate(20);

        $stats = [
            'available' => $property->units()->where('status', 'available')->count(),
            'reserved'  => $property->units()->where('status', 'reserved')->count(),
            'sold'      => $property->units()->where('status', 'sold')->count(),
            'total'     => $property->units()->count(),
        ];

        // Fetch leads for modal selection
        $user = auth()->user();
        $leadQuery = Lead::orderBy('full_name', 'asc');
        if ($user && $user->role === 'sales_executive') {
            $leadQuery->where('assigned_to', $user->id);
        }
        $leads = $leadQuery->get();

        return view('units.index', compact('property', 'units', 'stats', 'leads'));
    }

    public function create(Property $property)
    {
        return view('units.create', compact('property'));
    }

    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'unit_number'    => 'required|string|max:50',
            'unit_type'      => 'nullable|string|max:100',
            'floor_number'   => 'nullable|integer',
            'size_sqm'       => 'nullable|numeric|min:0',
            'price'          => 'required|numeric|min:0',
            'service_charge' => 'nullable|numeric|min:0',
            'status'         => 'required|in:available,reserved,sold,unavailable',
            'description'    => 'nullable|string',
            'features'       => 'nullable|array',
        ], [
            'unit_number.unique' => 'This unit number already exists on this property.',
        ]);

        // Check uniqueness at application level for better error messages
        $exists = $property->units()->where('unit_number', $validated['unit_number'])->exists();
        if ($exists) {
            return back()->withErrors(['unit_number' => 'This unit number already exists on this property.'])->withInput();
        }

        $validated['features'] = $request->input('features', []);
        $property->units()->create($validated);

        // Sync available_units count
        $this->syncPropertyUnitCount($property);

        return redirect()->route('properties.units.index', $property)
            ->with('success', "Unit {$validated['unit_number']} added.");
    }

    public function edit(Property $property, PropertyUnit $unit)
    {
        return view('units.edit', compact('property', 'unit'));
    }

    public function update(Request $request, Property $property, PropertyUnit $unit)
    {
        $validated = $request->validate([
            'unit_number'    => 'required|string|max:50',
            'unit_type'      => 'nullable|string|max:100',
            'floor_number'   => 'nullable|integer',
            'size_sqm'       => 'nullable|numeric|min:0',
            'price'          => 'required|numeric|min:0',
            'service_charge' => 'nullable|numeric|min:0',
            'status'         => 'required|in:available,reserved,sold,unavailable',
            'description'    => 'nullable|string',
            'features'       => 'nullable|array',
        ]);

        $validated['features'] = $request->input('features', []);
        $unit->update($validated);

        $this->syncPropertyUnitCount($property);

        return redirect()->route('properties.units.index', $property)
            ->with('success', 'Unit updated successfully.');
    }

    public function destroy(Property $property, PropertyUnit $unit)
    {
        $unit->delete();
        $this->syncPropertyUnitCount($property);

        return back()->with('success', 'Unit removed.');
    }

    // ── Reservation ───────────────────────────────────────────────────────────

    public function reserve(Request $request, Property $property, PropertyUnit $unit)
    {
        if ($unit->status !== 'available') {
            return back()->with('error', 'Unit is not available for reservation.');
        }

        $validated = $request->validate([
            'lead_id'           => 'required|exists:leads,id',
            'hold_days'         => 'nullable|integer|min:1|max:90',
            'reservation_notes' => 'nullable|string|max:1000',
        ]);

        $lead = Lead::findOrFail($validated['lead_id']);
        $unit->reserve($lead, $validated['hold_days'] ?? 7, $validated['reservation_notes'] ?? null);
        $this->syncPropertyUnitCount($property);

        return back()->with('success', "Unit {$unit->unit_number} reserved for {$lead->name}.");
    }

    public function release(Property $property, PropertyUnit $unit)
    {
        if ($unit->status !== 'reserved') {
            return back()->with('error', 'Unit is not currently reserved.');
        }

        $unit->release();
        $this->syncPropertyUnitCount($property);

        return back()->with('success', "Unit {$unit->unit_number} is now available.");
    }

    // ── Bulk Actions ─────────────────────────────────────────────────────────

    public function bulkCreate(Request $request, Property $property)
    {
        $validated = $request->validate([
            'prefix'         => 'required|string|max:20',
            'start_number'   => 'required|integer|min:1',
            'count'          => 'required|integer|min:1|max:200',
            'unit_type'      => 'nullable|string|max:100',
            'price'          => 'required|numeric|min:0',
            'service_charge' => 'nullable|numeric|min:0',
            'size_sqm'       => 'nullable|numeric|min:0',
        ]);

        $created = 0;
        for ($i = $validated['start_number']; $i < $validated['start_number'] + $validated['count']; $i++) {
            $unitNumber = $validated['prefix'] . str_pad($i, 2, '0', STR_PAD_LEFT);

            $exists = $property->units()->where('unit_number', $unitNumber)->exists();
            if (!$exists) {
                $property->units()->create([
                    'unit_number'    => $unitNumber,
                    'unit_type'      => $validated['unit_type'] ?? null,
                    'price'          => $validated['price'],
                    'service_charge' => $validated['service_charge'] ?? null,
                    'size_sqm'       => $validated['size_sqm'] ?? null,
                    'status'         => 'available',
                ]);
                $created++;
            }
        }

        $this->syncPropertyUnitCount($property);

        return back()->with('success', "{$created} units created successfully.");
    }

    /**
     * Convert reserved unit to sale transaction with milestones.
     */
    public function convertReservedToSale(Request $request, Property $property, PropertyUnit $unit, \App\Services\SalesService $salesService)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'deal_value' => 'required|numeric|min:0',
            'plan_type' => 'required|in:outright,installment,mortgage',
            'number_of_installments' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'milestones' => 'nullable|array',
            'milestones.*.label' => 'required|string',
            'milestones.*.amount_due' => 'required|numeric|min:0',
            'milestones.*.due_date' => 'required|date',
        ]);

        $salesData = [
            'lead_id' => $validated['lead_id'],
            'property_id' => $property->id,
            'property_unit_id' => $unit->id,
            'deal_value' => $validated['deal_value'],
            'units_purchased' => 1,
            'status' => 'Closed Won',
            'plan_type' => $validated['plan_type'],
            'number_of_installments' => $validated['number_of_installments'] ?? 1,
            'notes' => $validated['notes'] ?? null,
            'milestones' => $validated['milestones'] ?? [],
        ];

        $salesService->recordSale($salesData);
        $this->syncPropertyUnitCount($property);

        return back()->with('success', "Unit {$unit->unit_number} has been converted to Sold, and the payment milestones plan is active.");
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    private function syncPropertyUnitCount(Property $property): void
    {
        $available = $property->units()->where('status', 'available')->count();
        $total     = $property->units()->count();
        $property->update(['available_units' => $available, 'total_units' => $total]);
    }
}
