@extends('layouts.app')

@section('content')
<div class="space-y-8" x-data="{ addProjectOpen: false }">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Off-Plan Projects</h1>
            <p class="text-sm text-gray-500 mt-1">Track development phases, construction milestones, and total unit pipelines.</p>
        </div>
        @if(Auth::user()->role !== 'sales_executive')
        <div>
            <button @click="addProjectOpen = true" class="inline-flex items-center space-x-2 px-5 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/10 hover:shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Create New Project</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Projects Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($projects as $project)
        <div class="bg-white rounded-3xl border border-gray-150 shadow-sm overflow-hidden flex flex-col hover:border-gray-300 hover:shadow-md transition-all duration-300">
            <!-- Project Cover / Status -->
            <div class="h-44 bg-slate-100 relative overflow-hidden flex items-center justify-center">
                @if(!empty($project->images) && isset($project->images[0]))
                <img src="{{ asset('storage/' . $project->images[0]) }}" alt="{{ $project->name }}" class="w-full h-full object-cover">
                @else
                <span class="p-4 bg-brand-50 text-brand-500 rounded-full">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </span>
                @endif
                
                <span class="absolute top-4 left-4 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-white/95 text-dark-800 rounded-lg shadow-sm border border-gray-100">
                    {{ str_replace('_', ' ', $project->type) }}
                </span>

                <span class="absolute top-4 right-4 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider 
                    @if($project->status === 'completed') bg-emerald-100 text-emerald-800
                    @elseif($project->status === 'in_progress') bg-blue-100 text-blue-800
                    @elseif($project->status === 'planning') bg-yellow-100 text-yellow-800
                    @else bg-gray-100 text-gray-800 @endif rounded-lg shadow-sm">
                    {{ str_replace('_', ' ', $project->status) }}
                </span>
            </div>

            <!-- Project Details -->
            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="flex items-start justify-between">
                        <h3 class="font-extrabold text-dark-900 text-lg leading-tight hover:text-brand-500 transition-colors">
                            <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a>
                        </h3>
                    </div>
                    <div class="text-xs text-gray-550 flex items-center space-x-1">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>{{ $project->location }}</span>
                    </div>
                    <div class="text-xs text-gray-400">
                        <span>Developer: <strong class="text-gray-600">{{ $project->developer ?? 'NAW Properties' }}</strong></span>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="space-y-1">
                    <div class="flex justify-between items-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        <span>Completion</span>
                        <span class="text-dark-800">{{ $project->completion_percentage }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-brand-500 h-2 rounded-full transition-all duration-500" style="width: {{ $project->completion_percentage }}%"></div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="flex space-x-4">
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Units</span>
                            <span class="text-xs font-bold text-gray-700">{{ $project->total_units }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Milestones</span>
                            <span class="text-xs font-bold text-gray-750">{{ $project->milestones_count }} Stages</span>
                        </div>
                    </div>

                    <div class="flex space-x-1">
                        <a href="{{ route('projects.show', $project) }}" class="px-3 py-1.5 bg-gray-50 hover:bg-brand-50 text-gray-700 hover:text-brand-600 border border-gray-200 hover:border-brand-200 rounded-lg text-xs font-bold transition-all">
                            Manage
                        </a>
                        @if(Auth::user()->role !== 'sales_executive')
                        <a href="{{ route('projects.edit', $project) }}" class="p-1.5 bg-gray-50 text-gray-600 hover:bg-gray-100 border border-gray-200 rounded-lg" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-12 text-gray-500">
            <span class="p-4 bg-gray-50 text-gray-400 rounded-full inline-block mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </span>
            <h4 class="text-sm font-bold text-dark-900">No off-plan projects recorded</h4>
            <p class="text-xs text-gray-400 mt-1">Please create a new project to start tracking development milestones.</p>
        </div>
        @endforelse
    </div>

    @if($projects->hasPages())
    <div class="bg-gray-50 border-t border-gray-150 p-4 rounded-2xl">
        {{ $projects->links() }}
    </div>
    @endif

    <!-- Add Project Modal -->
    <div x-cloak x-show="addProjectOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-4" @click.away="addProjectOpen = false">
            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Create Off-Plan Project</h3>
                <button @click="addProjectOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <form action="{{ route('projects.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Project Name *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. Orange Valley Phase 2">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Developer / Contractor</label>
                    <input type="text" name="developer" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. NAW Properties Ltd">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Location *</label>
                    <input type="text" name="location" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. Lekki Phase 1, Lagos">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Project Type *</label>
                        <select name="type" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="mixed_use">Mixed Use</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Project Status *</label>
                        <select name="status" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                            <option value="planning">Planning</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="on_hold">On Hold</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Start Date</label>
                        <input type="date" name="start_date" class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Expected Completion</label>
                        <input type="date" name="expected_completion_date" class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Total Target Units</label>
                        <input type="number" name="total_units" min="0" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="100">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Land Size (SQM)</label>
                        <input type="number" step="0.01" name="land_size_sqm" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="5000">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Project Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-lg text-xs bg-white resize-none" placeholder="Enter project descriptions & target buyer profiles..."></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="addProjectOpen = false" class="text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg shadow-sm">Save Project</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
