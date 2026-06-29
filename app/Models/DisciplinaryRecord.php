<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'issued_by', 'incident_type', 'incident_date',
        'description', 'action_taken', 'status', 'resolution_notes',
        'resolved_at', 'attachment_path',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'resolved_at'   => 'datetime',
    ];

    const TYPES = [
        'warning'     => 'Written Warning',
        'suspension'  => 'Suspension',
        'termination' => 'Termination',
        'query'       => 'Query Letter',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
