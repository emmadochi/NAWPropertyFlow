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

    // Role helper methods
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isCompanyAdmin(): bool
    {
        return $this->role === 'company_admin';
    }

    public function isSalesManager(): bool
    {
        return $this->role === 'sales_manager';
    }

    public function isSalesExecutive(): bool
    {
        return $this->role === 'sales_executive';
    }

    public function isMediaManager(): bool
    {
        return $this->role === 'media_manager';
    }

    public function isProjectManager(): bool
    {
        return $this->role === 'project_manager';
    }

    public function isHR(): bool
    {
        return $this->role === 'hr';
    }

    public function hasRole(array|string $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
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
}
