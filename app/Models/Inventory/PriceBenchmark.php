<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceBenchmark extends Model
{
    use HasFactory;

    protected $table = 'price_benchmarks';

    protected $fillable = [
        'material_id',
        'city',
        'city_name_custom',
        'unit_price',
        'recorded_date',
        'entered_by_user_id',
        'source_market_name',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'recorded_date' => 'date',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialCatalogue::class, 'material_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by_user_id');
    }
}
