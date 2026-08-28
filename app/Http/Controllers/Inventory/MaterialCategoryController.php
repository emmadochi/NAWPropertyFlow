<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\MaterialCategory;
use Illuminate\Http\Request;

class MaterialCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = MaterialCategory::withCount('materials');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->orderBy('name')->paginate(25);

        return view('inventory.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $slug = MaterialCategory::generateUniqueSlug($validated['name']);

        $category = MaterialCategory::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Category '{$category->name}' created successfully.",
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
            ]);
        }

        return redirect()->route('inventory.categories.index')
            ->with('success', "Category '{$category->name}' added successfully.");
    }

    public function update(Request $request, MaterialCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('inventory.categories.index')
            ->with('success', "Category '{$category->name}' updated successfully.");
    }

    public function destroy(MaterialCategory $category)
    {
        $materialCount = $category->materials()->count();

        if ($materialCount > 0) {
            return back()->with('error', "Cannot delete category '{$category->name}' because {$materialCount} material SKU(s) are currently assigned to it. Please reassign those materials first or deactivate the category.");
        }

        $categoryName = $category->name;
        $category->delete();

        return redirect()->route('inventory.categories.index')
            ->with('success', "Category '{$categoryName}' removed successfully.");
    }
}
