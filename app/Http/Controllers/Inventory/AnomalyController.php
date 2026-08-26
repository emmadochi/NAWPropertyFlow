<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryAnomalyFlag;
use App\Models\Inventory\InventorySite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnomalyController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryAnomalyFlag::with(['site.project', 'resolver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('flag_type')) {
            $query->where('flag_type', $request->flag_type);
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        $anomalies = $query->latest()->paginate(20);
        $sites = InventorySite::where('is_active', true)->orderBy('name')->get();

        $openCount = InventoryAnomalyFlag::where('status', 'open')->count();
        $criticalCount = InventoryAnomalyFlag::where('status', 'open')->where('severity', 'critical')->count();

        return view('inventory.anomalies.index', compact('anomalies', 'sites', 'openCount', 'criticalCount'));
    }

    public function show(InventoryAnomalyFlag $anomaly)
    {
        $anomaly->load(['site.project', 'resolver']);
        return view('inventory.anomalies.show', compact('anomaly'));
    }

    public function updateStatus(Request $request, InventoryAnomalyFlag $anomaly)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,under_review,resolved,dismissed',
            'resolution_notes' => 'required|string|max:1000',
        ]);

        $anomaly->update([
            'status' => $validated['status'],
            'resolution_notes' => $validated['resolution_notes'],
            'resolved_by_user_id' => Auth::id(),
            'resolved_at' => now(),
        ]);

        return redirect()->route('inventory.anomalies.show', $anomaly)
            ->with('success', "Anomaly incident #{$anomaly->id} updated to {$validated['status']}.");
    }
}
