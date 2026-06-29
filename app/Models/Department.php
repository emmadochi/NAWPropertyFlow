<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'is_active',
        'hod_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function metrics()
    {
        return $this->hasMany(DepartmentMetric::class);
    }

    public function targets()
    {
        return $this->hasMany(DepartmentTarget::class);
    }

    public function hod()
    {
        return $this->belongsTo(User::class, 'hod_id');
    }

    public function submissions()
    {
        return $this->hasMany(StaffMetricSubmission::class);
    }
}
