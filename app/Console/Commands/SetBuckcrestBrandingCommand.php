<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Cache;

class SetBuckcrestBrandingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:set-buckcrest-branding';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply Buckcrest Havens Limited company name and logo branding to bhl tenant';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantIds = ['bhl', 'buckcrest'];
        $found = false;

        foreach ($tenantIds as $id) {
            $tenant = Tenant::find($id);
            if (! $tenant) {
                continue;
            }

            $found = true;
            $this->info("Setting branding for tenant: {$id}...");

            tenancy()->initialize($tenant);

            if (\Illuminate\Support\Facades\Schema::hasTable('company_settings')) {
                $setting = CompanySetting::first();
                if ($setting) {
                    $setting->update([
                        'company_name' => 'Buckcrest Havens Limited',
                        'logo_path'    => 'company/buckcrest-logo.png',
                    ]);
                } else {
                    CompanySetting::create([
                        'company_name' => 'Buckcrest Havens Limited',
                        'logo_path'    => 'company/buckcrest-logo.png',
                    ]);
                }
                $this->info("Successfully applied Buckcrest branding to company_settings for [{$id}].");
            } else {
                $this->warn("Table 'company_settings' does not exist yet for tenant [{$id}]. Skipping table update.");
            }

            // Also update tenant model name if present
            if (isset($tenant->name)) {
                $tenant->name = 'Buckcrest Havens Limited';
                $tenant->save();
            }

            // Clear cached settings
            Cache::forget('company_settings');
            Cache::forget('company_setting');
            tenancy()->end();
        }

        if (! $found) {
            $this->warn("No tenant with ID 'bhl' or 'buckcrest' was found.");
        }

        return Command::SUCCESS;
    }
}
