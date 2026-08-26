<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class CompanySettingSeeder extends Seeder
{
    public function run(): void
    {
        CompanySetting::updateOrCreate(
            ['id' => 1],
            [
                'company_name'      => 'NAW Properties Ltd',
                'email'             => 'info@nawpropertyflow.com.ng',
                'phone'             => '+234 810 135 8139',
                'address'           => 'Suite D7, Kuriftu Plaza, Jabi District, Abuja FCT',
                'package_tier'      => 'enterprise',
                'letterhead_header' => 'NAW PROPERTIES LTD — LUXURY & COMMERCIAL REAL ESTATE',
                'letterhead_footer' => 'Abuja Office: Kuriftu Plaza, Jabi | Lagos Office: Lekki Phase 1',
            ]
        );

        Cache::forget('active_company_setting');
    }
}
