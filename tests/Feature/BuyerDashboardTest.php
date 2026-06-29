<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Sale;
use App\Models\PaymentPlan;
use App\Models\PaymentMilestone;
use App\Models\Document;
use App\Models\GeneratedDocument;
use App\Models\DocumentTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class BuyerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $salesExecutive;

    protected function setUp(): void
    {
        parent::setUp();

        $this->salesExecutive = User::factory()->create([
            'role' => 'sales_executive',
            'status' => 'active'
        ]);
    }

    /**
     * Test that recording a sale automatically creates a customer account.
     */
    public function test_recording_sale_auto_creates_customer_account(): void
    {
        $this->actingAs($this->salesExecutive);

        $lead = Lead::factory()->create([
            'email' => 'buyer@example.com',
            'full_name' => 'John Buyer',
            'phone_number' => '1234567890'
        ]);

        $property = Property::factory()->create([
            'available_units' => 5
        ]);

        // Record Sale
        $response = $this->post('/sales', [
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'deal_value' => 15000000.00,
            'units_purchased' => 1
        ]);

        $response->assertRedirect();

        // Assert customer user exists
        $this->assertDatabaseHas('users', [
            'email' => 'buyer@example.com',
            'name' => 'John Buyer',
            'role' => 'customer',
            'status' => 'active'
        ]);
    }

    /**
     * Test customer redirect on login.
     */
    public function test_customer_redirects_to_buyer_dashboard(): void
    {
        $customer = User::create([
            'name' => 'John Buyer',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'active'
        ]);

        $response = $this->post('/login', [
            'email' => 'buyer@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect(route('buyer.dashboard'));

        // Test accessing login while logged in redirects to dashboard
        $response2 = $this->actingAs($customer)->get('/login');
        $response2->assertRedirect(route('buyer.dashboard'));
    }

    /**
     * Test data isolation in the buyer dashboard.
     */
    public function test_buyer_dashboard_data_isolation(): void
    {
        // Buyer 1
        $customer1 = User::create([
            'name' => 'Buyer One',
            'email' => 'buyer1@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'active'
        ]);

        $lead1 = Lead::factory()->create(['email' => 'buyer1@example.com']);
        $property1 = Property::factory()->create();
        $sale1 = Sale::create([
            'lead_id' => $lead1->id,
            'property_id' => $property1->id,
            'sales_officer_id' => $this->salesExecutive->id,
            'deal_value' => 1000000.00,
            'status' => 'Closed Won',
            'deal_closed_at' => now(),
        ]);
        $plan1 = PaymentPlan::create([
            'sale_id' => $sale1->id,
            'plan_type' => 'outright',
            'total_amount' => 1000000.00,
            'amount_paid' => 200000.00,
            'balance' => 800000.00,
            'status' => 'active'
        ]);

        // Buyer 2
        $customer2 = User::create([
            'name' => 'Buyer Two',
            'email' => 'buyer2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'active'
        ]);

        $lead2 = Lead::factory()->create(['email' => 'buyer2@example.com']);
        $property2 = Property::factory()->create(['name' => 'Secret Mansion']);
        $sale2 = Sale::create([
            'lead_id' => $lead2->id,
            'property_id' => $property2->id,
            'sales_officer_id' => $this->salesExecutive->id,
            'deal_value' => 9000000.00,
            'status' => 'Closed Won',
            'deal_closed_at' => now(),
        ]);

        // Access dashboard as customer 1
        $response = $this->actingAs($customer1)->get(route('buyer.dashboard'));
        $response->assertStatus(200);

        // Verify customer 1 sees their property but not customer 2's property
        $response->assertSee($property1->name);
        $response->assertDontSee('Secret Mansion');
        $response->assertSee('₦200,000.00'); // Sum of totalInvested for Buyer 1
    }

    /**
     * Test downloads and security checks.
     */
    public function test_buyer_receipt_and_document_downloads(): void
    {
        Storage::fake('public');

        $customer1 = User::create([
            'name' => 'Buyer One',
            'email' => 'buyer1@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'active'
        ]);

        $lead1 = Lead::factory()->create(['email' => 'buyer1@example.com']);
        $property1 = Property::factory()->create();
        $sale1 = Sale::create([
            'lead_id' => $lead1->id,
            'property_id' => $property1->id,
            'sales_officer_id' => $this->salesExecutive->id,
            'deal_value' => 1000000.00,
            'status' => 'Closed Won',
            'deal_closed_at' => now(),
        ]);
        $plan1 = PaymentPlan::create([
            'sale_id' => $sale1->id,
            'plan_type' => 'outright',
            'total_amount' => 1000000.00,
            'amount_paid' => 1000000.00,
            'balance' => 0.00,
            'status' => 'completed'
        ]);
        $milestone1 = PaymentMilestone::create([
            'payment_plan_id' => $plan1->id,
            'label' => 'Outright Payment',
            'amount_due' => 1000000.00,
            'due_date' => now()->toDateString(),
            'amount_paid' => 1000000.00,
            'status' => 'paid'
        ]);

        // Let's create a manual document
        $docPath = 'documents/contract1.pdf';
        Storage::disk('public')->put($docPath, 'test content');
        $document = Document::create([
            'lead_id' => $lead1->id,
            'name' => 'Deed of Assignment',
            'file_path' => $docPath,
            'category' => 'contract',
            'uploaded_by' => $this->salesExecutive->id
        ]);

        // Let's create a generated document
        $template = DocumentTemplate::create([
            'name' => 'Allocation Letter Template',
            'trigger_event' => 'deal_won',
            'is_active' => true
        ]);
        $genDocPath = 'generated_docs/alloc1.pdf';
        Storage::disk('public')->put($genDocPath, 'generated pdf content');
        $generatedDocument = GeneratedDocument::create([
            'lead_id' => $lead1->id,
            'document_template_id' => $template->id,
            'pdf_path' => $genDocPath,
            'title' => 'Allocation Letter - Buyer One',
            'content' => 'Dear Buyer One, congrats on unit 1'
        ]);

        // Buyer 2 tries to download Buyer 1's stuff
        $customer2 = User::create([
            'name' => 'Buyer Two',
            'email' => 'buyer2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'active'
        ]);

        // Attempting unauthorized downloads
        $this->actingAs($customer2)->get(route('buyer.payments.receipt', $milestone1))->assertStatus(403);
        $this->actingAs($customer2)->get(route('buyer.documents.download', $document))->assertStatus(403);
        $this->actingAs($customer2)->get(route('buyer.generated-documents.download', $generatedDocument))->assertStatus(403);

        // Authorized downloads
        $this->actingAs($customer1)->get(route('buyer.payments.receipt', $milestone1))->assertStatus(200);
        $this->actingAs($customer1)->get(route('buyer.documents.download', $document))->assertStatus(200);
        $this->actingAs($customer1)->get(route('buyer.generated-documents.download', $generatedDocument))->assertStatus(200);
    }
}
