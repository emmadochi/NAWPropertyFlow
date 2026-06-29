<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\SystemAdmin;

class SystemAdminSeeder extends Seeder
{
    public function run(): void
    {
        SystemAdmin::firstOrCreate(
            ['email' => 'admin@nawworld.com'],
            [
                'name'     => 'NAW System Admin',
                'password' => Hash::make('Admin@1234'),
            ]
        );

        $this->command->info('✅ System Admin created: admin@nawworld.com / Admin@1234');
        $this->command->warn('   ⚠ Change this password immediately after first login!');
    }
}
