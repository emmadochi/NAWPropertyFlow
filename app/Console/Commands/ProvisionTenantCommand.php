<?php

namespace App\Console\Commands;

use App\Models\CompanySetting;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProvisionTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:provision 
                            {id? : Tenant identifier / subdomain (e.g. bhl)}
                            {--name= : Full company name (e.g. "Buckcrest Havens")}
                            {--db= : Custom database name (e.g. stufedoc_nawcrm_bhl)}
                            {--tier=starter : Package tier (starter, professional, enterprise)}
                            {--modules=* : Specific modules to enable (default: crm, payment_plans for pure CRM)}
                            {--email= : Admin email address}
                            {--admin-name= : Admin full name}
                            {--password= : Admin initial password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provision a new multi-tenant instance with isolated database, migrations, domains, and initial admin credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id') ?: $this->ask('Enter tenant subdomain / ID (e.g. bhl)', 'bhl');
        $id = Str::slug($id);

        $name = $this->option('name') ?: $this->ask('Enter company name', 'Buckcrest Havens');
        $tier = $this->option('tier') ?: 'starter';
        $email = $this->option('email') ?: "admin@{$id}.nawpropertyflow.com.ng";
        $adminName = $this->option('admin-name') ?: "{$name} Admin";
        $password = $this->option('password') ?: 'Buckcrest@2026!';
        
        $modules = $this->option('modules');
        if (empty($modules)) {
            // Default to pure CRM system as requested
            $modules = ['crm', 'payment_plans'];
        }

        $dbPrefix = config('tenancy.database.prefix', 'nawcrm_');
        $dbSuffix = config('tenancy.database.suffix', '');
        $tenantDbName = $this->option('db') ?: ($dbPrefix . $id . $dbSuffix);

        $this->info("--------------------------------------------------");
        $this->info(" Provisioning Tenant: {$name} ({$id})");
        $this->info(" Domain: {$id}.nawpropertyflow.com.ng");
        $this->info(" Database: {$tenantDbName}");
        $this->info(" Functionality: Solely CRM System ({$tier} tier)");
        $this->info("--------------------------------------------------");

        // 1. Create Tenant Database if not exists
        $this->info("1. Ensuring database `{$tenantDbName}` exists...");
        $dbReady = false;

        // Try direct CREATE DATABASE first
        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$tenantDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $this->line("   <info>✓</info> Database `{$tenantDbName}` ready.");
            $dbReady = true;
        } catch (\Throwable $e) {
            // CREATE DATABASE failed (normal on cPanel shared hosting)
        }

        if (!$dbReady) {
            // Check if database already exists in cPanel MySQL
            try {
                $testConn = array_merge(config('database.connections.mysql'), ['database' => $tenantDbName]);
                config(['database.connections.tenant_check' => $testConn]);
                DB::purge('tenant_check');
                DB::connection('tenant_check')->getPdo();
                $this->line("   <info>✓</info> Database `{$tenantDbName}` already exists and is accessible.");
                $dbReady = true;
            } catch (\Throwable $ex) {
                $this->error("   Database `{$tenantDbName}` is not accessible: " . $ex->getMessage());
                $this->line("");
                $this->warn("   💡 cPanel Shared Hosting Setup Needed:");
                $this->line("   Because cPanel shared hosting blocks PHP from running `CREATE DATABASE`,");
                $this->line("   you need to create the database in cPanel first (takes 30 seconds):");
                $this->line("");
                $this->line("   1. Go to cPanel > MySQL® Databases");
                $this->line("   2. Under 'Create New Database', enter: nawcrm_{$id}");
                $this->line("      (Your full database name will be: {$tenantDbName})");
                $this->line("   3. Under 'Add User To Database', select user `" . config('database.connections.mysql.username') . "`");
                $this->line("      and database `{$tenantDbName}`");
                $this->line("   4. Click 'Add', tick 'ALL PRIVILEGES', and click 'Make Changes'");
                
                try {
                    $dbs = DB::select("SHOW DATABASES;");
                    $dbList = array_map(function($d) { return array_values((array)$d)[0]; }, $dbs);
                    $this->line("");
                    $this->info("   Currently visible databases on your account:");
                    foreach ($dbList as $visibleDb) {
                        $this->line("   - {$visibleDb}");
                    }
                } catch (\Throwable $t) {}

                $this->line("");
                $this->line("   Then re-run: php artisan tenant:provision {$id} --db={$tenantDbName}");
                return 1;
            }
        }

        // 2. Create or Update Tenant in Central DB
        $this->info("2. Registering central Tenant record...");
        if (!Schema::hasTable('tenants')) {
            $this->warn("   Central `tenants` table not found in `" . config('database.connections.mysql.database') . "`. Running central migrations...");
            Artisan::call('migrate', ['--force' => true]);
            $this->line(Artisan::output());
            $this->line("   <info>✓</info> Central tables created successfully.");
        }

        try {
            DB::connection('mysql')->getPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        } catch (\Throwable $e) {}

        try {
            $tenant = Tenant::firstOrNew(['id' => $id]);
            $tenant->company_name = $name;
            $tenant->package_tier = $tier;
            $tenant->admin_email  = $email;
            $tenant->admin_name   = $adminName;
            $tenant->is_active    = true;
            $tenant->data         = array_merge($tenant->data ?? [], [
                'tenancy_db_name' => $tenantDbName,
            ]);
            $tenant->save();
        } catch (\Throwable $e) {
            // Reconnect and retry with raw query if MySQL 1615 prepared statement error occurs
            DB::purge('mysql');
            DB::reconnect('mysql');
            try {
                DB::connection('mysql')->getPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
            } catch (\Throwable $e2) {}

            DB::table('tenants')->updateOrInsert(
                ['id' => $id],
                [
                    'company_name' => $name,
                    'package_tier' => $tier,
                    'admin_email'  => $email,
                    'admin_name'   => $adminName,
                    'is_active'    => 1,
                    'data'         => json_encode(['tenancy_db_name' => $tenantDbName]),
                    'updated_at'   => now(),
                ]
            );
            $tenant = Tenant::find($id);
        }
        $this->line("   <info>✓</info> Central tenant registered.");

        // 3. Register Associated Subdomains & Domains
        $this->info("3. Configuring routing domains...");
        $domains = [
            $id,
            "{$id}.nawpropertyflow.com.ng",
            "{$id}.localhost",
        ];

        foreach ($domains as $domainName) {
            try {
                DB::table('domains')->updateOrInsert(
                    ['domain' => $domainName],
                    [
                        'tenant_id'  => $id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            } catch (\Throwable $e) {
                // Already attached
            }
            $this->line("   <info>✓</info> Attached domain: {$domainName}");
        }

        // 4. Configure dynamic tenant connection and run migrations
        $this->info("4. Running tenant database migrations on `{$tenantDbName}`...");
        config(['database.connections.tenant' => [
            'driver'    => 'mysql',
            'host'      => config('database.connections.mysql.host', '127.0.0.1'),
            'port'      => config('database.connections.mysql.port', '3306'),
            'database'  => $tenantDbName,
            'username'  => config('database.connections.mysql.username', 'root'),
            'password'  => config('database.connections.mysql.password', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'options'   => [\PDO::ATTR_EMULATE_PREPARES => true],
        ]]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        try {
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path'     => 'database/migrations/tenant',
                '--force'    => true,
            ]);
            $this->line(Artisan::output());
            $this->line("   <info>✓</info> Migrations executed successfully on `{$tenantDbName}`.");
        } catch (\Throwable $e) {
            $this->error("   Migration error: " . $e->getMessage());
            return 1;
        }

        // 5. Seed Company Settings & Initial Users inside Tenant Environment
        $this->info("5. Initializing company settings and user accounts in `{$tenantDbName}`...");
        $previousConnection = DB::getDefaultConnection();
        DB::setDefaultConnection('tenant');

        try {
            // Company Setting
            $cs = CompanySetting::firstOrNew();
            $cs->company_name    = $name;
            $cs->email           = $email;
            $cs->package_tier    = $tier;
            $cs->enabled_modules = $modules;
            $cs->phone           = '+234 800 000 0000';
            $cs->address         = 'Abuja, Nigeria';
            $cs->save();

            // Run Permission Seeder on tenant connection
            try {
                (new PermissionSeeder())->run();
            } catch (\Throwable $e) {
                // Ignore if permissions already seeded
            }

            // Company Admin User
            $adminUser = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'     => $adminName,
                    'password' => Hash::make($password),
                    'role'     => 'company_admin',
                ]
            );

            // Seed an initial Sales Executive so the CRM pipeline is immediately operational
            $salesEmail = "sales@{$id}.nawpropertyflow.com.ng";
            User::updateOrCreate(
                ['email' => $salesEmail],
                [
                    'name'      => 'Lead Sales Consultant',
                    'password'  => Hash::make($password),
                    'role'      => 'sales_executive',
                    'job_title' => 'Retail Sales Executive',
                ]
            );

            $this->line("   <info>✓</info> Tenant database `{$tenantDbName}` initialized.");
        } finally {
            DB::setDefaultConnection($previousConnection);
        }

        $this->info("   <info>✓</info> Tenant environment successfully initialized!");
        $this->info("--------------------------------------------------");
        $this->info(" Tenant Provisioning Completed Successfully!");
        $this->info(" Company Name: {$name}");
        $this->info(" Subdomain:    {$id}.nawpropertyflow.com.ng");
        $this->info(" Local URL:    http://{$id}.localhost:8000/login");
        $this->info(" Live URL:     https://{$id}.nawpropertyflow.com.ng/login");
        $this->info(" Admin Email:  {$email}");
        $this->info(" Password:     {$password}");
        $this->info(" Sales Email:  {$salesEmail}");
        $this->info(" Modules:      " . implode(', ', $modules) . " (Solely CRM Suite)");
        $this->info("--------------------------------------------------");

        return 0;
    }
}
