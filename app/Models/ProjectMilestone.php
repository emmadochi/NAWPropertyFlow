<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'title', 'description', 'due_date',
        'completed_date', 'status', 'percentage_weight',
        'responsible_party', 'notes',
    ];

    protected $casts = [
        'due_date'          => 'date',
        'completed_date'    => 'date',
        'percentage_weight' => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isOverdue(): bool
    {
        return $this->status !== 'completed' && $this->due_date->isPast();
    }

    public function markCompleted(): void
    {
        $this->update([
            'status'           => 'completed',
            'completed_date'   => now(),
        ]);

        // Automatically update the parent project's completion percentage
        $this->project->recalculateCompletion();
    }
}
