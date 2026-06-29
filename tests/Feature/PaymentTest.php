<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Sale;
use App\Models\PaymentPlan;
use App\Models\PaymentMilestone;
use App\Models\Commission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentReminderMail;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $managerUser;
    protected $salesExecutive;

    protected function setUp(): void
    {
        parent::setUp();

        // Create testing users
        $this->adminUser = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active'
        ]);

        $this->managerUser = User::factory()->create([
            'role' => 'sales_manager',
            'status' => 'active'
        ]);

        $this->salesExecutive = User::factory()->create([
            'role' => 'sales_executive',
            'status' => 'active'
        ]);
    }

    /**
     * Test that recording a sale automatically creates a payment plan and commissions.
     */
    public function test_recording_sale_auto_creates_payment_plan_and_commissions(): void
    {
        $this->actingAs($this->salesExecutive);

        $lead = Lead::factory()->create([
            'assigned_to' => $this->salesExecutive->id
        ]);
        $property = Property::factory()->create([
            'available_units' => 5
        ]);

        // Record Sale
        $response = $this->post('/sales', [
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'deal_value' => 10000000.00,
            'units_purchased' => 1
        ]);

        $response->assertRedirect();

        // Assert sale exists
        $sale = Sale::first();
        $this->assertNotNull($sale);

        // Assert payment plan exists with default milestone
        $this->assertDatabaseHas('payment_plans', [
            'sale_id' => $sale->id,
            'total_amount' => 10000000.00,
            'balance' => 10000000.00,
            'plan_type' => 'outright'
        ]);

        $paymentPlan = PaymentPlan::first();
        $this->assertDatabaseHas('payment_milestones', [
            'payment_plan_id' => $paymentPlan->id,
            'amount_due' => 10000000.00,
            'status' => 'pending'
        ]);

        // Assert commissions calculated
        $this->assertDatabaseHas('commissions', [
            'sale_id' => $sale->id,
            'user_id' => $this->salesExecutive->id,
            'commission_type' => 'sales_officer',
            'calculated_amount' => 500000.00 // 5% of 10M
        ]);
    }

    /**
     * Test that a payment plan can be stored manually.
     */
    public function test_user_can_create_payment_plan(): void
    {
        $this->actingAs($this->adminUser);

        $lead = Lead::factory()->create([
            'assigned_to' => $this->salesExecutive->id
        ]);
        $property = Property::factory()->create();
        
        $sale = Sale::create([
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'sales_officer_id' => $this->salesExecutive->id,
            'deal_value' => 20000000.00,
            'units_purchased' => 1,
            'status' => 'Closed Won',
            'deal_closed_at' => now(),
        ]);

        $response = $this->post(route('payments.store-plan', $sale->id), [
            'plan_type' => 'installment',
            'number_of_installments' => 2,
            'notes' => 'Custom installments',
            'milestones' => [
                [
                    'label' => 'Tranche 1',
                    'amount_due' => 12000000.00,
                    'due_date' => now()->toDateString(),
                ],
                [
                    'label' => 'Tranche 2',
                    'amount_due' => 8000000.00,
                    'due_date' => now()->addDays(30)->toDateString(),
                ]
            ]
        ]);

        $response->assertRedirect(route('leads.show', $lead->id));

        $this->assertDatabaseHas('payment_plans', [
            'sale_id' => $sale->id,
            'total_amount' => 20000000.00,
            'plan_type' => 'installment'
        ]);

        $this->assertDatabaseHas('payment_milestones', [
            'label' => 'Tranche 1',
            'amount_due' => 12000000.00
        ]);
        $this->assertDatabaseHas('payment_milestones', [
            'label' => 'Tranche 2',
            'amount_due' => 8000000.00
        ]);
    }

    /**
     * Test recording milestone payment.
     */
    public function test_user_can_record_milestone_payment_manually(): void
    {
        $this->actingAs($this->salesExecutive);

        $lead = Lead::factory()->create([
            'assigned_to' => $this->salesExecutive->id
        ]);
        $property = Property::factory()->create();
        
        $sale = Sale::create([
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'sales_officer_id' => $this->salesExecutive->id,
            'deal_value' => 5000000.00,
            'units_purchased' => 1,
            'status' => 'Closed Won',
            'deal_closed_at' => now(),
        ]);

        // Default payment plan is created on Sale create inside boot/service, but since we created using model:
        $plan = PaymentPlan::create([
            'sale_id' => $sale->id,
            'plan_type' => 'outright',
            'total_amount' => 5000000.00,
            'amount_paid' => 0,
            'balance' => 5000000.00,
            'status' => 'active'
        ]);

        $milestone = PaymentMilestone::create([
            'payment_plan_id' => $plan->id,
            'label' => 'Full payment',
            'amount_due' => 5000000.00,
            'due_date' => now()->addDays(7),
            'amount_paid' => 0,
            'status' => 'pending'
        ]);

        $response = $this->post(route('payments.record-payment', $milestone->id), [
            'amount_paid' => 5000000.00,
            'bank_reference' => 'TEST-BANK-1122',
            'notes' => 'Paid in full'
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('payment_milestones', [
            'id' => $milestone->id,
            'amount_paid' => 5000000.00,
            'status' => 'paid',
            'bank_reference' => 'TEST-BANK-1122'
        ]);

        $this->assertDatabaseHas('payment_plans', [
            'id' => $plan->id,
            'amount_paid' => 5000000.00,
            'balance' => 0.00,
            'status' => 'completed'
        ]);
    }

    /**
     * Test command checks due dates and triggers mails.
     */
    public function test_scheduler_checks_due_dates_correctly(): void
    {
        Mail::fake();

        $lead = Lead::factory()->create([
            'email' => 'client-test@example.com'
        ]);
        $property = Property::factory()->create();
        
        $sale = Sale::create([
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'sales_officer_id' => $this->salesExecutive->id,
            'deal_value' => 5000000.00,
            'units_purchased' => 1,
            'status' => 'Closed Won',
            'deal_closed_at' => now(),
        ]);

        $plan = PaymentPlan::create([
            'sale_id' => $sale->id,
            'plan_type' => 'outright',
            'total_amount' => 5000000.00,
            'amount_paid' => 0,
            'balance' => 5000000.00,
            'status' => 'active'
        ]);

        // Milestone due in 7 days
        $milestone = PaymentMilestone::create([
            'payment_plan_id' => $plan->id,
            'label' => '7-day tranche',
            'amount_due' => 5000000.00,
            'due_date' => Carbon::today()->addDays(7)->toDateString(),
            'amount_paid' => 0,
            'status' => 'pending'
        ]);

        // Run artisan command
        Artisan::call('payments:check-due');

        Mail::assertSent(PaymentReminderMail::class, function ($mail) use ($milestone) {
            return $mail->milestone->id === $milestone->id;
        });
    }
}
