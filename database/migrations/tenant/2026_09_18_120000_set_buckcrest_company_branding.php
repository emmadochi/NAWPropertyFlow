<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tenantId = function_exists('tenant') && tenant() ? tenant('id') : null;

        // Strictly target Buckcrest Havens Limited tenant instances
        if (in_array($tenantId, ['bhl', 'buckcrest'])) {
            $setting = DB::table('company_settings')->first();
            if ($setting) {
                DB::table('company_settings')->where('id', $setting->id)->update([
                    'company_name' => 'Buckcrest Havens Limited',
                    'logo_path'    => 'company/buckcrest-logo.png',
                    'updated_at'   => now(),
                ]);
            } else {
                DB::table('company_settings')->insert([
                    'company_name' => 'Buckcrest Havens Limited',
                    'logo_path'    => 'company/buckcrest-logo.png',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive rollback needed
    }
};
