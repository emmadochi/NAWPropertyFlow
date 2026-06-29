<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'leave_type', 'start_date', 'end_date', 'days_requested',
        'reason', 'status', 'reviewed_by', 'review_notes', 'reviewed_at', 'attachment_path',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'reviewed_at' => 'datetime',
    ];

    const TYPES = [
        'annual'        => 'Annual Leave',
        'sick'          => 'Sick Leave',
        'unpaid'        => 'Unpaid Leave',
        'maternity'     => 'Maternity Leave',
        'paternity'     => 'Paternity Leave',
        'compassionate' => 'Compassionate Leave',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
