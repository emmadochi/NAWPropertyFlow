<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('payments:check-due')->dailyAt('08:00');
Schedule::command('commissions:monthly-summary')->monthlyOn(1, '00:00');
Schedule::command('crm:release-expired-reservations')->dailyAt('08:00');
Schedule::command('hr:weekly-digest')->weeklyOn(1, '07:00'); // Every Monday at 7am
Schedule::command('drip:process-steps')->hourly();

Artisan::command('inventory:seed-demo {--tenant=}', function () {
    $this->info('Starting Construction Inventory & Staff Seeder...');

    try {
        $tenantQuery = \App\Models\Tenant::query();
        if ($tenantId = $this->option('tenant')) {
            $tenantQuery->where('id', $tenantId);
        }
        $tenants = $tenantQuery->get();
    } catch (\Throwable $e) {
        $tenants = collect();
    }

    if ($tenants->isNotEmpty()) {
        foreach ($tenants as $tenant) {
            $this->info("--> Initializing Tenancy for: [{$tenant->id}] ({$tenant->company_name})");
            $tenant->run(function () use ($tenant) {
                $this->info("    - Seeding permissions & roles...");
                $this->call(\Database\Seeders\PermissionSeeder::class);

                $this->info("    - Seeding core staff personas...");
                $this->call(\Database\Seeders\UserSeeder::class);

                $this->info("    - Seeding construction multi-site dataset & suppliers...");
                $this->call(\Database\Seeders\InventoryDemoSeeder::class);
            });
            $this->info("✓ Successfully seeded tenant: [{$tenant->id}]");
        }
    } else {
        $this->info('--> Seeding default database connection...');
        $this->call(\Database\Seeders\PermissionSeeder::class);
        $this->call(\Database\Seeders\UserSeeder::class);
        $this->call(\Database\Seeders\InventoryDemoSeeder::class);
        $this->info('✓ Successfully seeded default connection.');
    }

    $this->newLine();
    $this->info('🎉 Construction Inventory Demo & Staff Logins are 100% READY!');
})->purpose('Seed construction inventory roles, user accounts, and test data across all tenants');

