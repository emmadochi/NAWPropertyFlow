<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'logo_path',
        'email',
        'phone',
        'address',
        'letterhead_header',
        'letterhead_footer',
        'package_tier',
    ];

    /**
     * Define the features available for each tier.
     */
    public const TIER_FEATURES = [
        'starter' => [
            'crm',
            // Team Settings & Company Settings always available
            // NO: leaderboard, activity_logs, department_setup, multi_branch, marketing, docs, hr, file_manager, advanced_reports
        ],
        'professional' => [
            'crm',
            'leaderboard',
            'marketing',
            'docs',
            'customer_portal',
            'department_setup',
            'multi_branch',
        ],
        'enterprise' => [
            'crm',
            'leaderboard',
            'marketing',
            'docs',
            'customer_portal',
            'department_setup',
            'multi_branch',
            'hr',
            'file_manager',
            'advanced_reports',
            'activity_logs',
        ],
    ];

    /**
     * Check if the current package tier has a specific feature.
     *
     * @param string $feature
     * @return bool
     */
    public function hasFeature(string $feature): bool
    {
        $tier = $this->package_tier ?? 'starter';
        
        // Ensure tier exists in our config, fallback to starter
        if (!array_key_exists($tier, self::TIER_FEATURES)) {
            $tier = 'starter';
        }

        return in_array($feature, self::TIER_FEATURES[$tier]);
    }
}
