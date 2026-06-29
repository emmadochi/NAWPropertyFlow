<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffMetricSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'department_metric_id',
        'value',
        'submission_month',
        'submission_year',
        'status',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'submission_month' => 'integer',
        'submission_year' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function metric()
    {
        return $this->belongsTo(DepartmentMetric::class, 'department_metric_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
