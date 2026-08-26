<?php

namespace App\Services\Inventory;

use App\Models\Inventory\GoodsReceivedNote;
use App\Models\Inventory\InventoryAnomalyFlag;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\SupplierInvoice;
use App\Services\Accounting\InventoryAccountingBridge;
use Illuminate\Support\Facades\DB;

class ThreeWayMatchService
{
    public function __construct(
        protected InventoryAccountingBridge $accountingBridge
    ) {}
    /**
     * Reconcile Purchase Order vs Goods Received Note vs Supplier Invoice.
     */
    public function matchInvoice(SupplierInvoice $invoice): array
    {
        return DB::transaction(function () use ($invoice) {
            $po = PurchaseOrder::with('items.material')->find($invoice->purchase_order_id);
            $grn = GoodsReceivedNote::with('items.material')->find($invoice->goods_received_note_id);

            if (!$po) {
                $invoice->update([
                    'payment_status' => 'disputed',
                    'discrepancy_notes' => 'Matching failed: Purchase Order record not found.',
                ]);
                return ['status' => 'failed', 'reason' => 'Missing Purchase Order'];
            }

            $discrepancies = [];
            $totalBilled = (float)$invoice->total_amount;
            $totalExpected = 0;

            // If GRN exists, match against actually received and accepted quantities
            if ($grn) {
                foreach ($grn->items as $grnItem) {
                    $poItem = $po->items->firstWhere('material_id', $grnItem->material_id);
                    $unitPrice = $poItem ? (float)$poItem->unit_price : 0;
                    $expectedItemTotal = (float)$grnItem->qty_received * $unitPrice;
                    $totalExpected += $expectedItemTotal;

                    // Check if rejected items were billed
                    if ($grnItem->qty_rejected > 0) {
                        $discrepancies[] = sprintf(
                            'Warning: %s had %s %s rejected at gate delivery (%s). Ensure invoice does not bill for rejected stock.',
                            $grnItem->material->name,
                            $grnItem->qty_rejected,
                            $grnItem->material->unit_of_measure,
                            $grnItem->rejection_reason ?? 'Damaged'
                        );
                    }
                }
            } else {
                // Pre-GRN match against PO grand total
                $totalExpected = (float)$po->total_amount;
            }

            // Include PO tax and delivery fee
            if ($grn) {
                $totalExpected += (float)$po->tax_amount + (float)$po->delivery_fee;
            }

            $priceVariance = round($totalBilled - $totalExpected, 2);
            $priceVariancePct = $totalExpected > 0 ? round(($priceVariance / $totalExpected) * 100, 2) : 0;

            if (abs($priceVariance) > 1.00) { // Tolerates minor ₦1 rounding
                $discrepancies[] = sprintf(
                    'Price Discrepancy: Invoice billed ₦%s vs verified PO/GRN expected ₦%s (Variance: ₦%s / %s%%).',
                    number_format($totalBilled, 2),
                    number_format($totalExpected, 2),
                    number_format($priceVariance, 2),
                    $priceVariancePct
                );
            }

            $matched = empty($discrepancies) || abs($priceVariance) <= 1.00;
            $paymentStatus = $matched ? 'approved_for_payment' : 'disputed';

            $invoice->update([
                'payment_status' => $paymentStatus,
                'discrepancy_notes' => implode("\n", $discrepancies) ?: '3-Way Match verified successfully with zero price or quantity variance.',
                'matched_at' => now(),
            ]);

            // If 3-Way Match Passes: Post Double-Entry Journal (Debit GRNI 2150 & VAT 1400, Credit AP 2100 & WHT 2120)
            if ($matched) {
                $this->accountingBridge->postInvoiceReconciliationEntry($invoice);
            }

            // Create Anomaly Flag if significant variance is detected
            if (!$matched && abs($priceVariancePct) >= 5.0) {
                InventoryAnomalyFlag::create([
                    'site_id' => $po->site_id,
                    'flag_type' => 'price_spike',
                    'title' => "Invoice Overbilling on {$po->ref_number}",
                    'severity' => abs($priceVariancePct) >= 15.0 ? 'high' : 'medium',
                    'description' => sprintf(
                        'Supplier Invoice %s on PO %s overbills verified amount by %s%% (₦%s).',
                        $invoice->invoice_number,
                        $po->ref_number,
                        $priceVariancePct,
                        number_format($priceVariance, 2)
                    ),
                    'flaggable_type' => SupplierInvoice::class,
                    'flaggable_id' => $invoice->id,
                    'status' => 'open',
                ]);
            }

            return [
                'status' => $paymentStatus,
                'matched' => $matched,
                'billed' => $totalBilled,
                'expected' => $totalExpected,
                'variance_pct' => $priceVariancePct,
                'discrepancies' => $discrepancies,
            ];
        });
    }
}
