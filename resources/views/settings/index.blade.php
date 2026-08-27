@extends('layouts.app')

@section('content')
<div class="space-y-8" x-data="{ 
    showModal: false,
    viewMode: 'departments', // 'departments' or 'table'
    selectedDeptName: '',
    selectedJobTitle: '',
    isDeptHead: false,
    specialistTitles: {{ json_encode($specialistTitles ?? []) }},
    getSuggestions() {
        return this.specialistTitles[this.selectedDeptName] || [];
    },
    setDept(deptName) {
        this.selectedDeptName = deptName;
        const suggestions = this.getSuggestions();
        if (suggestions.length > 0 && !this.selectedJobTitle) {
            this.selectedJobTitle = suggestions[0];
        }
    }
}">

    <!-- Page Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <span class="p-2.5 bg-brand-50 text-brand-600 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Team &amp; Department Directory</h1>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">Manage department heads, specialized team members (Designers, Editors, Engineers, Accountants), and permissions.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <!-- View Mode Switcher -->
            <div class="bg-gray-100 dark:bg-slate-800 p-1 rounded-xl flex items-center border border-gray-200 dark:border-slate-700">
                <button type="button" @click="viewMode = 'departments'" 
                        :class="viewMode === 'departments' ? 'bg-white dark:bg-slate-700 text-brand-600 dark:text-brand-400 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-slate-400 font-medium'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center space-x-1.5">
                    <span>🏢 By Department</span>
                </button>
                <button type="button" @click="viewMode = 'table'" 
                        :class="viewMode === 'table' ? 'bg-white dark:bg-slate-700 text-brand-600 dark:text-brand-400 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-slate-400 font-medium'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center space-x-1.5">
                    <span>📋 All Staff List</span>
                </button>
            </div>

            <button @click="showModal = true" class="bg-brand-600 hover:bg-brand-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-md hover:shadow-lg flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Add Team Member</span>
            </button>
        </div>
    </div>

    <!-- Quick Department Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
        @foreach($departments as $dept)
            @php
                $deptStaffCount = $users->where('department_id', $dept->id)->count() ?: $users->where('department', $dept->name)->count();
            @endphp
            <div class="bg-white dark:bg-slate-800 p-3.5 rounded-2xl border border-gray-100 dark:border-slate-700 text-center shadow-sm">
                <span class="text-[11px] font-bold text-gray-500 dark:text-slate-400 block truncate" title="{{ $dept->name }}">{{ $dept->name }}</span>
                <span class="text-xl font-black text-brand-600 dark:text-brand-400 mt-1 block">{{ $deptStaffCount }}</span>
                <span class="text-[10px] text-gray-400">Staff Members</span>
            </div>
        @endforeach
    </div>

    <!-- VIEW 1: Department Pods View (Default) -->
    <div x-show="viewMode === 'departments'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($departments as $dept)
                @php
                    $deptStaff = $users->filter(function($u) use ($dept) {
                        return $u->department_id == $dept->id || $u->department == $dept->name;
                    });
                    $deptHead = $deptStaff->where('is_department_head', true)->first() ?: $deptStaff->first();
                @endphp
                <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-200 dark:border-slate-700 shadow-sm p-6 space-y-5 flex flex-col justify-between">
                    <div>
                        <!-- Department Header -->
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-slate-700">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="w-3 h-3 rounded-full bg-brand-500"></span>
                                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">{{ $dept->name }}</h3>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $dept->description }}</p>
                            </div>
                            <span class="px-3 py-1 bg-brand-50 dark:bg-slate-700 text-brand-600 dark:text-brand-300 font-extrabold text-xs rounded-xl">
                                {{ $deptStaff->count() }} {{ Str::plural('Member', $deptStaff->count()) }}
                            </span>
                        </div>

                        <!-- Team Members Roster -->
                        <div class="space-y-3 mt-4">
                            @forelse($deptStaff as $staff)
                                <div class="p-3.5 rounded-2xl border transition-all flex items-center justify-between gap-3 {{ $staff->is_department_head ? 'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800/40' : 'bg-gray-50/70 dark:bg-slate-900/50 border-gray-100 dark:border-slate-700' }}">
                                    <div class="flex items-center space-x-3 min-w-0">
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-xs flex-shrink-0 {{ $staff->is_department_head ? 'bg-amber-500 text-white shadow-sm' : 'bg-brand-100 text-brand-700 dark:bg-slate-700 dark:text-slate-200' }}">
                                            {{ substr($staff->name, 0, 2) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <p class="font-extrabold text-xs text-slate-900 dark:text-white truncate">{{ $staff->name }}</p>
                                                @if($staff->is_department_head)
                                                    <span class="px-2 py-0.5 bg-amber-200 text-amber-900 dark:bg-amber-900 dark:text-amber-200 text-[9px] font-black rounded-md uppercase tracking-wider flex-shrink-0">HOD / Lead</span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] font-bold text-brand-600 dark:text-brand-400 mt-0.5 truncate">
                                                {{ $staff->job_title ?: ($staff->roleRelation ? $staff->roleRelation->name : str_replace('_', ' ', $staff->role)) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2 flex-shrink-0">
                                        <a href="{{ route('hr.staff.show', $staff->id) }}" class="px-2.5 py-1 text-[11px] font-bold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-gray-100 border border-gray-200 dark:border-slate-600 rounded-lg transition-all shadow-2xs">
                                            Dossier
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="py-6 text-center text-xs text-gray-400 bg-gray-50/50 dark:bg-slate-900/30 rounded-2xl border border-dashed border-gray-200 dark:border-slate-700">
                                    No staff assigned to this department yet. Click "Add Team Member" above.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="pt-3 border-t border-gray-100 dark:border-slate-700 flex justify-end">
                        <button type="button" @click="setDept('{{ $dept->name }}'); showModal = true;" class="text-xs font-bold text-brand-600 hover:text-brand-700 flex items-center space-x-1">
                            <span>+ Add Specialist to {{ $dept->name }}</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- VIEW 2: All Staff Table View -->
    <div x-show="viewMode === 'table'" class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden" x-cloak>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 dark:bg-slate-900/70 border-b border-gray-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4 font-bold text-xs text-gray-600 dark:text-slate-300 uppercase tracking-wider">Staff Name &amp; Contact</th>
                        <th class="px-6 py-4 font-bold text-xs text-gray-600 dark:text-slate-300 uppercase tracking-wider">Job Title / Role</th>
                        <th class="px-6 py-4 font-bold text-xs text-gray-600 dark:text-slate-300 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-4 font-bold text-xs text-gray-600 dark:text-slate-300 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-4 font-bold text-xs text-gray-600 dark:text-slate-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 font-bold text-xs text-gray-600 dark:text-slate-300 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @forelse($users as $u)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-brand-100 dark:bg-slate-700 text-brand-600 dark:text-brand-300 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    {{ substr($u->name, 0, 2) }}
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $u->name }}</p>
                                        @if($u->is_department_head)
                                            <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[9px] font-black rounded uppercase">Lead</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500">{{ $u->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-xs text-brand-600 dark:text-brand-400 block">
                                {{ $u->job_title ?: ($u->roleRelation ? $u->roleRelation->name : str_replace('_', ' ', $u->role)) }}
                            </span>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">
                                Perm: {{ $u->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-700 dark:text-slate-300 font-semibold text-xs">
                            {{ $u->departmentRelation->name ?? $u->department ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-gray-700 dark:text-slate-300 font-medium text-xs">
                            {{ $u->branch->name ?? 'All Branches' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($u->status === 'active')
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Active</span>
                            @else
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('hr.staff.show', $u->id) }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-brand-600 bg-brand-50 dark:bg-slate-700 dark:text-brand-300 border border-brand-200 dark:border-slate-600 px-3 py-1.5 rounded-xl transition-all mr-2 shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>HR Dossier</span>
                            </a>
                            @if($u->isSuperAdmin())
                                <span class="inline-flex items-center space-x-1 text-xs font-semibold text-gray-400 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 px-2.5 py-1 rounded-lg">
                                    <span>Protected</span>
                                </span>
                            @elseif($u->id === Auth::id())
                                <span class="text-xs font-medium text-gray-400 px-2 py-1">You</span>
                            @else
                                <form action="{{ route('settings.users.destroy', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 font-medium text-xs px-2 py-1">Remove</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            No team members found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add User Modal with Specialist Designations -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 overflow-y-auto" x-cloak>
        <div class="relative w-full max-w-2xl bg-white dark:bg-slate-800 rounded-3xl shadow-2xl p-6 md:p-8 max-h-[90vh] overflow-y-auto custom-sidebar-scroll border border-gray-200 dark:border-slate-700" @click.away="showModal = false">
            
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100 dark:border-slate-700">
                <div class="flex items-center space-x-3">
                    <span class="p-2 bg-brand-50 text-brand-600 rounded-xl font-bold">👤</span>
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Add New Department Specialist / Staff</h3>
                        <p class="text-xs text-gray-400">Configure job title, department assignment, and system permissions.</p>
                    </div>
                </div>
                <button @click="showModal = false" class="text-gray-400 hover:text-dark-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('settings.users.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Full Name *</label>
                        <input type="text" name="name" required class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold rounded-xl focus:border-brand-500 block p-3 outline-none" placeholder="e.g. Tunde Adeleke">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Official Email Address *</label>
                        <input type="email" name="email" required class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold rounded-xl focus:border-brand-500 block p-3 outline-none" placeholder="tunde@ricafltd.com">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Password *</label>
                        <input type="password" name="password" required class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold rounded-xl focus:border-brand-500 block p-3 outline-none" placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Phone Number</label>
                        <input type="text" name="phone_number" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold rounded-xl focus:border-brand-500 block p-3 outline-none" placeholder="+234 801 234 5678">
                    </div>
                    
                    <!-- Department Selection -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Department *</label>
                        <select name="department_id" required @change="setDept($event.target.options[$event.target.selectedIndex].text)" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold rounded-xl focus:border-brand-500 block p-3 outline-none">
                            <option value="" disabled selected>Select Department...</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" :selected="selectedDeptName === '{{ $dept->name }}'">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Role / Access Level -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">System Permission Role *</label>
                        <select name="role" required class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold rounded-xl focus:border-brand-500 block p-3 outline-none">
                            <option value="" disabled selected>Select Role Access...</option>
                            @if(isset($roles) && count($roles) > 0)
                                @foreach($roles as $r)
                                    @if($r->slug !== 'super_admin' || Auth::user()->isSuperAdmin())
                                    <option value="{{ $r->slug }}">{{ $r->name }}</option>
                                    @endif
                                @endforeach
                            @else
                                <option value="sales_executive">Sales Executive</option>
                                <option value="media_manager">Media / Creative Specialist</option>
                                <option value="accountant">Accountant / Finance</option>
                                <option value="hr">Human Resources</option>
                            @endif
                        </select>
                    </div>

                    <!-- Job Title / Specialist Designation -->
                    <div class="col-span-1 md:col-span-2 bg-gray-50 dark:bg-slate-900/60 p-4 rounded-2xl border border-gray-200 dark:border-slate-700 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-extrabold text-slate-800 dark:text-white uppercase tracking-wider">
                                Functional Job Title / Designation *
                            </label>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="is_department_head" value="1" x-model="isDeptHead" class="rounded text-brand-500">
                                <span class="text-xs font-bold text-amber-600 dark:text-amber-400">Mark as Department Lead (HOD)</span>
                            </label>
                        </div>
                        <input type="text" name="job_title" x-model="selectedJobTitle" required 
                               placeholder="e.g. Lead Graphic Designer, Video Editor, Quantity Surveyor..."
                               class="w-full bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-brand-600 dark:text-brand-400 font-extrabold text-xs rounded-xl p-3 outline-none">

                        <!-- Quick Suggestions Chips based on selected Department -->
                        <div x-show="getSuggestions().length > 0" class="pt-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1.5">Quick Suggested Titles:</span>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="title in getSuggestions()" :key="title">
                                    <button type="button" @click="selectedJobTitle = title"
                                            :class="selectedJobTitle === title ? 'bg-brand-500 text-white font-bold' : 'bg-gray-200 dark:bg-slate-700 text-gray-700 dark:text-slate-300'"
                                            class="text-[11px] px-2.5 py-1 rounded-lg transition-all hover:bg-brand-500 hover:text-white"
                                            x-text="title">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Branch Assignment -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Branch Assignment</label>
                        <select name="branch_id" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold rounded-xl focus:border-brand-500 block p-3 outline-none">
                            <option value="">All Branches (Global)</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Commission Rate -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Commission Rate (%) <span class="font-normal text-gray-400 text-xs">- Optional</span></label>
                        <input type="number" step="0.1" max="100" min="0" name="commission_rate" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold rounded-xl focus:border-brand-500 block p-3 outline-none" placeholder="e.g. 5.0">
                    </div>

                    <!-- Salary & Compensation Structure -->
                    <div class="col-span-1 md:col-span-2 pt-4 border-t border-gray-100 dark:border-slate-700">
                        <h4 class="text-xs font-bold text-dark-900 dark:text-white uppercase tracking-wider mb-3">Agreed Salary &amp; Compensation (Optional)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-1">Base Salary (₦/Month)</label>
                                <input type="number" step="0.01" min="0" name="base_salary" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold rounded-xl block p-3 outline-none" placeholder="e.g. 250000">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-1">Housing Allowance (₦)</label>
                                <input type="number" step="0.01" min="0" name="housing_allowance" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold rounded-xl block p-3 outline-none" placeholder="e.g. 50000">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-1">Transport Allowance (₦)</label>
                                <input type="number" step="0.01" min="0" name="transport_allowance" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold rounded-xl block p-3 outline-none" placeholder="e.g. 30000">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-slate-700">
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 text-xs font-bold text-gray-700 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 rounded-xl transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition-all shadow-md">Create Team Member</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
