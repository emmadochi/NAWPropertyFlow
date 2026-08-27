<?php

namespace App\Models\Accounting;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TaxRecord extends Model
{
    use HasFactory;

    protected $table = 'tax_records';

    protected $fillable = [
        'tax_type',
        'entity_type',
        'entity_id',
        'gross_amount',
        'tax_rate_pct',
        'tax_amount',
        'beneficiary_name',
        'beneficiary_tin',
        'status',
        'remitted_at',
        'remittance_reference',
        'recorded_by_user_id',
        'notes',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'tax_rate_pct' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'remitted_at' => 'datetime',
    ];

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function entity(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'entity_type', 'entity_id');
    }
}
