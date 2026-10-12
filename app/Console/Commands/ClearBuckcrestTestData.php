<?php

namespace App\Console\Commands;

use App\Models\CompanySetting;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Property;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearBuckcrestTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'buckcrest:clear-test-data 
                            {--tenant= : Specific tenant ID (e.g. bhl, buckcrest)}
                            {--include-properties : Also clear property listings & units}
                            {--keep-staff : Keep demo staff accounts in the system}
                            {--force : Force cleanup without confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely clear all test leads, sales, payments, inspections, field logs, and demo staff from Buckcrest Havens CRM';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('========================================================');
        $this->info('  Buckcrest Havens CRM — Test Data Purge & Reset Utility');
        $this->info('========================================================');

        $includeProperties = (bool) $this->option('include-properties');
        $keepStaff = (bool) $this->option('keep-staff');
        $force = (bool) $this->option('force');

        $this->line('Cleanup Configuration:');
        $this->line(' • Transactional Test Data (Leads, Sales, Milestones, Logs): <info>PURGE</info>');
        $this->line(' • Properties & Units Catalog: ' . ($includeProperties ? '<comment>PURGE</comment>' : '<info>PRESERVE</info>'));
        $this->line(' • Staff Accounts: ' . ($keepStaff ? '<info>KEEP ALL</info>' : '<comment>REMOVE DEMO ACCOUNTS (@propertyflow.com, demo agents)</comment>'));
        $this->line(' • Company Settings & Branding: <info>PRESERVE (Buckcrest Havens Limited)</info>');
        $this->newLine();

        if (! $force) {
            if (! $this->confirm('Are you sure you want to purge test data from the Buckcrest CRM system?', true)) {
                $this->warn('Operation cancelled by user.');
                return Command::SUCCESS;
            }
        }

        try {
            $tenantQuery = Tenant::query();
            if ($tenantId = $this->option('tenant')) {
                $tenantQuery->where('id', $tenantId);
            } else {
                $tenantQuery->whereIn('id', ['bhl', 'buckcrest']);
            }
            $tenants = $tenantQuery->get();
        } catch (\Throwable $e) {
            $tenants = collect();
        }

        if ($tenants->isNotEmpty()) {
            foreach ($tenants as $tenant) {
                $tenantName = $tenant->company_name ?? ($tenant->name ?? 'Buckcrest Havens Limited');
                $this->info("--> Initializing Tenancy for: [{$tenant->id}] ({$tenantName})");
                $tenant->run(function () use ($includeProperties, $keepStaff) {
                    $this->executeCleanup($includeProperties, $keepStaff);
                });
                $this->info("✓ Successfully cleaned up tenant: [{$tenant->id}]");
            }
        } else {
            $this->info('--> Processing current database connection...');
            $this->executeCleanup($includeProperties, $keepStaff);
        }

        $this->newLine();
        $this->info('🎉 BUCKCREST CRM SYSTEM IS NOW CLEAN & READY FOR LIVE OPERATIONS!');
        return Command::SUCCESS;
    }

    /**
     * Execute cleanup on the active database connection.
     */
    private function executeCleanup(bool $includeProperties, bool $keepStaff): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Transactional and test data tables to truncate
        $tablesToTruncate = [
            'leads',
            'lead_activities',
            'sales',
            'payment_plans',
            'payment_milestones',
            'inspections',
            'follow_ups',
            'sales_weekly_field_logs',
            'commissions',
            'documents',
            'campaign_contacts',
            'campaigns',
            'activity_logs',
            'expenses',
        ];

        if ($includeProperties) {
            $tablesToTruncate[] = 'properties';
            $tablesToTruncate[] = 'property_units';
            $tablesToTruncate[] = 'project_milestones';
            $tablesToTruncate[] = 'projects';
        }

        $this->info('1. Purging transactional test records:');
        foreach ($tablesToTruncate as $tableName) {
            if (Schema::hasTable($tableName)) {
                $count = DB::table($tableName)->count();
                DB::table($tableName)->truncate();
                $this->line("   ✓ Truncated `{$tableName}` ({$count} records removed, ID counter reset)");
            }
        }

        // 2. Demo staff accounts cleanup
        if (! $keepStaff) {
            $this->newLine();
            $this->info('2. Reviewing and purging demo staff accounts:');

            $demoUsers = User::where(function ($q) {
                $q->where('email', 'like', '%@propertyflow.com')
                  ->orWhere('email', 'like', '%@example.com')
                  ->orWhere('email', 'like', '%.audit@bhl.com')
                  ->orWhereIn('name', [
                      'Joy Adeyemi',
                      'Ibrahim Musa',
                      'Chidinma Okonkwo',
                      'Femi Adeleke',
                      'Amina Bello',
                      'Chidinma Okafor (Senior Executive)',
                      'Tunde Adeleke (Operations)',
                      'Amina Yusuf (Client Relations)',
                  ]);
            })->where(function ($q) {
                // Ensure real administrator accounts are NEVER removed
                $q->where('email', 'not like', '%@buckcresthavens.com')
                  ->where('email', 'not like', '%@buckcresthavensltd.org')
                  ->where('email', 'not like', '%@bhl.nawpropertyflow.com.ng');
            })->get();

            $countDemo = $demoUsers->count();
            if ($countDemo > 0) {
                $demoIds = $demoUsers->pluck('id')->toArray();

                $hrTables = [
                    'salary_structures',
                    'payroll_deductions',
                    'performance_reviews',
                    'disciplinary_records',
                    'staff_certifications',
                    'staff_submissions',
                    'daily_tasks',
                    'onboarding_tasks',
                ];

                foreach ($hrTables as $hrTable) {
                    if (Schema::hasTable($hrTable)) {
                        DB::table($hrTable)->whereIn('user_id', $demoIds)->delete();
                    }
                }

                User::whereIn('id', $demoIds)->delete();
                $this->line("   ✓ Removed {$countDemo} demo staff accounts.");
            } else {
                $this->line('   ✓ No demo staff accounts found to remove.');
            }
        }

        // 3. Ensure Company Settings & Branding are pristine
        if (Schema::hasTable('company_settings')) {
            $cs = CompanySetting::first();
            if ($cs) {
                $cs->update([
                    'company_name' => 'Buckcrest Havens Limited',
                    'logo_path'    => 'company/buckcrest-logo.png',
                ]);
            } else {
                CompanySetting::create([
                    'company_name' => 'Buckcrest Havens Limited',
                    'logo_path'    => 'company/buckcrest-logo.png',
                ]);
            }
            Cache::forget('company_settings');
            Cache::forget('company_setting');
            Cache::forget('active_company_setting');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 4. Status Summary
        $this->newLine();
        $this->info('3. Preserved System State:');
        if (Schema::hasTable('properties')) {
            $propCount = Property::count();
            $this->line("   • Properties Catalog: <info>{$propCount} listing(s) preserved</info>");
        }

        if (Schema::hasTable('users')) {
            $activeUsers = User::all();
            $this->line('   • Active Staff & Admin Accounts (' . $activeUsers->count() . ' remaining):');
            foreach ($activeUsers as $u) {
                $this->line("     - {$u->name} (<comment>{$u->email}</comment>) [Role: {$u->role}]");
            }
        }
    }
}
