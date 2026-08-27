<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DeveloperModuleController extends Controller
{
    /**
     * Display the Developer Master Control Center.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user || (!$user->isSuperAdmin() && $user->role !== 'super_admin' && !$user->isCompanyAdmin())) {
            abort(403, 'Unauthorized access. Developer & Platform Master privileges required.');
        }

        $companySetting = CompanySetting::first() ?? CompanySetting::create([
            'company_name' => 'RICAF Nigeria Limited',
            'package_tier' => 'enterprise',
        ]);

        $allModules = CompanySetting::ALL_MODULES;
        $activeKeys = $companySetting->getActiveModuleKeys();

        // Group modules by category for clean UI tabs/sections
        $categories = [];
        foreach ($allModules as $key => $module) {
            $cat = $module['category'] ?? 'General';
            $categories[$cat][$key] = $module;
        }

        return view('developer.modules', compact('companySetting', 'allModules', 'activeKeys', 'categories'));
    }

    /**
     * Update the tenant active module feature flags.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isSuperAdmin() && $user->role !== 'super_admin' && !$user->isCompanyAdmin())) {
            abort(403, 'Unauthorized. Developer Master credentials required.');
        }

        $companySetting = CompanySetting::first();
        if (!$companySetting) {
            return back()->with('error', 'Company profile not initialized.');
        }

        // Get array of checked module keys
        $modules = $request->input('modules', []);
        
        // Ensure CRM is always kept active as the foundation
        if (!in_array('crm', $modules)) {
            $modules[] = 'crm';
        }

        $companySetting->enabled_modules = array_values(array_unique($modules));
        
        if ($request->filled('package_tier')) {
            $companySetting->package_tier = $request->package_tier;
        }

        $companySetting->save();

        // Bust cache across the system
        Cache::forget('active_company_setting');

        return back()->with('success', 'Tenant feature flags updated successfully. The workspace interface has been realigned.');
    }

    /**
     * Reset modules back to the standard package tier defaults.
     */
    public function resetToTier(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isSuperAdmin() && $user->role !== 'super_admin' && !$user->isCompanyAdmin())) {
            abort(403, 'Unauthorized.');
        }

        $companySetting = CompanySetting::first();
        if ($companySetting) {
            $companySetting->enabled_modules = null;
            $companySetting->save();
            Cache::forget('active_company_setting');
        }

        return back()->with('success', 'Modules reset to default tier configuration.');
    }
}
