<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentMetric;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with(['metrics', 'hod'])->get();
        $users = User::where('status', 'active')->orderBy('name', 'asc')->get();
        return view('departments.index', compact('departments', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string',
            'icon' => 'required|string|max:10',
            'hod_id' => 'nullable|exists:users,id',
        ]);

        $dept = Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
            'hod_id' => $request->hod_id,
            'is_active' => true,
        ]);

        // Add default manual KPI
        DepartmentMetric::create([
            'department_id' => $dept->id,
            'key' => 'tasks_completed',
            'label' => 'Tasks Completed',
            'unit' => 'count',
            'type' => 'manual',
            'is_active' => true
        ]);

        return redirect()->route('departments.index')->with('success', 'Department created successfully with default KPI.');
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
            'icon' => 'required|string|max:10',
            'hod_id' => 'nullable|exists:users,id',
        ]);

        $department->update($request->only('name', 'description', 'icon', 'hod_id'));

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function toggle(Department $department)
    {
        $department->update([
            'is_active' => !$department->is_active
        ]);

        $status = $department->is_active ? 'activated' : 'deactivated';
        return redirect()->route('departments.index')->with('success', "Department has been {$status}.");
    }

    public function storeMetric(Request $request, Department $department)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'unit' => 'required|string|in:count,currency,percent',
        ]);

        // Generate a clean slug for the metric key
        $key = strtolower(str_replace([' ', '-'], '_', preg_replace('/[^a-zA-Z0-9\s_-]/', '', $request->label)));

        // Ensure key is unique within department
        $exists = $department->metrics()->where('key', $key)->exists();
        if ($exists) {
            $key = $key . '_' . time();
        }

        DepartmentMetric::create([
            'department_id' => $department->id,
            'key' => $key,
            'label' => $request->label,
            'unit' => $request->unit,
            'type' => 'manual', // new custom ones are always manual
            'is_active' => true
        ]);

        return redirect()->route('departments.index')->with('success', 'KPI Metric added successfully.');
    }

    public function toggleMetric(DepartmentMetric $metric)
    {
        $metric->update([
            'is_active' => !$metric->is_active
        ]);

        $status = $metric->is_active ? 'activated' : 'deactivated';
        return redirect()->route('departments.index')->with('success', "KPI Metric has been {$status}.");
    }
}
