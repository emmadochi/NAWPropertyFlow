<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Models\SalesTarget;
use App\Models\LeaveRequest;
use App\Models\StaffCertification;
use App\Models\PerformanceReview;
use App\Models\Sale;
use App\Models\Lead;
use App\Services\PerformanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HRPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'company_admin', 'branch_id' => null]);
    }

    private function exec(Branch $branch): User
    {
        return User::factory()->create(['role' => 'sales_executive', 'branch_id' => $branch->id]);
    }

    /** @test */
    public function leaderboard_is_accessible_to_managers(): void
    {
        $admin = $this->admin();
        Branch::factory()->create();

        $this->actingAs($admin)
            ->get(route('hr.leaderboard'))
            ->assertOk()
            ->assertViewIs('hr.leaderboard');
    }

    /** @test */
    public function sales_target_can_be_set_and_retrieved(): void
    {
        $admin = $this->admin();
        $branch = Branch::factory()->create();
        $exec = $this->exec($branch);

        $this->actingAs($admin)
            ->post(route('hr.targets.store'), [
                'user_id'        => $exec->id,
                'target_month'   => 6,
                'target_year'    => 2025,
                'leads_target'   => 20,
                'sales_target'   => 5,
                'revenue_target' => 5000000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('sales_targets', [
            'user_id'        => $exec->id,
            'target_month'   => 6,
            'target_year'    => 2025,
            'sales_target'   => 5,
            'revenue_target' => 5000000,
        ]);
    }

    /** @test */
    public function duplicate_target_update_doesnt_create_new_row(): void
    {
        $admin  = $this->admin();
        $branch = Branch::factory()->create();
        $exec   = $this->exec($branch);

        $payload = [
            'user_id' => $exec->id, 'target_month' => 1, 'target_year' => 2025,
            'leads_target' => 10, 'sales_target' => 3, 'revenue_target' => 2000000,
        ];

        $this->actingAs($admin)->post(route('hr.targets.store'), $payload);
        $this->actingAs($admin)->post(route('hr.targets.store'), array_merge($payload, ['sales_target' => 7]));

        $this->assertCount(1, SalesTarget::where('user_id', $exec->id)->get());
        $this->assertEquals(7, SalesTarget::where('user_id', $exec->id)->first()->sales_target);
    }

    /** @test */
    public function staff_can_submit_leave_request(): void
    {
        $branch = Branch::factory()->create();
        $exec   = $this->exec($branch);

        $this->actingAs($exec)
            ->post(route('hr.leave.store'), [
                'leave_type' => 'annual',
                'start_date' => now()->addDay()->format('Y-m-d'),
                'end_date'   => now()->addDays(5)->format('Y-m-d'),
                'reason'     => 'Family vacation',
            ])
            ->assertRedirect(route('hr.leave.index'));

        $this->assertDatabaseHas('leave_requests', [
            'user_id'    => $exec->id,
            'leave_type' => 'annual',
            'status'     => 'pending',
        ]);
    }

    /** @test */
    public function admin_can_approve_leave_request(): void
    {
        $admin  = $this->admin();
        $branch = Branch::factory()->create();
        $exec   = $this->exec($branch);

        $leave = LeaveRequest::create([
            'user_id'        => $exec->id,
            'leave_type'     => 'sick',
            'start_date'     => now()->addDay(),
            'end_date'       => now()->addDays(3),
            'days_requested' => 3,
            'status'         => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('hr.leave.review', $leave), [
                'status'       => 'approved',
                'review_notes' => 'Get well soon.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('leave_requests', [
            'id'         => $leave->id,
            'status'     => 'approved',
            'reviewed_by' => $admin->id,
        ]);
    }

    /** @test */
    public function staff_profile_page_loads_with_stats(): void
    {
        $admin  = $this->admin();
        $branch = Branch::factory()->create();
        $exec   = $this->exec($branch);

        $this->actingAs($admin)
            ->get(route('hr.staff.show', $exec))
            ->assertOk()
            ->assertViewIs('hr.staff.show')
            ->assertViewHas('user', $exec)
            ->assertViewHas('stats');
    }

    /** @test */
    public function manager_can_add_certification_to_staff(): void
    {
        $admin  = $this->admin();
        $branch = Branch::factory()->create();
        $exec   = $this->exec($branch);

        $this->actingAs($admin)
            ->post(route('hr.staff.certifications.store', $exec), [
                'title'       => 'NIESV Real Estate License',
                'issuing_body' => 'NIESV',
                'issued_date' => '2024-01-01',
                'expiry_date' => '2026-12-31',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('staff_certifications', [
            'user_id' => $exec->id,
            'title'   => 'NIESV Real Estate License',
        ]);
    }

    /** @test */
    public function manager_can_submit_performance_review(): void
    {
        $admin  = $this->admin();
        $branch = Branch::factory()->create();
        $exec   = $this->exec($branch);

        $this->actingAs($admin)
            ->post(route('hr.staff.reviews.store', $exec), [
                'review_period'         => 'Q2-2025',
                'score'                 => 82,
                'rating'                => 'good',
                'strengths'             => 'Excellent client communication',
                'areas_for_improvement' => 'Needs to improve closing rate',
                'manager_comments'      => 'Keep up the good work',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('performance_reviews', [
            'user_id'       => $exec->id,
            'reviewed_by'   => $admin->id,
            'review_period' => 'Q2-2025',
            'score'         => 82,
            'rating'        => 'good',
        ]);
    }

    /** @test */
    public function performance_service_leaderboard_returns_sorted_results(): void
    {
        $branch  = Branch::factory()->create();
        $exec1   = $this->exec($branch);
        $exec2   = $this->exec($branch);

        // Create sales for exec1 in current month
        Sale::factory()->count(3)->create([
            'sales_officer_id' => $exec1->id,
            'deal_value'       => 1000000,
        ]);
        // Only 1 sale for exec2
        Sale::factory()->count(1)->create([
            'sales_officer_id' => $exec2->id,
            'deal_value'       => 500000,
        ]);

        $service     = app(PerformanceService::class);
        $leaderboard = $service->leaderboard(now()->month, now()->year);

        $this->assertGreaterThan(0, $leaderboard->count());
        // exec1 should rank higher
        $ranks = $leaderboard->pluck('id')->toArray();
        $this->assertLessThan(
            array_search($exec2->id, $ranks),
            array_search($exec1->id, $ranks)
        );
    }
}
