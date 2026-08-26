<?php

namespace App\Services\Accounting;

use App\Models\Expense;
use App\Models\Inventory\GoodsReceivedNote;
use App\Models\Inventory\InventoryJournalEntry;
use App\Models\Inventory\MaterialIssueVoucher;
use App\Models\Inventory\SupplierInvoice;
use App\Models\Inventory\WasteLog;
use Illuminate\Support\Facades\DB;

class InventoryAccountingBridge
{
    /**
     * Post Double-Entry Journal for Goods Received Note (Capitalize Inventory Asset).
     */
    public function postGoodsReceivedEntry(GoodsReceivedNote $grn, ?int $userId = null): InventoryJournalEntry
    {
        return DB::transaction(function () use ($grn, $userId) {
            $po = $grn->purchaseOrder;
            $site = $grn->site;

            $totalValue = 0;
            foreach ($grn->items as $item) {
                $poItem = $po ? $po->items->firstWhere('material_id', $item->material_id) : null;
                $unitPrice = $poItem ? (float)$poItem->unit_price : ($item->unit_price_confirmed ?? 0);
                $totalValue += (float)$item->qty_received * $unitPrice;
            }

            if ($totalValue <= 0 && $po) {
                $totalValue = (float)$po->total_amount;
            }

            $count = InventoryJournalEntry::count() + 1;
            $entryNumber = sprintf('JE-%s-%05d', date('Y'), $count);

            $entry = InventoryJournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $grn->delivery_date ?? date('Y-m-d'),
                'reference_type' => GoodsReceivedNote::class,
                'reference_id' => $grn->id,
                'site_id' => $site?->id,
                'project_id' => $site?->project_id,
                'description' => "Capitalize physical inventory from delivery {$grn->ref_number} (PO: {$po?->ref_number})",
                'total_debit' => $totalValue,
                'total_credit' => $totalValue,
                'is_balanced' => true,
                'posted_by_user_id' => $userId ?? $grn->received_by_user_id,
            ]);

            // DEBIT: 1300 Construction Materials Inventory Asset
            $entry->items()->create([
                'account_code' => '1300',
                'entry_type' => 'debit',
                'amount' => $totalValue,
                'narration' => "Material stock asset received at site {$site?->name}",
            ]);

            // CREDIT: 2150 Goods Received Not Invoiced (GRNI Accrual)
            $entry->items()->create([
                'account_code' => '2150',
                'entry_type' => 'credit',
                'amount' => $totalValue,
                'narration' => "Unbilled delivery accrual for {$po?->supplier?->name}",
            ]);

            return $entry;
        });
    }

    /**
     * Post Double-Entry Journal for Approved Supplier Invoice (Clear GRNI, Record AP & WHT).
     */
    public function postInvoiceReconciliationEntry(SupplierInvoice $invoice, ?int $userId = null): InventoryJournalEntry
    {
        return DB::transaction(function () use ($invoice, $userId) {
            $po = $invoice->purchaseOrder;
            $site = $po?->site;

            $totalBilled = (float)$invoice->total_amount;
            $taxAmount = (float)$invoice->tax_amount;
            $subtotal = $totalBilled - $taxAmount;

            // 5% Nigerian Withholding Tax on construction supply subtotal
            $whtAmount = round($subtotal * 0.05, 2);
            $netPayableToVendor = $totalBilled - $whtAmount;

            $count = InventoryJournalEntry::count() + 1;
            $entryNumber = sprintf('JE-%s-%05d', date('Y'), $count);

            $entry = InventoryJournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $invoice->invoice_date ?? date('Y-m-d'),
                'reference_type' => SupplierInvoice::class,
                'reference_id' => $invoice->id,
                'site_id' => $site?->id,
                'project_id' => $site?->project_id,
                'description' => "Reconcile vendor invoice {$invoice->invoice_number} (PO: {$po?->ref_number}) with 5% WHT deduction",
                'total_debit' => $totalBilled,
                'total_credit' => $totalBilled,
                'is_balanced' => true,
                'posted_by_user_id' => $userId ?? $invoice->matched_by_user_id,
            ]);

            // DEBIT: 2150 Clear GRNI Accrual
            $entry->items()->create([
                'account_code' => '2150',
                'entry_type' => 'debit',
                'amount' => $subtotal,
                'narration' => "Clear GRNI accrual on invoice {$invoice->invoice_number}",
            ]);

            // DEBIT: 1400 Input VAT (if applicable)
            if ($taxAmount > 0) {
                $entry->items()->create([
                    'account_code' => '1400',
                    'entry_type' => 'debit',
                    'amount' => $taxAmount,
                    'narration' => "Input VAT 7.5% recoverable on {$invoice->invoice_number}",
                ]);
            }

            // CREDIT: 2100 Accounts Payable Net
            $entry->items()->create([
                'account_code' => '2100',
                'entry_type' => 'credit',
                'amount' => $netPayableToVendor,
                'narration' => "Net trade payable liability to {$invoice->supplier?->name}",
            ]);

            // CREDIT: 2120 WHT 5% Payable
            if ($whtAmount > 0) {
                $entry->items()->create([
                    'account_code' => '2120',
                    'entry_type' => 'credit',
                    'amount' => $whtAmount,
                    'narration' => "5% WHT payable to Tax Authority on {$invoice->invoice_number}",
                ]);
            }

            // Sync with main CRM Expenses record for P&L tracking
            Expense::create([
                'user_id' => $userId ?? ($invoice->payment_approved_by_user_id ?? 1),
                'approved_by' => $invoice->payment_approved_by_user_id ?? $userId,
                'title' => "Procurement: {$invoice->supplier?->name} ({$invoice->invoice_number})",
                'category' => 'Construction',
                'amount' => $totalBilled,
                'expense_date' => $invoice->invoice_date ?? date('Y-m-d'),
                'status' => 'approved',
                'payment_method' => 'Bank Transfer',
                'vendor_name' => $invoice->supplier?->name,
                'reference_number' => $invoice->invoice_number,
                'notes' => "Auto-posted from 3-Way Match for PO {$po?->ref_number} (Site: {$site?->name})",
                'approved_at' => now(),
            ]);

            return $entry;
        });
    }

    /**
     * Post Double-Entry Journal for Material Issue Voucher (Job Costing / WIP Expensing).
     */
    public function postMaterialIssuanceEntry(MaterialIssueVoucher $miv, ?int $userId = null): InventoryJournalEntry
    {
        return DB::transaction(function () use ($miv, $userId) {
            $site = $miv->site;

            $totalValue = 0;
            foreach ($miv->items as $item) {
                $material = $item->material;
                $unitCost = $material ? (float)$material->standard_unit_cost : 0;
                $totalValue += (float)$item->qty_issued * $unitCost;
            }

            $count = InventoryJournalEntry::count() + 1;
            $entryNumber = sprintf('JE-%s-%05d', date('Y'), $count);

            $entry = InventoryJournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => date('Y-m-d'),
                'reference_type' => MaterialIssueVoucher::class,
                'reference_id' => $miv->id,
                'site_id' => $site?->id,
                'project_id' => $site?->project_id,
                'description' => "Material issuance {$miv->ref_number} for activity: {$miv->activity_name}",
                'total_debit' => $totalValue,
                'total_credit' => $totalValue,
                'is_balanced' => true,
                'posted_by_user_id' => $userId ?? $miv->issued_by_user_id,
            ]);

            // DEBIT: 5100 Direct Construction Materials & Job Costing (WIP)
            $entry->items()->create([
                'account_code' => '5100',
                'entry_type' => 'debit',
                'amount' => $totalValue,
                'narration' => "Material cost charged to Project #{$site?->project_id} ({$miv->activity_name})",
            ]);

            // CREDIT: 1300 Construction Materials Inventory Asset
            $entry->items()->create([
                'account_code' => '1300',
                'entry_type' => 'credit',
                'amount' => $totalValue,
                'narration' => "Inventory asset reduction on site store disbursement",
            ]);

            return $entry;
        });
    }

    /**
     * Post Double-Entry Journal for Material Waste & Scrap Loss.
     */
    public function postWasteWriteOffEntry(WasteLog $waste, ?int $userId = null): InventoryJournalEntry
    {
        return DB::transaction(function () use ($waste, $userId) {
            $site = $waste->site;
            $material = $waste->material;
            $unitCost = $material ? (float)$material->standard_unit_cost : 0;
            $totalValue = (float)$waste->qty * $unitCost;

            $count = InventoryJournalEntry::count() + 1;
            $entryNumber = sprintf('JE-%s-%05d', date('Y'), $count);

            $entry = InventoryJournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => date('Y-m-d'),
                'reference_type' => WasteLog::class,
                'reference_id' => $waste->id,
                'site_id' => $site?->id,
                'project_id' => $site?->project_id,
                'description' => "Material loss write-off: {$waste->qty} {$material?->unit_of_measure} of {$material?->name} ({$waste->waste_type})",
                'total_debit' => $totalValue,
                'total_credit' => $totalValue,
                'is_balanced' => true,
                'posted_by_user_id' => $userId ?? $waste->logged_by_user_id,
            ]);

            // DEBIT: 5190 Material Shrinkage & Loss Write-Off
            $entry->items()->create([
                'account_code' => '5190',
                'entry_type' => 'debit',
                'amount' => $totalValue,
                'narration' => "Scrap loss expensed: {$waste->description}",
            ]);

            // CREDIT: 1300 Construction Materials Inventory Asset
            $entry->items()->create([
                'account_code' => '1300',
                'entry_type' => 'credit',
                'amount' => $totalValue,
                'narration' => "Asset write-off for damaged materials at {$site?->name}",
            ]);

            return $entry;
        });
    }
}
