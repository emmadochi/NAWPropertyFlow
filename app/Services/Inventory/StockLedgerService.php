<?php

namespace App\Services\Inventory;

use App\Models\Inventory\MaterialCatalogue;
use App\Models\Inventory\SiteStock;
use App\Models\Inventory\StockBatch;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockLedgerService
{
    /**
     * Credit stock on delivery (GRN) or inter-site receipt.
     */
    public function creditStock(
        int $siteId,
        int $materialId,
        float $qty,
        ?string $batchNumber = null,
        ?string $manufactureDate = null,
        ?string $expiryDate = null,
        ?int $grnId = null,
        ?int $userId = null
    ): SiteStock {
        return DB::transaction(function () use ($siteId, $materialId, $qty, $batchNumber, $manufactureDate, $expiryDate, $grnId, $userId) {
            $stock = SiteStock::firstOrCreate(
                ['site_id' => $siteId, 'material_id' => $materialId],
                ['qty_on_hand' => 0, 'qty_reserved' => 0, 'qty_quarantined' => 0]
            );

            $stock->increment('qty_on_hand', $qty);

            if ($batchNumber) {
                StockBatch::create([
                    'site_stock_id' => $stock->id,
                    'batch_number' => $batchNumber,
                    'manufacture_date' => $manufactureDate,
                    'expiry_date' => $expiryDate,
                    'qty_received' => $qty,
                    'qty_remaining' => $qty,
                    'received_on_grn_id' => $grnId,
                    'qc_status' => 'pass',
                ]);
            }

            if ($userId) {
                $stock->update([
                    'last_physical_count_at' => now(),
                    'last_count_by_user_id' => $userId,
                ]);
            }

            return $stock->fresh();
        });
    }

    /**
     * Debit stock for site issue (MIV) or waste using FIFO batch deduction.
     */
    public function debitStock(
        int $siteId,
        int $materialId,
        float $qty,
        ?int $stockBatchId = null
    ): SiteStock {
        return DB::transaction(function () use ($siteId, $materialId, $qty, $stockBatchId) {
            $stock = SiteStock::where('site_id', $siteId)
                ->where('material_id', $materialId)
                ->lockForUpdate()
                ->first();

            if (!$stock || $stock->qty_on_hand < $qty) {
                $available = $stock ? $stock->qty_on_hand : 0;
                throw new InvalidArgumentException("Insufficient stock on site. Requested: {$qty}, Available: {$available}");
            }

            $stock->decrement('qty_on_hand', $qty);

            // Deduct from specific batch or use FIFO across active batches
            if ($stockBatchId) {
                $batch = StockBatch::find($stockBatchId);
                if ($batch && $batch->site_stock_id === $stock->id) {
                    $deduct = min($qty, $batch->qty_remaining);
                    $batch->decrement('qty_remaining', $deduct);
                }
            } else {
                $remainingToDeduct = $qty;
                $activeBatches = StockBatch::where('site_stock_id', $stock->id)
                    ->where('qty_remaining', '>', 0)
                    ->orderBy('manufacture_date', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($activeBatches as $batch) {
                    if ($remainingToDeduct <= 0) break;
                    $deduct = min($remainingToDeduct, $batch->qty_remaining);
                    $batch->decrement('qty_remaining', $deduct);
                    $remainingToDeduct -= $deduct;
                }
            }

            return $stock->fresh();
        });
    }

    /**
     * Check reorder and health status for a site material balance.
     */
    public function getStockStatus(SiteStock $stock): string
    {
        $mat = $stock->material;
        if (!$mat) return 'unknown';

        if ($stock->qty_on_hand <= $mat->safety_stock_level) {
            return 'critical_low';
        }

        if ($stock->qty_on_hand <= $mat->reorder_level) {
            return 'reorder_warning';
        }

        return 'healthy';
    }
}
