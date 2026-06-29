@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ editModalOpen: false, activeDept: null, metricModalOpen: false }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">🏢 Department Management</h1>
            <p class="text-xs text-gray-500 mt-1">Configure business units, assign department heads, toggle active status, and define custom KPI metrics.</p>
        </div>
        <button @click="$dispatch('open-create-modal')" class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md shadow-brand-500/10 transition-all flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Department
        </button>
    </div>

    {{-- Departments List --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($departments as $dept)
        <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="text-2xl p-2.5 bg-gray-55 rounded-2xl">{{ $dept->icon }}</span>
                        <div>
                            <h3 class="font-extrabold text-dark-900 text-sm flex items-center gap-2">
                                {{ $dept->name }}
                                @if($dept->is_active)
                                    <span class="w-2 h-2 rounded-full bg-emerald-500" title="Active"></span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-rose-500" title="Deactivated"></span>
                                @endif
                            </h3>
                            <div class="flex flex-col text-[11px] text-gray-400 font-semibold space-y-0.5">
                                <span>{{ $dept->users_count ?? $dept->users()->count() }} Staff Members</span>
                                <span class="text-brand-600 font-bold">Head: {{ $dept->hod->name ?? 'Unassigned' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-1.5">
                        {{-- Toggle Status --}}
                        <form method="POST" action="{{ route('departments.toggle', $dept) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="p-1.5 rounded-lg border border-gray-150 text-xs font-bold transition-all {{ $dept->is_active ? 'bg-rose-50 text-rose-600 hover:bg-rose-100 border-rose-200' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border-emerald-200' }}">
                                {{ $dept->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        <button @click="activeDept = {{ json_encode($dept) }}; editModalOpen = true" class="p-1.5 rounded-lg border border-gray-150 bg-gray-50 hover:bg-gray-100 transition-all text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                    </div>
                </div>

                <p class="text-xs text-gray-500 mt-3">{{ $dept->description ?? 'No description provided.' }}</p>
                
                {{-- Metrics Section --}}
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">KPI Targets Defined</span>
                        <button @click="activeDept = {{ json_encode($dept) }}; metricModalOpen = true" class="text-[10px] font-bold text-brand-600 hover:text-brand-700 flex items-center gap-0.5">
                            + Add Custom Metric
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($dept->metrics as $metric)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold border transition-all {{ $metric->is_active ? 'bg-gray-50 text-gray-700 border-gray-150' : 'bg-gray-50 text-gray-300 border-gray-100 line-through' }}">
                            {{ $metric->label }}
                            <span class="text-gray-400 capitalize">({{ $metric->unit }})</span>
                            <form method="POST" action="{{ route('departments.metrics.toggle', $metric) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-[8px] ml-1 font-extrabold hover:text-brand-500" title="{{ $metric->is_active ? 'Deactivate Metric' : 'Activate Metric' }}">
                                    @if($metric->is_active) ✕ @else ↻ @endif
                                </button>
                            </form>
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Create Modal --}}
    <div x-data="{ open: false }" @open-create-modal.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-dark-900/40">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-xl border border-gray-150 space-y-4" @click.away="open = false">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-dark-900">Create New Department</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-500">✕</button>
            </div>
            <form method="POST" action="{{ route('departments.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Department Name *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg text-xs" placeholder="e.g. Media & Marketing">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Icon Emoji *</label>
                    <input type="text" name="icon" required class="w-full px-3 py-2 border rounded-lg text-xs" placeholder="e.g. 📱">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Head of Department</label>
                    <select name="hod_id" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white text-gray-700 font-semibold">
                        <option value="">Unassigned</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ str_replace('_', ' ', $user->role) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-lg text-xs" placeholder="Write a short summary..."></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="open = false" class="px-4 py-2 border rounded-lg text-xs font-bold text-gray-500">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-xs font-bold">Create</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-dark-900/40">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-xl border border-gray-150 space-y-4" @click.away="editModalOpen = false">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-dark-900">Edit Department</h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-500">✕</button>
            </div>
            <form method="POST" :action="activeDept ? `/settings/departments/${activeDept.id}` : '#'" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Department Name *</label>
                    <input type="text" name="name" required :value="activeDept?.name" class="w-full px-3 py-2 border rounded-lg text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Icon Emoji *</label>
                    <input type="text" name="icon" required :value="activeDept?.icon" class="w-full px-3 py-2 border rounded-lg text-xs">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Head of Department</label>
                    <select name="hod_id" :value="activeDept?.hod_id" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white text-gray-700 font-semibold">
                        <option value="">Unassigned</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-lg text-xs" x-text="activeDept?.description"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 border rounded-lg text-xs font-bold text-gray-500">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-xs font-bold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add KPI Metric Modal --}}
    <div x-show="metricModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-dark-900/40">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-xl border border-gray-150 space-y-4" @click.away="metricModalOpen = false">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-dark-900">Add KPI Metric (<span x-text="activeDept?.name"></span>)</h3>
                <button @click="metricModalOpen = false" class="text-gray-400 hover:text-gray-500">✕</button>
            </div>
            <form method="POST" :action="activeDept ? `/settings/departments/${activeDept.id}/metrics` : '#'" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Metric Label *</label>
                    <input type="text" name="label" required class="w-full px-3 py-2 border rounded-lg text-xs" placeholder="e.g. Videos Shot">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Unit Type *</label>
                    <select name="unit" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white text-gray-700">
                        <option value="count">Count (Numbers)</option>
                        <option value="currency">Currency (₦)</option>
                        <option value="percent">Percentage (%)</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="metricModalOpen = false" class="px-4 py-2 border rounded-lg text-xs font-bold text-gray-500">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-xs font-bold">Add Metric</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
