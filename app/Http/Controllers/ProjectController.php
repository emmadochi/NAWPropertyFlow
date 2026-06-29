<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Property;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::withCount(['units', 'milestones'])
            ->with(['milestones' => fn($q) => $q->orderBy('due_date')])
            ->latest()
            ->paginate(12);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                     => 'required|string|max:255',
            'developer'                => 'nullable|string|max:255',
            'location'                 => 'required|string|max:255',
            'type'                     => 'required|in:residential,commercial,mixed_use',
            'description'              => 'nullable|string',
            'start_date'               => 'nullable|date',
            'expected_completion_date' => 'nullable|date|after_or_equal:start_date',
            'status'                   => 'required|in:planning,in_progress,completed,on_hold,cancelled',
            'total_units'              => 'nullable|integer|min:0',
            'land_size_sqm'            => 'nullable|numeric|min:0',
            'amenities'                => 'nullable|array',
            'completion_percentage'    => 'nullable|integer|min:0|max:100',
        ]);

        $validated['amenities'] = $request->input('amenities', []);

        $project = Project::create($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', "Project \"{$project->name}\" created successfully.");
    }

    public function show(Project $project)
    {
        $project->load([
            'milestones',
            'units.property',
            'properties',
        ]);

        $unitStats = [
            'available' => $project->units()->where('status', 'available')->count(),
            'reserved'  => $project->units()->where('status', 'reserved')->count(),
            'sold'      => $project->units()->where('status', 'sold')->count(),
            'total'     => $project->units()->count(),
        ];

        return view('projects.show', compact('project', 'unitStats'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'                     => 'required|string|max:255',
            'developer'                => 'nullable|string|max:255',
            'location'                 => 'required|string|max:255',
            'type'                     => 'required|in:residential,commercial,mixed_use',
            'description'              => 'nullable|string',
            'start_date'               => 'nullable|date',
            'expected_completion_date' => 'nullable|date',
            'actual_completion_date'   => 'nullable|date',
            'status'                   => 'required|in:planning,in_progress,completed,on_hold,cancelled',
            'total_units'              => 'nullable|integer|min:0',
            'land_size_sqm'            => 'nullable|numeric|min:0',
            'amenities'                => 'nullable|array',
            'completion_percentage'    => 'nullable|integer|min:0|max:100',
        ]);

        $validated['amenities'] = $request->input('amenities', []);
        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')
            ->with('success', 'Project deleted.');
    }

    // ── Milestones ────────────────────────────────────────────────────────────

    public function storeMilestone(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'due_date'           => 'required|date',
            'status'             => 'required|in:pending,in_progress,completed,delayed',
            'percentage_weight'  => 'nullable|integer|min:0|max:100',
            'responsible_party'  => 'nullable|string|max:255',
            'notes'              => 'nullable|string',
        ]);

        $project->milestones()->create($validated);
        $project->recalculateCompletion();

        return back()->with('success', 'Milestone added.');
    }

    public function updateMilestone(Request $request, Project $project, ProjectMilestone $milestone)
    {
        $validated = $request->validate([
            'status'          => 'required|in:pending,in_progress,completed,delayed',
            'completed_date'  => 'nullable|date',
            'notes'           => 'nullable|string',
        ]);

        $milestone->update($validated);

        if ($validated['status'] === 'completed' && !$milestone->completed_date) {
            $milestone->update(['completed_date' => now()]);
        }

        $project->recalculateCompletion();

        return back()->with('success', 'Milestone updated.');
    }

    public function destroyMilestone(Project $project, ProjectMilestone $milestone)
    {
        $milestone->delete();
        $project->recalculateCompletion();
        return back()->with('success', 'Milestone removed.');
    }
}
