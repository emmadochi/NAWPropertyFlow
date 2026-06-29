<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::create([
            'name' => 'Super Admin Officer',
            'email' => 'superadmin@propertyflow.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'phone_number' => '+2348031112222',
            'status' => 'active',
        ]);

        // Company Admin
        User::create([
            'name' => 'Company Admin User',
            'email' => 'admin@propertyflow.com',
            'password' => Hash::make('password'),
            'role' => 'company_admin',
            'phone_number' => '+2348032223333',
            'status' => 'active',
        ]);

        // Sales Manager
        User::create([
            'name' => 'Sales Manager Officer',
            'email' => 'manager@propertyflow.com',
            'password' => Hash::make('password'),
            'role' => 'sales_manager',
            'phone_number' => '+2348034445555',
            'status' => 'active',
        ]);

        // Sales Executives
        User::create([
            'name' => 'Emeka Okafor (Sales Exec)',
            'email' => 'se1@propertyflow.com',
            'password' => Hash::make('password'),
            'role' => 'sales_executive',
            'phone_number' => '+2348036667777',
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Funmi Balogun (Sales Exec)',
            'email' => 'se2@propertyflow.com',
            'password' => Hash::make('password'),
            'role' => 'sales_executive',
            'phone_number' => '+2348038889999',
            'status' => 'active',
        ]);
    }
}
