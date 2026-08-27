<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPlanDuration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration_months',
        'interest_rate_pct',
        'initial_deposit_pct',
        'number_of_installments',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'duration_months' => 'integer',
        'interest_rate_pct' => 'decimal:2',
        'initial_deposit_pct' => 'decimal:2',
        'number_of_installments' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope for active plan durations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc')->orderBy('duration_months', 'asc');
    }

    /**
     * Calculate total price with interest added
     */
    public function calculateTotalPrice(float $baseAmount): float
    {
        $interest = ($baseAmount * (float)$this->interest_rate_pct) / 100;
        return round($baseAmount + $interest, 2);
    }

    /**
     * Calculate interest surcharge amount
     */
    public function calculateInterestAmount(float $baseAmount): float
    {
        return round(($baseAmount * (float)$this->interest_rate_pct) / 100, 2);
    }

    /**
     * Calculate deposit amount
     */
    public function calculateDepositAmount(float $baseAmount): float
    {
        $total = $this->calculateTotalPrice($baseAmount);
        return round(($total * (float)$this->initial_deposit_pct) / 100, 2);
    }
}
