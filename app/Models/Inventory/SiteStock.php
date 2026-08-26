<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteStock extends Model
{
    use HasFactory;

    protected $table = 'site_stock';

    protected $fillable = [
        'site_id',
        'material_id',
        'qty_on_hand',
        'qty_reserved',
        'qty_quarantined',
        'last_physical_count_at',
        'last_count_by_user_id',
    ];

    protected $casts = [
        'qty_on_hand' => 'decimal:3',
        'qty_reserved' => 'decimal:3',
        'qty_quarantined' => 'decimal:3',
        'last_physical_count_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(InventorySite::class, 'site_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialCatalogue::class, 'material_id');
    }

    public function lastCounter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_count_by_user_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(StockBatch::class, 'site_stock_id');
    }
}
