<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeLeadMail;
use App\Mail\InspectionScheduledMail;
use App\Mail\PaymentInvoiceMail;
use App\Mail\PoliteClosingMail;

class CrmTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Test user login authentication.
     */
    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'sales@propertyflow.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'role' => 'sales_executive'
        ]);

        $response = $this->post('/login', [
            'email' => 'sales@propertyflow.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test web lead creation.
     */
    public function test_authenticated_user_can_create_lead(): void
    {
        $user = User::factory()->create([
            'role' => 'sales_executive',
            'status' => 'active'
        ]);

        $this->actingAs($user);

        $property = Property::factory()->create();

        $response = $this->post('/leads', [
            'full_name' => 'Chidi Benz',
            'phone_number' => '+2348039998888',
            'whatsapp_number' => '+2348039998888',
            'email' => 'chidi@example.com',
            'budget_range' => '₦30M - ₦60M',
            'property_interest_id' => $property->id,
            'preferred_location' => 'Lekki',
            'lead_source' => 'Website',
            'status' => 'New',
            'notes' => 'Looking for quick purchase.'
        ]);

        $response->assertRedirect('/leads');
        
        $this->assertDatabaseHas('leads', [
            'full_name' => 'Chidi Benz',
            'email' => 'chidi@example.com',
            'status' => 'New'
        ]);
    }

    /**
     * Test REST API login and token response.
     */
    public function test_api_user_can_login_and_receive_token(): void
    {
        $user = User::factory()->create([
            'email' => 'api_exec@propertyflow.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'role' => 'sales_executive'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'api_exec@propertyflow.com',
            'password' => 'password123',
            'device_name' => 'flutter_app'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);
    }

    /**
     * Test REST API leads list retrieval.
     */
    public function test_authenticated_api_user_can_list_leads(): void
    {
        $user = User::factory()->create([
            'role' => 'sales_executive',
            'status' => 'active'
        ]);

        $lead = Lead::factory()->create([
            'assigned_to' => $user->id
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/leads');

        $response->assertStatus(200);
    }

    /**
     * Test welcome email is sent to new lead.
     */
    public function test_welcome_email_is_sent_to_new_lead(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => 'sales_executive',
            'status' => 'active'
        ]);
        $this->actingAs($user);
        $property = Property::factory()->create();

        $response = $this->post('/leads', [
            'full_name' => 'Chidi Benz',
            'phone_number' => '+2348039998888',
            'whatsapp_number' => '+2348039998888',
            'email' => 'chidi@example.com',
            'budget_range' => '₦30M - ₦60M',
            'property_interest_id' => $property->id,
            'preferred_location' => 'Lekki',
            'lead_source' => 'Website',
            'status' => 'New',
            'notes' => 'Looking for quick purchase.'
        ]);

        $response->assertRedirect('/leads');

        Mail::assertSent(WelcomeLeadMail::class, function ($mail) {
            return $mail->lead->email === 'chidi@example.com';
        });
    }

    /**
     * Test inspection email is sent on booking.
     */
    public function test_inspection_email_is_sent_on_booking(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => 'sales_executive',
            'status' => 'active'
        ]);
        $this->actingAs($user);

        $lead = Lead::factory()->create([
            'email' => 'client@example.com',
            'assigned_to' => $user->id
        ]);
        $property = Property::factory()->create();

        $response = $this->post('/inspections', [
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'inspection_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'notes' => 'Site tour.'
        ]);

        $response->assertRedirect();
        
        Mail::assertSent(InspectionScheduledMail::class, function ($mail) use ($lead) {
            return $mail->inspection->lead_id === $lead->id;
        });
    }

    /**
     * Test invoice email is sent on sale record.
     */
    public function test_invoice_email_is_sent_on_sale_record(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => 'sales_executive',
            'status' => 'active'
        ]);
        $this->actingAs($user);

        $lead = Lead::factory()->create([
            'email' => 'buyer@example.com',
            'assigned_to' => $user->id
        ]);
        $property = Property::factory()->create([
            'available_units' => 10
        ]);

        $response = $this->post('/sales', [
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'deal_value' => 50000000.00,
            'units_purchased' => 1
        ]);

        $response->assertRedirect();

        Mail::assertSent(PaymentInvoiceMail::class, function ($mail) use ($lead) {
            return $mail->sale->lead->email === 'buyer@example.com';
        });
    }

    /**
     * Test closing email is sent when lead status is set to Closed Lost.
     */
    public function test_closing_email_is_sent_on_closed_lost(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => 'sales_executive',
            'status' => 'active'
        ]);
        $this->actingAs($user);

        $lead = Lead::factory()->create([
            'full_name' => 'John Doe',
            'phone_number' => '+2348011112222',
            'email' => 'johndoe@example.com',
            'budget_range' => '₦30M - ₦60M',
            'lead_source' => 'Website',
            'status' => 'New',
            'assigned_to' => $user->id
        ]);

        $response = $this->put("/leads/{$lead->id}", [
            'full_name' => 'John Doe',
            'phone_number' => '+2348011112222',
            'email' => 'johndoe@example.com',
            'budget_range' => '₦30M - ₦60M',
            'lead_source' => 'Website',
            'status' => 'Closed Lost',
            'assigned_to' => $user->id
        ]);

        $response->assertRedirect();

        Mail::assertSent(PoliteClosingMail::class, function ($mail) {
            return $mail->lead->email === 'johndoe@example.com';
        });
    }
}
