<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class PropertyPortfolioTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $managerUser;
    protected $salesExecutive;

    protected function setUp(): void
    {
        parent::setUp();

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
     * Test full Project portfolio lifecycle (Create, Read, Update, Delete)
     */
    public function test_project_crud_lifecycle(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Create Project
        $response = $this->post('/projects', [
            'name' => 'Signature Terrace Block B',
            'developer' => 'NAW Builders Ltd',
            'location' => 'Ikoyi, Lagos',
            'type' => 'mixed_use',
            'status' => 'planning',
            'start_date' => now()->toDateString(),
            'expected_completion_date' => now()->addYears(2)->toDateString(),
            'total_units' => 45,
            'land_size_sqm' => 8000
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'name' => 'Signature Terrace Block B',
            'developer' => 'NAW Builders Ltd'
        ]);

        $project = Project::first();

        // 2. View Project Index and Detail
        $this->get('/projects')->assertStatus(200)->assertSee('Signature Terrace Block B');
        $this->get(route('projects.show', $project))->assertStatus(200)->assertSee('NAW Builders Ltd');

        // 3. Edit Project
        $response = $this->put(route('projects.update', $project), [
            'name' => 'Signature Terrace Block B (Renamed)',
            'developer' => 'NAW Builders Ltd',
            'location' => 'Victoria Island, Lagos',
            'type' => 'commercial',
            'status' => 'in_progress',
            'total_units' => 50,
            'land_size_sqm' => 8500
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Signature Terrace Block B (Renamed)',
            'location' => 'Victoria Island, Lagos',
            'type' => 'commercial'
        ]);

        // 4. Destroy Project
        $this->delete(route('projects.destroy', $project))->assertRedirect(route('projects.index'));
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    /**
     * Test Project Milestones inline store, update, and automatic progress weights.
     */
    public function test_project_milestones_and_completion_recalculations(): void
    {
        $this->actingAs($this->adminUser);

        $project = Project::create([
            'name' => 'Silicon Heights Estate',
            'location' => 'Lekki, Lagos',
            'type' => 'residential',
            'status' => 'in_progress',
            'completion_percentage' => 0
        ]);

        // Add Milestone 1
        $this->post(route('projects.milestones.store', $project), [
            'title' => 'Land excavation & Clearing',
            'due_date' => now()->addMonth()->toDateString(),
            'percentage_weight' => 20,
            'status' => 'completed'
        ]);

        // Add Milestone 2
        $this->post(route('projects.milestones.store', $project), [
            'title' => 'Substructure Piling',
            'due_date' => now()->addMonths(3)->toDateString(),
            'percentage_weight' => 30,
            'status' => 'pending'
        ]);

        $project->refresh();
        // Since milestone 1 is completed (weight 20) and milestone 2 is pending (weight 30),
        // completion should be: (20 / (20 + 30)) * 100 = 40%
        $this->assertEquals(40, $project->completion_percentage);

        // Update Milestone 2 to completed
        $milestone2 = $project->milestones()->where('title', 'Substructure Piling')->first();
        $this->put(route('projects.milestones.update', [$project, $milestone2]), [
            'status' => 'completed',
            'notes' => 'Piles signed off by surveyor.'
        ]);

        $project->refresh();
        // Both completed: (50 / 50) * 100 = 100%
        $this->assertEquals(100, $project->completion_percentage);
    }

    /**
     * Test Property Unit CRUD, reservation holds, release, and bulk generation.
     */
    public function test_property_unit_pipeline_and_reservations(): void
    {
        $this->actingAs($this->adminUser);

        $property = Property::create([
            'name' => 'Pearl Royal Court',
            'location' => 'Oniru, Lagos',
            'property_type' => 'Duplex',
            'price' => 120000000.00,
            'available_units' => 0,
            'total_units' => 0
        ]);

        // 1. Add Property Unit
        $this->post(route('properties.units.store', $property), [
            'unit_number' => 'Block A Unit 1',
            'price' => 120000000.00,
            'status' => 'available',
            'unit_type' => '4 Bedroom Duplex'
        ]);

        $this->assertDatabaseHas('property_units', [
            'property_id' => $property->id,
            'unit_number' => 'Block A Unit 1',
            'status' => 'available'
        ]);

        $property->refresh();
        $this->assertEquals(1, $property->available_units);
        $this->assertEquals(1, $property->total_units);

        // 2. Reserve the Unit
        $lead = Lead::factory()->create();
        $unit = PropertyUnit::first();

        $this->post(route('properties.units.reserve', [$property, $unit]), [
            'lead_id' => $lead->id,
            'hold_days' => 5,
            'reservation_notes' => 'Client is processing bank draft.'
        ]);

        $unit->refresh();
        $this->assertEquals('reserved', $unit->status);
        $this->assertEquals($lead->id, $unit->reserved_by_lead_id);
        $this->assertNotNull($unit->reservation_expires_at);

        $property->refresh();
        $this->assertEquals(0, $property->available_units); // Reserved means unavailable for other sales

        // 3. Release reservation
        $this->post(route('properties.units.release', [$property, $unit]));
        $unit->refresh();
        $this->assertEquals('available', $unit->status);
        $this->assertNull($unit->reserved_by_lead_id);

        $property->refresh();
        $this->assertEquals(1, $property->available_units);

        // 4. Bulk Generate units
        $this->post(route('properties.units.bulk-create', $property), [
            'prefix' => 'Apt ',
            'start_number' => 10,
            'count' => 5,
            'price' => 75000000.00
        ]);

        $property->refresh();
        // Previously 1 unit, now 5 more = 6 total units
        $this->assertEquals(6, $property->total_units);
        $this->assertEquals(6, $property->available_units);
    }

    /**
     * Test recording sale with property_unit_id marks the unit as sold.
     */
    public function test_sale_auto_marks_unit_sold_and_syncs_available_count(): void
    {
        $this->actingAs($this->salesExecutive);

        $lead = Lead::factory()->create();
        $property = Property::create([
            'name' => 'Grand Horizon Flat',
            'location' => 'Lekki, Lagos',
            'property_type' => 'Flat',
            'price' => 45000000.00,
            'available_units' => 2,
            'total_units' => 2
        ]);

        $unit = PropertyUnit::create([
            'property_id' => $property->id,
            'unit_number' => 'Flat 101',
            'price' => 45000000.00,
            'status' => 'available'
        ]);

        $unit2 = PropertyUnit::create([
            'property_id' => $property->id,
            'unit_number' => 'Flat 102',
            'price' => 45000000.00,
            'status' => 'available'
        ]);

        // Record Sale tied to the specific unit
        $this->post('/sales', [
            'lead_id' => $lead->id,
            'property_id' => $property->id,
            'property_unit_id' => $unit->id,
            'deal_value' => 45000000.00,
            'units_purchased' => 1
        ]);

        $unit->refresh();
        $this->assertEquals('sold', $unit->status);

        $property->refresh();
        // Since 1 unit of the 2 units was marked sold, available_units should drop to 1
        $this->assertEquals(1, $property->available_units);
    }

    /**
     * Test expired reservation release and milestone delay audits.
     */
    public function test_console_reservation_and_milestones_audit_command(): void
    {
        $property = Property::create([
            'name' => 'Zenith Estate',
            'location' => 'Abuja',
            'property_type' => 'Land',
            'price' => 30000000.00,
            'available_units' => 0,
            'total_units' => 1
        ]);

        $lead = Lead::factory()->create();
        // Unit reserved but expired yesterday
        $unit = PropertyUnit::create([
            'property_id' => $property->id,
            'unit_number' => 'Plot 24',
            'price' => 30000000.00,
            'status' => 'reserved',
            'reserved_by_lead_id' => $lead->id,
            'reserved_at' => now()->subDays(10),
            'reservation_expires_at' => now()->subDays(1)
        ]);

        $project = Project::create([
            'name' => 'Zenith Phase 2',
            'location' => 'Abuja',
            'type' => 'residential',
            'status' => 'in_progress'
        ]);

        // Milestone due yesterday, status pending
        $milestone = ProjectMilestone::create([
            'project_id' => $project->id,
            'title' => 'Fencing completion',
            'due_date' => now()->subDays(1),
            'status' => 'pending'
        ]);

        // Run audit command
        Artisan::call('crm:release-expired-reservations');

        $unit->refresh();
        $this->assertEquals('available', $unit->status);
        $this->assertNull($unit->reserved_by_lead_id);

        $property->refresh();
        $this->assertEquals(1, $property->available_units);

        $milestone->refresh();
        $this->assertEquals('delayed', $milestone->status);
    }
}
