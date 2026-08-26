<?php

namespace App\Services\Inventory;

use App\Models\Inventory\BomTemplate;
use App\Models\Inventory\MaterialCatalogue;

class BOMEngine
{
    /**
     * Get expected material quantities for a given construction activity and volume of work.
     */
    public function getExpectedQuantities(string $activityName, float $workQuantity, ?int $projectId = null): array
    {
        $query = BomTemplate::with('material')
            ->where('activity_name', $activityName);

        if ($projectId) {
            $query->where(function ($q) use ($projectId) {
                $q->where('project_id', $projectId)
                  ->orWhereNull('project_id');
            });
        } else {
            $query->whereNull('project_id');
        }

        $boms = $query->get();

        $results = [];
        foreach ($boms as $bom) {
            $expected = $bom->qty_per_unit * $workQuantity;
            $results[$bom->material_id] = [
                'material_id' => $bom->material_id,
                'material_name' => $bom->material->name,
                'material_code' => $bom->material->code,
                'unit_of_measure' => $bom->material->unit_of_measure,
                'qty_per_unit' => (float)$bom->qty_per_unit,
                'unit_of_work' => $bom->unit_of_work,
                'expected_qty' => round($expected, 3),
                'allowable_variance_pct' => (float)$bom->allowable_variance_pct,
                'standard_unit_cost' => (float)$bom->material->standard_unit_cost,
            ];
        }

        return $results;
    }

    /**
     * Validate an array of requested items against the QS BOM consumption benchmarks.
     * Returns an array of items with variance flags and reason notes.
     */
    public function validateRequisitionItems(array $items, string $activityName, float $workQuantity, ?int $projectId = null): array
    {
        $expectedMap = $this->getExpectedQuantities($activityName, $workQuantity, $projectId);

        $validatedItems = [];

        foreach ($items as $item) {
            $materialId = (int)$item['material_id'];
            $qtyRequested = (float)$item['qty_requested'];
            $varianceFlag = false;
            $varianceReason = null;
            $bomExpectedQty = null;

            if (isset($expectedMap[$materialId])) {
                $benchmark = $expectedMap[$materialId];
                $bomExpectedQty = $benchmark['expected_qty'];
                $allowableVariance = $benchmark['allowable_variance_pct'];

                // Max allowed without flag
                $maxAllowed = $bomExpectedQty * (1 + ($allowableVariance / 100));

                if ($qtyRequested > $maxAllowed) {
                    $varianceFlag = true;
                    $diffPct = round((($qtyRequested - $bomExpectedQty) / $bomExpectedQty) * 100, 1);
                    $varianceReason = "Over-consumption: Requested {$qtyRequested} exceeds standard BOM ({$bomExpectedQty}) by {$diffPct}% (tolerance is ±{$allowableVariance}%).";
                }
            } else {
                // Item requested without defined BOM rate
                $varianceReason = "No standard BOM benchmark defined for activity '{$activityName}' and material ID {$materialId}.";
            }

            $validatedItems[] = [
                'material_id' => $materialId,
                'qty_requested' => $qtyRequested,
                'qty_approved' => $item['qty_approved'] ?? $qtyRequested,
                'bom_expected_qty' => $bomExpectedQty,
                'variance_flag' => $varianceFlag,
                'variance_reason' => $varianceReason,
            ];
        }

        return $validatedItems;
    }
}
