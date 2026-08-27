<?php

namespace App\Services\Accounting;

use App\Models\Accounting\TaxRecord;
use App\Models\Inventory\SupplierInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaxComplianceService
{
    /**
     * Withholding Tax (WHT 5% & 10%) Schedule for FIRS Remittance
     */
    public function getWhtSchedule(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfYear();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        // 1. Pull 5% WHT from 3-Way Matched Supplier Invoices
        $invoices = SupplierInvoice::with('supplier')
            ->whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('payment_status', ['approved_for_payment', 'paid'])
            ->get();

        $whtRows = [];
        $totalGross = 0;
        $totalWht = 0;

        foreach ($invoices as $inv) {
            $gross = (float) $inv->total_amount;
            $whtAmount = round($gross * 0.05, 2); // 5% standard construction WHT
            $netPayable = $gross - $whtAmount;

            $totalGross += $gross;
            $totalWht += $whtAmount;

            $whtRows[] = [
                'type' => 'WHT 5%',
                'beneficiary' => $inv->supplier?->name ?? 'Vendor',
                'beneficiary_tin' => $inv->supplier?->code ?? 'N/A',
                'reference' => $inv->invoice_number,
                'date' => $inv->invoice_date->toDateString(),
                'gross_amount' => $gross,
                'rate_pct' => 5.0,
                'wht_amount' => $whtAmount,
                'net_payable' => $netPayable,
                'status' => $inv->payment_status === 'paid' ? 'remitted' : 'accrued',
            ];
        }

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'rows' => $whtRows,
            'total_gross' => $totalGross,
            'total_wht' => $totalWht,
            'count' => count($whtRows),
        ];
    }

    /**
     * Value Added Tax (VAT 7.5%) Monthly Summary
     */
    public function getVatSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfMonth();

        // Output VAT (7.5% on commercial property sales & management fees)
        $outputVat = (float) DB::table('inventory_journal_items as ji')
            ->join('inventory_journal_entries as je', 'ji.journal_entry_id', '=', 'je.id')
            ->whereBetween('je.entry_date', [$start->toDateString(), $end->toDateString()])
            ->where('ji.account_code', '2130')
            ->where('ji.entry_type', 'credit')
            ->sum('ji.amount');

        // Input VAT (7.5% paid on materials and vendor purchases)
        $inputVat = (float) DB::table('inventory_journal_items as ji')
            ->join('inventory_journal_entries as je', 'ji.journal_entry_id', '=', 'je.id')
            ->whereBetween('je.entry_date', [$start->toDateString(), $end->toDateString()])
            ->where('ji.account_code', '1400')
            ->where('ji.entry_type', 'debit')
            ->sum('ji.amount');

        $netVatPayable = max(0, $outputVat - $inputVat);

        return [
            'period' => $start->format('F Y'),
            'output_vat' => $outputVat,
            'input_vat' => $inputVat,
            'net_vat_payable' => $netVatPayable,
        ];
    }
}
