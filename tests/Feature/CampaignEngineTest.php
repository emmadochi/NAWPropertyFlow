<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use App\Models\Branch;
use App\Models\Campaign;
use App\Models\CampaignContact;
use App\Models\DripSequence;
use App\Models\DripStep;
use App\Models\DripEnrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendCampaignEmailJob;
use App\Jobs\SendCampaignSmsJob;
use App\Services\CampaignService;
use App\Services\DripService;

class CampaignEngineTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::factory()->create();
        $this->adminUser = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'branch_id' => $this->branch->id,
        ]);
    }

    /**
     * Test Campaign CRUD lifecycle
     */
    public function test_campaign_crud(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Create campaign draft
        $response = $this->post(route('campaigns.store'), [
            'name' => 'Mega Christmas Promo',
            'type' => 'email',
            'subject' => 'Huge discounts in Lekki Phase 1',
            'body' => '<p>Hello {{name}}, buy now!</p>',
            'audience_status' => 'new',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('campaigns', [
            'name' => 'Mega Christmas Promo',
            'type' => 'email',
            'status' => 'draft',
        ]);
    }

    /**
     * Test Audience segment building
     */
    public function test_audience_segmentation_filters(): void
    {
        // Create matching and non-matching leads
        Lead::factory()->create([
            'full_name' => 'Matching Lead',
            'status' => 'new',
            'lead_source' => 'Instagram',
            'email' => 'matching@naw.com',
            'branch_id' => $this->branch->id,
        ]);

        Lead::factory()->create([
            'full_name' => 'Wrong Status',
            'status' => 'qualified',
            'lead_source' => 'Instagram',
            'email' => 'wrongstatus@naw.com',
            'branch_id' => $this->branch->id,
        ]);

        Lead::factory()->create([
            'full_name' => 'No Email',
            'status' => 'new',
            'lead_source' => 'Instagram',
            'email' => null,
            'branch_id' => $this->branch->id,
        ]);

        $campaign = Campaign::create([
            'name' => 'Test Promo',
            'type' => 'email',
            'status' => 'draft',
            'body' => 'Body',
            'audience_segment' => 'custom',
            'audience_filters' => ['status' => 'new', 'lead_source' => 'Instagram'],
            'created_by' => $this->adminUser->id,
            'branch_id' => $this->branch->id,
        ]);

        $service = new CampaignService();
        $audience = $service->buildAudience($campaign);

        $this->assertCount(1, $audience);
        $this->assertEquals('Matching Lead', Lead::find($audience->first())->full_name);
    }

    /**
     * Test Campaign sending and Bus dispatching
     */
    public function test_campaign_dispatch_and_batching(): void
    {
        Bus::fake();

        $lead = Lead::factory()->create([
            'status' => 'new',
            'email' => 'client@naw.com',
            'branch_id' => $this->branch->id,
        ]);

        $campaign = Campaign::create([
            'name' => 'Broadcast',
            'type' => 'email',
            'status' => 'draft',
            'body' => 'Hello {{name}}',
            'audience_segment' => 'all',
            'created_by' => $this->adminUser->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->actingAs($this->adminUser);
        $response = $this->post(route('campaigns.send', $campaign));

        $response->assertRedirect();
        
        Bus::assertBatched(function ($batch) {
            return $batch->jobs->count() === 1;
        });

        $this->assertEquals('sending', $campaign->fresh()->status);
    }

    /**
     * Test Drip Sequence steps and enrollment triggers
     */
    public function test_drip_sequence_and_triggers(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Create drip sequence
        $response = $this->post(route('drip-sequences.store'), [
            'name' => 'New Lead Drip',
            'trigger_event' => 'lead_created',
            'description' => 'Triggers for new leads',
        ]);

        $response->assertRedirect();
        $sequence = DripSequence::first();
        $this->assertNotNull($sequence);

        // 2. Add step
        $response = $this->post(route('drip-sequences.steps.store', $sequence), [
            'type' => 'email',
            'subject' => 'Welcome to NAW Properties',
            'body' => 'Welcome message',
            'delay_days' => 1,
            'delay_hours' => 0,
        ]);

        $response->assertRedirect();
        $this->assertCount(1, $sequence->steps);

        // 3. Trigger Drip sequence via lead creation
        $lead = Lead::factory()->create([
            'full_name' => 'Tunde Folawiyo',
            'email' => 'tunde@folawiyo.com',
            'branch_id' => $this->branch->id,
        ]);

        // Enrollment should be created automatically
        $this->assertDatabaseHas('drip_enrollments', [
            'drip_sequence_id' => $sequence->id,
            'lead_id' => $lead->id,
            'status' => 'active',
        ]);
    }

    /**
     * Test Drip processing command
     */
    public function test_process_drip_steps_command(): void
    {
        Mail::fake();

        $sequence = DripSequence::create([
            'name' => 'Auto Drip',
            'trigger_event' => 'lead_created',
            'is_active' => true,
            'created_by' => $this->adminUser->id,
        ]);

        $step = DripStep::create([
            'drip_sequence_id' => $sequence->id,
            'step_order' => 1,
            'type' => 'email',
            'subject' => 'Immediate Welcome',
            'body' => 'Welcome to NAW Properties',
            'delay_days' => 0,
            'delay_hours' => 0,
            'is_active' => true,
        ]);

        $lead = Lead::factory()->create([
            'email' => 'test@client.com',
            'branch_id' => $this->branch->id,
        ]);

        // Enroll
        $service = new DripService();
        $enrollment = $service->enroll($sequence, $lead);
        $this->assertNotNull($enrollment);
        
        // Force next_send_at to be in past to process immediately
        $enrollment->update(['next_send_at' => now()->subHour()]);

        // Run command
        $this->artisan('drip:process-steps')
            ->expectsOutputToContain('Successfully processed 1 drip steps.')
            ->assertExitCode(0);

        // Assert step is sent and status updated to complete
        $this->assertEquals('completed', $enrollment->fresh()->status);
        Mail::assertSent(\App\Mail\CampaignMail::class, function ($mail) use ($lead) {
            return $mail->hasTo($lead->email);
        });
    }
}
