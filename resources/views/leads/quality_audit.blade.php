@extends('layouts.app')

@section('content')

@php
    $periodLabels = [
        'this_week'  => 'This Week',
        'last_week'  => 'Last Week',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'custom'     => 'Custom Range',
    ];

    /** Health badge helper */
    function healthBadge(string $health): array {
        return match($health) {
            'verified_active'    => ['label' => '✅ Verified Active', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-700'],
            'whatsapp_only'      => ['label' => '💬 WhatsApp Only',  'class' => 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-700'],
            'unreachable'        => ['label' => '⚠️ Unreachable', 'class' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:border-amber-700'],
            'suspected_inactive' => ['label' => '📵 Suspected Inactive', 'class' => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950 dark:text-orange-300 dark:border-orange-700'],
            'flagged_fake'       => ['label' => '🚨 Flagged Fake', 'class' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950 dark:text-rose-300 dark:border-rose-700'],
            'incomplete'         => ['label' => '📱 Incomplete No.', 'class' => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600'],
            'missing'            => ['label' => '❌ No Number', 'class' => 'bg-rose-50 text-rose-600 border-rose-200 dark:bg-rose-950 dark:text-rose-400 dark:border-rose-700'],
            'invalid_prefix'     => ['label' => '❓ Invalid Prefix', 'class' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950 dark:text-purple-300 dark:border-purple-700'],
            default              => ['label' => '⏳ Unknown', 'class' => 'bg-gray-100 text-gray-500 border-gray-200'],
        };
    }

    function tempBadge(string $temp): array {
        return match($temp) {
            'hot'  => ['label' => '🔥 Hot',  'class' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950 dark:text-red-300 dark:border-red-700'],
            'warm' => ['label' => '🌤️ Warm', 'class' => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-950 dark:text-amber-300 dark:border-amber-800'],
            'cold' => ['label' => '❄️ Cold',  'class' => 'bg-slate-100 text-slate-500 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600'],
            default => ['label' => '🌤️ Warm', 'class' => 'bg-amber-50 text-amber-700 border-amber-100'],
        };
    }
@endphp

<div class="space-y-6"
     x-data="{
         filterOpen: false,
         period: '{{ request('period', 'this_week') }}',
         selectedOfficer: '{{ $officerId ?? '' }}'
     }">

    {{-- ═══ HEADER ═══ --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-gray-150 dark:border-slate-800">
        <div>
            <a href="{{ route('leads.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Back to Leads Pipeline</span>
            </a>
            <h1 class="text-2xl font-black text-dark-950 dark:text-white tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </span>
                Lead Quality &amp; Executive Audit Board
            </h1>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                Data quality audit, phone health classification, and sales executive performance breakdown.
                @if($officerId)
                    <span class="font-bold text-brand-600 dark:text-brand-400">Filtered: {{ $officers->find($officerId)?->name ?? 'Officer' }}</span>
                @endif
                · <span class="font-semibold">{{ $periodLabels[$period] ?? $period }}</span>
                ({{ $startDate->format('d M') }} – {{ $endDate->format('d M Y') }})
            </p>
        </div>
        <div class="flex items-center flex-wrap gap-2">
            @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin() || Auth::user()->hasPermission('hr.manage_users'))
                <a href="{{ route('settings.index') }}"
                   class="inline-flex items-center space-x-2 px-3.5 py-2.5 bg-brand-50 hover:bg-brand-100 dark:bg-slate-800 dark:hover:bg-slate-700 border border-brand-200 dark:border-slate-700 text-brand-700 dark:text-brand-300 font-bold text-xs rounded-xl shadow-sm transition-all"
                   title="Add or manage Sales Executives and team members">
                    <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span>+ Add / Manage Executives</span>
                </a>
            @endif

            @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin() || Auth::user()->hasRole('sales_manager'))
                <form action="{{ route('leads.quality-audit.seed-demo') }}" method="POST" class="inline" onsubmit="return confirm('Generate comprehensive test audit data (Hot, Warm, Cold deals, WhatsApp-only prospects, and 3 executive profiles)?');">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl shadow-sm transition-all"
                        title="Generate realistic test data for all charts, donuts, WhatsApp-only leads, and executive risk scores">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span>⚡ Generate Audit Test Data</span>
                    </button>
                </form>

                @if(\App\Models\Lead::withoutGlobalScopes()->where('lead_source', 'like', '[Demo/Test]%')->count() > 0)
                <form action="{{ route('leads.quality-audit.clear-demo') }}" method="POST" onsubmit="return confirm('Wipe all generated test/demo leads? This will safely remove test records without touching real client leads.');" class="inline">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center space-x-1 px-3 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold text-xs rounded-xl transition-all"
                        title="Safely remove all test audit data">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Clear Test Data</span>
                    </button>
                </form>
                @endif
            @endif

            <button @click="filterOpen = !filterOpen"
                class="inline-flex items-center space-x-2 px-4 py-2.5 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-700 dark:text-slate-200 font-bold text-xs rounded-xl shadow-sm hover:bg-gray-50 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span>Filters</span>
            </button>
            <a href="{{ route('leads.quality-audit.export', request()->all()) }}"
               class="inline-flex items-center space-x-2 px-4 py-2.5 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-700 dark:text-slate-200 font-bold text-xs rounded-xl shadow-sm hover:bg-gray-50 transition-all">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    {{-- ═══ FILTER PANEL ═══ --}}
    <div x-show="filterOpen" x-transition x-cloak
         class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <form method="GET" action="{{ route('leads.quality-audit') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Period --}}
            <div>
                <label class="block text-[10px] font-extrabold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Time Period</label>
                <select name="period" x-model="period"
                    class="w-full border border-gray-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm font-medium text-dark-900 dark:text-white dark:bg-slate-800 focus:ring-2 focus:ring-brand-400 focus:border-brand-400 transition-all">
                    <option value="this_week">This Week</option>
                    <option value="last_week">Last Week</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>

            {{-- Custom dates --}}
            <div x-show="period === 'custom'" class="sm:col-span-2 grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-extrabold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                        class="w-full border border-gray-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label class="block text-[10px] font-extrabold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                        class="w-full border border-gray-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm dark:bg-slate-800 dark:text-white focus:ring-2 focus:ring-brand-400">
                </div>
            </div>

            {{-- Executive (manager only) --}}
            @if($isAdminOrManager)
            <div>
                <label class="block text-[10px] font-extrabold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Sales Executive</label>
                <select name="officer_id" x-model="selectedOfficer"
                    class="w-full border border-gray-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm font-medium text-dark-900 dark:text-white dark:bg-slate-800 focus:ring-2 focus:ring-brand-400 transition-all">
                    <option value="">All Executives</option>
                    @foreach($officers as $officer)
                        <option value="{{ $officer->id }}" {{ $officerId == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Health Filter --}}
            <div>
                <label class="block text-[10px] font-extrabold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Lead Health</label>
                <select name="health"
                    class="w-full border border-gray-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm font-medium text-dark-900 dark:text-white dark:bg-slate-800 focus:ring-2 focus:ring-brand-400 transition-all">
                    <option value="">All Health States</option>
                    <option value="whatsapp_only" {{ request('health') === 'whatsapp_only' ? 'selected' : '' }}>💬 WhatsApp Only</option>
                    <option value="flagged" {{ request('health') === 'flagged' ? 'selected' : '' }}>🚨 Flagged Fake</option>
                    <option value="unreachable" {{ request('health') === 'unreachable' ? 'selected' : '' }}>⚠️ Unreachable</option>
                    <option value="uncontacted" {{ request('health') === 'uncontacted' ? 'selected' : '' }}>🕗 Never Contacted</option>
                    <option value="incomplete" {{ request('health') === 'incomplete' ? 'selected' : '' }}>📱 Incomplete Number</option>
                </select>
            </div>

            {{-- Temperature Filter --}}
            <div>
                <label class="block text-[10px] font-extrabold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">Temperature</label>
                <select name="temp"
                    class="w-full border border-gray-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-sm font-medium text-dark-900 dark:text-white dark:bg-slate-800 focus:ring-2 focus:ring-brand-400 transition-all">
                    <option value="">All Temperatures</option>
                    <option value="hot" {{ request('temp') === 'hot' ? 'selected' : '' }}>🔥 Hot Leads</option>
                    <option value="warm" {{ request('temp') === 'warm' ? 'selected' : '' }}>🌤️ Warm Leads</option>
                    <option value="cold" {{ request('temp') === 'cold' ? 'selected' : '' }}>❄️ Cold Leads</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                    Apply Filters
                </button>
                <a href="{{ route('leads.quality-audit') }}"
                    class="py-2.5 px-4 bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-300 font-bold text-xs rounded-xl hover:bg-gray-200 transition-all">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ═══ KPI CARDS ═══ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        {{-- Total Assigned --}}
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <span class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider block mb-1">Total Leads</span>
            <span class="text-2xl font-black text-dark-950 dark:text-white block">{{ number_format($total) }}</span>
            <span class="text-[11px] text-gray-400 dark:text-slate-500 mt-1 block">Assigned in period</span>
        </div>

        {{-- Hot --}}
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <span class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider block mb-1">🔥 Hot Leads</span>
            <span class="text-2xl font-black text-red-600 dark:text-red-400 block">{{ number_format($hot) }}</span>
            <span class="text-[11px] text-gray-400 dark:text-slate-500 mt-1 block">{{ $total > 0 ? round(($hot/$total)*100, 1) : 0 }}% of total</span>
        </div>

        {{-- Contacted --}}
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <span class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider block mb-1">✅ Contacted</span>
            <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 block">{{ number_format($contacted) }}</span>
            <span class="text-[11px] text-gray-400 dark:text-slate-500 mt-1 block">{{ $total > 0 ? round(($contacted/$total)*100,1) : 0 }}% reachable</span>
        </div>

        {{-- Flagged Fake --}}
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <span class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider block mb-1">🚨 Flagged Fake</span>
            <span class="text-2xl font-black text-rose-600 dark:text-rose-400 block">{{ number_format($flagged) }}</span>
            <span class="text-[11px] {{ $total > 0 && $flagged/$total >= 0.2 ? 'text-rose-500 font-bold' : 'text-gray-400 dark:text-slate-500' }} mt-1 block">{{ $total > 0 ? round(($flagged/$total)*100,1) : 0 }}% of total</span>
        </div>

        {{-- Avg Contact Speed --}}
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <span class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider block mb-1">⚡ Contact Speed</span>
            @if($avgContactHours !== null)
                <span class="text-2xl font-black text-blue-600 dark:text-blue-400 block">
                    {{ $avgContactHours < 1 ? '<1' : number_format($avgContactHours, 1) }}h
                </span>
                <span class="text-[11px] text-gray-400 dark:text-slate-500 mt-1 block">Avg hrs to first call</span>
            @else
                <span class="text-2xl font-black text-gray-400 dark:text-slate-500 block">N/A</span>
                <span class="text-[11px] text-gray-400 dark:text-slate-500 mt-1 block">No contacts yet</span>
            @endif
        </div>

        {{-- Conversion Rate --}}
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
            <span class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider block mb-1">🏆 Conv. Rate</span>
            <span class="text-2xl font-black text-brand-600 dark:text-brand-400 block">{{ $convRate }}%</span>
            <span class="text-[11px] text-gray-400 dark:text-slate-500 mt-1 block">{{ number_format($closedWon) }} deals closed</span>
        </div>
    </div>

    {{-- ═══ CHART ROW: HEALTH DONUT + TEMPERATURE DONUT ═══ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Lead Health Quality Donut --}}
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
            <div class="mb-4 pb-3 border-b border-gray-100 dark:border-slate-800">
                <h3 class="text-base font-extrabold text-dark-950 dark:text-white">Lead Health Quality</h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Nigerian phone reachability & fraud classification</p>
            </div>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="healthDonutChart"></canvas>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-slate-800">
                @foreach($healthBreakdown as $label => $count)
                    <div class="text-center">
                        <span class="text-sm font-black text-dark-950 dark:text-white">{{ number_format($count) }}</span>
                        <span class="text-[10px] text-gray-400 dark:text-slate-500 block">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Lead Temperature Donut --}}
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
            <div class="mb-4 pb-3 border-b border-gray-100 dark:border-slate-800">
                <h3 class="text-base font-extrabold text-dark-950 dark:text-white">Lead Temperature</h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Deal urgency &amp; pipeline heat distribution</p>
            </div>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="tempDonutChart"></canvas>
            </div>
            <div class="grid grid-cols-3 gap-3 mt-3 pt-3 border-t border-gray-100 dark:border-slate-800">
                <div class="text-center p-2 bg-red-50 dark:bg-red-950/30 rounded-xl">
                    <span class="text-lg font-black text-red-600 dark:text-red-400">{{ number_format($hot) }}</span>
                    <span class="text-[10px] text-red-500 font-bold block">🔥 Hot</span>
                </div>
                <div class="text-center p-2 bg-amber-50 dark:bg-amber-950/30 rounded-xl">
                    <span class="text-lg font-black text-amber-600 dark:text-amber-400">{{ number_format($warm) }}</span>
                    <span class="text-[10px] text-amber-500 font-bold block">🌤️ Warm</span>
                </div>
                <div class="text-center p-2 bg-slate-100 dark:bg-slate-800/50 rounded-xl">
                    <span class="text-lg font-black text-slate-600 dark:text-slate-300">{{ number_format($cold + $flagged) }}</span>
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block">❄️ Cold</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ EXECUTIVE COMPARISON TABLE (admin/manager only) ═══ --}}
    @if($isAdminOrManager)
    <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-150 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h3 class="text-base font-extrabold text-dark-950 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Executive Lead Quality &amp; Risk Comparison
                </h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Ranked by fake/invalid lead rate and deal conversions. Red rows indicate high risk profile (≥30% flag rate).</p>
            </div>
            <a href="{{ route('settings.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-brand-600 dark:text-brand-400 hover:text-brand-700 bg-brand-50 dark:bg-slate-800 border border-brand-200 dark:border-slate-700 px-3 py-1.5 rounded-xl transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Add / Manage Executives</span>
            </a>
        </div>
        @if(!empty($executiveComparison))
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-slate-800/50 border-b border-gray-150 dark:border-slate-800 text-[10px] font-extrabold text-gray-400 dark:text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Executive</th>
                        <th class="py-3 px-4 text-center">Assigned</th>
                        <th class="py-3 px-4 text-center">🔥 Hot</th>
                        <th class="py-3 px-4 text-center">🚨 Flagged</th>
                        <th class="py-3 px-4 text-center">Fake %</th>
                        <th class="py-3 px-4 text-center">Conv. Rate</th>
                        <th class="py-3 px-4 text-center">Risk Status</th>
                        <th class="py-3 px-4 text-right">Drill Down</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800 text-xs">
                    @foreach($executiveComparison as $i => $exec)
                        <tr class="{{ $exec['is_high_risk'] ? 'bg-rose-50/50 dark:bg-rose-950/20' : 'hover:bg-gray-50/50 dark:hover:bg-slate-800/30' }} transition-colors">
                            <td class="py-3.5 px-4 text-gray-400 dark:text-slate-500 font-bold">{{ $i + 1 }}</td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-dark-900 dark:text-white block">{{ $exec['officer']->name }}</span>
                                <span class="text-[11px] text-gray-400 dark:text-slate-500">{{ $exec['officer']->job_title ?? ucfirst(str_replace('_', ' ', $exec['officer']->role)) }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-black text-dark-900 dark:text-white">{{ number_format($exec['total']) }}</td>
                            <td class="py-3.5 px-4 text-center font-bold text-red-600 dark:text-red-400">{{ number_format($exec['hot']) }}</td>
                            <td class="py-3.5 px-4 text-center font-bold text-rose-600 dark:text-rose-400">{{ number_format($exec['flagged']) }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $exec['fake_pct'] >= 30 ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' : ($exec['fake_pct'] >= 15 ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300') }}">
                                    {{ $exec['fake_pct'] }}%
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-brand-600 dark:text-brand-400">{{ $exec['conv_rate'] }}%</td>
                            <td class="py-3.5 px-4 text-center">
                                @if($exec['is_high_risk'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">⚠️ HIGH RISK</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">✅ Healthy</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('leads.quality-audit', array_merge(request()->all(), ['officer_id' => $exec['officer']->id])) }}"
                                   class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">
                                    Inspect →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <h4 class="text-sm font-extrabold text-dark-900 dark:text-white">No Executive Leads Recorded for {{ $periodLabels[$period] ?? $period }}</h4>
            <p class="text-xs text-gray-500 dark:text-slate-400 max-w-md mx-auto">Either no leads were assigned to sales executives during this date range, or no executive accounts exist yet.</p>
            <div class="flex items-center justify-center flex-wrap gap-3 pt-2">
                <a href="{{ route('settings.index') }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all">
                    + Add Sales Executive
                </a>
                <form action="{{ route('leads.quality-audit.seed-demo') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-sm transition-all">
                        ⚡ Generate Test Data
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ═══ DETAILED LEAD LOG TABLE ═══ --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-150 dark:border-slate-800 shadow-sm overflow-hidden relative"
         x-data="{
             selectedLeads: [],
             selectAll: false,
             pageLeadIds: {{ json_encode($leads->pluck('id')->values()->all()) }},
             inactiveLeadIds: {{ json_encode($leads->filter(fn($l) => $l->unreachable_count > 0 || $l->phone_health === 'whatsapp_only' || $l->is_flagged_fake || is_null($l->last_contacted_at))->pluck('id')->values()->all()) }},
             toggleSelectAll() {
                 this.selectedLeads = this.selectAll ? [...this.pageLeadIds] : [];
             },
             selectInactiveOnPage() {
                 this.selectedLeads = [...this.inactiveLeadIds];
                 this.selectAll = this.pageLeadIds.length > 0 && this.selectedLeads.length === this.pageLeadIds.length;
             },
             updateMasterState() {
                 this.selectAll = this.pageLeadIds.length > 0 && this.selectedLeads.length === this.pageLeadIds.length;
             }
         }">
        <div class="p-5 border-b border-gray-150 dark:border-slate-800 flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h3 class="text-base font-extrabold text-dark-950 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                    Lead Audit Log
                </h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                    {{ number_format($leads->total()) }} leads found. Showing page {{ $leads->currentPage() }} of {{ $leads->lastPage() }}.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="font-semibold text-gray-500 dark:text-slate-400">Quick filter:</span>
                <a href="{{ route('leads.quality-audit', array_merge(request()->except('health'), ['health' => 'whatsapp_only'])) }}"
                   class="px-2.5 py-1 rounded-full border font-bold {{ request('health') === 'whatsapp_only' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-emerald-300 text-emerald-600 dark:text-emerald-400 dark:border-emerald-700 hover:bg-emerald-50' }} transition-all">
                    💬 WhatsApp Only ({{ $whatsappOnly ?? 0 }})
                </a>
                <a href="{{ route('leads.quality-audit', array_merge(request()->except('health'), ['health' => 'flagged'])) }}"
                   class="px-2.5 py-1 rounded-full border font-bold {{ request('health') === 'flagged' ? 'bg-rose-500 text-white border-rose-500' : 'border-rose-300 text-rose-600 dark:text-rose-400 dark:border-rose-700 hover:bg-rose-50' }} transition-all">
                    🚨 Flagged ({{ $flagged }})
                </a>
                <a href="{{ route('leads.quality-audit', array_merge(request()->except('health'), ['health' => 'unreachable'])) }}"
                   class="px-2.5 py-1 rounded-full border font-bold {{ request('health') === 'unreachable' ? 'bg-amber-500 text-white border-amber-500' : 'border-amber-300 text-amber-600 dark:text-amber-400 dark:border-amber-700 hover:bg-amber-50' }} transition-all">
                    ⚠️ Unreachable ({{ $unreachable }})
                </a>
                <a href="{{ route('leads.quality-audit', array_merge(request()->except('health'), ['health' => 'uncontacted'])) }}"
                   class="px-2.5 py-1 rounded-full border font-bold {{ request('health') === 'uncontacted' ? 'bg-slate-600 text-white border-slate-600' : 'border-slate-300 text-slate-600 dark:text-slate-400 dark:border-slate-600 hover:bg-slate-50' }} transition-all">
                    🕗 Never Contacted ({{ $uncontacted }})
                </a>
                @if(request('health') || request('temp'))
                    <a href="{{ route('leads.quality-audit', request()->except(['health', 'temp'])) }}"
                       class="px-2.5 py-1 rounded-full border border-gray-300 dark:border-slate-600 text-gray-600 dark:text-slate-400 font-bold hover:bg-gray-50 transition-all">
                        ✕ Clear filter
                    </a>
                @endif
                @if($isAdminOrManager && $leads->count() > 0)
                    <button type="button"
                            @click="selectInactiveOnPage()"
                            class="px-2.5 py-1 rounded-full border border-orange-300 dark:border-orange-800 bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-300 font-bold hover:bg-orange-100 transition-all flex items-center gap-1">
                        <svg class="w-3 h-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Select Inactive on Page</span>
                    </button>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-slate-800/50 border-b border-gray-150 dark:border-slate-800 text-[10px] font-extrabold text-gray-400 dark:text-slate-400 uppercase tracking-wider">
                        @if($isAdminOrManager)
                            <th class="py-3 px-3 w-10 text-center">
                                <input type="checkbox"
                                       x-model="selectAll"
                                       @change="toggleSelectAll()"
                                       class="rounded border-gray-300 dark:border-slate-600 text-brand-600 focus:ring-brand-500 w-4 h-4 cursor-pointer"
                                       title="Select all on this page">
                            </th>
                        @endif
                        <th class="py-3 px-4">Lead Name</th>
                        <th class="py-3 px-4">Phone</th>
                        <th class="py-3 px-4">Health</th>
                        <th class="py-3 px-4">Temp</th>
                        <th class="py-3 px-4">Status</th>
                        @if($isAdminOrManager)
                            <th class="py-3 px-4">Assigned To</th>
                        @endif
                        <th class="py-3 px-4">Last Contact</th>
                        <th class="py-3 px-4">Last Activity</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800 text-xs">
                    @forelse($leads as $lead)
                        @php
                            $health = $lead->phone_health;
                            $temp   = $lead->computed_temperature;
                            $hBadge = healthBadge($health);
                            $tBadge = tempBadge($temp);
                            $lastAct = $lead->activities->first();
                            $cleanWa = $lead->clean_whatsapp;
                        @endphp
                        <tr class="{{ $lead->is_flagged_fake ? 'bg-rose-50/30 dark:bg-rose-950/10' : 'hover:bg-gray-50/50 dark:hover:bg-slate-800/30' }} transition-colors"
                            :class="selectedLeads.includes({{ $lead->id }}) ? '!bg-brand-50/60 dark:!bg-brand-950/40 border-l-4 border-brand-500' : ''">
                            @if($isAdminOrManager)
                                <td class="py-3.5 px-3 text-center">
                                    <input type="checkbox"
                                           :value="{{ $lead->id }}"
                                           x-model="selectedLeads"
                                           @change="updateMasterState()"
                                           class="rounded border-gray-300 dark:border-slate-600 text-brand-600 focus:ring-brand-500 w-4 h-4 cursor-pointer">
                                </td>
                            @endif
                            <td class="py-3.5 px-4">
                                <a href="{{ route('leads.show', $lead) }}" class="font-bold text-dark-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-400">
                                    {{ $lead->full_name }}
                                </a>
                                @if($lead->is_flagged_fake)
                                    <span class="block text-[10px] text-rose-500 italic" title="{{ $lead->flagged_reason }}">
                                        {{ Str::limit($lead->flagged_reason, 45) }}
                                    </span>
                                @endif
                                <span class="text-[10px] text-gray-400 dark:text-slate-500">{{ $lead->created_at->format('d M Y') }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs text-dark-900 dark:text-slate-200">
                                <div>{{ $lead->phone_number ?? '—' }}</div>
                                @if($cleanWa)
                                    <div class="mt-1">
                                        <a href="https://wa.me/{{ $cleanWa }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200 dark:border-emerald-800 rounded px-1.5 py-0.5 transition-colors"
                                           title="Chat with {{ $lead->full_name }} on WhatsApp">
                                            <svg class="w-3 h-3 fill-current text-emerald-500" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                            <span>{{ $lead->whatsapp_number }}</span>
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $hBadge['class'] }}">
                                    {{ $hBadge['label'] }}
                                </span>
                                @if($lead->unreachable_count > 0)
                                    <span class="block text-[10px] text-orange-500 mt-0.5">{{ $lead->unreachable_count }}× failed calls</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4" x-data="{ temp: '{{ $temp }}' }">
                                {{-- Temperature Chip with AJAX set --}}
                                <div class="inline-flex rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden text-[10px] font-bold"
                                     x-data="{ saving: false }">
                                    @foreach(['hot' => '🔥', 'warm' => '🌤️', 'cold' => '❄️'] as $t => $emoji)
                                        <button type="button"
                                            @click="saving = true; fetch('{{ route('leads.set-temperature', $lead) }}', { method: 'PATCH', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json'}, body: JSON.stringify({temperature: '{{ $t }}'}) }).then(r => r.json()).then(d => { if(d.success) temp = '{{ $t }}'; saving = false; }).catch(() => saving = false)"
                                            :class="temp === '{{ $t }}' ? '{{ $t === 'hot' ? 'bg-red-500 text-white' : ($t === 'warm' ? 'bg-amber-400 text-white' : 'bg-slate-500 text-white') }}' : 'bg-white dark:bg-slate-800 text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-700'"
                                            class="px-2 py-1 transition-all"
                                            title="Set {{ ucfirst($t) }}"
                                            :disabled="saving">{{ $emoji }}</button>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-200">
                                    {{ $lead->status }}
                                </span>
                                @if($lead->lead_source)
                                    <span class="block text-[10px] text-gray-400 mt-0.5">{{ $lead->lead_source }}</span>
                                @endif
                            </td>
                            @if($isAdminOrManager)
                                <td class="py-3.5 px-4">
                                    @if($lead->assignedOfficer)
                                        <span class="font-semibold text-dark-900 dark:text-slate-200">{{ $lead->assignedOfficer->name }}</span>
                                    @else
                                        <span class="text-gray-400 dark:text-slate-500 italic">Unassigned</span>
                                    @endif
                                </td>
                            @endif
                            <td class="py-3.5 px-4">
                                @if($lead->last_contacted_at)
                                    <span class="font-semibold text-dark-900 dark:text-slate-200">{{ $lead->last_contacted_at->diffForHumans() }}</span>
                                    <span class="block text-[10px] text-gray-400 dark:text-slate-500">via {{ ucfirst($lead->last_contact_channel ?? '?') }}</span>
                                @else
                                    <span class="text-rose-500 font-bold text-[10px]">🕗 Never</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 max-w-[180px]">
                                @if($lastAct)
                                    <span class="font-semibold text-[10px] text-brand-600 dark:text-brand-400 block">{{ $lastAct->activity_type }}</span>
                                    <span class="text-[11px] text-gray-500 dark:text-slate-400">{{ Str::limit($lastAct->description, 55) }}</span>
                                @else
                                    <span class="text-gray-300 dark:text-slate-600 italic text-[10px]">No activity logged</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('leads.show', $lead) }}"
                                       class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">
                                        View
                                    </a>

                                    @if($isAdminOrManager)
                                        {{-- Clear Flag --}}
                                        @if($lead->is_flagged_fake)
                                        <form method="POST" action="{{ route('leads.clear-flag', $lead) }}"
                                              onsubmit="return confirm('Clear fraud flag for {{ addslashes($lead->full_name) }}?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                class="text-[10px] font-extrabold text-emerald-600 hover:text-emerald-700 border border-emerald-300 dark:border-emerald-700 rounded-md px-2 py-0.5 hover:bg-emerald-50 transition-all">
                                                Clear Flag
                                            </button>
                                        </form>
                                        @endif

                                        {{-- Reassign --}}
                                        <div x-data="{ open: false }" class="relative">
                                            <button @click="open = !open"
                                                class="text-[10px] font-extrabold text-slate-600 dark:text-slate-300 hover:text-dark-900 border border-gray-200 dark:border-slate-600 rounded-md px-2 py-0.5 hover:bg-gray-100 transition-all">
                                                Reassign
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-transition
                                                class="absolute right-0 z-50 w-52 mt-1 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-lg p-2">
                                                <form method="POST" action="{{ route('leads.reassign', $lead) }}">
                                                    @csrf @method('PATCH')
                                                    <select name="officer_id"
                                                        class="w-full text-xs border border-gray-200 dark:border-slate-600 rounded-lg px-2 py-1.5 mb-2 dark:bg-slate-700 dark:text-white focus:ring-2 focus:ring-brand-400">
                                                        @foreach($officers as $officer)
                                                            <option value="{{ $officer->id }}" {{ $lead->assigned_to == $officer->id ? 'selected' : '' }}>
                                                                {{ $officer->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit"
                                                        class="w-full py-1.5 bg-brand-500 text-white text-xs font-bold rounded-lg hover:bg-brand-600 transition-all">
                                                        Reassign Lead
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdminOrManager ? 10 : 8 }}" class="py-16 text-center text-gray-400 dark:text-slate-500">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-200 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span class="text-sm font-semibold">No leads found matching the current filters.</span>
                                    <a href="{{ route('leads.quality-audit') }}" class="text-brand-600 dark:text-brand-400 text-xs font-bold hover:underline">Reset all filters</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Floating Bulk Action Toolbar --}}
        @if($isAdminOrManager)
        <div x-show="selectedLeads.length > 0"
             x-cloak
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-y-12 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="translate-y-12 opacity-0"
             class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 w-11/12 max-w-2xl bg-dark-950/95 dark:bg-slate-900/95 backdrop-blur-md text-white border border-white/15 dark:border-slate-700/90 rounded-2xl shadow-2xl px-5 py-3.5 flex flex-col sm:flex-row items-center justify-between gap-3">
            
            <div class="flex items-center gap-3">
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-brand-500"></span>
                </span>
                <div>
                    <span class="text-sm font-black text-white" x-text="selectedLeads.length + ' lead' + (selectedLeads.length === 1 ? '' : 's') + ' selected'"></span>
                    <span class="text-[11px] text-gray-400 block">Batch reassign to another executive</span>
                </div>
            </div>

            <form method="POST" action="{{ route('leads.bulk-reassign') }}" class="flex items-center gap-2 w-full sm:w-auto"
                  @submit="if(!confirm('Reassign ' + selectedLeads.length + ' selected lead(s)?')) $event.preventDefault()">
                @csrf
                <template x-for="id in selectedLeads" :key="id">
                    <input type="hidden" name="lead_ids[]" :value="id">
                </template>

                <select name="officer_id" required
                        class="text-xs font-semibold bg-dark-800 dark:bg-slate-800 text-white border border-white/20 dark:border-slate-700 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-400">
                    <option value="">Select Target Rep...</option>
                    @foreach($officers as $officer)
                        <option value="{{ $officer->id }}">{{ $officer->name }}</option>
                    @endforeach
                </select>

                <button type="submit"
                        class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-black rounded-xl shadow-lg transition-all flex items-center gap-1.5 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <span>Reassign</span>
                </button>

                <button type="button" @click="selectedLeads = []; selectAll = false"
                        class="px-3 py-2 bg-white/10 hover:bg-white/20 text-gray-300 hover:text-white text-xs font-bold rounded-xl transition-all whitespace-nowrap">
                    Clear
                </button>
            </form>
        </div>
        @endif

        {{-- Pagination --}}
        @if($leads->hasPages())
            <div class="p-4 border-t border-gray-150 dark:border-slate-800">
                {{ $leads->links() }}
            </div>
        @endif
    </div>

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="fixed bottom-4 right-4 z-50 bg-emerald-600 text-white px-5 py-3.5 rounded-2xl shadow-xl text-sm font-bold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="fixed bottom-4 right-4 z-50 bg-rose-600 text-white px-5 py-3.5 rounded-2xl shadow-xl text-sm font-bold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Chart.js Loader & Donut Initializers -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function () {
        function initAuditCharts() {
            if (typeof Chart === 'undefined') {
                setTimeout(initAuditCharts, 100);
                return;
            }

            const isDark = document.documentElement.classList.contains('dark');
            const border = isDark ? '#0f172a' : '#ffffff';
            const textColor = isDark ? '#94a3b8' : '#64748b';

            // 1. Health Donut Chart
            const healthEl = document.getElementById('healthDonutChart');
            if (healthEl) {
                if (window.__healthDonutChart) {
                    try { window.__healthDonutChart.destroy(); } catch(e){}
                }
                const healthLabels = {!! json_encode(array_keys($healthBreakdown)) !!};
                const healthData   = {!! json_encode(array_values($healthBreakdown)) !!};
                const totalHealth  = healthData.reduce((s, v) => s + v, 0);
                const healthColors = ['#10b981', '#25D366', '#f59e0b', '#f43f5e', '#94a3b8', '#cbd5e1'];

                window.__healthDonutChart = new Chart(healthEl, {
                    type: 'doughnut',
                    data: {
                        labels: healthLabels,
                        datasets: [{
                            data: totalHealth > 0 ? healthData : [1],
                            backgroundColor: totalHealth > 0 ? healthColors : ['#e2e8f0'],
                            borderColor: border,
                            borderWidth: 3,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: textColor,
                                    font: { family: 'Plus Jakarta Sans', size: 11, weight: 'bold' },
                                    padding: 12,
                                    boxWidth: 12
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        if (totalHealth === 0) return 'No leads recorded';
                                        const pct = Math.round((ctx.raw / totalHealth) * 100);
                                        return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '68%'
                    }
                });
            }

            // 2. Temperature Donut Chart
            const tempEl = document.getElementById('tempDonutChart');
            if (tempEl) {
                if (window.__tempDonutChart) {
                    try { window.__tempDonutChart.destroy(); } catch(e){}
                }
                const tempLabels = {!! json_encode(array_keys($temperatureBreakdown)) !!};
                const tempData   = {!! json_encode(array_values($temperatureBreakdown)) !!};
                const totalTemp  = tempData.reduce((s, v) => s + v, 0);

                window.__tempDonutChart = new Chart(tempEl, {
                    type: 'doughnut',
                    data: {
                        labels: tempLabels,
                        datasets: [{
                            data: totalTemp > 0 ? tempData : [1],
                            backgroundColor: totalTemp > 0 ? ['#ef4444', '#f59e0b', '#64748b'] : ['#e2e8f0'],
                            borderColor: border,
                            borderWidth: 3,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: textColor,
                                    font: { family: 'Plus Jakarta Sans', size: 11, weight: 'bold' },
                                    padding: 12,
                                    boxWidth: 12
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        if (totalTemp === 0) return 'No data yet';
                                        const pct = Math.round((ctx.raw / totalTemp) * 100);
                                        return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '68%'
                    }
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAuditCharts);
        } else {
            initAuditCharts();
        }
        setTimeout(initAuditCharts, 150);
        setTimeout(initAuditCharts, 500);
    })();
    </script>

</div>
@endsection
