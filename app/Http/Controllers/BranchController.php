<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{


    /**
     * Display a listing of branches.
     */
    public function index()
    {
        $branches = Branch::orderBy('name', 'asc')->get();
        return view('branches.index', compact('branches'));
    }

    /**
     * Store a newly created branch in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ]);

        Branch::create($validated);

        return back()->with('success', 'Branch created successfully.');
    }

    /**
     * Update the specified branch in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ]);

        $branch->update($validated);

        return back()->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified branch from storage.
     */
    public function destroy(Branch $branch)
    {
        if ($branch->users()->count() > 0 || $branch->leads()->count() > 0 || $branch->properties()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete branch because it has associated users, leads, or properties.']);
        }

        $branch->delete();

        return back()->with('success', 'Branch deleted successfully.');
    }
}
