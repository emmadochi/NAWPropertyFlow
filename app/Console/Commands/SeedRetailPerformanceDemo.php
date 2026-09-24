<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Database\Seeders\RetailPerformanceDemoSeeder;
use Illuminate\Console\Command;

class SeedRetailPerformanceDemo extends Command
{
    protected $signature = 'retail:seed-demo {--tenant= : Optional specific tenant ID (e.g. bhl)}';
    protected $description = 'Seed realistic retail sales team performance scorecard demo data (BSTAN model)';

    public function handle(): int
    {
        $this->info('Starting Retail Sales Performance Demo Seeder (BSTAN Model)...');

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
                $tenantName = $tenant->company_name ?? ($tenant->name ?? 'Tenant');
                $this->info("--> Initializing Tenancy for: [{$tenant->id}] ({$tenantName})");
                $tenant->run(function () use ($tenant) {
                    $this->info("    - Seeding consultants, leads, closed deals, site tours, and weekly field logs...");
                    $this->call(RetailPerformanceDemoSeeder::class);
                });
                $this->info("✓ Successfully seeded tenant: [{$tenant->id}]");
            }
        } else {
            $this->info('--> Seeding default database connection...');
            $this->call(RetailPerformanceDemoSeeder::class);
            $this->info('✓ Successfully seeded default connection.');
        }

        $this->newLine();
        $this->info('🎉 Retail Sales Team Performance Matrix is now populated with realistic test data!');
        return Command::SUCCESS;
    }
}
