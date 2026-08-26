<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\CompanyInventorySetting;
use Illuminate\Http\Request;

class CompanyInventorySettingController extends Controller
{
    public function edit()
    {
        $settings = CompanyInventorySetting::current();
        return view('inventory.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'po_tier1_max' => 'required|numeric|min:0',
            'po_tier2_max' => 'required|numeric|gt:po_tier1_max',
            'grn_geofence_strict' => 'boolean',
            'after_hours_start' => 'required|date_format:H:i',
            'after_hours_end' => 'required|date_format:H:i',
            'waste_alert_multiplier' => 'required|numeric|min:1|max:10',
            'cement_shelf_life_days' => 'required|integer|min:30|max:365',
            'perfect_match_consecutive_limit' => 'required|integer|min:2|max:20',
            'staff_pairing_occurrences_limit' => 'required|integer|min:2|max:20',
            'price_variance_alert_pct' => 'required|numeric|min:1|max:100',
        ]);

        $validated['grn_geofence_strict'] = $request->boolean('grn_geofence_strict');

        $settings = CompanyInventorySetting::current();
        $settings->update($validated);

        return redirect()->route('inventory.settings.edit')
            ->with('success', 'Construction inventory settings and approval thresholds updated successfully.');
    }
}
