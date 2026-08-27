<?php

namespace App\Http\Controllers;

use App\Services\SalesService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    protected $salesService;

    public function __construct(SalesService $salesService)
    {
        $this->salesService = $salesService;
    }

    /**
     * Store new sale transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'property_id' => 'required|exists:properties,id',
            'property_unit_id' => 'nullable|exists:property_units,id',
            'sales_officer_id' => 'nullable|exists:users,id',
            'deal_value' => 'required|numeric|min:0',
            'base_deal_value' => 'nullable|numeric|min:0',
            'interest_rate_pct' => 'nullable|numeric|min:0|max:100',
            'interest_amount' => 'nullable|numeric|min:0',
            'payment_plan_duration_id' => 'nullable|exists:payment_plan_durations,id',
            'units_purchased' => 'required|integer|min:1',
            'plan_type' => 'nullable|string|in:outright,installment',
            'initial_deposit' => 'nullable|numeric|min:0',
            'installment_months' => 'nullable|integer|min:1|max:60',
            'bank_reference' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:100',
            'payment_receipt' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:10240',
            'deal_closed_at' => 'nullable|date',
            'send_notification_email' => 'nullable',
        ]);

        $validated['send_notification_email'] = $request->has('send_notification_email');

        if ($request->hasFile('payment_receipt')) {
            $path = $request->file('payment_receipt')->store('receipts', 'public');
            $validated['payment_receipt'] = $path;
        }

        $sale = $this->salesService->recordSale($validated);

        $msg = 'Sale recorded successfully! Payment plan initialized & official PDF receipt generated.';
        if (!$validated['send_notification_email']) {
            $msg .= ' (Saved in Silent / Historical Mode - No client emails sent).';
        }

        return back()->with('success', $msg);
    }
}
