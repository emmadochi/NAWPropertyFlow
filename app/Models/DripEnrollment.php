<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DripEnrollment extends Model
{
    protected $fillable = [
        'drip_sequence_id', 'lead_id', 'current_step_id',
        'status', 'next_send_at', 'enrolled_at', 'completed_at',
    ];

    protected $casts = [
        'next_send_at'  => 'datetime',
        'enrolled_at'   => 'datetime',
        'completed_at'  => 'datetime',
    ];

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(DripSequence::class, 'drip_sequence_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(DripStep::class, 'current_step_id');
    }
}
