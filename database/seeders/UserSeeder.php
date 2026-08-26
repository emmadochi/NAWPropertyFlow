<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // 1. Super Admin
            [
                'name' => 'Super Admin Officer',
                'email' => 'superadmin@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'department' => 'Executive',
                'phone_number' => '+2348031112222',
                'status' => 'active',
            ],
            // 2. Company Admin
            [
                'name' => 'Company Administrator',
                'email' => 'admin@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'company_admin',
                'department' => 'Management',
                'phone_number' => '+2348032223333',
                'status' => 'active',
            ],
            // 3. Sales Manager
            [
                'name' => 'Tunde Bakare (Sales Manager)',
                'email' => 'manager@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'sales_manager',
                'department' => 'Sales',
                'phone_number' => '+2348034445555',
                'status' => 'active',
            ],
            // 4. Sales Executive
            [
                'name' => 'Emeka Okafor (Sales Exec)',
                'email' => 'se1@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'sales_executive',
                'department' => 'Sales',
                'phone_number' => '+2348036667777',
                'status' => 'active',
            ],
            // 5. HR Officer
            [
                'name' => 'Zainab Ahmed (HR Lead)',
                'email' => 'hr@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'hr',
                'department' => 'Human Resources',
                'phone_number' => '+2348035556666',
                'status' => 'active',
            ],
            // 6. Accountant / Finance
            [
                'name' => 'Femi Adeleke (Lead Accountant)',
                'email' => 'accountant@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'accountant',
                'department' => 'Finance & Accounts',
                'phone_number' => '+2348037778888',
                'status' => 'active',
            ],
            // 7. Customer Service Rep
            [
                'name' => 'Blessing Nnamdi (Customer Care)',
                'email' => 'support@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'customer_service_rep',
                'department' => 'Customer Service',
                'phone_number' => '+2348039990000',
                'status' => 'active',
            ],
            // 8. Marketing Lead
            [
                'name' => 'Aisha Bello (Marketing Lead)',
                'email' => 'marketing@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'marketing',
                'department' => 'Marketing',
                'phone_number' => '+2348035558888',
                'status' => 'active',
            ],
            // 9. Legal Officer
            [
                'name' => 'Barrister Chidi Eze (Legal)',
                'email' => 'legal@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'legal_personnel',
                'department' => 'Legal & Compliance',
                'phone_number' => '+2348034449999',
                'status' => 'active',
            ],
            // 10. Media / Creative Manager
            [
                'name' => 'David Olatunji (Media Producer)',
                'email' => 'media@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'media_manager',
                'department' => 'Media & Production',
                'phone_number' => '+2348038881111',
                'status' => 'active',
            ],
            // 11. Client / Investor (Customer Portal)
            [
                'name' => 'Chief Kenneth Ofori (Investor)',
                'email' => 'client@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'department' => 'Investors',
                'phone_number' => '+2348021119999',
                'status' => 'active',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
