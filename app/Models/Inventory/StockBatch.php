<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBatch extends Model
{
    use HasFactory;

    protected $table = 'stock_batches';

    protected $fillable = [
        'site_stock_id',
        'batch_number',
        'manufacture_date',
        'expiry_date',
        'qty_received',
        'qty_remaining',
        'received_on_grn_id',
        'qc_status',
        'qc_notes',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'qty_received' => 'decimal:3',
        'qty_remaining' => 'decimal:3',
    ];

    public function siteStock(): BelongsTo
    {
        return $this->belongsTo(SiteStock::class, 'site_stock_id');
    }

    public function grn(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'received_on_grn_id');
    }
}
