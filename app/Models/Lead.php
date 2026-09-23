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
            } catch (\Throwable $e) {
                // Ignore background drip errors to never break lead creation
            }
        });

        static::updated(function ($lead) {
            $isHotStatus = $lead->wasChanged('status') && strcasecmp($lead->status, 'hot') === 0;
            $isHotTemp = $lead->wasChanged('lead_temperature') && strcasecmp($lead->lead_temperature, 'hot') === 0;

            if ($isHotStatus || $isHotTemp) {
                try {
                    app(\App\Services\DripService::class)->triggerFor($lead, 'status_changed_hot');
                } catch (\Throwable $e) {}
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
        'lead_temperature',
        'notes',
        'branch_id',
        'outreach_location',
        'last_contacted_at',
        'last_contact_channel',
        'portal_token',
        'portal_token_created_at',
        'unreachable_count',
        'is_flagged_fake',
        'flagged_reason',
        'flagged_at',
    ];

    protected $casts = [
        'last_contacted_at' => 'datetime',
        'portal_token_created_at' => 'datetime',
        'flagged_at' => 'datetime',
        'is_flagged_fake' => 'boolean',
        'unreachable_count' => 'integer',
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

    public function scopeFlaggedFake($query)
    {
        return $query->where('is_flagged_fake', true);
    }

    public function scopeReachable($query)
    {
        return $query->where('is_flagged_fake', false);
    }

    /**
     * Hot leads: active deal signals — negotiation, inspection scheduled, payment committed.
     */
    public function scopeHot($query)
    {
        return $query->whereIn('status', ['Negotiation', 'Payment Processing', 'Inspection Scheduled', 'Closed Won']);
    }

    /**
     * Cold leads: not yet contacted or dormant for 14+ days and not flagged.
     */
    public function scopeCold($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('last_contacted_at')
              ->orWhere('last_contacted_at', '<', now()->subDays(14));
        })->where('is_flagged_fake', false);
    }

    /**
     * Healthy leads: not flagged and have been contacted at least once.
     */
    public function scopeHealthy($query)
    {
        return $query->where('is_flagged_fake', false)->whereNotNull('last_contacted_at');
    }

    public function isFlaggedFake(): bool
    {
        return (bool) $this->is_flagged_fake;
    }

    /**
     * Derived temperature: manual override > auto-computed from pipeline signals.
     */
    public function getComputedTemperatureAttribute(): string
    {
        if (!empty($this->lead_temperature)) {
            return $this->lead_temperature;
        }

        $status = strtolower($this->status ?? '');

        if (in_array($this->status, ['Negotiation', 'Payment Processing', 'Inspection Scheduled'])) {
            return 'hot';
        }
        if ($this->is_flagged_fake) {
            return 'cold';
        }
        if (is_null($this->last_contacted_at)) {
            return 'cold';
        }
        if ($this->last_contacted_at < now()->subDays(14)) {
            return 'cold';
        }

        return 'warm';
    }

    /**
     * Check if a valid, reachable WhatsApp number exists on the lead and return international digits format (e.g. 23480...).
     */
    public function getCleanWhatsappAttribute(): ?string
    {
        $wa = preg_replace('/\D/', '', $this->whatsapp_number ?? '');
        if (empty($wa)) return null;

        if (str_starts_with($wa, '0') && strlen($wa) === 11) {
            return '234' . substr($wa, 1);
        }
        if (str_starts_with($wa, '234') && strlen($wa) >= 13) {
            return $wa;
        }
        if (strlen($wa) === 10 && !str_starts_with($wa, '0')) {
            return '234' . $wa;
        }
        return strlen($wa) >= 10 ? $wa : null;
    }

    /**
     * Determine if this lead is reachable primarily via WhatsApp due to call issues or missing phone line.
     */
    public function getIsWhatsappOnlyAttribute(): bool
    {
        return $this->phone_health === 'whatsapp_only';
    }

    /**
     * Scope leads where phone calls fail or are missing, but WhatsApp is available.
     */
    public function scopeWhatsappOnly($query)
    {
        return $query->where(function ($q) {
            $q->where('unreachable_count', '>=', 1)
              ->orWhereNull('phone_number')
              ->orWhere('phone_number', '')
              ->orWhereRaw("CHAR_LENGTH(REGEXP_REPLACE(COALESCE(phone_number,''), '[^0-9]', '')) < 11");
        })
        ->whereNotNull('whatsapp_number')
        ->where('whatsapp_number', '!=', '')
        ->whereRaw("CHAR_LENGTH(REGEXP_REPLACE(COALESCE(whatsapp_number,''), '[^0-9]', '')) >= 10")
        ->where('is_flagged_fake', false);
    }

    /**
     * Nigerian phone number health classification.
     */
    public function getPhoneHealthAttribute(): string
    {
        if ($this->is_flagged_fake) {
            return 'flagged_fake';
        }

        $phone = preg_replace('/\D/', '', $this->phone_number ?? '');

        // Normalize 234 international prefix to local 0-prefix for consistent checking
        if (str_starts_with($phone, '234') && strlen($phone) >= 13) {
            $phone = '0' . substr($phone, 3);
        } elseif (strlen($phone) === 10 && !str_starts_with($phone, '0')) {
            $phone = '0' . $phone;
        }

        $knownPrefixes = ['070', '071', '080', '081', '090', '091'];
        $hasValidPrefix = !empty($phone) && strlen($phone) >= 3 && in_array(substr($phone, 0, 3), $knownPrefixes);

        $isPhoneFailing = empty($phone)
            || strlen($phone) < 11
            || !$hasValidPrefix
            || $this->unreachable_count >= 1;

        $hasValidWa = !empty($this->clean_whatsapp);

        // If phone is failing/unreachable/missing but a valid WhatsApp line is available
        if ($isPhoneFailing && $hasValidWa) {
            return 'whatsapp_only';
        }

        if (empty($phone))                     return 'missing';
        if (strlen($phone) < 11)               return 'incomplete';
        if ($this->unreachable_count >= 3)     return 'suspected_inactive';
        if ($this->unreachable_count >= 1)     return 'unreachable';
        if (!$hasValidPrefix)                  return 'invalid_prefix';

        return 'verified_active';
    }
}
