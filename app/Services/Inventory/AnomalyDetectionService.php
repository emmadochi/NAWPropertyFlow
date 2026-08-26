<?php

namespace App\Services\Inventory;

use App\Models\Inventory\CompanyInventorySetting;
use App\Models\Inventory\GoodsReceivedNote;
use App\Models\Inventory\InventoryAnomalyFlag;
use App\Models\Inventory\PriceBenchmark;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\WasteLog;

class AnomalyDetectionService
{
    /**
     * Inspect a Goods Received Note (GRN) for ghost delivery or uninspected delivery patterns.
     */
    public function inspectDelivery(GoodsReceivedNote $grn): array
    {
        $settings = CompanyInventorySetting::current();
        $flagsRaised = [];

        // 1. Geofence Check
        if (!$grn->geofence_check_passed) {
            $flag = InventoryAnomalyFlag::create([
                'site_id' => $grn->site_id,
                'flag_type' => 'ghost_delivery',
                'title' => "Geofence Breach on GRN {$grn->ref_number}",
                'severity' => 'critical',
                'description' => sprintf(
                    'Gate Delivery GRN %s was logged outside the authorized site geofence boundary. Coordinates: Lat %s, Lng %s.',
                    $grn->ref_number,
                    $grn->delivery_gps_lat ?? 'N/A',
                    $grn->delivery_gps_lng ?? 'N/A'
                ),
                'flaggable_type' => GoodsReceivedNote::class,
                'flaggable_id' => $grn->id,
                'status' => 'open',
            ]);
            $flagsRaised[] = $flag;
        }

        // 2. Staff-Vendor Collusion Frequency Check
        $po = $grn->purchaseOrder;
        if ($po && $grn->received_by_user_id) {
            $recentDeliveriesCount = GoodsReceivedNote::where('received_by_user_id', $grn->received_by_user_id)
                ->whereHas('purchaseOrder', function ($q) use ($po) {
                    $q->where('supplier_id', $po->supplier_id);
                })
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            if ($recentDeliveriesCount >= $settings->staff_pairing_occurrences_limit) {
                $flag = InventoryAnomalyFlag::create([
                    'site_id' => $grn->site_id,
                    'flag_type' => 'staff_pairing',
                    'title' => "Repeated Staff-Vendor Pairing: {$po->supplier?->name}",
                    'severity' => 'high',
                    'description' => sprintf(
                        'Storekeeper #%s has personally signed for Supplier #%s (%s) %d times in the last 30 days (Threshold: %d).',
                        $grn->received_by_user_id,
                        $po->supplier_id,
                        $po->supplier?->name,
                        $recentDeliveriesCount,
                        $settings->staff_pairing_occurrences_limit
                    ),
                    'flaggable_type' => GoodsReceivedNote::class,
                    'flaggable_id' => $grn->id,
                    'status' => 'open',
                ]);
                $flagsRaised[] = $flag;
            }
        }

        return $flagsRaised;
    }

    /**
     * Inspect Purchase Order for price spikes above regional market benchmarks.
     */
    public function inspectPurchaseOrderPrices(PurchaseOrder $po): array
    {
        $settings = CompanyInventorySetting::current();
        $flagsRaised = [];

        foreach ($po->items as $item) {
            $benchmark = PriceBenchmark::where('material_id', $item->material_id)
                ->latest('recorded_date')
                ->first();

            if ($benchmark && $benchmark->unit_price > 0) {
                $benchPrice = (float)$benchmark->unit_price;
                $poPrice = (float)$item->unit_price;

                if ($poPrice > $benchPrice) {
                    $diffPct = round((($poPrice - $benchPrice) / $benchPrice) * 100, 1);

                    if ($diffPct >= $settings->price_variance_alert_pct) {
                        $flag = InventoryAnomalyFlag::create([
                            'site_id' => $po->site_id,
                            'flag_type' => 'price_spike',
                            'title' => "Market Price Spike: {$item->material?->name}",
                            'severity' => $diffPct >= 25.0 ? 'critical' : 'high',
                            'description' => sprintf(
                                '%s quoted at ₦%s on PO %s exceeds regional benchmark (₦%s) by %s%% (Alert Threshold: %s%%).',
                                $item->material?->name,
                                number_format($poPrice, 2),
                                $po->ref_number,
                                number_format($benchPrice, 2),
                                $diffPct,
                                $settings->price_variance_alert_pct
                            ),
                            'flaggable_type' => PurchaseOrder::class,
                            'flaggable_id' => $po->id,
                            'status' => 'open',
                        ]);
                        $flagsRaised[] = $flag;
                    }
                }
            }
        }

        return $flagsRaised;
    }

    /**
     * Inspect waste logs for sudden anomalous spikes.
     */
    public function inspectWasteLog(WasteLog $waste): ?InventoryAnomalyFlag
    {
        $settings = CompanyInventorySetting::current();

        $avgWaste = WasteLog::where('site_id', $waste->site_id)
            ->where('material_id', $waste->material_id)
            ->where('id', '!=', $waste->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->avg('qty');

        if ($avgWaste && $avgWaste > 0) {
            $multiplier = $waste->qty / $avgWaste;
            if ($multiplier >= $settings->waste_alert_multiplier) {
                return InventoryAnomalyFlag::create([
                    'site_id' => $waste->site_id,
                    'flag_type' => 'waste_spike',
                    'title' => "Waste Spike: {$waste->material?->name}",
                    'severity' => $multiplier >= 3.0 ? 'critical' : 'high',
                    'description' => sprintf(
                        'Wasted %s %s (%s) is %sx higher than the 30-day site average (%s). Type: %s.',
                        $waste->qty,
                        $waste->material?->unit_of_measure,
                        $waste->material?->name,
                        round($multiplier, 1),
                        round($avgWaste, 2),
                        $waste->waste_type
                    ),
                    'flaggable_type' => WasteLog::class,
                    'flaggable_id' => $waste->id,
                    'status' => 'open',
                ]);
            }
        }

        return null;
    }
}
