@extends('layouts.app')

@section('content')
<div class="p-6 md:p-8 space-y-8 max-w-7xl mx-auto" x-data="{
    activeModules: {{ json_encode($activeKeys) }},
    hasModule(key) {
        return this.activeModules.includes(key);
    },
    toggleModule(key) {
        if (key === 'crm') return; // Core CRM is mandatory
        if (this.hasModule(key)) {
            this.activeModules = this.activeModules.filter(m => m !== key);
        } else {
            this.activeModules.push(key);
        }
    },
    applyPreset(preset) {
        if (preset === 'crm_only') {
            this.activeModules = ['crm', 'payment_plans'];
        } else if (preset === 'sales_hr') {
            this.activeModules = ['crm', 'payment_plans', 'hr', 'leaderboard', 'marketing', 'docs', 'customer_portal'];
        } else if (preset === 'full_enterprise') {
            this.activeModules = [
                'crm', 'payment_plans', 'inventory', 'accounting', 
                'hr', 'docs', 'file_manager', 'marketing', 
                'multi_branch', 'leaderboard', 'department_setup', 
                'activity_logs', 'customer_portal', 'advanced_reports'
            ];
        }
    }
}">

    <!-- Top Breadcrumb & Developer Status Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">
                <span class="px-2 py-0.5 rounded-md bg-slate-900 text-amber-400 border border-amber-500/30 flex items-center space-x-1">
                    <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                    <span>Developer Master Center</span>
                </span>
                <span>•</span>
                <span>Tenant Isolation & Feature Flags</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                <span>Platform Feature Switchboard</span>
                <span class="text-xs font-extrabold px-3 py-1 rounded-full bg-brand-500/10 text-brand-600 border border-brand-500/20">
                    {{ $companySetting->company_name }}
                </span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                As the platform owner, you have master authority to enable or disable entire subsystems for this client workspace.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('settings.index') }}" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 hover:bg-gray-100 dark:hover:bg-slate-800 text-xs font-bold text-gray-700 dark:text-slate-200 transition-all flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Settings</span>
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-sm font-semibold flex items-center justify-between shadow-sm">
        <div class="flex items-center space-x-2.5">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Control Master Cockpit & Fast Presets Bar -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-dark-950 rounded-3xl p-6 md:p-8 text-white shadow-2xl border border-slate-700/60 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2 max-w-xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold">
                    <span>👑 Master Package Tier:</span>
                    <span class="uppercase tracking-wider font-extrabold">{{ $companySetting->package_tier ?? 'enterprise' }}</span>
                </div>
                <h3 class="text-xl font-bold text-white">Active Subsystems Overview</h3>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Modules turned <strong class="text-emerald-400">OFF</strong> will be completely hidden from the client's sidebar, dashboard navigation, and API access. Turning them <strong class="text-emerald-400">ON</strong> instantly reactivates full functionality without restarting or code redeployment.
                </p>
                <div class="pt-2 flex items-center gap-4 text-xs font-bold">
                    <span class="text-emerald-400 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span x-text="activeModules.length + ' / {{ count($allModules) }} Modules Active'"></span>
                    </span>
                </div>
            </div>

            <!-- Quick Presets -->
            <div class="bg-slate-800/80 backdrop-blur rounded-2xl p-5 border border-slate-700 flex flex-col gap-3 min-w-[280px]">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Quick 1-Tap Presets:</span>
                
                <button type="button" @click="applyPreset('crm_only')" class="px-3.5 py-2 rounded-xl text-left bg-slate-700/60 hover:bg-slate-700 border border-slate-600 text-xs font-bold text-white transition-all flex items-center justify-between">
                    <span>🏡 Lean Real Estate CRM Only</span>
                    <span class="text-[10px] px-2 py-0.5 bg-slate-600 rounded-md text-amber-300">Basic</span>
                </button>

                <button type="button" @click="applyPreset('sales_hr')" class="px-3.5 py-2 rounded-xl text-left bg-slate-700/60 hover:bg-slate-700 border border-slate-600 text-xs font-bold text-white transition-all flex items-center justify-between">
                    <span>👥 Sales + HR + Marketing</span>
                    <span class="text-[10px] px-2 py-0.5 bg-slate-600 rounded-md text-blue-300">Pro</span>
                </button>

                <button type="button" @click="applyPreset('full_enterprise')" class="px-3.5 py-2 rounded-xl text-left bg-brand-500/20 hover:bg-brand-500/30 border border-brand-500/40 text-xs font-bold text-brand-300 transition-all flex items-center justify-between">
                    <span>🏗️ Full Suite (Inventory + Accounting)</span>
                    <span class="text-[10px] px-2 py-0.5 bg-brand-500 text-white rounded-md">Enterprise</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Module Switchboard Form -->
    <form action="{{ route('developer.modules.update') }}" method="POST" class="space-y-8">
        @csrf

        @foreach($categories as $categoryName => $modules)
        <div class="space-y-4">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-500"></span>
                    <h2 class="text-base font-extrabold text-slate-800 dark:text-white uppercase tracking-wider">{{ $categoryName }}</h2>
                </div>
                <span class="text-xs text-gray-500 dark:text-slate-400 font-medium">{{ count($modules) }} Subsystems</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($modules as $key => $mod)
                <div class="rounded-2xl p-5 border transition-all relative overflow-hidden flex flex-col justify-between"
                     :class="hasModule('{{ $key }}') 
                        ? 'bg-white dark:bg-slate-900 border-brand-500/40 dark:border-brand-500/30 shadow-md ring-1 ring-brand-500/20' 
                        : 'bg-gray-50/70 dark:bg-slate-900/40 border-gray-200 dark:border-slate-800 opacity-60 hover:opacity-100'">
                    
                    <div>
                        <!-- Header -->
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                     :class="hasModule('{{ $key }}') ? 'bg-brand-50 text-brand-600 dark:bg-slate-800 dark:text-brand-400' : 'bg-gray-200 text-gray-400 dark:bg-slate-800'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $mod['icon'] }}"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">
                                        {{ $mod['name'] }}
                                    </h3>
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Key: <code class="text-brand-600 dark:text-brand-400 font-mono">{{ $key }}</code></span>
                                </div>
                            </div>

                            <!-- Live Toggle Switch -->
                            @if(!empty($mod['core']))
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 text-[10px] font-extrabold uppercase tracking-wider flex-shrink-0">
                                    CORE MANDATORY
                                </span>
                                <input type="hidden" name="modules[]" value="crm">
                            @else
                                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                    <input type="checkbox" 
                                           name="modules[]" 
                                           value="{{ $key }}" 
                                           class="sr-only peer"
                                           :checked="hasModule('{{ $key }}')"
                                           @change="toggleModule('{{ $key }}')">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-500"></div>
                                </label>
                            @endif
                        </div>

                        <!-- Description -->
                        <p class="text-xs text-gray-500 dark:text-slate-400 leading-relaxed mt-2">
                            {{ $mod['description'] }}
                        </p>
                    </div>

                    <!-- Bottom Status Indicator -->
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-slate-800 flex items-center justify-between text-[11px]">
                        <span class="font-bold" :class="hasModule('{{ $key }}') ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'">
                            <span x-show="hasModule('{{ $key }}')">● Active on Workspace</span>
                            <span x-show="!hasModule('{{ $key }}')">○ Disabled &amp; Hidden</span>
                        </span>
                        
                        @if($key === 'inventory')
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300">
                            12 Sub-Pages
                        </span>
                        @elseif($key === 'accounting')
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                            P&amp;L + Treasury + Tax
                        </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <!-- Floating Action Dock -->
        <div class="sticky bottom-6 z-30 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md p-4 md:p-5 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center space-x-3 text-xs text-gray-500 dark:text-slate-400">
                <svg class="w-5 h-5 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Changes take effect immediately across all users and staff in this tenant.</span>
            </div>

            <div class="flex items-center space-x-3 w-full sm:w-auto">
                <button type="button" onclick="if(confirm('Reset all modules back to standard tier package defaults?')) { document.getElementById('reset-form').submit(); }" class="px-5 py-3 rounded-xl border border-gray-200 dark:border-slate-700 text-xs font-bold text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition-all flex-1 sm:flex-initial text-center">
                    Reset Defaults
                </button>

                <button type="submit" class="px-7 py-3 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-xs shadow-lg shadow-brand-500/25 transition-all flex items-center justify-center space-x-2 flex-1 sm:flex-initial">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Deploy &amp; Save Module Flags</span>
                </button>
            </div>
        </div>
    </form>

    <!-- Hidden Reset Form -->
    <form id="reset-form" action="{{ route('developer.modules.reset') }}" method="POST" class="hidden">
        @csrf
    </form>

</div>
@endsection
