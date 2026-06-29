<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'developer', 'location', 'type', 'description',
        'start_date', 'expected_completion_date', 'actual_completion_date',
        'status', 'total_units', 'land_size_sqm', 'amenities',
        'images', 'brochure_path', 'completion_percentage',
    ];

    protected $casts = [
        'start_date'                => 'date',
        'expected_completion_date'  => 'date',
        'actual_completion_date'    => 'date',
        'amenities'                 => 'array',
        'images'                    => 'array',
        'land_size_sqm'             => 'decimal:2',
        'completion_percentage'     => 'integer',
        'total_units'               => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('due_date');
    }

    public function units()
    {
        return $this->hasMany(PropertyUnit::class);
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function recalculateCompletion(): void
    {
        $milestones = $this->milestones;
        if ($milestones->isEmpty()) {
            return;
        }

        $totalWeight    = $milestones->sum('percentage_weight') ?: 100;
        $completedWeight = $milestones->where('status', 'completed')->sum('percentage_weight');

        $this->completion_percentage = (int) round(($completedWeight / $totalWeight) * 100);
        $this->save();
    }

    public function availableUnitsCount(): int
    {
        return $this->units()->where('status', 'available')->count();
    }

    public function soldUnitsCount(): int
    {
        return $this->units()->where('status', 'sold')->count();
    }
}
