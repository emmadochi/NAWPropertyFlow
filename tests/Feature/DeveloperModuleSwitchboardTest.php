<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class DeveloperModuleSwitchboardTest extends TestCase
{
    protected User $admin;
    protected CompanySetting $companySetting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
            '--realpath' => false,
        ]);

        $this->seed(PermissionSeeder::class);
        $this->seed(UserSeeder::class);

        $this->companySetting = CompanySetting::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'RICAF Nigeria Limited',
                'package_tier' => 'enterprise',
                'enabled_modules' => null,
            ]
        );

        $this->admin = User::where('role', 'super_admin')->orWhere('role', 'company_admin')->first();
    }

    public function test_developer_can_view_switchboard(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('developer.modules.index'));
        $response->assertOk();
        $response->assertSee('Platform Feature Switchboard');
        $response->assertSee('Construction Inventory & Procurement Suite');
        $response->assertSee('Enterprise Double-Entry Accounting Suite');
    }

    public function test_developer_can_toggle_off_inventory_and_accounting_modules(): void
    {
        $this->actingAs($this->admin);

        // 1. Initial State: Enterprise tier has inventory & accounting enabled
        $this->assertTrue($this->companySetting->hasFeature('inventory'));
        $this->assertTrue($this->companySetting->hasFeature('accounting'));

        // 2. Developer toggles OFF inventory & accounting, keeping CRM & Payment Plans & HR
        $response = $this->post(route('developer.modules.update'), [
            'modules' => ['crm', 'payment_plans', 'hr', 'leaderboard']
        ]);

        $response->assertRedirect();
        $this->companySetting->refresh();

        // 3. Verify feature check flags
        $this->assertFalse($this->companySetting->hasFeature('inventory'));
        $this->assertFalse($this->companySetting->hasFeature('accounting'));
        $this->assertTrue($this->companySetting->hasFeature('crm'));
        $this->assertTrue($this->companySetting->hasFeature('payment_plans'));
        $this->assertTrue($this->companySetting->hasFeature('hr'));

        // 4. Verify Sidebar rendering hides Construction Inventory & Accounting
        $viewResponse = $this->get(route('dashboard'));
        $viewResponse->assertOk();
        $viewResponse->assertDontSee('Inventory Cockpit');
        $viewResponse->assertDontSee('Goods Received (GRN)');
        $viewResponse->assertDontSee('Bank Treasury & Recon');
        $viewResponse->assertDontSee('Tax & FIRS Hub');
        
        // CRM and Payment plans still visible
        $viewResponse->assertSee('Payment Plans & Interest');
    }

    public function test_developer_can_reset_modules_to_tier_defaults(): void
    {
        $this->actingAs($this->admin);

        $this->companySetting->enabled_modules = ['crm'];
        $this->companySetting->save();

        $this->assertFalse($this->companySetting->hasFeature('inventory'));

        // Reset
        $resetResponse = $this->post(route('developer.modules.reset'));
        $resetResponse->assertRedirect();

        $this->companySetting->refresh();
        $this->assertNull($this->companySetting->enabled_modules);
        $this->assertTrue($this->companySetting->hasFeature('inventory'));
    }
}
