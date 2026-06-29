<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffCertification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'issuing_body', 'issued_date',
        'expiry_date', 'certificate_number', 'attachment_path', 'notes',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(): bool
    {
        return $this->expiry_date && !$this->isExpired() && $this->expiry_date->diffInDays(now()) <= 30;
    }
}
