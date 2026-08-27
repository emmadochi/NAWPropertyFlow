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
        'enabled_modules',
    ];

    protected $casts = [
        'enabled_modules' => 'array',
    ];

    /**
     * Master registry of all available platform modules with metadata for Developer Control Hub.
     */
    public const ALL_MODULES = [
        'crm' => [
            'key' => 'crm',
            'name' => 'Core Leads & Real Estate CRM',
            'category' => 'Core CRM',
            'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
            'badge_color' => 'orange',
            'description' => 'Prospect pipeline, follow-ups, inspection schedules, deal closing and all-staff lead assignment.',
            'core' => true,
        ],
        'payment_plans' => [
            'key' => 'payment_plans',
            'name' => 'Payment Plans & Dynamic Interest Surcharges',
            'category' => 'Sales & Finance',
            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'badge_color' => 'amber',
            'description' => 'Configurable tenure durations (0M-24M), milestone schedules, initial deposit percentages, and automatic interest calculations.',
        ],
        'inventory' => [
            'key' => 'inventory',
            'name' => 'Construction Inventory & Procurement Suite',
            'category' => 'Operations',
            'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            'badge_color' => 'purple',
            'description' => 'Multi-site stock, QS Bill of Materials (BOM), Material Requisitions (MRF), Purchase Orders (PO), GRN, MIV, 3-Way Match & Fraud Radar.',
        ],
        'accounting' => [
            'key' => 'accounting',
            'name' => 'Enterprise Double-Entry Accounting Suite',
            'category' => 'Finance',
            'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
            'badge_color' => 'emerald',
            'description' => 'Real-time Profit & Loss, Balance Sheet, Trial Balance, Bank Treasury & Reconciliation, AR/AP Aging Matrix, FIRS WHT/VAT Hub.',
        ],
        'hr' => [
            'key' => 'hr',
            'name' => 'HR, Performance KPI Logs & Naira Payroll',
            'category' => 'Personnel',
            'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
            'badge_color' => 'blue',
            'description' => 'Employee staff directory, daily task/KPI submissions, leave approvals, salary structure, and automated payslip generation.',
        ],
        'docs' => [
            'key' => 'docs',
            'name' => 'Deed of Assignment & Document Templates',
            'category' => 'Legal Vault',
            'icon' => 'M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'badge_color' => 'indigo',
            'description' => 'Automated Legal Deeds generation with merge tags, contract templates, and official title issuance.',
        ],
        'file_manager' => [
            'key' => 'file_manager',
            'name' => 'Cloud File Vault & KYC Document Center',
            'category' => 'Legal Vault',
            'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
            'badge_color' => 'sky',
            'description' => 'Encrypted cloud document repository for buyer identity documents, surveys, and site plans.',
        ],
        'marketing' => [
            'key' => 'marketing',
            'name' => 'Marketing Campaigns & WhatsApp Engine',
            'category' => 'Marketing',
            'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
            'badge_color' => 'rose',
            'description' => 'Targeted buyer broadcasts, SMS campaigns, WhatsApp bulk messaging, and conversion attribution.',
        ],
        'multi_branch' => [
            'key' => 'multi_branch',
            'name' => 'Multi-Branch & Regional Office Hub',
            'category' => 'Enterprise',
            'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
            'badge_color' => 'teal',
            'description' => 'Branch office isolation, regional performance filtering, and localized inventory & staff access.',
        ],
        'leaderboard' => [
            'key' => 'leaderboard',
            'name' => 'Sales Leaderboard & Gamification',
            'category' => 'Sales & Finance',
            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            'badge_color' => 'yellow',
            'description' => 'Live sales ranking, revenue targets, executive trophies, and top closers recognition.',
        ],
        'department_setup' => [
            'key' => 'department_setup',
            'name' => 'Custom Department OKRs & Metrics',
            'category' => 'Enterprise',
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
            'badge_color' => 'slate',
            'description' => 'Define custom KPI metrics, assign weekly department goals, and track corporate objectives.',
        ],
        'activity_logs' => [
            'key' => 'activity_logs',
            'name' => 'Security Audit Trail & Activity Logs',
            'category' => 'Enterprise',
            'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            'badge_color' => 'zinc',
            'description' => 'Real-time timestamped audit logs of all user logins, financial transactions, and record modifications.',
        ],
        'customer_portal' => [
            'key' => 'customer_portal',
            'name' => 'Investor / Buyer Self-Service Portal',
            'category' => 'Client Experience',
            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            'badge_color' => 'cyan',
            'description' => 'Secure client login to track construction progress, milestone payment history, and download receipts.',
        ],
        'advanced_reports' => [
            'key' => 'advanced_reports',
            'name' => 'Executive Analytics & BI Reports',
            'category' => 'Analytics',
            'icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z',
            'badge_color' => 'violet',
            'description' => 'Executive summary graphs, sales velocity, conversion metrics, and cash flow forecasts.',
        ],
    ];

    /**
     * Define the features available for each tier default fallback.
     */
    public const TIER_FEATURES = [
        'starter' => [
            'crm',
            'payment_plans',
        ],
        'professional' => [
            'crm',
            'payment_plans',
            'leaderboard',
            'marketing',
            'docs',
            'customer_portal',
            'department_setup',
            'multi_branch',
        ],
        'enterprise' => [
            'crm',
            'payment_plans',
            'inventory',
            'accounting',
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
     * Get cached company setting to eliminate repeated database queries.
     */
    public static function getCached(): ?self
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('active_company_setting', function () {
            return static::first();
        });
    }

    /**
     * Clear cached company setting on update/save.
     */
    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('active_company_setting');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('active_company_setting');
        });
    }

    /**
     * Check if the company has a specific feature enabled.
     *
     * @param string $feature
     * @return bool
     */
    public function hasFeature(string $feature): bool
    {
        // 1. If Developer has explicitly configured enabled modules, respect that exact list
        if (is_array($this->enabled_modules) && !empty($this->enabled_modules)) {
            return in_array($feature, $this->enabled_modules);
        }

        // 2. Fallback to package tier defaults
        $tier = $this->package_tier ?? 'enterprise';
        if (!array_key_exists($tier, self::TIER_FEATURES)) {
            $tier = 'enterprise';
        }

        return in_array($feature, self::TIER_FEATURES[$tier]);
    }

    /**
     * Get list of active enabled module keys for this company.
     */
    public function getActiveModuleKeys(): array
    {
        if (is_array($this->enabled_modules) && !empty($this->enabled_modules)) {
            return $this->enabled_modules;
        }

        $tier = $this->package_tier ?? 'enterprise';
        return self::TIER_FEATURES[$tier] ?? self::TIER_FEATURES['enterprise'];
    }
}

