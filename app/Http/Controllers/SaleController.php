<?php

namespace App\Http\Controllers;

use App\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $property = \App\Models\Property::find($request->property_id);
        $baseDealValue = $request->filled('base_deal_value') && floatval($request->base_deal_value) > 0
            ? floatval($request->base_deal_value)
            : ($property ? floatval($property->price) : 0);

        $interestPct = floatval($request->interest_rate_pct ?? 0);
        $interestAmount = $request->filled('interest_amount')
            ? floatval($request->interest_amount)
            : round(($baseDealValue * $interestPct) / 100, 2);

        $dealValue = $request->filled('deal_value') && floatval($request->deal_value) > 0
            ? floatval($request->deal_value)
            : ($baseDealValue + $interestAmount);

        $durationId = $request->filled('payment_plan_duration_id') 
            ? $request->payment_plan_duration_id 
            : ($request->filled('selected_duration_id') ? $request->selected_duration_id : null);

        // Clean empty string dropdown values and ensure computed deal value
        $request->merge([
            'property_unit_id' => $request->filled('property_unit_id') ? $request->property_unit_id : null,
            'sales_officer_id' => $request->filled('sales_officer_id') ? $request->sales_officer_id : null,
            'payment_plan_duration_id' => $durationId,
            'bank_reference' => $request->filled('bank_reference') ? $request->bank_reference : null,
            'payment_method' => $request->filled('payment_method') ? $request->payment_method : null,
            'deal_closed_at' => $request->filled('deal_closed_at') ? $request->deal_closed_at : null,
            'deal_value' => $dealValue,
            'base_deal_value' => $baseDealValue,
            'interest_amount' => $interestAmount,
        ]);

        // Check if payment_plan_durations table exists before validating against it
        $durationRule = 'nullable';
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('payment_plan_durations')) {
                $durationRule = 'nullable|exists:payment_plan_durations,id';
            }
        } catch (\Throwable $e) {
            // table doesn't exist yet — skip exists check
        }

        $validated = $request->validate([
            'lead_id'                  => 'required|exists:leads,id',
            'property_id'              => 'required|exists:properties,id',
            'property_unit_id'         => 'nullable|exists:property_units,id',
            'sales_officer_id'         => 'nullable|exists:users,id',
            'deal_value'               => 'required|numeric|min:0',
            'base_deal_value'          => 'nullable|numeric|min:0',
            'interest_rate_pct'        => 'nullable|numeric|min:0|max:100',
            'interest_amount'          => 'nullable|numeric|min:0',
            'payment_plan_duration_id' => $durationRule,
            'units_purchased'          => 'required|integer|min:1',
            'plan_type'                => 'nullable|string|in:outright,installment',
            'initial_deposit'          => 'nullable|numeric|min:0',
            'installment_months'       => 'nullable|integer|min:1|max:60',
            'bank_reference'           => 'nullable|string|max:100',
            'payment_method'           => 'nullable|string|max:100',
            'payment_receipt'          => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:10240',
            'deal_closed_at'           => 'nullable|date',
            'send_notification_email'  => 'nullable',
        ]);

        $validated['send_notification_email'] = $request->has('send_notification_email');

        if ($request->hasFile('payment_receipt')) {
            try {
                $path = $request->file('payment_receipt')->store('receipts', 'public');
                $validated['payment_receipt'] = $path;
            } catch (\Throwable $e) {
                Log::warning('Payment receipt file upload failed: ' . $e->getMessage());
            }
        }

        try {
            $sale = $this->salesService->recordSale($validated);

            $msg = 'Sale recorded successfully! Payment plan initialized & official PDF receipt generated.';
            if (!$validated['send_notification_email']) {
                $msg .= ' (Saved in Silent / Historical Mode - No client emails sent).';
            }

            $redirectUrl = route('leads.show', ['lead' => $validated['lead_id'], 'tab' => 'payments']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'redirect' => $redirectUrl,
                ]);
            }

            return redirect()->to($redirectUrl)->with('success', $msg);
        } catch (\Throwable $e) {
            Log::error('Sale recording error:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $errMsg = 'Failed to record sale: ' . $e->getMessage() . ' (in ' . basename($e->getFile()) . ':' . $e->getLine() . ')';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errMsg,
                ], 422);
            }

            return redirect()->route('leads.show', ['lead' => $validated['lead_id'], 'tab' => 'payments'])
                ->withInput()
                ->with('error', $errMsg);
        }
    }
}
