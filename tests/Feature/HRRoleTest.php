<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\StaffMetricSubmission;
use App\Models\DepartmentMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HRRoleTest extends TestCase
{
    use RefreshDatabase;

    private function hrUser(): User
    {
        $dept = Department::where('name', 'Admin')->first();
        return User::factory()->create([
            'role' => 'hr',
            'department' => 'Admin',
            'department_id' => $dept ? $dept->id : null,
            'status' => 'active'
        ]);
    }

    private function adminUser(): User
    {
        $dept = Department::where('name', 'Admin')->first();
        return User::factory()->create([
            'role' => 'company_admin',
            'department' => 'Admin',
            'department_id' => $dept ? $dept->id : null,
            'status' => 'active'
        ]);
    }

    private function salesExecUser(): User
    {
        $dept = Department::where('name', 'Sales')->first();
        return User::factory()->create([
            'role' => 'sales_executive',
            'department' => 'Sales',
            'department_id' => $dept ? $dept->id : null,
            'status' => 'active'
        ]);
    }

    /** @test */
    public function hr_can_access_team_settings_page(): void
    {
        $hr = $this->hrUser();

        $response = $this->actingAs($hr)
            ->get(route('settings.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function hr_can_create_a_sales_executive(): void
    {
        $hr = $this->hrUser();
        $dept = Department::where('name', 'Sales')->first();

        $response = $this->actingAs($hr)
            ->post(route('settings.users.store'), [
                'name' => 'New Exec',
                'email' => 'newexec@example.com',
                'password' => 'password123',
                'role' => 'sales_executive',
                'department_id' => $dept->id,
                'phone_number' => '+2348000000000',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'email' => 'newexec@example.com',
            'role' => 'sales_executive'
        ]);
    }

    /** @test */
    public function hr_cannot_create_a_company_admin(): void
    {
        $hr = $this->hrUser();
        $dept = Department::where('name', 'Sales')->first();

        $response = $this->actingAs($hr)
            ->post(route('settings.users.store'), [
                'name' => 'Fake Admin',
                'email' => 'fakeadmin@example.com',
                'password' => 'password123',
                'role' => 'company_admin',
                'department_id' => $dept->id,
            ]);

        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseMissing('users', [
            'email' => 'fakeadmin@example.com'
        ]);
    }

    /** @test */
    public function hr_cannot_edit_company_admin_user(): void
    {
        $hr = $this->hrUser();
        $admin = $this->adminUser();

        $response = $this->actingAs($hr)
            ->put(route('settings.users.update', $admin), [
                'role' => 'sales_executive',
                'status' => 'inactive',
                'phone_number' => '+234999999999',
            ]);

        $response->assertSessionHasErrors(['error']);
        // Verify user role did not change
        $this->assertEquals('company_admin', $admin->fresh()->role);
    }

    /** @test */
    public function hr_cannot_promote_user_to_company_admin(): void
    {
        $hr = $this->hrUser();
        $exec = $this->salesExecUser();

        $response = $this->actingAs($hr)
            ->put(route('settings.users.update', $exec), [
                'role' => 'company_admin',
                'status' => 'active',
                'department_id' => $exec->department_id,
            ]);

        $response->assertSessionHasErrors(['role']);
        $this->assertEquals('sales_executive', $exec->fresh()->role);
    }

    /** @test */
    public function hr_cannot_delete_company_admin_user(): void
    {
        $hr = $this->hrUser();
        $admin = $this->adminUser();

        $response = $this->actingAs($hr)
            ->delete(route('settings.users.destroy', $admin));

        $response->assertSessionHasErrors(['error']);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    /** @test */
    public function hr_can_review_leaves(): void
    {
        $hr = $this->hrUser();
        $exec = $this->salesExecUser();

        $leave = LeaveRequest::create([
            'user_id' => $exec->id,
            'leave_type' => 'annual',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
            'days_requested' => 3,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($hr)
            ->patch(route('hr.leave.review', $leave), [
                'status' => 'approved',
                'review_notes' => 'Approved by HR',
            ]);

        $response->assertRedirect();
        $this->assertEquals('approved', $leave->fresh()->status);
    }

    /** @test */
    public function hr_can_review_kpis(): void
    {
        $hr = $this->hrUser();
        $exec = $this->salesExecUser();
        $dept = Department::where('name', 'Media')->first();
        $exec->update(['department_id' => $dept->id, 'department' => 'Media']);
        $metric = DepartmentMetric::where('department_id', $dept->id)->where('key', 'videos_shot')->first();

        $submission = StaffMetricSubmission::create([
            'user_id' => $exec->id,
            'department_id' => $dept->id,
            'department_metric_id' => $metric->id,
            'value' => 5,
            'submission_month' => now()->month,
            'submission_year' => now()->year,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($hr)
            ->post(route('hr.submissions.approve', $submission));

        $response->assertRedirect();
        $this->assertEquals('approved', $submission->fresh()->status);
    }

    /** @test */
    public function hr_cannot_access_campaigns_or_drip_sequences(): void
    {
        $hr = $this->hrUser();

        $response1 = $this->actingAs($hr)->get(route('campaigns.index'));
        $response1->assertStatus(403);

        $response2 = $this->actingAs($hr)->get(route('drip-sequences.index'));
        $response2->assertStatus(403);
    }

    /** @test */
    public function hr_cannot_access_branches_or_company_settings(): void
    {
        $hr = $this->hrUser();

        $response1 = $this->actingAs($hr)->get(route('branches.index'));
        $response1->assertStatus(403);

        $response2 = $this->actingAs($hr)->get(route('settings.company.edit'));
        $response2->assertStatus(403);
    }
}
