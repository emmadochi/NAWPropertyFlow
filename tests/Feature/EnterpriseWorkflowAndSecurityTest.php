<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Sale;
use App\Models\PaymentPlan;
use App\Models\PaymentMilestone;
use App\Models\Commission;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EnterpriseWorkflowAndSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Run all tenant migrations into in-memory sqlite
        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
            '--realpath' => false,
        ]);
    }
    public function test_sales_executive_can_only_create_and_view_their_own_leads()
    {
        // 1. Setup two sales executives
        $exec1 = User::forceCreate([
            'name' => 'Agent One',
            'email' => 'exec1_' . Str::random(5) . '@ricafltd.com',
            'password' => Hash::make('secret'),
            'role' => 'sales_executive',
            'status' => 'active'
        ]);

        $exec2 = User::forceCreate([
            'name' => 'Agent Two',
            'email' => 'exec2_' . Str::random(5) . '@ricafltd.com',
            'password' => Hash::make('secret'),
            'role' => 'sales_executive',
            'status' => 'active'
        ]);

        // 2. Exec1 creates a lead
        $lead1 = Lead::forceCreate([
            'full_name' => 'Prospect A',
            'phone_number' => '08011112222',
            'email' => 'prospect_a@example.com',
            'lead_source' => 'WhatsApp',
            'assigned_to' => $exec1->id,
            'status' => 'New'
        ]);

        // Exec2 creates a lead
        $lead2 = Lead::forceCreate([
            'full_name' => 'Prospect B',
            'phone_number' => '08033334444',
            'email' => 'prospect_b@example.com',
            'lead_source' => 'Direct',
            'assigned_to' => $exec2->id,
            'status' => 'New'
        ]);

        // 3. Authenticate as Exec1 and attempt to view Exec2's lead
        $response = $this->actingAs($exec1)->get(route('leads.show', $lead2->id));
        $response->assertStatus(403); // Security: Unauthorized access blocked!

        // 4. Exec1 views their own lead
        $responseSelf = $this->actingAs($exec1)->get(route('leads.show', $lead1->id));
        $responseSelf->assertStatus(200);
    }

    /**
     * TEST 2: Admin-Only Payment Verification & Automated Marketer Commission Approval.
     */
    public function test_only_admin_can_verify_payment_and_approve_commissions()
    {
        $admin = User::forceCreate([
            'name' => 'Super Admin',
            'email' => 'admin_' . Str::random(5) . '@ricafltd.com',
            'password' => Hash::make('secret'),
            'role' => 'company_admin',
            'status' => 'active'
        ]);

        $exec = User::forceCreate([
            'name' => 'Sales Exec',
            'email' => 'exec_' . Str::random(5) . '@ricafltd.com',
            'password' => Hash::make('secret'),
            'role' => 'sales_executive',
            'status' => 'active'
        ]);

        $lead = Lead::forceCreate([
            'full_name' => 'Investor Chinedu',
            'phone_number' => '08099998888',
            'email' => 'investor_' . Str::random(5) . '@gmail.com',
            'assigned_to' => $exec->id,
            'status' => 'Payment Processing'
        ]);

        $property = Property::forceCreate([
            'name' => 'Royal Palm Estate',
            'estate_name' => 'Royal Palm',
            'location' => 'Epe, Lagos',
            'property_type' => 'Land',
            'price' => 10000000.00,
            'available_units' => 5
        ]);

        $sale = Sale::forceCreate([
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'sales_officer_id' => $exec->id,
            'units_purchased' => 1,
            'deal_value' => 10000000.00,
            'status' => 'Closed Won',
            'deal_closed_at' => now()
        ]);

        $paymentPlan = PaymentPlan::forceCreate([
            'sale_id' => $sale->id,
            'total_amount' => 10000000.00,
            'amount_paid' => 3000000.00,
            'balance' => 7000000.00,
            'plan_type' => 'installment',
            'status' => 'active'
        ]);

        $milestone = PaymentMilestone::forceCreate([
            'payment_plan_id' => $paymentPlan->id,
            'label' => 'Initial Deposit',
            'amount_due' => 3000000.00,
            'amount_paid' => 3000000.00,
            'due_date' => now(),
            'status' => 'paid',
            'verified_at' => null,
            'verified_by' => null
        ]);

        $commission = Commission::forceCreate([
            'sale_id' => $sale->id,
            'user_id' => $exec->id,
            'commission_type' => 'sales_officer',
            'rate_percent' => 5.00,
            'calculated_amount' => 150000.00,
            'status' => 'pending'
        ]);

        // Security check: Sales Exec cannot verify payment
        $unauthVerify = $this->actingAs($exec)->post(route('payments.verify-payment', $milestone->id));
        $unauthVerify->assertStatus(403);

        // Admin verifies payment
        $authVerify = $this->actingAs($admin)->post(route('payments.verify-payment', $milestone->id));
        $authVerify->assertRedirect();

        $milestone->refresh();
        $this->assertNotNull($milestone->verified_at);
        $this->assertEquals($admin->id, $milestone->verified_by);

        // Verify commission auto-approved
        $commission->refresh();
        $this->assertEquals('approved', $commission->status);
    }

    /**
     * TEST 3: Client Portal 1-Tap Magic Token & 0-Friction Authentication.
     */
    public function test_client_portal_magic_token_authentication_and_isolation()
    {
        $lead = Lead::forceCreate([
            'full_name' => 'Dr. Emeka Okafor',
            'phone_number' => '08022223333',
            'email' => 'emeka_' . Str::random(5) . '@yahoo.com',
            'status' => 'Closed Won'
        ]);

        // 1. Generate 64-character token
        $token = $lead->getOrCreatePortalToken();
        $this->assertEquals(64, strlen($token));

        // 2. Client clicks 1-tap link (Unauthenticated session)
        $response = $this->withSession([])->get(route('portal.magic-login', ['token' => $token]));
        $response->assertRedirect(route('buyer.dashboard'));

        // 3. User is now authenticated as customer role
        $this->assertAuthenticated();
        $this->assertEquals('customer', auth()->user()->role);

        // 4. Test tampering with invalid token
        $fakeResponse = $this->get(route('portal.magic-login', ['token' => 'invalid_fake_token_12345']));
        $fakeResponse->assertStatus(404);
    }
}
