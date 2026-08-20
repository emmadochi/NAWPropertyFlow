@extends('layouts.app')

@section('title', 'Configure Role Permissions - ' . $role->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-slate-700">
        <div>
            <a href="{{ route('settings.roles.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back to All Roles</span>
            </a>
            <h1 class="text-2xl font-extrabold text-dark-900 dark:text-white tracking-tight">
                Configure Permissions: <span class="text-brand-500">{{ $role->name }}</span>
            </h1>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                Toggle exact operational privileges and data privacy rules for all staff holding this role.
            </p>
        </div>

        @if($role->slug === 'super_admin')
        <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-purple-100 text-purple-700 border border-purple-200">
            Root Admin (All Granted)
        </span>
        @endif
    </div>

    <!-- Edit Permissions Form -->
    <form action="{{ route('settings.roles.update', $role->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-200 dark:border-slate-700 p-6 md:p-8 shadow-sm space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Role Title *</label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" required {{ $role->slug === 'super_admin' ? 'readonly' : '' }}
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-semibold text-gray-800 dark:text-white focus:border-brand-500 focus:outline-none {{ $role->slug === 'super_admin' ? 'bg-gray-100 dark:bg-slate-800 cursor-not-allowed' : '' }}">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Role Description</label>
                    <input type="text" name="description" value="{{ old('description', $role->description) }}" placeholder="e.g. Front-line real estate sales consultant"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-semibold text-gray-800 dark:text-white focus:border-brand-500 focus:outline-none">
                </div>
            </div>

            @if($role->slug !== 'super_admin')
            <div class="space-y-6 pt-4 border-t border-gray-100 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-dark-900 dark:text-white uppercase tracking-wider">
                        Modular Capabilities Matrix
                    </h3>
                    <span class="text-xs font-bold text-gray-400">Check to activate capability</span>
                </div>

                @foreach($permissions as $moduleName => $permList)
                <div class="bg-gray-50 dark:bg-slate-900/60 rounded-2xl p-5 border border-gray-150 dark:border-slate-700 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-gray-200 dark:border-slate-700">
                        <span class="text-xs font-extrabold text-dark-900 dark:text-white">{{ $moduleName }}</span>
                        <span class="text-[10px] text-gray-400">{{ count($permList) }} capabilities</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($permList as $perm)
                        <label class="flex items-start space-x-3 p-3 rounded-xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 cursor-pointer hover:border-brand-400 transition-all">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->slug }}" 
                                   {{ in_array($perm->slug, $rolePermissions) ? 'checked' : '' }}
                                   class="rounded text-brand-500 focus:ring-brand-500 mt-0.5">
                            <div class="text-xs">
                                <div class="font-bold text-dark-900 dark:text-white leading-tight">{{ $perm->name }}</div>
                                <div class="text-[11px] text-gray-400 mt-0.5 leading-tight">{{ $perm->description }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-4 rounded-2xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 text-purple-800 dark:text-purple-300 text-xs">
                🛡️ <strong>Immutable Root Role</strong>: The Super Administrator role holds all system privileges across all modules by default to prevent organizational lockouts.
            </div>
            @endif

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-slate-700">
                <a href="{{ route('settings.roles.index') }}" class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</a>
                <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white font-bold px-6 py-2.5 rounded-xl text-xs shadow-md shadow-brand-500/20 transition-all">
                    Save Permission Changes
                </button>
            </div>

        </div>
    </form>

</div>
@endsection
