<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Database\Seeders\InventoryDemoSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Command;

class SeedTenantInventoryDemo extends Command
{
    protected $signature = 'inventory:seed-demo {--tenant= : Optional specific tenant ID (e.g. demo)}';
    protected $description = 'Seed construction inventory roles, user accounts, and test data across all tenants';

    public function handle(): int
    {
        $this->info('Starting Construction Inventory & Staff Seeder...');

        try {
            $tenantQuery = Tenant::query();
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
                    $this->call(PermissionSeeder::class);

                    $this->info("    - Seeding core staff personas...");
                    $this->call(UserSeeder::class);

                    $this->info("    - Seeding construction multi-site dataset & suppliers...");
                    $this->call(InventoryDemoSeeder::class);
                });
                $this->info("✓ Successfully seeded tenant: [{$tenant->id}]");
            }
        } else {
            $this->info('--> Seeding default database connection...');
            $this->call(PermissionSeeder::class);
            $this->call(UserSeeder::class);
            $this->call(InventoryDemoSeeder::class);
            $this->info('✓ Successfully seeded default connection.');
        }

        $this->newLine();
        $this->info('🎉 Construction Inventory Demo & Staff Logins are 100% READY!');
        return Command::SUCCESS;
    }
}
