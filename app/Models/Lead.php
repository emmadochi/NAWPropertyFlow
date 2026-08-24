<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Lead extends Model
{
    use HasFactory, LogsActivity;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\BranchScope);

        static::created(function ($lead) {
            try {
                app(\App\Services\DripService::class)->triggerFor($lead, 'lead_created');
            } catch (\Exception $e) {
                // Ignore
            }
        });

        static::updated(function ($lead) {
            if ($lead->wasChanged('status') && strcasecmp($lead->status, 'hot') === 0) {
                try {
                    app(\App\Services\DripService::class)->triggerFor($lead, 'status_changed_hot');
                } catch (\Exception $e) {}
            }
        });
    }

    protected $fillable = [
        'full_name',
        'phone_number',
        'whatsapp_number',
        'email',
        'address',
        'budget_range',
        'property_interest_id',
        'preferred_location',
        'lead_source',
        'assigned_to',
        'status',
        'notes',
        'branch_id',
        'portal_token',
        'portal_token_created_at',
    ];

    protected $casts = [
        'portal_token_created_at' => 'datetime',
    ];

    /**
     * Get existing portal token or securely generate a new 64-character token.
     */
    public function getOrCreatePortalToken(): string
    {
        if (empty($this->portal_token)) {
            $this->portal_token = bin2hex(random_bytes(32));
            $this->portal_token_created_at = now();
            $this->saveQuietly();
        }
        return $this->portal_token;
    }

    /**
     * Get the full secure magic link URL for the client.
     */
    public function getPortalMagicUrl(): string
    {
        return route('portal.magic-login', ['token' => $this->getOrCreatePortalToken()]);
    }

    // Relationships
    public function propertyInterest()
    {
        return $this->belongsTo(Property::class, 'property_interest_id');
    }

    public function assignedOfficer()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->orderBy('created_at', 'desc');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // Scopes
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('full_name', 'like', "%{$term}%")
              ->orWhere('phone_number', 'like', "%{$term}%")
              ->orWhere('whatsapp_number', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%");
        });
    }

    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOfOfficer($query, $officerId)
    {
        return $query->where('assigned_to', $officerId);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeOfBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
