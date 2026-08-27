<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CompanySetting;
use App\Models\Lead;
use App\Models\PaymentPlan;
use App\Models\PaymentPlanDuration;
use App\Models\Property;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentPlanDurationAndLeadAssignmentTest extends TestCase
{
    protected User $admin;
    protected User $accountant;
    protected User $salesExecutive;
    protected Lead $lead;
    protected Property $property;
    protected Sale $sale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
            '--realpath' => false,
        ]);

        $this->seed(PermissionSeeder::class);
        $this->seed(UserSeeder::class);

        CompanySetting::updateOrCreate(
            ['id' => 1],
            ['company_name' => 'RICAF Nigeria Limited', 'package_tier' => 'enterprise']
        );

        $this->admin = User::where('role', 'company_admin')->orWhere('role', 'super_admin')->first();
        $this->accountant = User::where('role', 'accountant')->first() ?? User::first();
        $this->salesExecutive = User::where('role', 'sales_executive')->first() ?? User::factory()->create(['role' => 'sales_executive']);

        $this->property = Property::create([
            'name' => 'Hutu Prestige Smart Villa',
            'property_type' => 'Terrace',
            'price' => 50000000.00,
            'status' => 'available',
            'location' => 'Abuja',
        ]);

        $this->lead = Lead::create([
            'full_name' => 'Obiagwu Precious Ebuka',
            'phone_number' => '+2348012345678',
            'email' => 'precious@client.com',
            'budget_range' => '₦40M - ₦60M',
            'lead_source' => 'WhatsApp',
            'status' => 'New',
        ]);

        $this->sale = Sale::create([
            'lead_id' => $this->lead->id,
            'property_id' => $this->property->id,
            'sales_officer_id' => $this->admin->id,
            'deal_value' => 50000000.00,
            'status' => 'closed',
            'deal_closed_at' => now(),
        ]);
    }

    public function test_any_staff_member_can_be_assigned_to_a_lead(): void
    {
        $this->actingAs($this->admin);

        // Assign to Accountant
        $response = $this->post(route('leads.assign', $this->lead->id), [
            'assigned_to' => $this->accountant->id
        ]);

        $response->assertRedirect();
        $this->lead->refresh();
        $this->assertEquals($this->accountant->id, $this->lead->assigned_to);

        // Verify index view renders all staff in the dropdown
        $viewResponse = $this->get(route('leads.index'));
        $viewResponse->assertOk();
        $viewResponse->assertSee($this->accountant->name);
        $viewResponse->assertSee($this->admin->name);
        $viewResponse->assertSee($this->salesExecutive->name);
    }

    public function test_admin_can_crud_payment_plan_durations_and_interest_rates(): void
    {
        $this->actingAs($this->admin);

        // 1. Create 9-month plan with 7.5% interest
        $createResponse = $this->post(route('settings.payment-plans.store'), [
            'name' => '9 Months Milestone Plan',
            'duration_months' => 9,
            'interest_rate_pct' => 7.50,
            'initial_deposit_pct' => 25.00,
            'number_of_installments' => 9,
            'description' => '25% deposit followed by 8 monthly tranches at 7.5% interest.',
            'is_active' => '1',
        ]);

        $createResponse->assertRedirect(route('settings.payment-plans.index'));
        $plan = PaymentPlanDuration::where('name', '9 Months Milestone Plan')->first();
        $this->assertNotNull($plan);
        $this->assertEquals(9, $plan->duration_months);
        $this->assertEquals(7.50, $plan->interest_rate_pct);

        // 2. Update to 8.00% interest
        $updateResponse = $this->put(route('settings.payment-plans.update', $plan->id), [
            'name' => '9 Months Milestone Plan (Updated)',
            'duration_months' => 9,
            'interest_rate_pct' => 8.00,
            'initial_deposit_pct' => 30.00,
            'number_of_installments' => 9,
            'description' => 'Updated terms.',
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect(route('settings.payment-plans.index'));
        $plan->refresh();
        $this->assertEquals(8.00, $plan->interest_rate_pct);
        $this->assertEquals(30.00, $plan->initial_deposit_pct);

        // 3. Toggle Status
        $this->post(route('settings.payment-plans.toggle', $plan->id));
        $plan->refresh();
        $this->assertFalse($plan->is_active);

        // 4. Delete
        $deleteResponse = $this->delete(route('settings.payment-plans.destroy', $plan->id));
        $deleteResponse->assertRedirect(route('settings.payment-plans.index'));
        $this->assertNull(PaymentPlanDuration::find($plan->id));
    }

    public function test_building_payment_plan_with_interest_calculates_total_and_creates_milestones(): void
    {
        $this->actingAs($this->admin);

        $duration = PaymentPlanDuration::create([
            'name' => '6 Months Structured',
            'duration_months' => 6,
            'interest_rate_pct' => 5.00,
            'initial_deposit_pct' => 30.00,
            'number_of_installments' => 6,
            'is_active' => true,
        ]);

        // Base: 50M + 5% Interest (2.5M) = 52.5M
        $base = 50000000.00;
        $interest = 2500000.00;
        $total = 52500000.00;
        $deposit = 15750000.00; // 30% of 52.5M
        $tranche = 7350000.00; // (52.5M - 15.75M) / 5 = 36.75M / 5 = 7.35M

        $response = $this->post(route('payments.store-plan', $this->sale->id), [
            'payment_plan_duration_id' => $duration->id,
            'duration_months' => 6,
            'plan_type' => 'installment',
            'base_deal_value' => $base,
            'interest_rate_pct' => 5.00,
            'interest_amount' => $interest,
            'total_amount' => $total,
            'number_of_installments' => 6,
            'milestones' => [
                ['label' => 'Initial 30% Deposit', 'amount_due' => $deposit, 'due_date' => now()->toDateString()],
                ['label' => 'Tranche #1', 'amount_due' => $tranche, 'due_date' => now()->addDays(30)->toDateString()],
                ['label' => 'Tranche #2', 'amount_due' => $tranche, 'due_date' => now()->addDays(60)->toDateString()],
                ['label' => 'Tranche #3', 'amount_due' => $tranche, 'due_date' => now()->addDays(90)->toDateString()],
                ['label' => 'Tranche #4', 'amount_due' => $tranche, 'due_date' => now()->addDays(120)->toDateString()],
                ['label' => 'Tranche #5', 'amount_due' => $tranche, 'due_date' => now()->addDays(150)->toDateString()],
            ]
        ]);

        $response->assertRedirect(route('leads.show', $this->lead->id));
        $this->sale->refresh();

        $plan = $this->sale->paymentPlan;
        $this->assertNotNull($plan);
        $this->assertEquals($total, (float)$plan->total_amount);
        $this->assertEquals($base, (float)$plan->base_deal_value);
        $this->assertEquals(5.00, (float)$plan->interest_rate_pct);
        $this->assertEquals($interest, (float)$plan->interest_amount);
        $this->assertEquals(6, $plan->milestones()->count());
    }
}
