<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\DepartmentMetric;
use App\Models\DepartmentTarget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default departments/metrics
        $this->artisan('migrate');

        // Create admin user for testing
        $this->adminUser = User::factory()->create([
            'role' => 'company_admin',
            'department' => 'Admin'
        ]);

        // Dynamically get the seeded Admin department id and associate
        $adminDept = Department::where('name', 'Admin')->first();
        if ($adminDept) {
            $this->adminUser->update(['department_id' => $adminDept->id]);
        }
    }

    public function test_departments_page_loads_for_admin(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('departments.index'));

        $response->assertStatus(200);
        $response->assertSee('Department Management');
        $response->assertSee('Sales');
        $response->assertSee('Media');
    }

    public function test_admin_can_create_department(): void
    {
        $data = [
            'name' => 'Human Resources',
            'icon' => '👥',
            'description' => 'Handles recruitment and employee relations.'
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('departments.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('departments', ['name' => 'Human Resources', 'icon' => '👥']);

        // Assert default metric was created
        $dept = Department::where('name', 'Human Resources')->first();
        $this->assertDatabaseHas('department_metrics', [
            'department_id' => $dept->id,
            'key' => 'tasks_completed'
        ]);
    }

    public function test_admin_can_update_department(): void
    {
        $dept = Department::create([
            'name' => 'Designers',
            'icon' => '🎨',
            'is_active' => true
        ]);

        $updateData = [
            'name' => 'Creative & Design',
            'icon' => '🎨',
            'description' => 'Updated description'
        ];

        $response = $this->actingAs($this->adminUser)
            ->put(route('departments.update', $dept), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('departments', [
            'id' => $dept->id,
            'name' => 'Creative & Design',
            'description' => 'Updated description'
        ]);
    }

    public function test_admin_can_toggle_department_active_status(): void
    {
        $dept = Department::create([
            'name' => 'Legal',
            'icon' => '⚖️',
            'is_active' => true
        ]);

        // Toggle to inactive
        $response = $this->actingAs($this->adminUser)
            ->patch(route('departments.toggle', $dept));

        $response->assertRedirect();
        $this->assertDatabaseHas('departments', [
            'id' => $dept->id,
            'is_active' => false
        ]);

        // Toggle back to active
        $response = $this->actingAs($this->adminUser)
            ->patch(route('departments.toggle', $dept));

        $response->assertRedirect();
        $this->assertDatabaseHas('departments', [
            'id' => $dept->id,
            'is_active' => true
        ]);
    }

    public function test_admin_can_add_custom_kpi_metric(): void
    {
        $dept = Department::create([
            'name' => 'Content Studio',
            'icon' => '📹',
            'is_active' => true
        ]);

        $metricData = [
            'label' => 'Videos Shot & Edited',
            'unit' => 'count'
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('departments.metrics.store', $dept), $metricData);

        $response->assertRedirect();
        $this->assertDatabaseHas('department_metrics', [
            'department_id' => $dept->id,
            'label' => 'Videos Shot & Edited',
            'key' => 'videos_shot__edited',
            'unit' => 'count',
            'type' => 'manual'
        ]);
    }

    public function test_admin_can_toggle_metric_active_status(): void
    {
        $dept = Department::create([
            'name' => 'Support',
            'icon' => '☎️',
            'is_active' => true
        ]);

        $metric = DepartmentMetric::create([
            'department_id' => $dept->id,
            'key' => 'tickets_resolved',
            'label' => 'Tickets Resolved',
            'unit' => 'count',
            'type' => 'manual',
            'is_active' => true
        ]);

        $response = $this->actingAs($this->adminUser)
            ->patch(route('departments.metrics.toggle', $metric));

        $response->assertRedirect();
        $this->assertDatabaseHas('department_metrics', [
            'id' => $metric->id,
            'is_active' => false
        ]);
    }
}
