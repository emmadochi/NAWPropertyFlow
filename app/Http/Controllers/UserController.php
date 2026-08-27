<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display listing of team members (Users).
     */
    public function index()
    {
        // Self-healing: Ensure job_title and is_department_head columns exist
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'job_title')) {
            try {
                \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('job_title')->nullable()->after('role');
                    $table->boolean('is_department_head')->default(false)->after('job_title');
                });
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        $users       = User::with(['branch', 'departmentRelation', 'roleRelation'])->orderBy('name', 'asc')->get();
        $branches    = \App\Models\Branch::orderBy('name', 'asc')->get();
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name', 'asc')->get();
        $roles       = \App\Models\Role::orderBy('name', 'asc')->get();

        $specialistTitles = [
            'Media and Creative' => ['Media Officer / Manager', 'Lead Graphic Designer', 'Senior Video Editor', 'Content Creator & Copywriter', 'Drone Pilot & Videographer', '3D Visualizer & Motion Designer', 'Social Media Specialist'],
            'Project Management' => ['Project Manager / Lead', 'Site Engineer', 'Quantity Surveyor (QS)', 'Site Storekeeper / Yard Officer', 'Site Supervisor / Foreman', 'Procurement Specialist'],
            'Accounting' => ['Head of Finance & Accounts', 'Staff Accountant', 'Treasury & Billing Officer', 'Cashier', 'Internal Auditor', 'Tax & Compliance Associate'],
            'Marketing & Sales' => ['Sales Manager', 'Senior Sales Executive / Realtor', 'Digital Marketer / Media Buyer', 'Telemarketing Representative', 'Lead Acquisition Specialist'],
            'Admin' => ['Head of Administration', 'HR Officer', 'Front Desk / Customer Service Executive', 'Executive Assistant', 'Operations Officer'],
            'Legal' => ['Head of Legal & Company Secretary', 'Legal Officer / Contract Drafter', 'Regulatory Compliance Officer'],
            'Logistics' => ['Logistics Manager', 'Site Inspection Driver', 'Dispatch Officer', 'Fleet Supervisor'],
            'Procurement' => ['Procurement Manager', 'Vendor Sourcing Specialist', 'Material Buyer']
        ];

        return view('settings.index', compact('users', 'branches', 'departments', 'roles', 'specialistTitles'));
    }

    /**
     * Create new team member.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email|max:255',
            'password'        => 'required|string|min:6',
            'role'            => 'required|string',
            'role_id'         => 'nullable|exists:roles,id',
            'job_title'       => 'nullable|string|max:255',
            'is_department_head' => 'nullable',
            'department_id'   => 'nullable|exists:departments,id',
            'department'      => 'nullable|string',
            'phone_number'    => 'nullable|string|max:30',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'branch_id'       => 'nullable|exists:branches,id',
        ]);

        $validated['is_department_head'] = $request->has('is_department_head') || $request->boolean('is_department_head');

        // Enforce hierarchy rules
        if (!Auth::user()->hasRole(['super_admin', 'company_admin'])) {
            if ($validated['role'] === 'company_admin' || $validated['role'] === 'super_admin') {
                return back()->withInput()->withErrors(['role' => 'Only administrators can assign executive roles.']);
            }
        }

        // Link role_id if matched
        if (empty($validated['role_id'])) {
            $matchedRole = \App\Models\Role::where('slug', $validated['role'])->orWhere('name', $validated['role'])->first();
            if ($matchedRole) {
                $validated['role_id'] = $matchedRole->id;
                $validated['role'] = $matchedRole->slug;
            }
        }

        if (!empty($validated['department_id'])) {
            $dept = \App\Models\Department::findOrFail($validated['department_id']);
        } elseif (!empty($validated['department'])) {
            $dept = \App\Models\Department::where('name', $validated['department'])->firstOrFail();
            $validated['department_id'] = $dept->id;
        } else {
            return back()->withInput()->withErrors(['department_id' => 'Please select a department.']);
        }

        $validated['department'] = $dept->name;
        $validated['password']   = Hash::make($request->password);
        $validated['status']     = 'active';

        $user = User::create($validated);

        // Save initial agreed salary structure if provided
        if (!empty($request->base_salary) || !empty($request->housing_allowance) || !empty($request->transport_allowance)) {
            \App\Models\SalaryStructure::create([
                'user_id' => $user->id,
                'base_salary' => $request->base_salary ?? 0,
                'housing_allowance' => $request->housing_allowance ?? 0,
                'transport_allowance' => $request->transport_allowance ?? 0,
                'other_allowances' => 0,
                'tax_percent' => 0,
                'pension_percent' => 0,
            ]);
        }

        // Seed default onboarding tasks
        $defaultTasks = [
            'Submit ID documents & signed employment contract',
            'Configure CRM profile image & contact details',
            'Complete HR & Department Orientation',
            'Set up personal email signature & tools',
            'Set first monthly sales targets with Manager',
        ];

        foreach ($defaultTasks as $taskName) {
            \App\Models\OnboardingTask::create([
                'user_id'     => $user->id,
                'task_name'   => $taskName,
                'assigned_by' => Auth::id() ?? 1,
                'due_date'    => now()->addDays(7),
                'is_completed'=> false,
            ]);
        }

        return back()->with('success', "✅ {$user->name} has been added to the team. Their account is ready and 5 onboarding tasks have been generated.");
    }

    /**
     * Update team member details.
     */
    public function update(Request $request, User $user)
    {
        // Don't allow self role changes or status changes for security
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot update your own role or status.']);
        }

        $validated = $request->validate([
            'role' => 'required|string',
            'role_id' => 'nullable|exists:roles,id',
            'job_title' => 'nullable|string|max:255',
            'is_department_head' => 'nullable',
            'department_id' => 'nullable|exists:departments,id',
            'department'    => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
            'phone_number' => 'nullable|string|max:30',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $validated['is_department_head'] = $request->has('is_department_head') || $request->boolean('is_department_head');

        // Link role_id if matched
        if (empty($validated['role_id'])) {
            $matchedRole = \App\Models\Role::where('slug', $validated['role'])->orWhere('name', $validated['role'])->first();
            if ($matchedRole) {
                $validated['role_id'] = $matchedRole->id;
                $validated['role'] = $matchedRole->slug;
            }
        }

        // Enforce hierarchy rules
        if (!Auth::user()->hasRole(['super_admin', 'company_admin'])) {
            if ($user->hasRole(['super_admin', 'company_admin'])) {
                return back()->withErrors(['error' => 'You do not have permission to modify this administrator.']);
            }
            if ($validated['role'] === 'company_admin') {
                return back()->withErrors(['role' => 'Only admins can assign the Company Admin role.']);
            }
        }

        if (!empty($validated['department_id'])) {
            $dept = \App\Models\Department::findOrFail($validated['department_id']);
        } elseif (!empty($validated['department'])) {
            $dept = \App\Models\Department::where('name', $validated['department'])->firstOrFail();
            $validated['department_id'] = $dept->id;
        } else {
            return back()->withErrors(['department_id' => 'The department field is required.']);
        }

        $validated['department'] = $dept->name;

        $user->update($validated);

        return back()->with('success', 'User updated successfully.');
    }

    /**
     * Delete user.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot delete yourself.']);
        }

        // Immutable Super Admin Guard - Super Admin can NEVER be removed
        if ($user->isSuperAdmin()) {
            return back()->withErrors(['error' => '🛡️ Security Violation: The Super Administrator account is immutable and cannot be removed.']);
        }

        // Enforce hierarchy rules: HR and lower roles cannot delete Company Admins
        if (!Auth::user()->isSuperAdmin()) {
            if ($user->isCompanyAdmin() || $user->hasRole(['super_admin', 'company_admin'])) {
                return back()->withErrors(['error' => 'You do not have permission to remove an executive administrator.']);
            }
        }

        $user->delete();

        return back()->with('success', "{$user->name} has been removed from the team.");
    }
}
