<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Property extends Model
{
    use HasFactory, LogsActivity;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\BranchScope);
    }

    protected $fillable = [
        'project_id', 'name', 'estate_name', 'location', 'property_type',
        'description', 'price', 'available_units', 'total_units', 'images',
        'is_off_plan', 'latitude', 'longitude', 'landmark',
        'amenities', 'completion_status', 'branch_id',
    ];

    protected $casts = [
        'price'           => 'decimal:2',
        'available_units' => 'integer',
        'total_units'     => 'integer',
        'images'          => 'array',
        'amenities'       => 'array',
        'is_off_plan'     => 'boolean',
        'latitude'        => 'decimal:8',
        'longitude'       => 'decimal:8',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function units()
    {
        return $this->hasMany(PropertyUnit::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'property_interest_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function availableUnitsCount(): int
    {
        return $this->units()->where('status', 'available')->count();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeOfBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
