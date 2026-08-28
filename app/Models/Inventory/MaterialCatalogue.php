<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialCatalogue extends Model
{
    use HasFactory;

    protected $table = 'material_catalogue';

    protected $fillable = [
        'name',
        'code',
        'category',
        'unit_of_measure',
        'standard_unit_cost',
        'reorder_level',
        'safety_stock_level',
        'shelf_life_days',
        'is_trackable_by_batch',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'standard_unit_cost' => 'decimal:2',
        'reorder_level' => 'decimal:3',
        'safety_stock_level' => 'decimal:3',
        'shelf_life_days' => 'integer',
        'is_trackable_by_batch' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function categoryRef()
    {
        return $this->belongsTo(MaterialCategory::class, 'category', 'slug');
    }

    public function siteStocks(): HasMany
    {
        return $this->hasMany(SiteStock::class, 'material_id');
    }

    public function bomTemplates(): HasMany
    {
        return $this->hasMany(BomTemplate::class, 'material_id');
    }

    public function priceBenchmarks(): HasMany
    {
        return $this->hasMany(PriceBenchmark::class, 'material_id');
    }

    public function wasteLogs(): HasMany
    {
        return $this->hasMany(WasteLog::class, 'material_id');
    }
}
