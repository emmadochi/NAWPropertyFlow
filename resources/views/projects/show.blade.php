@extends('layouts.app')

@section('content')
<div class="space-y-8" x-data="{ addMilestoneOpen: false }">

    <!-- Back to Projects & Title Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <a href="{{ route('projects.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Projects List</span>
            </a>
            <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">{{ $project->name }}</h1>
            <p class="text-sm text-gray-550 mt-1">
                Off-Plan Portfolio managed by <strong class="text-gray-700">{{ $project->developer ?? 'NAW Properties' }}</strong>.
            </p>
        </div>
        <div class="flex space-x-2">
            @if(Auth::user()->role !== 'sales_executive')
            <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-white border border-gray-250 text-gray-700 font-bold text-xs rounded-xl shadow-sm hover:bg-gray-50 transition-all">
                <svg class="w-4 h-4 text-gray-550" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>Edit Project Details</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Quick Analytics Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Progress Card -->
        <div class="bg-white p-5 rounded-3xl border border-gray-150 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Completion Weight</span>
            <div class="mt-2 flex items-baseline space-x-1">
                <span class="text-2xl font-extrabold text-dark-900">{{ $project->completion_percentage }}%</span>
                <span class="text-xs text-gray-400">weighted</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-3">
                <div class="bg-brand-500 h-1.5 rounded-full" style="width: {{ $project->completion_percentage }}%"></div>
            </div>
        </div>

        <!-- Available Card -->
        <div class="bg-white p-5 rounded-3xl border border-gray-150 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Available Units</span>
            <div class="mt-2 flex items-baseline space-x-1">
                <span class="text-2xl font-extrabold text-emerald-600">{{ $unitStats['available'] }}</span>
                <span class="text-xs text-gray-400">/ {{ $unitStats['total'] ?: $project->total_units }} total</span>
            </div>
            <span class="text-[10px] text-gray-500 mt-3 font-medium">Ready for allocation</span>
        </div>

        <!-- Reserved Card -->
        <div class="bg-white p-5 rounded-3xl border border-gray-150 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Reserved Units</span>
            <div class="mt-2 flex items-baseline space-x-1">
                <span class="text-2xl font-extrabold text-amber-500">{{ $unitStats['reserved'] }}</span>
                <span class="text-xs text-gray-400">under hold</span>
            </div>
            <span class="text-[10px] text-gray-500 mt-3 font-medium">Requires deposit verification</span>
        </div>

        <!-- Sold Card -->
        <div class="bg-white p-5 rounded-3xl border border-gray-150 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sold Units</span>
            <div class="mt-2 flex items-baseline space-x-1">
                <span class="text-2xl font-extrabold text-rose-500">{{ $unitStats['sold'] }}</span>
                <span class="text-xs text-gray-400">sales logged</span>
            </div>
            <span class="text-[10px] text-gray-500 mt-3 font-medium">₦{{ number_format($project->units()->where('status', 'sold')->sum('price'), 2) }} revenue</span>
        </div>
    </div>

    <!-- Main Detail Tabs & Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Project Info & Milestones Timeline -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Description Block -->
            <div class="bg-white rounded-3xl border border-gray-150 p-6 md:p-8 space-y-4">
                <h3 class="font-extrabold text-dark-900 text-lg">Project Scope & Guidelines</h3>
                <p class="text-sm text-gray-650 leading-relaxed bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    {{ $project->description ?: 'No detailed scope recorded yet.' }}
                </p>

                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="block text-gray-400 font-semibold mb-1">Start Date</span>
                        <span class="font-bold text-dark-800">{{ $project->start_date?->format('M d, Y') ?: 'Not set' }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-400 font-semibold mb-1">Target Handover</span>
                        <span class="font-bold text-dark-800">{{ $project->expected_completion_date?->format('M d, Y') ?: 'Not set' }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-400 font-semibold mb-1">Development Phase</span>
                        <span class="font-bold text-dark-850 capitalize">{{ str_replace('_', ' ', $project->status) }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-400 font-semibold mb-1">Total Landmass (SQM)</span>
                        <span class="font-bold text-dark-800">{{ $project->land_size_sqm ? number_format($project->land_size_sqm) . ' SQM' : 'Not set' }}</span>
                    </div>
                </div>
            </div>

            <!-- Milestones Timeline Tracker -->
            <div class="bg-white rounded-3xl border border-gray-150 p-6 md:p-8 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-dark-900 text-lg">Milestones & Phase Timeline</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Construction stages weighting the total completion progress index.</p>
                    </div>
                    @if(Auth::user()->role !== 'sales_executive')
                    <button @click="addMilestoneOpen = true" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-600 text-xs font-bold rounded-lg border border-brand-100 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Add Stage</span>
                    </button>
                    @endif
                </div>

                <div class="relative pl-6 border-l-2 border-gray-100 space-y-6">
                    @forelse($project->milestones as $milestone)
                    <div class="relative">
                        <!-- Bullet indicator -->
                        <span class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full border-2 bg-white flex items-center justify-center
                            @if($milestone->status === 'completed') border-emerald-500 bg-emerald-500
                            @elseif($milestone->status === 'in_progress') border-blue-500
                            @elseif($milestone->status === 'delayed') border-rose-500
                            @else border-gray-300 @endif">
                            @if($milestone->status === 'completed')
                            <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                            @endif
                        </span>

                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-150 flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <h4 class="font-bold text-dark-900 text-sm">{{ $milestone->title }}</h4>
                                    <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded-md bg-white border border-gray-200">
                                        Weight: {{ $milestone->percentage_weight }}%
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 leading-relaxed">{{ $milestone->description }}</p>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 text-[10px] text-gray-400 pt-1">
                                    <span>Due Date: <strong class="text-gray-600">{{ $milestone->due_date->format('M d, Y') }}</strong></span>
                                    @if($milestone->completed_date)
                                    <span>Completed: <strong class="text-emerald-600">{{ $milestone->completed_date->format('M d, Y') }}</strong></span>
                                    @endif
                                    @if($milestone->responsible_party)
                                    <span>Responsible: <strong class="text-gray-600">{{ $milestone->responsible_party }}</strong></span>
                                    @endif
                                </div>
                                @if($milestone->notes)
                                <div class="bg-white p-2 rounded-lg border border-gray-100 text-[10px] text-gray-500 italic mt-2">
                                    Notes: {{ $milestone->notes }}
                                </div>
                                @endif
                            </div>

                            <div class="flex items-center space-x-1.5 flex-shrink-0 self-end md:self-start">
                                @if(Auth::user()->role !== 'sales_executive' && $milestone->status !== 'completed')
                                <form action="{{ route('projects.milestones.update', [$project, $milestone]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-250 rounded-lg text-[10px] font-bold transition-all">
                                        Mark Completed
                                    </button>
                                </form>
                                @endif

                                @if(Auth::user()->role !== 'sales_executive')
                                <form action="{{ route('projects.milestones.destroy', [$project, $milestone]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this milestone stage?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 bg-white hover:bg-rose-50 text-gray-400 hover:text-rose-600 border border-gray-250 hover:border-rose-200 rounded-lg" title="Remove Stage">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-gray-500 -ml-6">
                        <p class="text-xs">No project milestone timeline stages created yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right: Connected Portfolios & Property Units -->
        <div class="space-y-6">
            
            <!-- Associated Portfolios List -->
            <div class="bg-white rounded-3xl border border-gray-150 p-6 space-y-4">
                <h3 class="font-extrabold text-dark-900 text-base">Linked Properties</h3>
                <div class="space-y-3">
                    @forelse($project->properties as $prop)
                    <div class="p-3 bg-gray-50 rounded-2xl border border-gray-150 flex items-center justify-between">
                        <div class="truncate">
                            <h4 class="font-bold text-dark-900 text-xs truncate">{{ $prop->name }}</h4>
                            <span class="text-[10px] text-gray-400 capitalize">{{ $prop->property_type }} • {{ $prop->location }}</span>
                        </div>
                        <a href="{{ route('properties.units.index', $prop) }}" class="px-2 py-1 bg-white hover:bg-brand-50 border border-gray-250 hover:border-brand-200 text-gray-700 hover:text-brand-600 rounded-lg text-[10px] font-bold transition-all">
                            View Units
                        </a>
                    </div>
                    @empty
                    <div class="text-center py-6 text-gray-400 text-xs">
                        No property layouts linked yet. Add a property and link it to this project.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Off-Plan Units Pipeline Summary -->
            <div class="bg-white rounded-3xl border border-gray-150 p-6 space-y-4">
                <h3 class="font-extrabold text-dark-900 text-base">Allocation Pipeline</h3>
                <div class="divide-y divide-gray-100 text-xs">
                    @forelse($project->units->groupBy('status') as $status => $unitsGroup)
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="capitalize font-semibold text-gray-600">{{ $status }} Units</span>
                        <span class="font-bold px-2 py-0.5 rounded-md 
                            @if($status === 'available') bg-emerald-50 text-emerald-700
                            @elseif($status === 'reserved') bg-amber-50 text-amber-700
                            @elseif($status === 'sold') bg-rose-50 text-rose-700
                            @else bg-gray-50 text-gray-700 @endif">
                            {{ $unitsGroup->count() }} Units
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-6 text-gray-400">
                        No unit inventory created for this project's layouts.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- Add Milestone Modal -->
    <div x-cloak x-show="addMilestoneOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-4" @click.away="addMilestoneOpen = false">
            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Add Milestone Stage</h3>
                <button @click="addMilestoneOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <form action="{{ route('projects.milestones.store', $project) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Stage / Milestone Title *</label>
                    <input type="text" name="title" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. Foundation & Piling Complete">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Due Date *</label>
                        <input type="date" name="due_date" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Completion Weight *</label>
                        <input type="number" name="percentage_weight" required min="1" max="100" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. 15">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Initial Status *</label>
                        <select name="status" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="delayed">Delayed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Contractor / Agency</label>
                        <input type="text" name="responsible_party" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. Julius Berger">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Stage Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-lg text-xs bg-white resize-none" placeholder="Provide stage quality inspection targets..."></textarea>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Notes / Quality Logs</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 border rounded-lg text-xs bg-white resize-none" placeholder="Add specific quality requirements..."></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="addMilestoneOpen = false" class="text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg shadow-sm">Save Stage</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
