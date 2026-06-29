<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DripStep extends Model
{
    protected $fillable = [
        'drip_sequence_id', 'step_order', 'type', 'subject', 'body',
        'delay_days', 'delay_hours', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'delay_days' => 'integer',
        'delay_hours'=> 'integer',
    ];

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(DripSequence::class, 'drip_sequence_id');
    }

    public function delayLabel(): string
    {
        $parts = [];
        if ($this->delay_days > 0)  $parts[] = $this->delay_days . 'd';
        if ($this->delay_hours > 0) $parts[] = $this->delay_hours . 'h';
        return empty($parts) ? 'Immediately' : implode(' ', $parts) . ' after previous';
    }
}
