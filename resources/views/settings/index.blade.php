@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showModal: false }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">Team Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your company's staff, marketers, and administrators.</p>
        </div>
        <div>
            <button @click="showModal = true" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-colors shadow-sm flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Add Team Member</span>
            </button>
        </div>
    </div>

    <!-- Team Members Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-gray-600">Name</th>
                        <th class="px-6 py-4 font-semibold text-gray-600">Role</th>
                        <th class="px-6 py-4 font-semibold text-gray-600">Department</th>
                        <th class="px-6 py-4 font-semibold text-gray-600">Branch</th>
                        <th class="px-6 py-4 font-semibold text-gray-600">Status</th>
                        <th class="px-6 py-4 font-semibold text-gray-600 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $u)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    {{ substr($u->name, 0, 2) }}
                                </div>
                                <div>
                                    <p class="font-bold text-dark-900">{{ $u->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $u->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg border bg-blue-50 text-blue-600 border-blue-200 uppercase tracking-wider">
                                {{ str_replace('_', ' ', $u->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-700 font-medium">
                            {{ $u->departmentRelation->name ?? $u->department ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-gray-700 font-medium">
                            {{ $u->branch->name ?? 'All Branches' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($u->status === 'active')
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">Active</span>
                            @else
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <form action="{{ route('settings.users.destroy', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this user?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 font-medium text-xs px-2 py-1">Remove</button>
                            </form>
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

    <!-- Add User Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4 overflow-y-auto" x-cloak>
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl p-6 md:p-8 max-h-[90vh] overflow-y-auto custom-sidebar-scroll" @click.away="showModal = false">
            
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-dark-900">Add New Team Member</h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-dark-900 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('settings.users.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Full Name</label>
                        <input type="text" name="name" required class="w-full bg-gray-50 border border-gray-200 text-dark-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block p-3" placeholder="John Doe">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                        <input type="email" name="email" required class="w-full bg-gray-50 border border-gray-200 text-dark-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block p-3" placeholder="john@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Temporary Password</label>
                        <input type="password" name="password" required class="w-full bg-gray-50 border border-gray-200 text-dark-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block p-3" placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Phone Number</label>
                        <input type="text" name="phone_number" class="w-full bg-gray-50 border border-gray-200 text-dark-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block p-3" placeholder="+234...">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Role (System Access)</label>
                        <select name="role" required class="w-full bg-gray-50 border border-gray-200 text-dark-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block p-3">
                            <option value="" disabled selected>Select a role...</option>
                            <option value="sales_executive">Sales Executive (Marketer)</option>
                            <option value="sales_manager">Sales Manager</option>
                            <option value="hr">Human Resources</option>
                            <option value="media_manager">Media / Marketing Manager</option>
                            <option value="project_manager">Project Manager</option>
                            @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin())
                                <option value="company_admin">Company Admin</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Department</label>
                        <select name="department_id" required class="w-full bg-gray-50 border border-gray-200 text-dark-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block p-3">
                            <option value="" disabled selected>Assign to department...</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Branch Assignment</label>
                        <select name="branch_id" class="w-full bg-gray-50 border border-gray-200 text-dark-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block p-3">
                            <option value="">All Branches (Global)</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Commission Rate (%) <span class="font-normal text-gray-400 text-xs">- Optional</span></label>
                        <input type="number" step="0.1" max="100" min="0" name="commission_rate" class="w-full bg-gray-50 border border-gray-200 text-dark-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block p-3" placeholder="e.g. 5.0">
                    </div>

                    <!-- Salary & Compensation Structure -->
                    <div class="col-span-1 md:col-span-2 pt-4 border-t border-gray-100">
                        <h4 class="text-xs font-bold text-dark-900 uppercase tracking-wider mb-3">Agreed Salary & Compensation (Optional)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Agreed Base Salary (₦/Month)</label>
                                <input type="number" step="0.01" min="0" name="base_salary" class="w-full bg-gray-50 border border-gray-200 text-dark-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block p-3" placeholder="e.g. 250000">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Housing Allowance (₦)</label>
                                <input type="number" step="0.01" min="0" name="housing_allowance" class="w-full bg-gray-50 border border-gray-200 text-dark-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block p-3" placeholder="e.g. 50000">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Transport Allowance (₦)</label>
                                <input type="number" step="0.01" min="0" name="transport_allowance" class="w-full bg-gray-50 border border-gray-200 text-dark-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block p-3" placeholder="e.g. 30000">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-xl transition-colors">Create Team Member</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
