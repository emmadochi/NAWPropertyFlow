@extends('layouts.app')

@section('title', 'Roles & Granular Permissions Engine')

@section('content')
<div class="space-y-6" x-data="{ addRoleModal: false }">

    <!-- Top Action & Navigation Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-brand-500/10 text-brand-500 border border-brand-500/30">
                    Enterprise RBAC
                </span>
                <span class="text-xs font-semibold text-gray-400">Security &amp; Permissions</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-dark-900 dark:text-white tracking-tight mt-1">
                Roles &amp; Granular Permissions
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-slate-400">
                Create custom company roles, configure modular access rights, and prevent lead theft or unauthorized financial approvals.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button @click="addRoleModal = true" class="inline-flex items-center space-x-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-lg shadow-brand-500/20 transition-all transform hover:scale-105 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Create Custom Role</span>
            </button>
        </div>
    </div>

    <!-- Roles Grid Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($roles as $role)
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-200 dark:border-slate-700 p-6 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-5">
            
            <div class="space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="text-base font-extrabold text-dark-900 dark:text-white">{{ $role->name }}</h3>
                        </div>
                        <span class="text-[11px] font-mono text-gray-400">{{ $role->slug }}</span>
                    </div>

                    @if($role->is_system || $role->slug === 'super_admin')
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-300 border border-purple-200">
                            Root System
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-300 border border-emerald-200">
                            Custom Role
                        </span>
                    @endif
                </div>

                <p class="text-xs text-gray-500 dark:text-slate-400 line-clamp-2">
                    {{ $role->description ?: 'Configured company role with custom operational capabilities.' }}
                </p>

                <!-- Stats Badges -->
                <div class="flex items-center gap-3 pt-1">
                    <div class="flex items-center space-x-1 text-xs text-gray-600 dark:text-slate-300 bg-gray-50 dark:bg-slate-900 px-2.5 py-1 rounded-lg border border-gray-100 dark:border-slate-700">
                        <span class="font-black text-brand-600">{{ $role->users_count }}</span>
                        <span class="text-[11px] text-gray-400">Staff assigned</span>
                    </div>
                    <div class="flex items-center space-x-1 text-xs text-gray-600 dark:text-slate-300 bg-gray-50 dark:bg-slate-900 px-2.5 py-1 rounded-lg border border-gray-100 dark:border-slate-700">
                        <span class="font-black text-emerald-600">
                            {{ $role->slug === 'super_admin' ? 'All (Unrestricted)' : $role->permissions_count }}
                        </span>
                        <span class="text-[11px] text-gray-400">Permissions</span>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="pt-4 border-t border-gray-100 dark:border-slate-700 flex items-center justify-between">
                <a href="{{ route('settings.roles.edit', $role->id) }}" class="inline-flex items-center space-x-1 text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                    <span>Configure Permissions</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>

                @if(!$role->is_system && $role->slug !== 'super_admin')
                <form action="{{ route('settings.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this custom role? Any attached staff will be safely moved to standard Marketer.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-bold">
                        Delete
                    </button>
                </form>
                @endif
            </div>

        </div>
        @endforeach
    </div>

    <!-- Modal: Create New Custom Role -->
    <div x-cloak x-show="addRoleModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-gray-200 dark:border-slate-700 space-y-5 my-8 max-h-[90vh] overflow-y-auto custom-sidebar-scroll"
             @click.away="addRoleModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-slate-700">
                <div class="flex items-center space-x-2.5">
                    <span class="p-2 rounded-xl bg-brand-500/10 text-brand-500 font-bold text-base">🛡️</span>
                    <div>
                        <h3 class="text-base font-extrabold text-dark-900 dark:text-white">Create Custom Company Role</h3>
                        <p class="text-[11px] text-gray-400">Define title and select granular capabilities for this position.</p>
                    </div>
                </div>
                <button @click="addRoleModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('settings.roles.store') }}" method="POST" class="space-y-5">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Role Title *</label>
                        <input type="text" name="name" required placeholder="e.g. Site Engineer / Surveyor"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-semibold text-gray-800 dark:text-white focus:border-brand-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Role Description</label>
                        <input type="text" name="description" placeholder="e.g. Inspects on-site development and logs construction diesel expenses."
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-semibold text-gray-800 dark:text-white focus:border-brand-500 focus:outline-none">
                    </div>
                </div>

                <!-- Permission Modules Accordion / Matrix -->
                <div class="space-y-4 pt-2 border-t border-gray-100 dark:border-slate-700">
                    <label class="block text-[11px] font-extrabold text-dark-900 dark:text-white uppercase tracking-wider">
                        Select Allowed Capabilities &amp; Permissions
                    </label>

                    @foreach($permissions as $moduleName => $permList)
                    <div class="bg-gray-50 dark:bg-slate-900/60 rounded-2xl p-4 border border-gray-150 dark:border-slate-700 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-gray-200 dark:border-slate-700">
                            <span class="text-xs font-extrabold text-dark-900 dark:text-white">{{ $moduleName }}</span>
                            <span class="text-[10px] text-gray-400">{{ count($permList) }} permissions</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            @foreach($permList as $perm)
                            <label class="flex items-start space-x-2.5 p-2 rounded-xl bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 cursor-pointer hover:border-brand-300 transition-all">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->slug }}" class="rounded text-brand-500 focus:ring-brand-500 mt-0.5">
                                <div class="text-[11px]">
                                    <div class="font-bold text-dark-900 dark:text-white leading-tight">{{ $perm->name }}</div>
                                    <div class="text-[10px] text-gray-400 mt-0.5 leading-tight">{{ $perm->description }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-slate-700">
                    <button type="button" @click="addRoleModal = false" class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white font-bold px-5 py-2.5 rounded-xl text-xs shadow-md shadow-brand-500/20">
                        Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
