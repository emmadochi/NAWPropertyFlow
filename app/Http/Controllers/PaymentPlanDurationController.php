<?php

namespace App\Http\Controllers;

use App\Models\PaymentPlanDuration;
use Illuminate\Http\Request;

class PaymentPlanDurationController extends Controller
{
    /**
     * Display listing of payment plan durations and interest settings.
     */
    public function index()
    {
        $durations = PaymentPlanDuration::orderBy('sort_order', 'asc')
            ->orderBy('duration_months', 'asc')
            ->get();

        return view('settings.payment-plans.index', compact('durations'));
    }

    /**
     * Store a newly created payment plan duration.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration_months' => 'required|integer|min:0|max:120',
            'interest_rate_pct' => 'required|numeric|min:0|max:100',
            'initial_deposit_pct' => 'required|numeric|min:0|max:100',
            'number_of_installments' => 'required|integer|min:1|max:120',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? (PaymentPlanDuration::max('sort_order') + 1);

        PaymentPlanDuration::create($validated);

        return redirect()->route('settings.payment-plans.index')
            ->with('success', 'Payment plan duration & interest rate created successfully.');
    }

    /**
     * Update the specified payment plan duration.
     */
    public function update(Request $request, PaymentPlanDuration $paymentPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration_months' => 'required|integer|min:0|max:120',
            'interest_rate_pct' => 'required|numeric|min:0|max:100',
            'initial_deposit_pct' => 'required|numeric|min:0|max:100',
            'number_of_installments' => 'required|integer|min:1|max:120',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $paymentPlan->update($validated);

        return redirect()->route('settings.payment-plans.index')
            ->with('success', 'Payment plan duration updated successfully.');
    }

    /**
     * Toggle active status.
     */
    public function toggle(PaymentPlanDuration $paymentPlan)
    {
        $paymentPlan->update([
            'is_active' => !$paymentPlan->is_active
        ]);

        $status = $paymentPlan->is_active ? 'activated' : 'deactivated';
        return redirect()->route('settings.payment-plans.index')
            ->with('success', "Payment plan {$paymentPlan->name} {$status}.");
    }

    /**
     * Remove the specified payment plan duration.
     */
    public function destroy(PaymentPlanDuration $paymentPlan)
    {
        $paymentPlan->delete();

        return redirect()->route('settings.payment-plans.index')
            ->with('success', 'Payment plan duration deleted successfully.');
    }
}
