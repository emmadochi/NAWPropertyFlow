<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'payment_plan_duration_id',
        'duration_months',
        'plan_type',
        'base_deal_value',
        'interest_rate_pct',
        'interest_amount',
        'total_amount',
        'amount_paid',
        'balance',
        'number_of_installments',
        'notes',
        'status',
    ];

    protected $casts = [
        'duration_months' => 'integer',
        'base_deal_value' => 'decimal:2',
        'interest_rate_pct' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'number_of_installments' => 'integer',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function duration()
    {
        return $this->belongsTo(PaymentPlanDuration::class, 'payment_plan_duration_id');
    }

    public function milestones()
    {
        return $this->hasMany(PaymentMilestone::class);
    }
}
