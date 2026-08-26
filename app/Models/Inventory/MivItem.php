<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MivItem extends Model
{
    use HasFactory;

    protected $table = 'miv_items';

    protected $fillable = [
        'miv_id',
        'material_id',
        'stock_batch_id',
        'qty_requested',
        'qty_issued',
        'qty_returned',
        'consumption_rate_variance_pct',
        'variance_flagged',
    ];

    protected $casts = [
        'qty_requested' => 'decimal:3',
        'qty_issued' => 'decimal:3',
        'qty_returned' => 'decimal:3',
        'consumption_rate_variance_pct' => 'decimal:2',
        'variance_flagged' => 'boolean',
    ];

    public function issueVoucher(): BelongsTo
    {
        return $this->belongsTo(MaterialIssueVoucher::class, 'miv_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialCatalogue::class, 'material_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class, 'stock_batch_id');
    }
}
