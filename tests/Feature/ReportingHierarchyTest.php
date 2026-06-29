<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\DepartmentMetric;
use App\Models\DepartmentTarget;
use App\Models\StaffMetricSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingHierarchyTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $mediaHod;
    protected $mediaStaff;
    protected $salesStaff;
    protected $mediaDept;
    protected $salesDept;
    protected $videoMetric;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');

        // Setup departments
        $this->mediaDept = Department::where('name', 'Media')->first();
        $this->salesDept = Department::where('name', 'Sales')->first();

        // Setup users
        $this->adminUser = User::factory()->create(['role' => 'company_admin', 'department' => 'Admin']);
        
        $this->mediaHod = User::factory()->create(['role' => 'sales_manager', 'department' => 'Media', 'department_id' => $this->mediaDept->id]);
        $this->mediaDept->update(['hod_id' => $this->mediaHod->id]);

        $this->mediaStaff = User::factory()->create(['role' => 'sales_executive', 'department' => 'Media', 'department_id' => $this->mediaDept->id]);
        $this->salesStaff = User::factory()->create(['role' => 'sales_executive', 'department' => 'Sales', 'department_id' => $this->salesDept->id]);

        // Get manual metric for Media (e.g. videos_shot)
        $this->videoMetric = DepartmentMetric::where('department_id', $this->mediaDept->id)
            ->where('key', 'videos_shot')
            ->first();
    }

    public function test_staff_can_load_submission_page(): void
    {
        $response = $this->actingAs($this->mediaStaff)
            ->get(route('hr.submissions.index'));

        $response->assertStatus(200);
        $response->assertSee('My KPI Logs');
        $response->assertSee('Videos Shot');
    }

    public function test_staff_can_log_manual_kpi(): void
    {
        $data = [
            'department_metric_id' => $this->videoMetric->id,
            'value' => 5,
            'submission_month' => 6,
            'submission_year' => 2026,
            'notes' => 'Recorded 5 property video shoots.'
        ];

        $response = $this->actingAs($this->mediaStaff)
            ->post(route('hr.submissions.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('staff_metric_submissions', [
            'user_id' => $this->mediaStaff->id,
            'department_id' => $this->mediaDept->id,
            'department_metric_id' => $this->videoMetric->id,
            'value' => 5.00,
            'status' => 'pending'
        ]);
    }

    public function test_hod_can_view_department_submissions_queue(): void
    {
        // Log a submission
        $sub = StaffMetricSubmission::create([
            'user_id' => $this->mediaStaff->id,
            'department_id' => $this->mediaDept->id,
            'department_metric_id' => $this->videoMetric->id,
            'value' => 3,
            'submission_month' => 6,
            'submission_year' => 2026,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->mediaHod)
            ->get(route('hr.submissions.review', ['month' => 6, 'year' => 2026]));

        $response->assertStatus(200);
        $response->assertSee($this->mediaStaff->name);
        $response->assertSee('Videos Shot');
    }

    public function test_hod_can_approve_submission_and_aggregate_metrics(): void
    {
        // 1. Establish target milestone (Target: 10, Actual: 0)
        $target = DepartmentTarget::create([
            'department' => 'Media',
            'department_id' => $this->mediaDept->id,
            'target_month' => 6,
            'target_year' => 2026,
            'metric' => 'videos_shot',
            'target_value' => 10,
            'actual_value' => 0
        ]);

        // 2. Log submission
        $sub = StaffMetricSubmission::create([
            'user_id' => $this->mediaStaff->id,
            'department_id' => $this->mediaDept->id,
            'department_metric_id' => $this->videoMetric->id,
            'value' => 4,
            'submission_month' => 6,
            'submission_year' => 2026,
            'status' => 'pending'
        ]);

        // 3. Approve submission
        $response = $this->actingAs($this->mediaHod)
            ->post(route('hr.submissions.approve', $sub));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('staff_metric_submissions', [
            'id' => $sub->id,
            'status' => 'approved',
            'approved_by' => $this->mediaHod->id
        ]);

        // 4. Verify department target actual_value was updated to 4
        $this->assertDatabaseHas('department_targets', [
            'id' => $target->id,
            'actual_value' => 4.00
        ]);
    }

    public function test_non_hod_cannot_approve_submission(): void
    {
        $sub = StaffMetricSubmission::create([
            'user_id' => $this->mediaStaff->id,
            'department_id' => $this->mediaDept->id,
            'department_metric_id' => $this->videoMetric->id,
            'value' => 4,
            'submission_month' => 6,
            'submission_year' => 2026,
            'status' => 'pending'
        ]);

        // Try to approve using another department's staff
        $response = $this->actingAs($this->salesStaff)
            ->post(route('hr.submissions.approve', $sub));

        $response->assertStatus(403);
    }
}
