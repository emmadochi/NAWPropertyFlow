<?php

namespace App\Services\Inventory;

use App\Models\Inventory\CompanyInventorySetting;
use App\Models\Inventory\GoodsReceivedNote;
use App\Models\Inventory\GrnItem;
use App\Models\Inventory\InventorySite;
use App\Models\Inventory\MaterialIssueVoucher;
use App\Models\Inventory\MaterialRequisition;
use App\Models\Inventory\MaterialRequisitionItem;
use App\Models\Inventory\MivItem;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\PurchaseOrderItem;
use App\Models\Inventory\WasteLog;
use App\Services\Accounting\InventoryAccountingBridge;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProcurementService
{
    public function __construct(
        protected BOMEngine $bomEngine,
        protected StockLedgerService $stockLedger,
        protected AnomalyDetectionService $anomalyService,
        protected InventoryAccountingBridge $accountingBridge
    ) {}

    /**
     * Raise a new Material Requisition Form (MRF) with automatic BOM analysis.
     */
    public function createRequisition(array $data, int $userId): MaterialRequisition
    {
        return DB::transaction(function () use ($data, $userId) {
            $year = date('Y');
            $count = MaterialRequisition::whereYear('created_at', $year)->count() + 1;
            $refNumber = sprintf('MRF-%s-%04d', $year, $count);

            $site = InventorySite::findOrFail($data['site_id']);

            $requisition = MaterialRequisition::create([
                'ref_number' => $refNumber,
                'site_id' => $site->id,
                'project_id' => $site->project_id,
                'requested_by_user_id' => $userId,
                'activity_name' => $data['activity_name'],
                'required_date' => $data['required_date'],
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            $workQuantity = (float)($data['work_quantity'] ?? 1);
            $validatedItems = $this->bomEngine->validateRequisitionItems(
                $data['items'],
                $data['activity_name'],
                $workQuantity,
                $site->project_id
            );

            foreach ($validatedItems as $item) {
                MaterialRequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'material_id' => $item['material_id'],
                    'qty_requested' => $item['qty_requested'],
                    'qty_approved' => $item['qty_approved'],
                    'bom_expected_qty' => $item['bom_expected_qty'],
                    'variance_flag' => $item['variance_flag'],
                    'variance_reason' => $item['variance_reason'],
                ]);
            }

            return $requisition->load('items.material');
        });
    }

    /**
     * Approve MRF with optional quantity adjustments.
     */
    public function approveRequisition(MaterialRequisition $mrf, int $userId, ?array $approvedItems = null): MaterialRequisition
    {
        return DB::transaction(function () use ($mrf, $userId, $approvedItems) {
            if ($approvedItems) {
                foreach ($approvedItems as $itemId => $qty) {
                    MaterialRequisitionItem::where('id', $itemId)
                        ->where('requisition_id', $mrf->id)
                        ->update(['qty_approved' => $qty]);
                }
            }

            $mrf->update([
                'status' => 'approved',
                'approved_by_user_id' => $userId,
                'approved_at' => now(),
            ]);

            return $mrf->fresh();
        });
    }

    /**
     * Create a Purchase Order (PO) and assign its approval tier according to company thresholds.
     */
    public function createPurchaseOrder(array $data, int $userId): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $userId) {
            $year = date('Y');
            $count = PurchaseOrder::whereYear('created_at', $year)->count() + 1;
            $refNumber = sprintf('PO-%s-%04d', $year, $count);

            $subtotal = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $qty = (float)$item['qty_ordered'];
                $price = (float)$item['unit_price'];
                $total = round($qty * $price, 2);
                $subtotal += $total;

                $itemsData[] = [
                    'material_id' => $item['material_id'],
                    'qty_ordered' => $qty,
                    'unit_price' => $price,
                    'total_price' => $total,
                ];
            }

            $tax = (float)($data['tax_amount'] ?? 0);
            $deliveryFee = (float)($data['delivery_fee'] ?? 0);
            $grandTotal = $subtotal + $tax + $deliveryFee;

            // Determine approval tier based on configurable company inventory settings
            $settings = CompanyInventorySetting::current();
            if ($grandTotal <= $settings->po_tier1_max) {
                $tier = 'tier1';
                $status = 'pending_t1';
            } elseif ($grandTotal <= $settings->po_tier2_max) {
                $tier = 'tier2';
                $status = 'pending_t2';
            } else {
                $tier = 'tier3';
                $status = 'pending_t3';
            }

            $po = PurchaseOrder::create([
                'ref_number' => $refNumber,
                'requisition_id' => $data['requisition_id'] ?? null,
                'site_id' => $data['site_id'],
                'supplier_id' => $data['supplier_id'],
                'created_by_user_id' => $userId,
                'status' => $status,
                'subtotal_amount' => $subtotal,
                'tax_amount' => $tax,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $grandTotal,
                'approval_tier' => $tier,
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
            ]);

            foreach ($itemsData as $item) {
                $po->items()->create($item);
            }

            // Real-time Anomaly Scan: Price Spikes vs Market Benchmarks
            $this->anomalyService->inspectPurchaseOrderPrices($po);

            return $po->load('items.material');
        });
    }

    /**
     * Authorize PO through tier gates.
     */
    public function approvePurchaseOrder(PurchaseOrder $po, int $userId): PurchaseOrder
    {
        return DB::transaction(function () use ($po, $userId) {
            $po->update([
                'status' => 'approved',
                'approved_by_user_id' => $userId,
                'approved_at' => now(),
            ]);

            return $po->fresh();
        });
    }

    /**
     * Receive gate delivery (GRN) with GPS geofence validation and stock credit.
     */
    public function receiveGoods(array $data, int $userId): GoodsReceivedNote
    {
        return DB::transaction(function () use ($data, $userId) {
            $po = PurchaseOrder::with('items')->findOrFail($data['purchase_order_id']);
            $site = InventorySite::findOrFail($po->site_id);

            $year = date('Y');
            $count = GoodsReceivedNote::whereYear('created_at', $year)->count() + 1;
            $refNumber = sprintf('GRN-%s-%04d', $year, $count);

            // Geofence check
            $geofencePassed = true;
            if (!empty($data['delivery_gps_lat']) && !empty($data['delivery_gps_lng']) && $site->gps_lat && $site->gps_lng) {
                $distanceMeters = $this->calculateDistanceMeters(
                    (float)$data['delivery_gps_lat'],
                    (float)$data['delivery_gps_lng'],
                    (float)$site->gps_lat,
                    (float)$site->gps_lng
                );

                if ($distanceMeters > $site->geofence_radius_meters) {
                    $geofencePassed = false;
                }
            }

            $grn = GoodsReceivedNote::create([
                'ref_number' => $refNumber,
                'purchase_order_id' => $po->id,
                'site_id' => $site->id,
                'received_by_user_id' => $userId,
                'delivery_date' => $data['delivery_date'] ?? date('Y-m-d'),
                'delivery_time' => $data['delivery_time'] ?? date('H:i:s'),
                'waybill_number' => $data['waybill_number'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'driver_phone' => $data['driver_phone'] ?? null,
                'vehicle_plate' => $data['vehicle_plate'] ?? null,
                'delivery_gps_lat' => $data['delivery_gps_lat'] ?? null,
                'delivery_gps_lng' => $data['delivery_gps_lng'] ?? null,
                'geofence_check_passed' => $geofencePassed,
                'photo_evidence_paths' => $data['photo_evidence_paths'] ?? null,
                'status' => 'complete',
                'remarks' => $data['remarks'] ?? null,
            ]);

            // Process items & update stock ledger
            $allDelivered = true;
            foreach ($data['items'] as $item) {
                $poItem = $po->items->firstWhere('id', $item['po_item_id'] ?? null);
                $materialId = $item['material_id'] ?? ($poItem ? $poItem->material_id : null);
                $qtyReceived = (float)$item['qty_received'];
                $qtyRejected = (float)($item['qty_rejected'] ?? 0);

                GrnItem::create([
                    'grn_id' => $grn->id,
                    'po_item_id' => $poItem?->id,
                    'material_id' => $materialId,
                    'qty_ordered' => $poItem ? $poItem->qty_ordered : $qtyReceived,
                    'qty_received' => $qtyReceived,
                    'qty_rejected' => $qtyRejected,
                    'rejection_reason' => $item['rejection_reason'] ?? null,
                    'batch_number' => $item['batch_number'] ?? null,
                    'manufacture_date' => $item['manufacture_date'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'unit_price_confirmed' => $poItem ? $poItem->unit_price : null,
                ]);

                // Update PO item cumulative delivered
                if ($poItem) {
                    $poItem->increment('qty_delivered_cumulative', $qtyReceived);
                    if ($poItem->qty_delivered_cumulative < $poItem->qty_ordered) {
                        $allDelivered = false;
                    }
                }

                // Credit active site stock balance
                if ($qtyReceived > 0) {
                    $this->stockLedger->creditStock(
                        siteId: $site->id,
                        materialId: $materialId,
                        qty: $qtyReceived,
                        batchNumber: $item['batch_number'] ?? null,
                        manufactureDate: $item['manufacture_date'] ?? null,
                        expiryDate: $item['expiry_date'] ?? null,
                        grnId: $grn->id,
                        userId: $userId
                    );
                }
            }

            $po->update([
                'status' => $allDelivered ? 'delivered' : 'partially_delivered',
            ]);

            // Real-time Anomaly Scan: Geofence, Collusion, Short Landings
            $this->anomalyService->inspectDelivery($grn);

            // Double-Entry Ledger: Capitalize Inventory Asset (Debit 1300, Credit 2150 GRNI)
            $this->accountingBridge->postGoodsReceivedEntry($grn, $userId);

            return $grn->load('items.material');
        });
    }

    /**
     * Issue materials from site store to site engineer/foreman with digital signatures.
     */
    public function issueMaterial(array $data, int $userId): MaterialIssueVoucher
    {
        return DB::transaction(function () use ($data, $userId) {
            $year = date('Y');
            $count = MaterialIssueVoucher::whereYear('created_at', $year)->count() + 1;
            $refNumber = sprintf('MIV-%s-%04d', $year, $count);

            $miv = MaterialIssueVoucher::create([
                'ref_number' => $refNumber,
                'site_id' => $data['site_id'],
                'issued_by_user_id' => $userId,
                'received_by_user_id' => $data['received_by_user_id'],
                'activity_name' => $data['activity_name'],
                'work_quantity' => $data['work_quantity'] ?? null,
                'work_unit' => $data['work_unit'] ?? null,
                'bom_expected_quantities' => $data['bom_expected_quantities'] ?? null,
                'status' => 'issued',
                'foreman_signature_data' => $data['foreman_signature_data'] ?? null,
                'storekeeper_signature_data' => $data['storekeeper_signature_data'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $qtyIssued = (float)$item['qty_issued'];
                $stockBatchId = !empty($item['stock_batch_id']) ? (int)$item['stock_batch_id'] : null;

                MivItem::create([
                    'miv_id' => $miv->id,
                    'material_id' => $item['material_id'],
                    'stock_batch_id' => $stockBatchId,
                    'qty_requested' => $item['qty_requested'] ?? $qtyIssued,
                    'qty_issued' => $qtyIssued,
                    'qty_returned' => 0,
                    'consumption_rate_variance_pct' => $item['consumption_rate_variance_pct'] ?? 0,
                    'variance_flagged' => !empty($item['variance_flagged']),
                ]);

                // Debit active stock balance
                $this->stockLedger->debitStock(
                    siteId: $data['site_id'],
                    materialId: (int)$item['material_id'],
                    qty: $qtyIssued,
                    stockBatchId: $stockBatchId
                );
            }

            // Double-Entry Ledger: Job Costing / Project WIP Expensing (Debit 5100, Credit 1300 Asset)
            $this->accountingBridge->postMaterialIssuanceEntry($miv, $userId);

            return $miv->load('items.material');
        });
    }

    /**
     * Log material waste and damage incidents.
     */
    public function logWaste(array $data, int $userId): WasteLog
    {
        return DB::transaction(function () use ($data, $userId) {
            $waste = WasteLog::create([
                'site_id' => $data['site_id'],
                'material_id' => $data['material_id'],
                'miv_id' => $data['miv_id'] ?? null,
                'qty' => (float)$data['qty'],
                'waste_type' => $data['waste_type'],
                'activity_name' => $data['activity_name'] ?? null,
                'responsible_team' => $data['responsible_team'] ?? null,
                'description' => $data['description'],
                'photo_paths' => $data['photo_paths'] ?? null,
                'weather_condition' => $data['weather_condition'] ?? null,
                'insurance_claim_raised' => !empty($data['insurance_claim_raised']),
                'logged_by_user_id' => $userId,
            ]);

            // If not logged against an already debited MIV, debit the stock directly
            if (empty($data['miv_id']) && !empty($data['deduct_from_stock'])) {
                $this->stockLedger->debitStock(
                    siteId: (int)$data['site_id'],
                    materialId: (int)$data['material_id'],
                    qty: (float)$data['qty']
                );
            }

            // Real-time Anomaly Scan: Waste Multiplier Spike
            $this->anomalyService->inspectWasteLog($waste);

            // Double-Entry Ledger: Loss Write-Off (Debit 5190, Credit 1300 Asset)
            $this->accountingBridge->postWasteWriteOffEntry($waste, $userId);

            return $waste->load('material', 'site');
        });
    }

    /**
     * Haversine formula to calculate distance in meters between two GPS coordinate points.
     */
    private function calculateDistanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
