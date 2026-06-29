<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{


    /**
     * Show the company settings form.
     */
    public function edit()
    {
        $settings = CompanySetting::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'NAW Properties Ltd',
                'email' => 'info@nawproperties.com',
                'phone' => '+234 800 000 0000',
                'address' => 'Lekki Phase 1, Lagos, Nigeria',
            ]
        );

        return view('settings.company', compact('settings'));
    }

    /**
     * Update the company settings.
     */
    public function update(Request $request)
    {
        $settings = CompanySetting::firstOrCreate(['id' => 1]);

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'letterhead_header' => 'nullable|string',
            'letterhead_footer' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $path = $request->file('logo')->store('company', 'public');
            $validated['logo_path'] = $path;
        }

        $settings->update($validated);

        return back()->with('success', 'Company settings updated successfully.');
    }
}
