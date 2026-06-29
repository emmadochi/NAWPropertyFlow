<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FollowUp extends Model
{
    use HasFactory;

    protected $table = 'follow_ups';

    protected $fillable = [
        'lead_id',
        'type',
        'due_date',
        'notes',
        'status',
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    // Relationships
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }

    public function scopeDueToday($query)
    {
        return $query->where('status', 'Pending')
                     ->whereDate('due_date', Carbon::today());
    }

    public function scopeDueTomorrow($query)
    {
        return $query->where('status', 'Pending')
                     ->whereDate('due_date', Carbon::tomorrow());
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'Pending')
                     ->where('due_date', '<', Carbon::now());
    }
}
