<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RicafProductionSeeder extends Seeder
{
    /**
     * Seed initial configuration and admin account for Ricaf Nigeria Limited.
     */
    public function run(): void
    {
        // 1. Initialize Company Settings for RICAF
        CompanySetting::updateOrCreate(
            ['id' => 1],
            [
                'company_name'      => 'Ricaf Nigeria Limited',
                'email'             => 'info@ricafltd.com',
                'phone'             => '+234 800 000 0000',
                'address'           => 'Suite D7, 3rd Floor, Kuriftu Plaza, Plot 519, Olu Awotesu Street, Jabi, Abuja, Nigeria',
                'package_tier'      => 'enterprise',
                'logo_path'         => 'company/ricaf-logo.png',
                'letterhead_header' => null,
                'letterhead_footer' => null,
            ]
        );

        // 2. Create Initial Company Super Administrator
        User::updateOrCreate(
            ['email' => 'admin@ricafltd.com'],
            [
                'name'         => 'Ricaf Administrator',
                'email'        => 'admin@ricafltd.com',
                'password'     => Hash::make('AdminRicaf@2026!'),
                'role'         => 'super_admin',
                'phone_number' => '+2348000000000',
                'status'       => 'active',
            ]
        );

        $this->command->info('Ricaf Nigeria Limited production environment initialized successfully!');
        $this->command->info('Default Admin: admin@ricafltd.com | Password: AdminRicaf@2026!');
    }
}
