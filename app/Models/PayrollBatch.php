<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'month',
        'year',
        'total_base',
        'total_allowances',
        'total_commissions',
        'total_gross',
        'total_deductions',
        'total_net',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'total_base' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_commissions' => 'decimal:2',
        'total_gross' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }
}
