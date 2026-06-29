@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-150">
        <div>
            <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Project Detail</span>
            </a>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">Edit Project Details</h1>
            <p class="text-xs text-gray-500 mt-1">Modify general parameters, scope of work, and expected timeline.</p>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-3xl border border-gray-150 p-6 md:p-8 shadow-sm">
        <form action="{{ route('projects.update', $project) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase">Project Name *</label>
                <input type="text" name="name" value="{{ old('name', $project->name) }}" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase">Developer / Contractor</label>
                <input type="text" name="developer" value="{{ old('developer', $project->developer) }}" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase">Location *</label>
                <input type="text" name="location" value="{{ old('location', $project->location) }}" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Project Type *</label>
                    <select name="type" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                        <option value="residential" {{ $project->type === 'residential' ? 'selected' : '' }}>Residential</option>
                        <option value="commercial" {{ $project->type === 'commercial' ? 'selected' : '' }}>Commercial</option>
                        <option value="mixed_use" {{ $project->type === 'mixed_use' ? 'selected' : '' }}>Mixed Use</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Project Status *</label>
                    <select name="status" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                        <option value="planning" {{ $project->status === 'planning' ? 'selected' : '' }}>Planning</option>
                        <option value="in_progress" {{ $project->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $project->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="on_hold" {{ $project->status === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="cancelled" {{ $project->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Expected Completion Date</label>
                    <input type="date" name="expected_completion_date" value="{{ old('expected_completion_date', $project->expected_completion_date?->format('Y-m-d')) }}" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Total Target Units</label>
                    <input type="number" name="total_units" value="{{ old('total_units', $project->total_units) }}" min="0" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Land Size (SQM)</label>
                    <input type="number" step="0.01" name="land_size_sqm" value="{{ old('land_size_sqm', $project->land_size_sqm) }}" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase">Project Description</label>
                <textarea name="description" rows="5" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white resize-none">{{ old('description', $project->description) }}</textarea>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-150">
                <button type="button" onclick="history.back()" class="text-xs font-bold text-gray-500 hover:text-gray-700">
                    Cancel & Go Back
                </button>
                <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
