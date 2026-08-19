<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_batch_id',
        'user_id',
        'base_salary',
        'housing_allowance',
        'transport_allowance',
        'other_allowances',
        'total_allowances',
        'commission_amount',
        'gross_pay',
        'tax_deduction',
        'pension_deduction',
        'loan_deduction',
        'other_deductions',
        'total_deductions',
        'net_pay',
        'bank_name',
        'account_number',
        'account_name',
        'status',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'housing_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'pension_deduction' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    public function payrollBatch()
    {
        return $this->belongsTo(PayrollBatch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deductions()
    {
        return $this->hasMany(PayrollDeduction::class);
    }
}
