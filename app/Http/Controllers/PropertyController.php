<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties.
     */
    public function index(Request $request)
    {
        $query = Property::query();

        if ($request->filled('type')) {
            $query->where('property_type', $request->type);
        }
        if ($request->filled('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        $properties = $query->with('project')->orderBy('name', 'asc')->paginate(12)->withQueryString();
        $projects = \App\Models\Project::orderBy('name')->get();
        $branches = \App\Models\Branch::orderBy('name')->get();

        return view('properties.index', compact('properties', 'projects', 'branches'));
    }

    /**
     * Store a newly created property.
     */
    public function store(Request $request)
    {
        // Only Admins or Managers can add properties
        if (Auth::user()->role === 'sales_executive') {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'estate_name' => 'nullable|string|max:255',
            'location' => 'required|string|max:255',
            'property_type' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'available_units' => 'required|integer|min:0',
            'total_units' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'is_off_plan' => 'nullable|boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'landmark' => 'nullable|string|max:255',
            'amenities' => 'nullable|array',
            'completion_status' => 'nullable|string|max:100',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Store under storage/app/public/properties
                $path = $image->store('properties', 'public');
                $imagePaths[] = $path;
            }
        }

        $validated['images'] = $imagePaths;
        $validated['is_off_plan'] = $request->has('is_off_plan');
        $validated['amenities'] = $request->input('amenities', []);

        if (!in_array(Auth::user()->role, ['super_admin', 'company_admin'])) {
            $validated['branch_id'] = Auth::user()->branch_id;
        } else {
            if (empty($validated['branch_id']) && session()->has('selected_branch_id') && session('selected_branch_id') !== 'all') {
                $validated['branch_id'] = session('selected_branch_id');
            }
        }

        Property::create($validated);

        return redirect()->route('properties.index')->with('success', 'Property added successfully.');
    }

    /**
     * Update the specified property.
     */
    public function update(Request $request, Property $property)
    {
        if (Auth::user()->role === 'sales_executive') {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'estate_name' => 'nullable|string|max:255',
            'location' => 'required|string|max:255',
            'property_type' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'available_units' => 'required|integer|min:0',
            'total_units' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'is_off_plan' => 'nullable|boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'landmark' => 'nullable|string|max:255',
            'amenities' => 'nullable|array',
            'completion_status' => 'nullable|string|max:100',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $imagePaths = $property->images ?? [];
        
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties', 'public');
                $imagePaths[] = $path;
            }
        }

        $validated['images'] = $imagePaths;
        $validated['is_off_plan'] = $request->has('is_off_plan');
        $validated['amenities'] = $request->input('amenities', []);

        if (!in_array(Auth::user()->role, ['super_admin', 'company_admin'])) {
            unset($validated['branch_id']);
        }

        $property->update($validated);

        return back()->with('success', 'Property updated successfully.');
    }

    /**
     * Remove property from storage.
     */
    public function destroy(Property $property)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'company_admin'])) {
            abort(403, 'Unauthorized.');
        }

        // Delete images from disk
        if ($property->images) {
            foreach ($property->images as $imgPath) {
                Storage::disk('public')->delete($imgPath);
            }
        }

        $property->delete();

        return redirect()->route('properties.index')->with('success', 'Property deleted successfully.');
    }
}
