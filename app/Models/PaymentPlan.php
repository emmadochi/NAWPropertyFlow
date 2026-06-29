<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'plan_type',
        'total_amount',
        'amount_paid',
        'balance',
        'number_of_installments',
        'notes',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'number_of_installments' => 'integer',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function milestones()
    {
        return $this->hasMany(PaymentMilestone::class);
    }
}
