<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DripSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'trigger_event', 'trigger_conditions', 'is_active', 'created_by',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'is_active'          => 'boolean',
    ];

    const TRIGGERS = [
        'lead_created'       => 'New Lead Created',
        'status_changed_hot' => 'Lead Marked as Hot',
        'inspection_booked'  => 'Inspection Booked',
        'deal_won'           => 'Deal Closed (Won)',
        'no_activity_7d'     => 'No Activity for 7 Days',
        'no_activity_30d'    => 'No Activity for 30 Days',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(DripStep::class)->orderBy('step_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(DripEnrollment::class);
    }
}
