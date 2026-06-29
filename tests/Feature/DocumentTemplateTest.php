<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Sale;
use App\Models\PaymentPlan;
use App\Models\PaymentMilestone;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateVersion;
use App\Models\GeneratedDocument;
use App\Models\Inspection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Mail\GeneratedDocumentMail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class DocumentTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $salesExecutive;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active'
        ]);

        $this->salesExecutive = User::factory()->create([
            'role' => 'sales_executive',
            'status' => 'active'
        ]);

        Storage::fake('public');
    }

    /**
     * Test template CRUD operations
     */
    public function test_document_template_crud(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Create Template
        $response = $this->post(route('document-templates.store'), [
            'name' => 'Offer Letter of Allocation',
            'trigger_event' => 'deal_won',
            'is_active' => 1,
            'content' => '<p>Dear {{client_name}}, we allocate {{property_name}}.</p>'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('document_templates', [
            'name' => 'Offer Letter of Allocation',
            'trigger_event' => 'deal_won'
        ]);

        $template = DocumentTemplate::first();
        $this->assertDatabaseHas('document_template_versions', [
            'document_template_id' => $template->id,
            'version_number' => 1,
            'content' => '<p>Dear {{client_name}}, we allocate {{property_name}}.</p>'
        ]);

        // 2. Edit / Version update
        $response = $this->put(route('document-templates.update', $template), [
            'name' => 'Offer Letter of Allocation (Revised)',
            'trigger_event' => 'deal_won',
            'is_active' => 1,
            'content' => '<p>Dear {{client_name}}, we allocate {{property_name}} with term revisions.</p>'
        ]);

        $response->assertRedirect(route('document-templates.show', $template));
        $this->assertDatabaseHas('document_template_versions', [
            'document_template_id' => $template->id,
            'version_number' => 2,
            'content' => '<p>Dear {{client_name}}, we allocate {{property_name}} with term revisions.</p>'
        ]);
    }

    /**
     * Test automated document compilation on Deal Won event.
     */
    public function test_automated_generation_on_deal_won(): void
    {
        Mail::fake();
        $this->actingAs($this->salesExecutive);

        // Create template
        $template = DocumentTemplate::create([
            'name' => 'Offer Letter',
            'trigger_event' => 'deal_won',
            'is_active' => true,
        ]);

        DocumentTemplateVersion::create([
            'document_template_id' => $template->id,
            'version_number' => 1,
            'content' => '<h1>OFFER LETTER</h1><p>Dear {{client_name}}, you purchased {{property_name}}.</p>'
        ]);

        $lead = Lead::factory()->create([
            'full_name' => 'Aliko Dangote',
            'email' => 'aliko@dangote.com'
        ]);
        $property = Property::factory()->create([
            'name' => 'Banana Island Palace'
        ]);

        // Trigger Sale -> automatically fires DealWon event inside SalesService
        $this->post('/sales', [
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'deal_value' => 500000000.00,
            'units_purchased' => 1
        ]);

        // Assert generated document exists
        $this->assertDatabaseHas('generated_documents', [
            'document_template_id' => $template->id,
            'lead_id' => $lead->id,
            'title' => 'Offer Letter - Aliko Dangote'
        ]);

        $doc = GeneratedDocument::first();
        $this->assertStringContainsString('Dear Aliko Dangote, you purchased Banana Island Palace.', $doc->content);
        $this->assertNotNull($doc->pdf_path);

        // Assert email is sent to client
        Mail::assertSent(GeneratedDocumentMail::class, function ($mail) use ($doc) {
            return $mail->document->id === $doc->id && $mail->hasTo('aliko@dangote.com');
        });
    }

    /**
     * Test milestones table rendering inside generated documents on Payment Received event.
     */
    public function test_payment_received_renders_milestones_table(): void
    {
        Mail::fake();
        $this->actingAs($this->salesExecutive);

        // Create template
        $template = DocumentTemplate::create([
            'name' => 'Payment Receipt Confirmation',
            'trigger_event' => 'payment_received',
            'is_active' => true,
        ]);

        DocumentTemplateVersion::create([
            'document_template_id' => $template->id,
            'version_number' => 1,
            'content' => '<h1>RECEIPT</h1><p>{{client_name}} paid.</p><div>{{milestone_payments}}</div>'
        ]);

        $lead = Lead::factory()->create([
            'full_name' => 'Femi Otedola',
            'email' => 'femi@otedola.com'
        ]);
        $property = Property::factory()->create();
        
        $sale = Sale::create([
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'sales_officer_id' => $this->salesExecutive->id,
            'deal_value' => 100000000.00,
            'units_purchased' => 1,
            'status' => 'Closed Won',
            'deal_closed_at' => now(),
        ]);

        $plan = PaymentPlan::create([
            'sale_id' => $sale->id,
            'plan_type' => 'installment',
            'total_amount' => 100000000.00,
            'amount_paid' => 0,
            'balance' => 100000000.00,
            'status' => 'active'
        ]);

        $milestone = PaymentMilestone::create([
            'payment_plan_id' => $plan->id,
            'label' => 'Deposit Milestone',
            'amount_due' => 50000000.00,
            'due_date' => now()->addDays(7),
            'amount_paid' => 0,
            'status' => 'pending'
        ]);

        // Record milestone payment -> dispatches PaymentReceived event in PaymentService
        $this->post(route('payments.record-payment', $milestone->id), [
            'amount_paid' => 50000000.00,
            'bank_reference' => 'ZENITH-BANK-5566',
            'notes' => 'Received deposit'
        ]);

        // Assert compiled document contains HTML milestones table
        $doc = GeneratedDocument::first();
        $this->assertNotNull($doc);
        $this->assertStringContainsString('ZENITH-BANK-5566', $milestone->fresh()->bank_reference);
        $this->assertStringContainsString('Deposit Milestone', $doc->content);
        $this->assertStringContainsString('<table>', $doc->content);

        // Assert email sent
        Mail::assertSent(GeneratedDocumentMail::class);
    }
}
