<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'department',
        'department_id',
        'target_month',
        'target_year',
        'metric',
        'target_value',
        'actual_value',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'target_month' => 'integer',
        'target_year' => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
