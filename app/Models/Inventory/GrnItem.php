<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrnItem extends Model
{
    use HasFactory;

    protected $table = 'grn_items';

    protected $fillable = [
        'grn_id',
        'po_item_id',
        'material_id',
        'qty_ordered',
        'qty_received',
        'qty_rejected',
        'rejection_reason',
        'batch_number',
        'manufacture_date',
        'expiry_date',
        'unit_price_confirmed',
    ];

    protected $casts = [
        'qty_ordered' => 'decimal:3',
        'qty_received' => 'decimal:3',
        'qty_rejected' => 'decimal:3',
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'unit_price_confirmed' => 'decimal:2',
    ];

    public function grn(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'grn_id');
    }

    public function poItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialCatalogue::class, 'material_id');
    }
}
