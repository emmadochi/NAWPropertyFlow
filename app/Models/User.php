<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Traits\LogsActivity;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'job_title',
        'is_department_head',
        'department',
        'department_id',
        'phone_number',
        'commission_rate',
        'profile_image',
        'status',
        'branch_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship to dynamic custom Role model.
     */
    public function roleRelation()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Check if user has a granular permission.
     * Supports both dynamic RBAC permissions and seamless fallback to string roles.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // 1. Super Admin always bypasses and has full system authorization
        if ($this->role === 'super_admin' || ($this->roleRelation && $this->roleRelation->slug === 'super_admin')) {
            return true;
        }

        // 2. Check dynamic Role permissions if role_id is assigned
        if ($this->roleRelation) {
            return $this->roleRelation->hasPermission($permissionSlug);
        }

        // 3. Backward Compatibility Fallback for legacy un-migrated roles
        return $this->legacyPermissionFallback($permissionSlug);
    }

    /**
     * Fallback mapping for existing string roles during transition.
     */
    protected function legacyPermissionFallback(string $slug): bool
    {
        if (in_array($this->role, ['super_admin', 'company_admin'])) {
            return true;
        }

        $matrix = [
            'sales_executive' => [
                'leads.view_own', 'leads.create', 'leads.edit', 'sales.record',
                'inspections.schedule', 'followups.manage', 'properties.view', 'finance.log_expenses'
            ],
            'sales_manager' => [
                'leads.view_all', 'leads.create', 'leads.edit', 'leads.reassign', 'sales.record',
                'inspections.view_all', 'inspections.schedule', 'followups.manage', 'properties.view',
                'finance.log_expenses', 'marketing.view'
            ],
            'accountant' => [
                'finance.view_ledger', 'finance.verify_payments', 'finance.approve_expenses',
                'finance.disburse_expenses', 'finance.manage_payroll', 'properties.view'
            ],
            'finance_manager' => [
                'finance.view_ledger', 'finance.verify_payments', 'finance.approve_expenses',
                'finance.disburse_expenses', 'finance.manage_payroll', 'properties.view'
            ],
            'hr' => [
                'hr.view_staff', 'hr.approve_leaves', 'hr.review_submissions', 'hr.manage_targets',
                'hr.manage_users', 'finance.manage_payroll'
            ],
            'media_manager' => [
                'marketing.view', 'marketing.send_broadcast', 'marketing.manage_drip', 'properties.view'
            ],
            'project_manager' => [
                'properties.view', 'properties.create', 'properties.edit', 'units.manage', 'finance.log_expenses'
            ],
        ];

        return in_array($slug, $matrix[$this->role] ?? []);
    }

    // Role helper methods (Backward Compatible)
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin' || ($this->roleRelation && $this->roleRelation->slug === 'super_admin');
    }

    public function isCompanyAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'company_admin']) || ($this->roleRelation && in_array($this->roleRelation->slug, ['super_admin', 'company_admin']));
    }

    public function isSalesManager(): bool
    {
        return $this->role === 'sales_manager' || ($this->roleRelation && $this->roleRelation->slug === 'sales_manager');
    }

    public function isSalesExecutive(): bool
    {
        return $this->role === 'sales_executive' || ($this->roleRelation && $this->roleRelation->slug === 'sales_executive');
    }

    public function isMediaManager(): bool
    {
        return $this->role === 'media_manager' || ($this->roleRelation && $this->roleRelation->slug === 'media_manager');
    }

    public function isProjectManager(): bool
    {
        return $this->role === 'project_manager' || ($this->roleRelation && $this->roleRelation->slug === 'project_manager');
    }

    public function isHR(): bool
    {
        return $this->role === 'hr' || ($this->roleRelation && $this->roleRelation->slug === 'hr');
    }

    public function isAccountant(): bool
    {
        return in_array($this->role, ['accountant', 'finance_manager']) || ($this->roleRelation && in_array($this->roleRelation->slug, ['accountant', 'finance_manager']));
    }

    public function hasRole(array|string $roles): bool
    {
        if ($this->role === 'super_admin' || ($this->roleRelation && $this->roleRelation->slug === 'super_admin')) {
            return true;
        }

        $currentRoleSlug = $this->roleRelation ? $this->roleRelation->slug : $this->role;

        if (is_string($roles)) {
            $roles = str_contains($roles, ',') ? explode(',', $roles) : [$roles];
        } else {
            $flat = [];
            array_walk_recursive($roles, function ($r) use (&$flat) {
                if (is_string($r) && str_contains($r, ',')) {
                    $flat = array_merge($flat, explode(',', $r));
                } else {
                    $flat[] = $r;
                }
            });
            $roles = $flat;
        }

        $roles = array_map('trim', $roles);

        return in_array($currentRoleSlug, $roles, true) || in_array($this->role, $roles, true);
    }

    // Relationships
    public function assignedLeads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function assignedInspections()
    {
        return $this->hasMany(Inspection::class, 'assigned_to');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'sales_officer_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function salesTargets()
    {
        return $this->hasMany(SalesTarget::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function certifications()
    {
        return $this->hasMany(StaffCertification::class);
    }

    public function performanceReviews()
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function uploadedDocuments()
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function departmentRelation()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function managedDepartments()
    {
        return $this->hasMany(Department::class, 'hod_id');
    }

    public function isHodOf($department): bool
    {
        if (!$department) return false;
        return $department->hod_id === $this->id;
    }

    public function onboardingTasks()
    {
        return $this->hasMany(OnboardingTask::class);
    }

    public function onboardingPercentage(): int
    {
        $total = $this->onboardingTasks()->count();
        if ($total === 0) {
            return 100; // No onboarding tasks means onboarding is complete or not set up
        }
        $completed = $this->onboardingTasks()->where('is_completed', true)->count();
        return (int) (($completed / $total) * 100);
    }

    public function salaryStructure()
    {
        return $this->hasOne(SalaryStructure::class);
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function payrollDeductions()
    {
        return $this->hasMany(PayrollDeduction::class);
    }
}
