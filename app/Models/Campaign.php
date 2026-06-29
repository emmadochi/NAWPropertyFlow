<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'status', 'subject', 'body', 'from_name', 'from_email',
        'audience_segment', 'audience_filters', 'audience_count',
        'sent_count', 'opened_count', 'clicked_count', 'unsubscribed_count',
        'scheduled_at', 'sent_at', 'created_by', 'branch_id',
    ];

    protected $casts = [
        'audience_filters' => 'array',
        'scheduled_at'     => 'datetime',
        'sent_at'          => 'datetime',
    ];

    const TYPES = [
        'email'     => 'Email',
        'sms'       => 'SMS',
        'whatsapp'  => 'WhatsApp',
    ];

    const STATUSES = [
        'draft'      => 'Draft',
        'scheduled'  => 'Scheduled',
        'sending'    => 'Sending',
        'sent'       => 'Sent',
        'paused'     => 'Paused',
        'cancelled'  => 'Cancelled',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CampaignContact::class);
    }

    /* Derived analytics helpers */
    public function openRate(): float
    {
        return $this->sent_count > 0
            ? round(($this->opened_count / $this->sent_count) * 100, 1)
            : 0;
    }

    public function clickRate(): float
    {
        return $this->sent_count > 0
            ? round(($this->clicked_count / $this->sent_count) * 100, 1)
            : 0;
    }
}
