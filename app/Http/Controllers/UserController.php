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
        $users       = User::with(['branch', 'departmentRelation'])->orderBy('name', 'asc')->get();
        $branches    = \App\Models\Branch::orderBy('name', 'asc')->get();
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name', 'asc')->get();
        return view('settings.index', compact('users', 'branches', 'departments'));
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
            'role'            => 'required|string|in:company_admin,hr,sales_manager,sales_executive,media_manager,project_manager',
            'department_id'   => 'nullable|exists:departments,id',
            'department'      => 'nullable|string',
            'phone_number'    => 'nullable|string|max:30',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'branch_id'       => 'nullable|exists:branches,id',
        ]);

        // Enforce hierarchy rules
        if (!Auth::user()->hasRole(['super_admin', 'company_admin'])) {
            if ($validated['role'] === 'company_admin') {
                return back()->withInput()->withErrors(['role' => 'Only admins can assign the Company Admin role.']);
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
            'role' => 'required|string|in:company_admin,hr,sales_manager,sales_executive,media_manager,project_manager',
            'department_id' => 'nullable|exists:departments,id',
            'department'    => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
            'phone_number' => 'nullable|string|max:30',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

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

        // Enforce hierarchy rules
        if (!Auth::user()->hasRole(['super_admin', 'company_admin'])) {
            if ($user->hasRole(['super_admin', 'company_admin'])) {
                return back()->withErrors(['error' => 'You do not have permission to remove this administrator.']);
            }
        }

        $user->delete();

        return back()->with('success', 'User removed successfully.');
    }
}
