<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_plan_id',
        'label',
        'amount_due',
        'due_date',
        'amount_paid',
        'paid_at',
        'bank_reference',
        'receipt_path',
        'verified_at',
        'verified_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'due_date' => 'date',
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function paymentPlan()
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
