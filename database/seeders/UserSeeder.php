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
                'job_title' => 'Managing Director / CEO',
                'is_department_head' => true,
                'department' => 'Admin',
                'phone_number' => '+2348031112222',
                'status' => 'active',
            ],
            // 2. Company Admin
            [
                'name' => 'Company Administrator',
                'email' => 'admin@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'company_admin',
                'job_title' => 'Chief Operations Officer (COO)',
                'is_department_head' => true,
                'department' => 'Admin',
                'phone_number' => '+2348032223333',
                'status' => 'active',
            ],
            // 3. Sales Manager
            [
                'name' => 'Tunde Bakare',
                'email' => 'manager@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'sales_manager',
                'job_title' => 'Head of Sales & Marketing',
                'is_department_head' => true,
                'department' => 'Marketing & Sales',
                'phone_number' => '+2348034445555',
                'status' => 'active',
            ],
            // 4. Sales Executive
            [
                'name' => 'Emeka Okafor',
                'email' => 'se1@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'sales_executive',
                'job_title' => 'Senior Real Estate Sales Executive',
                'is_department_head' => false,
                'department' => 'Marketing & Sales',
                'phone_number' => '+2348036667777',
                'status' => 'active',
            ],
            // 5. HR Officer
            [
                'name' => 'Zainab Ahmed',
                'email' => 'hr@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'hr',
                'job_title' => 'Human Resources Lead',
                'is_department_head' => true,
                'department' => 'Admin',
                'phone_number' => '+2348035556666',
                'status' => 'active',
            ],
            // 6. Accountant / Finance
            [
                'name' => 'Femi Adeleke',
                'email' => 'accountant@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'accountant',
                'job_title' => 'Head of Finance & Accounts',
                'is_department_head' => true,
                'department' => 'Accounting',
                'phone_number' => '+2348037778888',
                'status' => 'active',
            ],
            // 7. Customer Service Rep
            [
                'name' => 'Blessing Nnamdi',
                'email' => 'support@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'customer_service_rep',
                'job_title' => 'Front Desk & Client Relations Officer',
                'is_department_head' => false,
                'department' => 'Admin',
                'phone_number' => '+2348039990000',
                'status' => 'active',
            ],
            // 8. Marketing Lead
            [
                'name' => 'Aisha Bello',
                'email' => 'marketing@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'marketing',
                'job_title' => 'Digital Marketing & Ads Specialist',
                'is_department_head' => false,
                'department' => 'Marketing & Sales',
                'phone_number' => '+2348035558888',
                'status' => 'active',
            ],
            // 9. Legal Officer
            [
                'name' => 'Barrister Chidi Eze',
                'email' => 'legal@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'legal_personnel',
                'job_title' => 'Head of Legal & Company Secretary',
                'is_department_head' => true,
                'department' => 'Legal',
                'phone_number' => '+2348034449999',
                'status' => 'active',
            ],
            // 10. Media Manager
            [
                'name' => 'David Olatunji',
                'email' => 'media@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'media_manager',
                'job_title' => 'Media & Production Lead',
                'is_department_head' => true,
                'department' => 'Media and Creative',
                'phone_number' => '+2348038881111',
                'status' => 'active',
            ],
            // 11. Graphic Designer (Media Squad Member)
            [
                'name' => 'Sarah Obi',
                'email' => 'graphic.designer@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'media_manager',
                'job_title' => 'Lead Graphic & Brand Designer',
                'is_department_head' => false,
                'department' => 'Media and Creative',
                'phone_number' => '+2348038882222',
                'status' => 'active',
            ],
            // 12. Video Editor & Motion Designer (Media Squad Member)
            [
                'name' => 'Tunde Williams',
                'email' => 'video.editor@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'media_manager',
                'job_title' => 'Senior Video Editor & Drone Pilot',
                'is_department_head' => false,
                'department' => 'Media and Creative',
                'phone_number' => '+2348038883333',
                'status' => 'active',
            ],
            // 13. Client / Investor (Customer Portal)
            [
                'name' => 'Chief Kenneth Ofori (Investor)',
                'email' => 'client@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'job_title' => 'Diaspora VIP Investor',
                'department' => 'Investors',
                'phone_number' => '+2348021119999',
                'status' => 'active',
            ],
            // 14. Site Manager / Civil Engineer
            [
                'name' => 'Engr. Emeka Nwosu',
                'email' => 'site.manager@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'site_engineer',
                'job_title' => 'Chief Project Engineer',
                'is_department_head' => true,
                'department' => 'Project Management',
                'phone_number' => '+2348033330003',
                'status' => 'active',
            ],
            // 15. Quantity Surveyor (QS)
            [
                'name' => 'QS Babatunde Sanusi',
                'email' => 'qs@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'quantity_surveyor',
                'job_title' => 'Senior Quantity Surveyor (QS)',
                'is_department_head' => false,
                'department' => 'Project Management',
                'phone_number' => '+2348032220002',
                'status' => 'active',
            ],
            // 16. Site Storekeeper
            [
                'name' => 'Musa Aliyu',
                'email' => 'storekeeper@propertyflow.com',
                'password' => Hash::make('password'),
                'role' => 'store_keeper',
                'job_title' => 'Materials & Inventory Officer',
                'is_department_head' => false,
                'department' => 'Project Management',
                'phone_number' => '+2348034440004',
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
