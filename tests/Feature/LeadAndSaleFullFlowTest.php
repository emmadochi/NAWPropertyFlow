<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Sale;
use App\Models\PaymentPlanDuration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

class LeadAndSaleFullFlowTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
            '--realpath' => false,
        ]);

        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);

        \App\Models\CompanySetting::updateOrCreate(
            ['id' => 1],
            ['company_name' => 'RICAF Nigeria Limited', 'package_tier' => 'enterprise']
        );

        Mail::fake();
    }

    public function test_lead_creation_with_1m_to_9m_budget_and_unassigned_values()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'company_admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('leads.store'), [
            'full_name' => 'John Doe Tester',
            'phone_number' => '+2348012345678',
            'whatsapp_number' => '+2348012345678',
            'email' => 'johndoe@test.com',
            'address' => '123 Test Street, Abuja',
            'budget_range' => '₦1M - ₦9M',
            'property_interest_id' => '',
            'preferred_location' => 'Abuja',
            'lead_source' => 'Website',
            'assigned_to' => '',
            'status' => 'New',
            'branch_id' => '',
            'notes' => 'Testing budget range and unassigned fields.',
        ]);

        $response->assertRedirect(route('leads.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leads', [
            'full_name' => 'John Doe Tester',
            'budget_range' => '₦1M - ₦9M',
            'email' => 'johndoe@test.com',
            'status' => 'New',
            'assigned_to' => null,
            'branch_id' => null,
        ]);
    }

    public function test_lead_pipeline_and_branch_scope_visibility()
    {
        $admin = User::create([
            'name' => 'Company Admin',
            'email' => 'cadmin@test.com',
            'password' => bcrypt('password'),
            'role' => 'company_admin',
            'status' => 'active',
        ]);

        $lead = Lead::create([
            'full_name' => 'Corporate Unassigned Lead',
            'phone_number' => '+2348000000001',
            'budget_range' => '₦1M - ₦9M',
            'lead_source' => 'Website',
            'status' => 'New',
            'branch_id' => null,
        ]);

        // Even with a branch filter active in session, corporate leads should be visible
        session(['selected_branch_id' => 99]);

        $response = $this->actingAs($admin)->get(route('leads.index'));
        $response->assertStatus(200);
        $response->assertSee('Corporate Unassigned Lead');
        $response->assertSee('₦1M - ₦9M');
    }

    public function test_record_sale_and_view_lead_timeline_and_payments()
    {
        $admin = User::create([
            'name' => 'Sales Admin',
            'email' => 'salesadmin@test.com',
            'password' => bcrypt('password'),
            'role' => 'company_admin',
            'status' => 'active',
        ]);

        $property = Property::create([
            'name' => 'Sunset Ridge Estate',
            'location' => 'Lekki, Lagos',
            'price' => 50000000,
            'total_units' => 10,
            'available_units' => 10,
            'property_type' => 'Residential',
            'status' => 'available',
        ]);

        $lead = Lead::create([
            'full_name' => 'Buyer Client',
            'phone_number' => '+2348099999999',
            'email' => 'buyer@test.com',
            'budget_range' => '₦30M - ₦60M',
            'lead_source' => 'Referral',
            'status' => 'Contacted',
            'assigned_to' => $admin->id,
        ]);

        $duration = PaymentPlanDuration::create([
            'name' => '6 Months Standard',
            'duration_months' => 6,
            'interest_rate_pct' => 5,
            'initial_deposit_pct' => 30,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Record a sale
        $response = $this->actingAs($admin)->post(route('sales.store'), [
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'property_unit_id' => '',
            'sales_officer_id' => $admin->id,
            'deal_value' => 52500000,
            'base_deal_value' => 50000000,
            'interest_rate_pct' => 5,
            'interest_amount' => 2500000,
            'payment_plan_duration_id' => $duration->id,
            'units_purchased' => 1,
            'plan_type' => 'installment',
            'initial_deposit' => 15750000,
            'installment_months' => 6,
            'bank_reference' => 'TXN-TEST-12345',
            'payment_method' => 'Bank Transfer',
        ]);

        $response->assertSessionHas('success');

        // Check lead status updated to Closed Won
        $lead->refresh();
        $this->assertEquals('Closed Won', $lead->status);

        // Check sale created in database
        $this->assertDatabaseHas('sales', [
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'deal_value' => 52500000,
            'status' => 'Closed Won',
        ]);

        // Check payment plan and milestones created
        $this->assertDatabaseHas('payment_plans', [
            'sale_id' => Sale::where('lead_id', $lead->id)->first()->id,
            'total_amount' => 52500000,
            'status' => 'active',
        ]);

        // View lead profile page and verify no 500 errors and payments tab renders
        $showResponse = $this->actingAs($admin)->get(route('leads.show', ['lead' => $lead->id, 'tab' => 'payments']));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Sunset Ridge Estate');
        $showResponse->assertSee('Payments (1)');
        $showResponse->assertSee('52,500,000');
    }

    public function test_record_outright_sale_on_unassigned_general_inquiry_lead()
    {
        $admin = User::create([
            'name' => 'General Admin',
            'email' => 'genadmin@test.com',
            'password' => bcrypt('password'),
            'role' => 'company_admin',
            'status' => 'active',
        ]);

        $property = Property::create([
            'name' => 'Grand Palm Villa',
            'location' => 'Kuje, Abuja',
            'price' => 25000000,
            'total_units' => 5,
            'available_units' => 5,
            'property_type' => 'Residential',
            'status' => 'available',
        ]);

        $lead = Lead::create([
            'full_name' => 'EMMAN test',
            'phone_number' => '+2349042988676',
            'email' => 'emmadocgi11@gmail.com',
            'budget_range' => '₦1M - ₦9M',
            'lead_source' => 'Website',
            'status' => 'New',
            'assigned_to' => null,
            'branch_id' => null,
        ]);

        // Submit sale with 100% Outright
        $response = $this->actingAs($admin)->post(route('sales.store'), [
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'property_unit_id' => '',
            'sales_officer_id' => '',
            'deal_value' => 25000000,
            'base_deal_value' => 25000000,
            'units_purchased' => 1,
            'plan_type' => 'outright',
            'payment_method' => 'Bank Transfer',
        ]);

        $response->assertRedirect(route('leads.show', ['lead' => $lead->id, 'tab' => 'payments']));
        $response->assertSessionHas('success');

        $lead->refresh();
        $this->assertEquals('Closed Won', $lead->status);

        $showResponse = $this->actingAs($admin)->get(route('leads.show', ['lead' => $lead->id, 'tab' => 'payments']));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Grand Palm Villa');
        $showResponse->assertSee('Payments (1)');
        $showResponse->assertSee('25,000,000');
    }
}
