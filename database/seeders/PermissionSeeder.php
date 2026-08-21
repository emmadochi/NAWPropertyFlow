<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Seed all granular system permissions and link pre-existing users to default roles.
     */
    public function run(): void
    {
        $permissions = [
            // 1. Properties & Estates Module
            ['module' => 'Properties & Estates', 'name' => 'View Properties & Inventory', 'slug' => 'properties.view', 'description' => 'View property listings, plot availability & 3D tour.'],
            ['module' => 'Properties & Estates', 'name' => 'Create Properties', 'slug' => 'properties.create', 'description' => 'Add new property developments and estates.'],
            ['module' => 'Properties & Estates', 'name' => 'Edit Property Details & Pricing', 'slug' => 'properties.edit', 'description' => 'Modify estate information, photos and unit prices.'],
            ['module' => 'Properties & Estates', 'name' => 'Delete Properties', 'slug' => 'properties.delete', 'description' => 'Permanently remove properties from the inventory.'],
            ['module' => 'Properties & Estates', 'name' => 'Manage Units & Plots Pipeline', 'slug' => 'units.manage', 'description' => 'Allocate, reserve and adjust plots/units.'],

            // 2. Sales & Leads Pipeline Module
            ['module' => 'Sales & Leads', 'name' => 'View All Leads (Global Scope)', 'slug' => 'leads.view_all', 'description' => 'Access all company-wide leads across all sales reps.'],
            ['module' => 'Sales & Leads', 'name' => 'View Own Assigned Leads Only', 'slug' => 'leads.view_own', 'description' => 'Restricted only to leads assigned to the logged-in user.'],
            ['module' => 'Sales & Leads', 'name' => 'Create & Capture Leads', 'slug' => 'leads.create', 'description' => 'Log new inquiries, buyers and prospects.'],
            ['module' => 'Sales & Leads', 'name' => 'Edit & Update Leads', 'slug' => 'leads.edit', 'description' => 'Modify stage, status and client profile details.'],
            ['module' => 'Sales & Leads', 'name' => 'Reassign Leads to Officers', 'slug' => 'leads.reassign', 'description' => 'Distribute or reassign leads among sales executives.'],
            ['module' => 'Sales & Leads', 'name' => 'Delete Leads', 'slug' => 'leads.delete', 'description' => 'Permanently delete lead records.'],
            ['module' => 'Sales & Leads', 'name' => 'Record Closed Sales & Contracts', 'slug' => 'sales.record', 'description' => 'Generate closed contracts, payment plans and instant receipts.'],

            // 3. Finance, Payments & OPEX Module
            ['module' => 'Finance & Accounting', 'name' => 'View Financial Ledgers & P&L', 'slug' => 'finance.view_ledger', 'description' => 'Access revenue analytics, cash inflows and net profit.'],
            ['module' => 'Finance & Accounting', 'name' => 'Verify Proof of Payment & POP', 'slug' => 'finance.verify_payments', 'description' => 'Audit client bank receipts and stamp official payments.'],
            ['module' => 'Finance & Accounting', 'name' => 'Log Operational Expenses', 'slug' => 'finance.log_expenses', 'description' => 'Submit site diesel, maintenance and petty cash expenses.'],
            ['module' => 'Finance & Accounting', 'name' => 'Approve & Reject Expenses', 'slug' => 'finance.approve_expenses', 'description' => 'Audit and grant official approval for expense claims.'],
            ['module' => 'Finance & Accounting', 'name' => 'Disburse Expense Payments', 'slug' => 'finance.disburse_expenses', 'description' => 'Mark approved operational expenses as paid/disbursed.'],
            ['module' => 'Finance & Accounting', 'name' => 'Manage Staff Payroll & Payslips', 'slug' => 'finance.manage_payroll', 'description' => 'Generate monthly payroll, salary structures and payslips.'],

            // 4. Inspections & Follow-Ups Module
            ['module' => 'Inspections & Tasks', 'name' => 'View All Inspections', 'slug' => 'inspections.view_all', 'description' => 'Monitor company-wide property inspection calendar.'],
            ['module' => 'Inspections & Tasks', 'name' => 'Schedule Site Inspections', 'slug' => 'inspections.schedule', 'description' => 'Book client site visitations and vehicle logistics.'],
            ['module' => 'Inspections & Tasks', 'name' => 'Manage Follow-Up Reminders', 'slug' => 'followups.manage', 'description' => 'Set and complete sales follow-up calls and tasks.'],

            // 5. Human Resources Module
            ['module' => 'Human Resources', 'name' => 'View Staff Directory & Profiles', 'slug' => 'hr.view_staff', 'description' => 'Access employee records, departments and branches.'],
            ['module' => 'Human Resources', 'name' => 'Approve Staff Leave Requests', 'slug' => 'hr.approve_leaves', 'description' => 'Review and approve/reject employee annual/casual leave.'],
            ['module' => 'Human Resources', 'name' => 'Review Staff Daily Submissions', 'slug' => 'hr.review_submissions', 'description' => 'Audit daily work reports and department KPIs.'],
            ['module' => 'Human Resources', 'name' => 'Manage Department Targets', 'slug' => 'hr.manage_targets', 'description' => 'Set and monitor monthly department quota and goals.'],
            ['module' => 'Human Resources', 'name' => 'Manage User Accounts', 'slug' => 'hr.manage_users', 'description' => 'Create staff logins and manage onboardings.'],

            // 6. Media & Creative Production Module
            ['module' => 'Media & Creative Assets', 'name' => 'View Media Assets & Storage', 'slug' => 'media.view_assets', 'description' => 'Browse property drone shoots, videos, photos & brochures.'],
            ['module' => 'Media & Creative Assets', 'name' => 'Manage Media Production Quota', 'slug' => 'media.manage_production', 'description' => 'Track video shoot quotas, edited reels, and delivery calendars.'],

            // 7. Marketing & Lead Acquisition Module
            ['module' => 'Marketing & Campaigns', 'name' => 'View Marketing Campaigns & ROI', 'slug' => 'marketing.view', 'description' => 'View campaign performance, deliverability, and lead acquisition sources.'],
            ['module' => 'Marketing & Campaigns', 'name' => 'Create & Dispatch Broadcasts', 'slug' => 'marketing.send_broadcast', 'description' => 'Send email newsletters and SMS/WhatsApp blasts to prospects.'],
            ['module' => 'Marketing & Campaigns', 'name' => 'Manage Automated Drip Sequences', 'slug' => 'marketing.manage_drip', 'description' => 'Configure automated lead nurturing drip sequences.'],

            // 8. System & Administration Module
            ['module' => 'System & Administration', 'name' => 'Manage Custom Roles & Permissions', 'slug' => 'system.manage_roles', 'description' => 'Create custom roles and configure modular capabilities.'],
            ['module' => 'System & Administration', 'name' => 'View Audit Activity Logs', 'slug' => 'system.view_audit_logs', 'description' => 'Inspect complete immutable system activity history.'],
            ['module' => 'System & Administration', 'name' => 'Manage Company Settings', 'slug' => 'system.manage_settings', 'description' => 'Configure company logo, letterheads, branches & tier.'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['slug' => $perm['slug']], $perm);
        }

        // 1. Seed Official 8 RICAF Departments
        $departments = [
            ['name' => 'Admin', 'description' => 'Executive governance, human resources, company compliance, and customer service.'],
            ['name' => 'Marketing & Sales', 'description' => 'Client acquisition, promotional campaigns, leads pipeline, and deal closures.'],
            ['name' => 'Media and Creative', 'description' => 'Video production, drone shoots, promotional photography, and creative branding.'],
            ['name' => 'Accounting', 'description' => 'Revenue auditing, client proof of payments, OPEX claims, and payroll management.'],
            ['name' => 'Procurement', 'description' => 'Vendor sourcing, estate building materials quotation, and inventory logistics.'],
            ['name' => 'Legal', 'description' => 'Contract deed drafting, title vetting, statutory regulatory documentation, and compliance.'],
            ['name' => 'Project Management', 'description' => 'Architectural master plans, site engineering, and estate construction milestones.'],
            ['name' => 'Logistics', 'description' => 'Vehicle dispatch, property inspection tours logistics, and transport fleet maintenance.'],
        ];

        foreach ($departments as $deptData) {
            \App\Models\Department::updateOrCreate(
                ['name' => $deptData['name']],
                [
                    'description' => $deptData['description'],
                    'is_active' => true,
                ]
            );
        }

        // 2. Define Official RICAF System Roles
        $rolesConfig = [
            'super_admin' => [
                'name' => 'Super Administrator (Managing Director)',
                'description' => 'Root enterprise executive with unrestricted global access across all departments.',
                'is_system' => true,
                'permissions' => Permission::pluck('slug')->toArray(),
            ],
            'company_admin' => [
                'name' => 'Company Administrator',
                'description' => 'Executive administrator managing daily operations, staff directory, and financial approvals.',
                'is_system' => true,
                'permissions' => Permission::pluck('slug')->toArray(),
            ],
            'hr' => [
                'name' => 'Human Resource',
                'description' => 'Manages staff profiles, onboarding, daily attendance KPI logs, and leave approvals.',
                'is_system' => false,
                'permissions' => [
                    'hr.view_staff', 'hr.approve_leaves', 'hr.review_submissions', 'hr.manage_targets', 'hr.manage_users',
                    'finance.manage_payroll'
                ],
            ],
            'marketing' => [
                'name' => 'Marketing',
                'description' => 'Coordinates promotional campaigns, broadcasts, automated email/SMS drips, and lead acquisition.',
                'is_system' => false,
                'permissions' => [
                    'properties.view',
                    'marketing.view', 'marketing.send_broadcast', 'marketing.manage_drip',
                    'media.view_assets', 'finance.log_expenses'
                ],
            ],
            'sales_executive' => [
                'name' => 'Sales Executive',
                'description' => 'Front-line real estate sales officer managing assigned prospects, site tours, and deal closings.',
                'is_system' => false,
                'permissions' => [
                    'properties.view',
                    'leads.view_own', 'leads.create', 'leads.edit', 'sales.record',
                    'inspections.schedule', 'followups.manage',
                    'finance.log_expenses'
                ],
            ],
            'customer_service_rep' => [
                'name' => 'Customer Service Representative',
                'description' => 'Handles incoming prospect inquiries, general customer queries, and lead routing.',
                'is_system' => false,
                'permissions' => [
                    'properties.view',
                    'leads.view_all', 'leads.create', 'leads.edit', 'followups.manage',
                    'inspections.view_all'
                ],
            ],
            'telecommunications' => [
                'name' => 'Telecommunications',
                'description' => 'Outbound telemarketing, cold call outreach, client follow-up calls, and phone appointments.',
                'is_system' => false,
                'permissions' => [
                    'properties.view',
                    'leads.view_own', 'leads.create', 'leads.edit', 'followups.manage'
                ],
            ],
            'media_manager' => [
                'name' => 'Media Officer / Manager',
                'description' => 'Produces drone site shoots, estate videos, promotional flyers, reels, and digital assets.',
                'is_system' => false,
                'permissions' => [
                    'properties.view',
                    'media.view_assets', 'media.manage_production',
                    'marketing.view', 'finance.log_expenses'
                ],
            ],
            'business_development_manager' => [
                'name' => 'Business Development Officer / Manager',
                'description' => 'Drives high-value corporate deals, joint venture partnerships, and strategic real estate expansions.',
                'is_system' => false,
                'permissions' => [
                    'properties.view', 'units.manage',
                    'leads.view_all', 'leads.create', 'leads.edit', 'leads.reassign', 'sales.record',
                    'inspections.view_all', 'inspections.schedule', 'followups.manage',
                    'marketing.view', 'finance.log_expenses'
                ],
            ],
            'accountant' => [
                'name' => 'Chief Financial Officer / Accountant',
                'description' => 'Audits incoming client payments, approves OPEX claims, and oversees financial ledgers & payroll.',
                'is_system' => false,
                'permissions' => [
                    'properties.view',
                    'finance.view_ledger', 'finance.verify_payments', 'finance.log_expenses',
                    'finance.approve_expenses', 'finance.disburse_expenses', 'finance.manage_payroll',
                    'hr.view_staff'
                ],
            ],
            'architect' => [
                'name' => 'Architect',
                'description' => 'Designs estate layouts, architectural 3D building renderings, and plot master plan allocations.',
                'is_system' => false,
                'permissions' => [
                    'properties.view', 'properties.edit', 'units.manage',
                    'media.view_assets', 'finance.log_expenses'
                ],
            ],
            'site_engineer' => [
                'name' => 'Site Engineer',
                'description' => 'Supervises physical construction milestones, plot beacons, site inspections, and site diesel logs.',
                'is_system' => false,
                'permissions' => [
                    'properties.view', 'properties.edit', 'units.manage',
                    'inspections.schedule', 'inspections.view_all',
                    'finance.log_expenses'
                ],
            ],
            'legal_personnel' => [
                'name' => 'Legal Personnel',
                'description' => 'Vets land titles, drafts Contract of Sale & Deed of Assignment agreements, and manages legal compliance.',
                'is_system' => false,
                'permissions' => [
                    'properties.view', 'leads.view_all', 'sales.record',
                    'finance.log_expenses'
                ],
            ],
            'procurement_officer' => [
                'name' => 'Procurement Officer',
                'description' => 'Sources estate construction materials, negotiates vendor purchase orders, and audits inventory supplies.',
                'is_system' => false,
                'permissions' => [
                    'properties.view', 'units.manage',
                    'finance.log_expenses'
                ],
            ],
            'driver' => [
                'name' => 'Driver / Logistics Personnel',
                'description' => 'Drives client inspection site tours, maintains company vehicles, and logs fuel receipts.',
                'is_system' => false,
                'permissions' => [
                    'inspections.view_all',
                    'finance.log_expenses'
                ],
            ],
            'sales_manager' => [
                'name' => 'Head of Sales / Sales Manager',
                'description' => 'Oversees sales reps, leads pipeline distribution, closed deals, and inspections.',
                'is_system' => false,
                'permissions' => [
                    'properties.view', 'units.manage',
                    'leads.view_all', 'leads.create', 'leads.edit', 'leads.reassign', 'sales.record',
                    'inspections.view_all', 'inspections.schedule', 'followups.manage',
                    'finance.log_expenses', 'marketing.view', 'hr.view_staff'
                ],
            ],
            'project_manager' => [
                'name' => 'Site / Project Manager',
                'description' => 'Monitors on-site estate construction, plots allocation pipeline, and site diesel expenses.',
                'is_system' => false,
                'permissions' => [
                    'properties.view', 'properties.create', 'properties.edit', 'units.manage',
                    'finance.log_expenses'
                ],
            ],
        ];

        foreach ($rolesConfig as $slug => $data) {
            $role = Role::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_system' => $data['is_system'],
                ]
            );

            $role->syncPermissions($data['permissions']);
        }

        // Migrate existing users without breaking any accounts
        $allUsers = User::all();
        foreach ($allUsers as $u) {
            $roleSlug = $u->role ?: 'sales_executive';
            $matchedRole = Role::where('slug', $roleSlug)->first();
            if ($matchedRole) {
                $u->role_id = $matchedRole->id;
                $u->saveQuietly();
            }
        }
    }
}
