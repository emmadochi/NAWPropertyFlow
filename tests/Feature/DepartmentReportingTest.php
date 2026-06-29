<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DepartmentTarget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentReportingTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user for testing
        $this->adminUser = User::factory()->create([
            'role' => 'company_admin',
            'department' => 'Admin'
        ]);
    }

    /**
     * Test department targets index page loads successfully.
     */
    public function test_department_targets_page_loads(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('hr.department-targets.index'));

        $response->assertStatus(200);
        $response->assertSee('Departmental Goals');
    }

    /**
     * Test department target creation.
     */
    public function test_store_department_target(): void
    {
        $targetData = [
            'department' => 'Media',
            'target_month' => 6,
            'target_year' => 2026,
            'metric' => 'campaigns_sent',
            'target_value' => 25,
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('hr.department-targets.store'), $targetData);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('department_targets', [
            'department' => 'Media',
            'target_month' => 6,
            'target_year' => 2026,
            'metric' => 'campaigns_sent',
            'target_value' => 25,
        ]);
    }

    /**
     * Test department reports page loads successfully.
     */
    public function test_department_reports_page_loads(): void
    {
        // Set a target to check if it's visible on reports
        DepartmentTarget::create([
            'department' => 'Sales',
            'target_month' => 6,
            'target_year' => 2026,
            'metric' => 'revenue',
            'target_value' => 10000000.00
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('reports.departments.index', ['month' => 6, 'year' => 2026]));

        $response->assertStatus(200);
        $response->assertSee('Sales Department');
        $response->assertSee('Media Department');
        $response->assertSee('Project Management');
        $response->assertSee('Admin Department');
    }
}
