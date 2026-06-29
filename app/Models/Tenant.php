<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $keyType = 'string';
    public $incrementing = false;

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    /**
     * Custom columns stored as native DB columns (not in the JSON data column).
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'company_name',
            'package_tier',
            'admin_email',
            'admin_name',
            'is_active',
        ];
    }

    protected $fillable = [
        'id',
        'company_name',
        'package_tier',
        'admin_email',
        'admin_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get a human-readable package tier label.
     */
    public function getTierLabelAttribute(): string
    {
        return match($this->package_tier) {
            'starter'      => 'Starter',
            'professional' => 'Professional',
            'enterprise'   => 'Enterprise',
            default        => 'Starter',
        };
    }

    /**
     * Get the CSS class for the tier badge.
     */
    public function getTierColorAttribute(): string
    {
        return match($this->package_tier) {
            'starter'      => 'bg-slate-500/20 text-slate-300',
            'professional' => 'bg-brand-500/20 text-brand-300',
            'enterprise'   => 'bg-purple-500/20 text-purple-300',
            default        => 'bg-slate-500/20 text-slate-300',
        };
    }
}
