<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;

class TenantController extends Controller
{
    /**
     * System Admin Dashboard — lists all tenants.
     */
    public function dashboard()
    {
        $tenants = Tenant::latest()->get();
        $stats = [
            'total'        => $tenants->count(),
            'active'       => $tenants->where('is_active', true)->count(),
            'starter'      => $tenants->where('package_tier', 'starter')->count(),
            'professional' => $tenants->where('package_tier', 'professional')->count(),
            'enterprise'   => $tenants->where('package_tier', 'enterprise')->count(),
        ];
        return view('system.dashboard', compact('tenants', 'stats'));
    }

    /**
     * Show the "Register New Company" form.
     */
    public function create()
    {
        return view('system.tenants.create');
    }

    /**
     * Provision a new tenant: creates DB, runs migrations, seeds admin user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'subdomain'    => ['required', 'string', 'alpha_dash', 'max:50', 'unique:domains,domain'],
            'package_tier' => ['required', 'in:starter,professional,enterprise'],
            'admin_name'   => ['required', 'string', 'max:255'],
            'admin_email'  => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);

        // 1. Build subdomain
        $subdomain = Str::slug($validated['subdomain']);
        $domain    = $subdomain . '.' . config('app.domain', 'localhost');

        // 2. Create tenant record in central DB
        $tenant = Tenant::create([
            'id'           => $subdomain,
            'company_name' => $validated['company_name'],
            'package_tier' => $validated['package_tier'],
            'admin_email'  => $validated['admin_email'],
            'admin_name'   => $validated['admin_name'],
            'is_active'    => true,
        ]);

        // 3. Attach domain (using just the subdomain because routes/tenant.php uses InitializeTenancyBySubdomain)
        $tenant->domains()->create(['domain' => $subdomain]);

        // 4. Run tenant migrations & seed initial company_admin user
        $tenant->run(function () use ($validated) {
            // Create the Company Setting record
            \App\Models\CompanySetting::on('tenant')->create([
                'company_name' => $validated['company_name'],
                'email'        => $validated['admin_email'],
                'package_tier' => $validated['package_tier'],
            ]);

            // Create the initial admin user inside the tenant DB
            \App\Models\User::on('tenant')->create([
                'name'     => $validated['admin_name'],
                'email'    => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'role'     => 'company_admin',
            ]);
        });

        return redirect()->route('system.dashboard')
            ->with('success', "Company \"{$validated['company_name']}\" provisioned! They can log in at: http://{$domain}:8000/login");
    }

    /**
     * Upgrade (or downgrade) a tenant's package plan.
     * Updates both the central tenants table AND the tenant's own company_settings.
     */
    public function upgradePlan(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'package_tier' => ['required', 'in:starter,professional,enterprise'],
        ]);

        $oldTier = $tenant->package_tier;
        $newTier = $validated['package_tier'];

        if ($oldTier === $newTier) {
            return back()->with('info', "\"{$tenant->company_name}\" is already on the {$newTier} plan.");
        }

        // 1. Update central tenants table
        $tenant->update(['package_tier' => $newTier]);

        // 2. Update tenant's own company_settings table inside their DB
        $tenant->run(function () use ($newTier) {
            \App\Models\CompanySetting::first()?->update(['package_tier' => $newTier]);
        });

        $tierLabel = ucfirst($newTier);
        return back()->with('success', "✅ \"{$tenant->company_name}\" has been upgraded to the {$tierLabel} plan. Their sidebar features will update on next page load.");
    }

    /**
     * Toggle a tenant's active status (suspend/unsuspend).
     */
    public function toggleStatus(Tenant $tenant)
    {
        $tenant->update(['is_active' => !$tenant->is_active]);
        $status = $tenant->is_active ? 'activated' : 'suspended';
        return back()->with('success', "Company \"{$tenant->company_name}\" has been {$status}.");
    }

    /**
     * Permanently delete a tenant and its database.
     */
    public function destroy(Tenant $tenant)
    {
        $name = $tenant->company_name;
        $tenant->delete(); // stancl/tenancy auto-drops the DB via TenantDeleted event
        return redirect()->route('system.dashboard')
            ->with('success', "Company \"{$name}\" and all its data have been permanently deleted.");
    }
}
