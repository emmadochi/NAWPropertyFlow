<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RolePermissionController extends Controller
{
    /**
     * Display listing of roles with assigned permissions count and user tallies.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->hasPermission('system.manage_roles')) {
            abort(403, 'Unauthorized. Only Super Administrators can manage organizational roles.');
        }

        $roles = Role::withCount(['users', 'permissions'])->orderBy('is_system', 'desc')->orderBy('name')->get();
        $permissions = Permission::all()->groupBy('module');

        return view('settings.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Store newly created custom role.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->hasPermission('system.manage_roles')) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,slug',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name'], '_'),
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('settings.roles.index')->with('success', "Role '{$role->name}' created successfully with " . count($validated['permissions'] ?? []) . " permissions.");
    }

    /**
     * Show edit form for an existing role.
     */
    public function edit(Role $role)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->hasPermission('system.manage_roles')) {
            abort(403, 'Unauthorized.');
        }

        $permissions = Permission::all()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('slug')->toArray();

        return view('settings.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update permissions and details of a role.
     */
    public function update(Request $request, Role $role)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->hasPermission('system.manage_roles')) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,slug',
        ]);

        // Safety lock: Super admin root slug and permissions must never be disabled
        if ($role->slug === 'super_admin') {
            $role->update([
                'description' => $validated['description'] ?? $role->description,
            ]);
            return redirect()->route('settings.roles.index')->with('success', 'Super Administrator settings updated.');
        }

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('settings.roles.index')->with('success', "Permissions for '{$role->name}' updated successfully.");
    }

    /**
     * Delete custom role safely (reassigning active users).
     */
    public function destroy(Role $role)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->hasPermission('system.manage_roles')) {
            abort(403, 'Unauthorized.');
        }

        if ($role->is_system || $role->slug === 'super_admin') {
            return back()->withErrors(['error' => 'System default roles are protected and cannot be deleted.']);
        }

        // Reassign any attached users to default marketer role before deletion
        $defaultFallbackRole = Role::where('slug', 'sales_executive')->first();
        if ($defaultFallbackRole) {
            User::where('role_id', $role->id)->update([
                'role_id' => $defaultFallbackRole->id,
                'role' => $defaultFallbackRole->slug
            ]);
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('settings.roles.index')->with('success', "Role '{$role->name}' deleted successfully.");
    }
}
