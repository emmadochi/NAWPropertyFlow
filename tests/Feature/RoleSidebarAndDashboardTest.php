<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use App\Models\CompanySetting;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RoleSidebarAndDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Run tenant database migrations into sqlite memory
        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
            '--realpath' => false,
        ]);

        // 2. Seed Permissions and official system roles
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // 3. Ensure enterprise company setting exists with all features active
        CompanySetting::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'RICAF Real Estate Enterprise',
                'package_tier' => 'enterprise',
                'email' => 'info@ricafproperties.com',
                'features' => json_encode([
                    'multi_branch' => true,
                    'advanced_reports' => true,
                    'docs' => true,
                    'file_manager' => true,
                    'marketing' => true,
                    'hr' => true,
                    'leaderboard' => true,
                    'department_setup' => true,
                    'activity_logs' => true,
                    'customer_portal' => true,
                ]),
            ]
        );
    }

    private function createUserWithRole(string $roleSlug): User
    {
        $dept = Department::first();
        $matchedRole = \App\Models\Role::where('slug', $roleSlug)->first();

        return User::forceCreate([
            'name' => 'Test ' . ucfirst($roleSlug),
            'email' => $roleSlug . '_' . Str::random(5) . '@testcrm.com',
            'password' => Hash::make('password123'),
            'role' => $roleSlug,
            'role_id' => $matchedRole?->id,
            'department_id' => $dept?->id,
            'department' => $dept?->name ?? 'Admin',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function test_super_admin_has_full_executive_dashboard_and_complete_sidebar(): void
    {
        $user = $this->createUserWithRole('super_admin');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        // Dashboard Header & Controls
        $response->assertSee('Executive Control Center');
        $response->assertSee('Add New Lead');

        // Sidebar Navigation
        $response->assertSee('Overview');
        $response->assertSee('HR &amp; Personnel', false);
        $response->assertSee('Team &amp; Staff Access', false);
        $response->assertSee('Leave Management');
        $response->assertSee('Naira Payroll &amp; Salaries', false);
        $response->assertSee('Sales &amp; CRM', false);
        $response->assertSee('Leads Pipeline');
        $response->assertSee('Estates &amp; Inventory', false);
        $response->assertSee('Contracts &amp; Legal Vault', false);
        $response->assertSee('Generated Title Deeds');
        $response->assertSee('Finance &amp; Accounts', false);
        $response->assertSee('Expenses &amp; OPEX', false);
        $response->assertSee('Marketing &amp; Growth', false);
        $response->assertSee('Marketing Campaigns');
        $response->assertSee('Enterprise Config');
        $response->assertSee('Roles &amp; Permissions', false);
        $response->assertSee('Multi-Branch Setup');
        $response->assertSee('Company &amp; Letterhead', false);

        // Backend Route Authorization
        $this->actingAs($user)->get(route('settings.company.edit'))->assertStatus(200);
        $this->actingAs($user)->get(route('settings.roles.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('settings.activity-logs.index'))->assertStatus(200);
    }

    /** @test */
    public function test_company_admin_has_full_operational_dashboard_and_sidebar(): void
    {
        $user = $this->createUserWithRole('company_admin');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        // Dashboard & Sidebar
        $response->assertSee('Executive Control Center');
        $response->assertSee('HR &amp; Personnel', false);
        $response->assertSee('Sales &amp; CRM', false);
        $response->assertSee('Estates &amp; Inventory', false);
        $response->assertSee('Contracts &amp; Legal Vault', false);
        $response->assertSee('Finance &amp; Accounts', false);
        $response->assertSee('Marketing &amp; Growth', false);
        $response->assertSee('Enterprise Config');

        // Backend Route Authorization
        $this->actingAs($user)->get(route('branches.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('departments.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('settings.company.edit'))->assertStatus(200);
    }

    /** @test */
    public function test_sales_manager_has_global_sales_scope_and_restricted_admin(): void
    {
        $user = $this->createUserWithRole('sales_manager');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        // Dashboard & Visible Sidebar
        $response->assertSee('Sales &amp; Deals Dashboard', false);
        $response->assertSee('Sales Leaderboard');
        $response->assertSee('Sales &amp; CRM', false);
        $response->assertSee('Leads Pipeline');
        $response->assertSee('Follow-Ups');
        $response->assertSee('Site Inspections');
        $response->assertSee('Estates &amp; Inventory', false);
        $response->assertSee('Properties &amp; Schemes', false);
        $response->assertSee('Financial &amp; Sales Reports', false);

        // Hidden Sections
        $response->assertDontSee('Contracts &amp; Legal Vault', false);
        $response->assertDontSee('Multi-Branch Setup');
        $response->assertDontSee('Company &amp; Letterhead', false);
        $response->assertDontSee('Activity Audit Trail');

        // Backend Route Protection
        $this->actingAs($user)->get(route('settings.company.edit'))->assertStatus(403);
        $this->actingAs($user)->get(route('branches.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('settings.roles.index'))->assertStatus(403);
    }

    /** @test */
    public function test_sales_executive_has_personal_pipeline_and_locked_management(): void
    {
        $user = $this->createUserWithRole('sales_executive');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        // Dashboard & Visible Sidebar
        $response->assertSee('Sales &amp; Deals Dashboard', false);
        $response->assertSee('Add New Lead');
        $response->assertSee('Sales &amp; CRM', false);
        $response->assertSee('Leads Pipeline');
        $response->assertSee('Follow-Ups');
        $response->assertSee('Site Inspections');
        $response->assertSee('Estates &amp; Inventory', false);
        $response->assertSee('Properties &amp; Schemes', false);
        $response->assertSee('My Daily KPI Logs');
        $response->assertSee('My Salary &amp; Payslips', false);

        // Hidden Modules
        $response->assertDontSee('Leave Management');
        $response->assertDontSee('Team &amp; Staff Access', false);
        $response->assertDontSee('Naira Payroll &amp; Salaries', false);
        $response->assertDontSee('Marketing Campaigns');
        $response->assertDontSee('Enterprise Config');

        // Backend Route Protection
        $this->actingAs($user)->get(route('payroll.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('campaigns.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('settings.company.edit'))->assertStatus(403);
        $this->actingAs($user)->get(route('settings.roles.index'))->assertStatus(403);
    }

    /** @test */
    public function test_accountant_has_financial_and_payroll_access_without_sales_leads(): void
    {
        $user = $this->createUserWithRole('accountant');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        // Dashboard Header & Metric Indicators
        $response->assertSee('Financial &amp; Accounting Dashboard', false);
        $response->assertSee('Monthly Inflows');
        $response->assertSee('Pending POPs');
        $response->assertSee('Pending OPEX Claims');
        $response->assertSee('Log New Expense');

        // Visible Sidebar
        $response->assertSee('Finance &amp; Accounts', false);
        $response->assertSee('Expenses &amp; OPEX', false);
        $response->assertSee('Financial &amp; Sales Reports', false);
        $response->assertSee('Naira Payroll &amp; Salaries', false);

        // Hidden Modules
        $response->assertDontSee('Sales &amp; CRM', false);
        $response->assertDontSee('Leads Pipeline');
        $response->assertDontSee('Marketing Campaigns');
        $response->assertDontSee('Enterprise Config');

        // Backend Route Authorization & Protection
        $this->actingAs($user)->get(route('accounting.expenses.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('payroll.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('campaigns.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('settings.company.edit'))->assertStatus(403);
    }

    /** @test */
    public function test_hr_has_workforce_workspace_without_marketing_or_legal_vault(): void
    {
        $user = $this->createUserWithRole('hr');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        // Dashboard Header & Metric Indicators
        $response->assertSee('Human Resources Dashboard');
        $response->assertSee('Active Staff');
        $response->assertSee('Pending Leaves');
        $response->assertSee('Today\'s Submissions', false);
        $response->assertSee('Add Staff Member');

        // Visible Sidebar
        $response->assertSee('HR &amp; Personnel', false);
        $response->assertSee('Team &amp; Staff Access', false);
        $response->assertSee('Leave Management');
        $response->assertSee('Submissions Review');
        $response->assertSee('Department Targets');
        $response->assertSee('Naira Payroll &amp; Salaries', false);
        $response->assertSee('Sales Leaderboard');

        // Hidden Modules
        $response->assertDontSee('Sales &amp; CRM', false);
        $response->assertDontSee('Contracts &amp; Legal Vault', false);
        $response->assertDontSee('Marketing &amp; Growth', false);
        $response->assertDontSee('Enterprise Config');

        // Backend Route Authorization & Protection
        $this->actingAs($user)->get(route('settings.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('hr.leave.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('payroll.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('campaigns.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('generated-documents.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('settings.company.edit'))->assertStatus(403);
    }

    /** @test */
    public function test_marketing_officer_has_campaigns_and_drips_without_finance_or_hr(): void
    {
        $user = $this->createUserWithRole('marketing');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        // Dashboard Header
        $response->assertSee('Media &amp; Marketing Operations', false);

        // Visible Sidebar
        $response->assertSee('Marketing &amp; Growth', false);
        $response->assertSee('Marketing Campaigns');
        $response->assertSee('Drip Sequences');
        $response->assertSee('Estates &amp; Inventory', false);
        $response->assertSee('Properties &amp; Schemes', false);

        // Hidden Modules
        $response->assertDontSee('Leave Management');
        $response->assertDontSee('Team &amp; Staff Access', false);
        $response->assertDontSee('Naira Payroll &amp; Salaries', false);
        $response->assertDontSee('Sales &amp; CRM', false);
        $response->assertDontSee('Enterprise Config');

        // Backend Route Authorization & Protection
        $this->actingAs($user)->get(route('campaigns.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('drip-sequences.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('payroll.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('settings.roles.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('settings.company.edit'))->assertStatus(403);
    }

    /** @test */
    public function test_legal_personnel_has_contract_vault_access(): void
    {
        $user = $this->createUserWithRole('legal_personnel');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        // Visible Sidebar
        $response->assertSee('Contracts &amp; Legal Vault', false);
        $response->assertSee('Generated Title Deeds');
        $response->assertSee('Estates &amp; Inventory', false);

        // Hidden Modules
        $response->assertDontSee('Marketing &amp; Growth', false);
        $response->assertDontSee('Leave Management');
        $response->assertDontSee('Naira Payroll &amp; Salaries', false);
        $response->assertDontSee('Enterprise Config');

        // Backend Route Authorization & Protection
        $this->actingAs($user)->get(route('generated-documents.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('payroll.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('campaigns.index'))->assertStatus(403);
    }

    /** @test */
    public function test_customer_role_is_isolated_to_buyer_portal_only(): void
    {
        $customer = $this->createUserWithRole('customer');

        $response = $this->actingAs($customer)->get(route('buyer.dashboard'));
        $response->assertStatus(200);

        // Buyer Portal Content
        $response->assertSee('Buyer Portal');
        $response->assertSee('Total Amount Paid');
        $response->assertSee('Outstanding Balance');

        // Verify that Internal CRM Sidebar Navigation is Completely Hidden
        $response->assertDontSee('Executive Dashboard');
        $response->assertDontSee('Sales &amp; CRM', false);
        $response->assertDontSee('Leads Pipeline');
        $response->assertDontSee('HR &amp; Personnel', false);
        $response->assertDontSee('Finance &amp; Accounts', false);
        $response->assertDontSee('Enterprise Config');
    }
}
