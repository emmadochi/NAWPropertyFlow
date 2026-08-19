<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'base_salary',
        'housing_allowance',
        'transport_allowance',
        'other_allowances',
        'tax_percent',
        'pension_percent',
        'bank_name',
        'account_number',
        'account_name',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'housing_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'pension_percent' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalAllowancesAttribute(): float
    {
        return (float) ($this->housing_allowance + $this->transport_allowance + $this->other_allowances);
    }

    public function getTotalFixedGrossAttribute(): float
    {
        return (float) ($this->base_salary + $this->total_allowances);
    }
}
