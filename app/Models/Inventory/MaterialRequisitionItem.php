<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRequisitionItem extends Model
{
    use HasFactory;

    protected $table = 'material_requisition_items';

    protected $fillable = [
        'requisition_id',
        'material_id',
        'qty_requested',
        'qty_approved',
        'bom_expected_qty',
        'variance_flag',
        'variance_reason',
    ];

    protected $casts = [
        'qty_requested' => 'decimal:3',
        'qty_approved' => 'decimal:3',
        'bom_expected_qty' => 'decimal:3',
        'variance_flag' => 'boolean',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(MaterialRequisition::class, 'requisition_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialCatalogue::class, 'material_id');
    }
}
