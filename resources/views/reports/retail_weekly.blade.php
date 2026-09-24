@extends('layouts.app')

@section('content')
@php
    $accentColor = $isBuckcrest ? '#946E19' : '#F37021';
    $accentHover = $isBuckcrest ? '#785712' : '#d95d14';
    $brandSoftBg = $isBuckcrest ? 'bg-amber-500/10 text-amber-500 border-amber-500/20' : 'bg-brand-50 text-brand-600 border-brand-100';
    $brandBadge = $isBuckcrest ? 'bg-[#946E19]/10 text-[#946E19] border-[#946E19]/25' : 'bg-brand-50 text-brand-600 border-brand-100';
@endphp

<div class="space-y-6" x-data="retailScorecardApp()">

    <!-- Top Breadcrumb & Executive Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-4 border-b border-gray-150 dark:border-dark-700">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold text-gray-400 mb-1">
                <a href="{{ route('reports.index') }}" class="hover:text-brand-600 transition-colors">Reports</a>
                <span>/</span>
                <span class="{{ $isBuckcrest ? 'text-[#946E19]' : 'text-brand-600' }}">Executive Retail Matrix</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-black text-dark-900 dark:text-white tracking-tight flex items-center flex-wrap gap-2.5">
                <span>🏛️ Retail Sales Performance Matrix</span>
                <span class="text-xs px-3 py-1 rounded-full font-bold {{ $brandBadge }}">
                    {{ $periodLabel }}
                </span>
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Multi-tier executive scorecard tracking verified field canvassing, customer engagements, site inspections, and realized revenue collections.
            </p>
        </div>

        <!-- Action Toolbar -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Log Field Canvassing Modal Trigger -->
            <button @click="openFieldLogModal()" 
                class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all shadow-sm {{ $isBuckcrest ? 'bg-[#946E19] hover:bg-[#785712] text-white shadow-amber-900/20' : 'bg-brand-600 hover:bg-brand-700 text-white shadow-brand-500/20' }}">
                <span>✏️</span>
                <span>Log Field Canvassing & Notes</span>
            </button>

            <!-- Print Landscape View -->
            <a href="{{ route('reports.retail.print', request()->all()) }}" target="_blank"
                class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-gray-900 hover:bg-dark-900 dark:bg-dark-700 dark:hover:bg-dark-600 text-white font-bold text-xs rounded-xl shadow-sm transition-all">
                <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Print Landscape</span>
            </a>

            <!-- Export to CSV / Excel -->
            <a href="{{ route('reports.retail.export', request()->all()) }}" 
                class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-white dark:bg-dark-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-dark-700 border border-gray-250 dark:border-dark-600 font-bold text-xs rounded-xl shadow-sm transition-all">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Export Excel</span>
            </a>

            <!-- Quick Catch-up Modal Trigger -->
            <button @click="dailyCatchupOpen = true" 
                class="inline-flex items-center space-x-1.5 px-3 py-2 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 font-bold text-xs rounded-xl border border-emerald-200 dark:border-emerald-800/50 transition-all">
                <span>💬</span>
                <span>Quick Pulse</span>
            </button>
        </div>
    </div>

    <!-- Filter & Sprint Selector -->
    <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-150 dark:border-dark-700 p-4 shadow-sm">
        <form method="GET" action="{{ route('reports.retail.index') }}" class="flex flex-wrap items-center gap-3">
            <!-- Period Type Toggle -->
            <div class="flex items-center bg-gray-100 dark:bg-dark-700 p-1 rounded-xl">
                <button type="button" onclick="document.getElementById('period_type_input').value='weekly'; this.form.submit();"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodType === 'weekly' ? 'bg-white dark:bg-dark-800 text-dark-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-dark-900 dark:hover:text-white' }}">
                    Weekly Sprint
                </button>
                <button type="button" onclick="document.getElementById('period_type_input').value='monthly'; this.form.submit();"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodType === 'monthly' ? 'bg-white dark:bg-dark-800 text-dark-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-dark-900 dark:hover:text-white' }}">
                    Monthly Overview
                </button>
            </div>
            <input type="hidden" id="period_type_input" name="period_type" value="{{ $periodType }}">

            <!-- Month Dropdown -->
            <div class="flex items-center space-x-1.5">
                <label class="text-xs font-bold text-gray-500">Month:</label>
                <select name="month" onchange="this.form.submit()" class="bg-gray-50 dark:bg-dark-700 border border-gray-250 dark:border-dark-600 rounded-xl px-2.5 py-1.5 text-xs font-bold text-gray-700 dark:text-gray-200 outline-none">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            @if($periodType === 'weekly')
            <!-- Week of Month Selector -->
            <div class="flex items-center space-x-1.5">
                <label class="text-xs font-bold text-gray-500">Sprint Week:</label>
                <select name="week_number" onchange="this.form.submit()" class="bg-gray-50 dark:bg-dark-700 border border-gray-250 dark:border-dark-600 rounded-xl px-2.5 py-1.5 text-xs font-bold text-gray-700 dark:text-gray-200 outline-none">
                    <option value="all" {{ $activeWeek === 'all' ? 'selected' : '' }}>All Weeks Combined</option>
                    <option value="1" {{ $activeWeek == 1 ? 'selected' : '' }}>Week 1 (Day 1 - 7)</option>
                    <option value="2" {{ $activeWeek == 2 ? 'selected' : '' }}>Week 2 (Day 8 - 14)</option>
                    <option value="3" {{ $activeWeek == 3 ? 'selected' : '' }}>Week 3 (Day 15 - 21)</option>
                    <option value="4" {{ $activeWeek == 4 ? 'selected' : '' }}>Week 4 (Day 22 - 28)</option>
                    <option value="5" {{ $activeWeek == 5 ? 'selected' : '' }}>Week 5 (Day 29 - End)</option>
                </select>
            </div>
            @endif

            <!-- Year Selector -->
            <div class="flex items-center space-x-1.5">
                <label class="text-xs font-bold text-gray-500">Year:</label>
                <select name="year" onchange="this.form.submit()" class="bg-gray-50 dark:bg-dark-700 border border-gray-250 dark:border-dark-600 rounded-xl px-2.5 py-1.5 text-xs font-bold text-gray-700 dark:text-gray-200 outline-none">
                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <!-- Branch Filter -->
            @if($branches->count() > 1)
            <div class="flex items-center space-x-1.5">
                <label class="text-xs font-bold text-gray-500">Branch:</label>
                <select name="branch_id" onchange="this.form.submit()" class="bg-gray-50 dark:bg-dark-700 border border-gray-250 dark:border-dark-600 rounded-xl px-2.5 py-1.5 text-xs font-bold text-gray-700 dark:text-gray-200 outline-none">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <span class="ml-auto text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">
                ✓ 100% Verified CRM Database Data
            </span>
        </form>
    </div>

    <!-- Executive KPI Pulse Overview -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
        <!-- Total Inflow Realized -->
        <div class="col-span-2 md:col-span-2 lg:col-span-2 bg-gradient-to-br from-emerald-600 to-teal-700 text-white rounded-2xl p-4 shadow-sm relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
            <span class="block text-[10px] font-bold text-emerald-100 uppercase tracking-wider">Total Realized Cash Inflow</span>
            <div class="text-2xl lg:text-3xl font-black mt-1">
                ₦{{ number_format($aggregates['total_realized_revenue'], 2) }}
            </div>
            <div class="flex items-center space-x-3 text-[11px] text-emerald-100/90 mt-1 font-medium">
                <span>New: ₦{{ number_format($aggregates['actual_payments_value'], 0) }}</span>
                <span>•</span>
                <span>Top-ups: ₦{{ number_format($aggregates['topups_value'], 0) }}</span>
            </div>
        </div>

        <!-- Closed Deals Count -->
        <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-150 dark:border-dark-700 p-4 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Closed Deals</span>
            <span class="text-xl lg:text-2xl font-black text-amber-600 mt-1 block">
                {{ number_format($aggregates['actual_payments_count']) }}
            </span>
            <span class="text-[10px] text-gray-400 mt-0.5 block">Outright & Initial</span>
        </div>

        <!-- Site Inspections -->
        <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-150 dark:border-dark-700 p-4 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Site Inspections</span>
            <span class="text-xl lg:text-2xl font-black text-purple-600 mt-1 block">
                {{ number_format($aggregates['inspections_count']) }}
            </span>
            <span class="text-[10px] text-gray-400 mt-0.5 block">Physical estate tours</span>
        </div>

        <!-- Office Visits -->
        <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-150 dark:border-dark-700 p-4 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Office Visits</span>
            <span class="text-xl lg:text-2xl font-black text-indigo-600 mt-1 block">
                {{ number_format($aggregates['office_visits_count']) }}
            </span>
            <span class="text-[10px] text-gray-400 mt-0.5 block">Client Walk-ins</span>
        </div>

        <!-- Verified Calls & WhatsApp -->
        <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-150 dark:border-dark-700 p-4 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Calls / WhatsApp</span>
            <div class="flex items-baseline space-x-1.5 mt-1">
                <span class="text-xl lg:text-2xl font-black text-blue-600">{{ number_format($aggregates['calls_count']) }}</span>
                <span class="text-xs text-gray-400 font-bold">/ {{ number_format($aggregates['whatsapp_count']) }}</span>
            </div>
            <span class="text-[10px] text-gray-400 mt-0.5 block">Audited engagements</span>
        </div>

        <!-- New Contacts Captured -->
        <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-150 dark:border-dark-700 p-4 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Contacts Captured</span>
            <span class="text-xl lg:text-2xl font-black text-emerald-600 mt-1 block">
                {{ number_format($aggregates['new_contacts_count']) }}
            </span>
            <span class="text-[10px] text-gray-400 mt-0.5 block">{{ $aggregates['contacts_phone_count'] }} with active phone</span>
        </div>
    </div>

    <!-- Main BSTAN-Model Performance Scorecard Table (10x Enhanced) -->
    <div class="bg-white dark:bg-dark-800 rounded-3xl border border-gray-150 dark:border-dark-700 shadow-sm overflow-hidden">
        <!-- Table Header Bar -->
        <div class="px-6 py-4 border-b border-gray-150 dark:border-dark-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-dark-900 dark:text-white text-base flex items-center gap-2">
                    <span>Retail Sales Team Performance Scorecard Matrix</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-gray-100 dark:bg-dark-700 text-gray-600 dark:text-gray-300">
                        {{ count($matrixRows) }} Sales Personnel
                    </span>
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    Click any highlighted count (Calls, Leads, Deals, Inspections) to inspect genuine CRM transaction audits.
                </p>
            </div>
            
            <div class="flex items-center space-x-2 text-xs text-gray-500">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                <span>Real-time Multi-tenant Sync</span>
            </div>
        </div>

        <!-- Responsive Matrix Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <!-- Tier 1 Super Headers (Exact BSTAN Group Structure) -->
                    <tr class="bg-gray-100/90 dark:bg-dark-900 border-b border-gray-200 dark:border-dark-700 text-[10px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-300">
                        <th colspan="2" class="py-2.5 px-3 border-r border-gray-200 dark:border-dark-700 text-center bg-gray-200/50 dark:bg-dark-950">CONSULTANT PROFILE</th>
                        <th colspan="2" class="py-2.5 px-3 border-r border-gray-200 dark:border-dark-700 text-center bg-blue-50/50 dark:bg-blue-950/20 text-blue-800 dark:text-blue-300">LOCATION(S) / EVENT(S)</th>
                        <th colspan="4" class="py-2.5 px-3 border-r border-gray-200 dark:border-dark-700 text-center bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-300">PAYMENTS / REVENUE (₦)</th>
                        <th colspan="2" class="py-2.5 px-3 border-r border-gray-200 dark:border-dark-700 text-center bg-purple-50/50 dark:bg-purple-950/20 text-purple-800 dark:text-purple-300">ACTIVITIES</th>
                        <th colspan="4" class="py-2.5 px-3 border-r border-gray-200 dark:border-dark-700 text-center bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-800 dark:text-indigo-300">ENGAGEMENTS (VERIFIED)</th>
                        <th colspan="2" class="py-2.5 px-3 text-center bg-amber-50/50 dark:bg-amber-950/20 text-amber-800 dark:text-amber-300">SUMMARY REPORT & OBSERVATIONS</th>
                    </tr>

                    <!-- Tier 2 Granular Metric Columns -->
                    <tr class="bg-gray-50/70 dark:bg-dark-850 border-b border-gray-200 dark:border-dark-700 text-[10px] font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <th class="py-2.5 px-2 text-center w-10">S/N</th>
                        <th class="py-2.5 px-3 min-w-[150px] border-r border-gray-200 dark:border-dark-700">Sales Consultant</th>
                        
                        <!-- Location(s) / Event(s) -->
                        <th class="py-2.5 px-3 min-w-[160px]">Locations Visited / Canvassed</th>
                        <th class="py-2.5 px-2.5 text-center min-w-[70px] border-r border-gray-200 dark:border-dark-700">Office Visits</th>
                        
                        <!-- Payments / Revenue -->
                        <th class="py-2.5 px-2.5 text-right min-w-[110px]">New Sales (₦)</th>
                        <th class="py-2.5 px-2.5 text-right min-w-[110px]">Part / Top-ups (₦)</th>
                        <th class="py-2.5 px-2.5 text-right min-w-[110px]">Expected Inflow (₦)</th>
                        <th class="py-2.5 px-3 text-right min-w-[120px] font-black border-r border-gray-200 dark:border-dark-700 bg-emerald-50/30 dark:bg-emerald-950/10">Total Realized (₦)</th>
                        
                        <!-- Activities -->
                        <th class="py-2.5 px-2 text-center min-w-[65px]">Inspections</th>
                        <th class="py-2.5 px-3 min-w-[130px] border-r border-gray-200 dark:border-dark-700">Projects Inspected</th>
                        
                        <!-- Engagements -->
                        <th class="py-2.5 px-2 text-center min-w-[55px]">Calls</th>
                        <th class="py-2.5 px-2 text-center min-w-[55px]">WhatsApp</th>
                        <th class="py-2.5 px-2 text-center min-w-[45px]">SMS</th>
                        <th class="py-2.5 px-2.5 text-center min-w-[90px] border-r border-gray-200 dark:border-dark-700">Contacts (P/E)</th>
                        
                        <!-- Summary Report & Notes -->
                        <th class="py-2.5 px-3 min-w-[200px]">Observations & Feedback</th>
                        <th class="py-2.5 px-2 text-center w-12">Edit</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-dark-700">
                    @forelse($matrixRows as $idx => $row)
                    @php
                        $user = $row['user'];
                        $isMe = ($currentUser->id === $user->id);
                        $fieldLog = $row['field_log'];
                    @endphp
                    <tr class="hover:bg-gray-50/80 dark:hover:bg-dark-750 transition-colors {{ $isMe ? ($isBuckcrest ? 'bg-amber-500/5' : 'bg-brand-50/20') : '' }}">
                        <!-- S/N -->
                        <td class="py-3 px-2 text-center text-gray-400 font-bold text-[11px]">
                            {{ $idx + 1 }}
                        </td>

                        <!-- Sales Consultant Identity -->
                        <td class="py-3 px-3 border-r border-gray-200 dark:border-dark-700">
                            <div class="flex items-center space-x-2.5">
                                <div class="w-8 h-8 rounded-full {{ $isBuckcrest ? 'bg-[#946E19]/15 text-[#946E19]' : 'bg-brand-100 text-brand-700' }} font-black text-xs flex items-center justify-center flex-shrink-0">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                                <div>
                                    <div class="flex items-center space-x-1.5">
                                        <span class="font-bold text-dark-900 dark:text-white">{{ $user->name }}</span>
                                        @if($isMe)
                                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded {{ $isBuckcrest ? 'bg-[#946E19]/20 text-[#946E19]' : 'bg-brand-100 text-brand-700' }}">You</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-gray-400 block">{{ $user->branch ? $user->branch->name : 'Main Office' }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Locations Visited / Canvassed -->
                        <td class="py-3 px-3 text-[11px]">
                            <div class="text-gray-700 dark:text-gray-300 font-medium leading-relaxed max-w-[180px] break-words">
                                📍 {{ $row['canvassing_locations'] }}
                            </div>
                        </td>

                        <!-- Office Visits -->
                        <td class="py-3 px-2.5 text-center border-r border-gray-200 dark:border-dark-700">
                            <span class="font-bold {{ $row['office_visits_count'] > 0 ? 'text-indigo-600 dark:text-indigo-400 font-black' : 'text-gray-300 dark:text-gray-600' }}">
                                {{ $row['office_visits_count'] }}
                            </span>
                        </td>

                        <!-- New Sales (₦) with Click-to-Drilldown -->
                        <td class="py-3 px-2.5 text-right font-mono">
                            @if($row['actual_payments_count'] > 0)
                                <button type="button" @click="openDrilldown({{ $user->id }}, 'sales', '{{ addslashes($user->name) }}')"
                                    class="text-amber-700 dark:text-amber-400 font-bold hover:underline cursor-pointer block w-full text-right"
                                    title="Click to view verified closed sales">
                                    ₦{{ number_format($row['actual_payments_value'], 0) }}
                                    <span class="text-[9px] block text-gray-400">({{ $row['actual_payments_count'] }} deal{{ $row['actual_payments_count'] > 1 ? 's' : '' }})</span>
                                </button>
                            @else
                                <span class="text-gray-300 dark:text-gray-600">—</span>
                            @endif
                        </td>

                        <!-- Part / Top-ups (₦) with Click-to-Drilldown -->
                        <td class="py-3 px-2.5 text-right font-mono">
                            @if($row['topups_count'] > 0)
                                <button type="button" @click="openDrilldown({{ $user->id }}, 'topups', '{{ addslashes($user->name) }}')"
                                    class="text-emerald-700 dark:text-emerald-400 font-bold hover:underline cursor-pointer block w-full text-right"
                                    title="Click to view verified milestone receipts">
                                    ₦{{ number_format($row['topups_value'], 0) }}
                                    <span class="text-[9px] block text-gray-400">({{ $row['topups_count'] }} topup{{ $row['topups_count'] > 1 ? 's' : '' }})</span>
                                </button>
                            @else
                                <span class="text-gray-300 dark:text-gray-600">—</span>
                            @endif
                        </td>

                        <!-- Expected Inflow (₦) & Prospective Contacts -->
                        <td class="py-3 px-2.5 text-right">
                            @if($row['expected_payments_count'] > 0 || !empty($row['expected_payments_notes']))
                                <span class="text-gray-600 dark:text-gray-300 font-medium block">
                                    {{ $row['expected_payments_count'] > 0 ? $row['expected_payments_count'] . ' pipeline' : 'Pending' }}
                                </span>
                                @if(!empty($row['expected_payments_notes']))
                                    <span class="text-[9px] text-gray-400 block truncate max-w-[110px]" title="{{ $row['expected_payments_notes'] }}">
                                        {{ $row['expected_payments_notes'] }}
                                    </span>
                                @endif
                            @else
                                <span class="text-gray-300 dark:text-gray-600">—</span>
                            @endif
                        </td>

                        <!-- Total Realized Inflow (₦) -->
                        <td class="py-3 px-3 text-right font-black border-r border-gray-200 dark:border-dark-700 bg-emerald-50/20 dark:bg-emerald-950/10 font-mono">
                            <span class="{{ $row['total_revenue'] > 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-gray-300 dark:text-gray-600' }}">
                                ₦{{ number_format($row['total_revenue'], 0) }}
                            </span>
                        </td>

                        <!-- Inspections Count with Click-to-Drilldown -->
                        <td class="py-3 px-2 text-center">
                            @if($row['inspections_count'] > 0)
                                <button type="button" @click="openDrilldown({{ $user->id }}, 'inspections', '{{ addslashes($user->name) }}')"
                                    class="inline-flex items-center justify-center px-2 py-0.5 rounded-lg font-bold bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 hover:bg-purple-100 transition-all cursor-pointer">
                                    {{ $row['inspections_count'] }}
                                </button>
                            @else
                                <span class="text-gray-300 dark:text-gray-600">—</span>
                            @endif
                        </td>

                        <!-- Projects / Estates Inspected -->
                        <td class="py-3 px-3 text-[11px] border-r border-gray-200 dark:border-dark-700">
                            <span class="text-gray-600 dark:text-gray-400 font-medium block truncate max-w-[130px]" title="{{ $row['project_locations_inspected'] }}">
                                {{ $row['project_locations_inspected'] }}
                            </span>
                        </td>

                        <!-- Verified Calls with Click-to-Drilldown -->
                        <td class="py-3 px-2 text-center">
                            @if($row['calls_count'] > 0)
                                <button type="button" @click="openDrilldown({{ $user->id }}, 'calls', '{{ addslashes($user->name) }}')"
                                    class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-md font-bold text-[11px] bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-800 hover:bg-blue-100 cursor-pointer">
                                    {{ $row['calls_count'] }}
                                </button>
                            @else
                                <span class="text-gray-300 dark:text-gray-600">0</span>
                            @endif
                        </td>

                        <!-- WhatsApp with Click-to-Drilldown -->
                        <td class="py-3 px-2 text-center">
                            @if($row['whatsapp_count'] > 0)
                                <button type="button" @click="openDrilldown({{ $user->id }}, 'whatsapp', '{{ addslashes($user->name) }}')"
                                    class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-md font-bold text-[11px] bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800 hover:bg-emerald-100 cursor-pointer">
                                    {{ $row['whatsapp_count'] }}
                                </button>
                            @else
                                <span class="text-gray-300 dark:text-gray-600">0</span>
                            @endif
                        </td>

                        <!-- SMS -->
                        <td class="py-3 px-2 text-center font-bold text-gray-500">
                            {{ $row['sms_count'] }}
                        </td>

                        <!-- Contacts (Phone / Email) with Click-to-Drilldown -->
                        <td class="py-3 px-2.5 text-center border-r border-gray-200 dark:border-dark-700">
                            @if($row['new_contacts_count'] > 0)
                                <button type="button" @click="openDrilldown({{ $user->id }}, 'leads', '{{ addslashes($user->name) }}')"
                                    class="font-black text-dark-900 dark:text-white hover:text-brand-600 cursor-pointer text-xs"
                                    title="Click to view captured leads">
                                    {{ $row['new_contacts_count'] }}
                                </button>
                                <span class="text-[9px] text-gray-400 block font-medium">({{ $row['contacts_phone_count'] }} ph / {{ $row['contacts_email_count'] }} em)</span>
                            @else
                                <span class="text-gray-300 dark:text-gray-600">—</span>
                            @endif
                        </td>

                        <!-- Summary Observations & Feedback -->
                        <td class="py-3 px-3 text-[11px]">
                            <p class="text-gray-700 dark:text-gray-300 line-clamp-2 leading-relaxed" title="{{ $row['observations_recommendations'] }}">
                                {{ $row['observations_recommendations'] }}
                            </p>
                        </td>

                        <!-- Edit / Field Log Action Button -->
                        <td class="py-3 px-2 text-center">
                            <button type="button" @click="openFieldLogModal({{ json_encode([
                                'user_id' => $user->id,
                                'user_name' => $user->name,
                                'canvassing_locations' => $row['canvassing_locations'] === 'Territory Prospecting' ? '' : $row['canvassing_locations'],
                                'office_visits_count' => $row['office_visits_count'],
                                'expected_payments_count' => $row['expected_payments_count'],
                                'expected_payments_notes' => $row['expected_payments_notes'],
                                'observations_recommendations' => $row['observations_recommendations'],
                                'manager_feedback' => $fieldLog ? $fieldLog->manager_feedback : '',
                            ]) }})" 
                                class="p-1 rounded-lg text-gray-400 hover:text-brand-600 hover:bg-gray-100 dark:hover:bg-dark-700 transition-all"
                                title="Edit weekly field logs and observations for {{ $user->name }}">
                                ✏️
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="16" class="py-12 text-center text-gray-400 text-sm">
                            No sales consultants found for the selected period or branch criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                <!-- Aggregate Totals Footer Row (Identical to BSTAN Group Total Row) -->
                @if(count($matrixRows) > 0)
                <tfoot>
                    <tr class="bg-gray-100 dark:bg-dark-900 border-t-2 border-gray-300 dark:border-dark-600 text-xs font-black text-dark-900 dark:text-white">
                        <td colspan="2" class="py-3.5 px-3 border-r border-gray-300 dark:border-dark-700 text-dark-900 dark:text-white uppercase tracking-wider text-[11px]">
                            TOTAL TEAM AGGREGATE
                        </td>
                        <td class="py-3.5 px-3 text-[10px] text-gray-400 font-bold uppercase">All Outreaches</td>
                        <td class="py-3.5 px-2.5 text-center border-r border-gray-300 dark:border-dark-700 text-indigo-700 dark:text-indigo-400">
                            {{ number_format($aggregates['office_visits_count']) }}
                        </td>
                        <td class="py-3.5 px-2.5 text-right font-mono text-amber-700 dark:text-amber-400">
                            ₦{{ number_format($aggregates['actual_payments_value'], 0) }}
                            <span class="text-[9px] block font-normal text-gray-500">({{ $aggregates['actual_payments_count'] }} deals)</span>
                        </td>
                        <td class="py-3.5 px-2.5 text-right font-mono text-emerald-700 dark:text-emerald-400">
                            ₦{{ number_format($aggregates['topups_value'], 0) }}
                            <span class="text-[9px] block font-normal text-gray-500">({{ $aggregates['topups_count'] }} topups)</span>
                        </td>
                        <td class="py-3.5 px-2.5 text-right text-gray-600 dark:text-gray-300">
                            {{ $aggregates['expected_payments_count'] }} leads
                        </td>
                        <td class="py-3.5 px-3 text-right font-mono text-emerald-800 dark:text-emerald-300 border-r border-gray-300 dark:border-dark-700 bg-emerald-100/50 dark:bg-emerald-950/30 text-sm">
                            ₦{{ number_format($aggregates['total_realized_revenue'], 0) }}
                        </td>
                        <td class="py-3.5 px-2 text-center text-purple-700 dark:text-purple-400">
                            {{ number_format($aggregates['inspections_count']) }}
                        </td>
                        <td class="py-3.5 px-3 text-[10px] text-gray-400 uppercase border-r border-gray-300 dark:border-dark-700">
                            All Sites
                        </td>
                        <td class="py-3.5 px-2 text-center text-blue-700 dark:text-blue-400">
                            {{ number_format($aggregates['calls_count']) }}
                        </td>
                        <td class="py-3.5 px-2 text-center text-emerald-700 dark:text-emerald-400">
                            {{ number_format($aggregates['whatsapp_count']) }}
                        </td>
                        <td class="py-3.5 px-2 text-center text-gray-500">
                            {{ number_format($aggregates['sms_count']) }}
                        </td>
                        <td class="py-3.5 px-2.5 text-center border-r border-gray-300 dark:border-dark-700">
                            <span class="block text-dark-900 dark:text-white">{{ number_format($aggregates['new_contacts_count']) }}</span>
                            <span class="text-[9px] text-gray-400 font-normal">({{ $aggregates['contacts_phone_count'] }} ph)</span>
                        </td>
                        <td colspan="2" class="py-3.5 px-3 text-[10px] text-gray-400 italic">
                            Aggregate metrics compiled from 100% verified CRM records.
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Interactive CRM Audit Drilldown Modal (Alpine.js) -->
    <div x-cloak x-show="drilldownModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/70 backdrop-blur-xs transition-opacity">
        <div class="bg-white dark:bg-dark-800 rounded-3xl max-w-2xl w-full shadow-2xl overflow-hidden border border-gray-150 dark:border-dark-700"
            @click.away="drilldownModalOpen = false">
            
            <div class="px-6 py-4 border-b border-gray-150 dark:border-dark-700 flex items-center justify-between {{ $isBuckcrest ? 'bg-amber-500/5' : 'bg-brand-50/30' }}">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                        Verified CRM Database Audit
                    </span>
                    <h3 class="text-base font-bold text-dark-900 dark:text-white mt-0.5" x-text="drilldownTitle"></h3>
                </div>
                <button @click="drilldownModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 max-h-[60vh] overflow-y-auto">
                <template x-if="drilldownLoading">
                    <div class="py-12 text-center space-y-3">
                        <div class="w-8 h-8 border-3 border-brand-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
                        <p class="text-xs text-gray-400 font-medium">Auditing real database records...</p>
                    </div>
                </template>

                <template x-if="!drilldownLoading && drilldownItems.length === 0">
                    <div class="py-12 text-center text-gray-400 space-y-2">
                        <span class="text-3xl block">📋</span>
                        <p class="text-sm font-bold text-gray-600 dark:text-gray-300">No records found</p>
                        <p class="text-xs text-gray-400">Zero entries logged for this specific metric and period.</p>
                    </div>
                </template>

                <template x-if="!drilldownLoading && drilldownItems.length > 0">
                    <div class="space-y-2.5">
                        <template x-for="(item, i) in drilldownItems" :key="i">
                            <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-dark-750 border border-gray-150 dark:border-dark-700 flex items-start justify-between gap-3 hover:border-gray-300 dark:hover:border-dark-600 transition-all">
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-bold text-dark-900 dark:text-white text-xs" x-text="item.title"></span>
                                        <span class="text-[10px] px-2 py-0.2 rounded-full font-bold bg-gray-200 dark:bg-dark-600 text-gray-700 dark:text-gray-300" x-text="item.badge"></span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-snug" x-text="item.subtitle"></p>
                                    <div class="flex items-center space-x-3 text-[10px] text-gray-400 pt-0.5">
                                        <span x-text="item.date"></span>
                                        <template x-if="item.phone && item.phone !== 'N/A'">
                                            <div class="flex items-center space-x-2">
                                                <span>•</span>
                                                <a :href="'tel:' + item.phone" class="text-blue-600 hover:underline" x-text="item.phone"></a>
                                                <a :href="'https://wa.me/' + item.phone.replace(/[^0-9]/g, '')" target="_blank" class="text-emerald-600 hover:underline">WhatsApp</a>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <template x-if="item.link">
                                    <a :href="item.link" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline flex-shrink-0 flex items-center space-x-1 pt-0.5">
                                        <span>View</span>
                                        <span>&rarr;</span>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3.5 bg-gray-50 dark:bg-dark-850 border-t border-gray-150 dark:border-dark-700 flex items-center justify-between text-xs text-gray-500">
                <span x-text="drilldownItems.length + ' item(s) logged'"></span>
                <button type="button" @click="drilldownModalOpen = false" class="px-4 py-2 bg-gray-200 dark:bg-dark-700 text-dark-900 dark:text-white font-bold rounded-xl hover:bg-gray-300 transition-all">
                    Close Audit
                </button>
            </div>
        </div>
    </div>

    <!-- Weekly Field Outreaches & Observation Log Drawer/Modal -->
    <div x-cloak x-show="fieldLogModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/70 backdrop-blur-xs transition-opacity">
        <div class="bg-white dark:bg-dark-800 rounded-3xl max-w-xl w-full shadow-2xl p-6 md:p-8 space-y-5 border border-gray-150 dark:border-dark-700"
            @click.away="fieldLogModalOpen = false">
            
            <div class="flex justify-between items-center pb-3 border-b border-gray-150 dark:border-dark-700">
                <div class="flex items-center space-x-2.5">
                    <span class="w-8 h-8 rounded-xl {{ $isBuckcrest ? 'bg-[#946E19]/15 text-[#946E19]' : 'bg-brand-50 text-brand-600' }} flex items-center justify-center font-bold">📍</span>
                    <div>
                        <h3 class="text-base font-bold text-dark-900 dark:text-white">Record Canvassing & Field Observations</h3>
                        <p class="text-xs text-gray-400">Offline territory prospecting, office walk-ins & pipeline notes</p>
                    </div>
                </div>
                <button @click="fieldLogModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form @submit.prevent="submitFieldLog()" class="space-y-4 text-xs">
                <!-- Consultant Selector -->
                <div>
                    <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Sales Consultant</label>
                    <select x-model="fieldLogData.user_id" required class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-250 dark:border-dark-600 rounded-xl font-bold text-gray-800 dark:text-white outline-none">
                        @foreach($matrixRows as $r)
                            <option value="{{ $r['user']->id }}">{{ $r['user']->name }} ({{ $r['user']->branch ? $r['user']->branch->name : 'Main Office' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Canvassing Locations Visited -->
                <div>
                    <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                        Canvassing Locations Visited / Target Organizations
                    </label>
                    <input type="text" x-model="fieldLogData.canvassing_locations" placeholder="e.g. CAC Head Office, Banex Plaza, Ministry of Finance, NNPC Towers" 
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-250 dark:border-dark-600 rounded-xl text-gray-800 dark:text-white outline-none">
                    <span class="text-[10px] text-gray-400 mt-1 block">Separate multiple locations with commas.</span>
                </div>

                <!-- Two-column Row: Office Visits & Expected Inflow -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Office Walk-in Visits</label>
                        <input type="number" min="0" x-model="fieldLogData.office_visits_count" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-250 dark:border-dark-600 rounded-xl text-gray-800 dark:text-white outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Expected Deals Count</label>
                        <input type="number" min="0" x-model="fieldLogData.expected_payments_count" class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-250 dark:border-dark-600 rounded-xl text-gray-800 dark:text-white outline-none">
                    </div>
                </div>

                <!-- Expected Payments Notes / Pipeline Contacts -->
                <div>
                    <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Expected Payments & Hot Contacts</label>
                    <input type="text" x-model="fieldLogData.expected_payments_notes" placeholder="e.g. Alhaji Mustapha committed ₦5M deposit; Banex trader requesting 12mo plan" 
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-250 dark:border-dark-600 rounded-xl text-gray-800 dark:text-white outline-none">
                </div>

                <!-- Observations & Recommendations -->
                <div>
                    <label class="block font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Weekly Observations & Market Feedback</label>
                    <textarea x-model="fieldLogData.observations_recommendations" rows="3" placeholder="Challenges faced, client price resistance, estate demand preferences, marketing collateral needed..."
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-dark-700 border border-gray-250 dark:border-dark-600 rounded-xl text-gray-800 dark:text-white outline-none"></textarea>
                </div>

                @if(!$isExecutive)
                <!-- Manager Directives & Feedback (Visible to Management) -->
                <div>
                    <label class="block font-bold text-amber-600 uppercase tracking-wider mb-1">Manager Directives & 1-on-1 Feedback</label>
                    <input type="text" x-model="fieldLogData.manager_feedback" placeholder="Directives given during Monday morning review or weekly performance sprint"
                        class="w-full px-3 py-2 bg-amber-500/10 border border-amber-500/30 rounded-xl text-dark-900 dark:text-white outline-none">
                </div>
                @endif

                <div class="flex justify-end space-x-2 pt-3 border-t border-gray-150 dark:border-dark-700">
                    <button type="button" @click="fieldLogModalOpen = false" class="px-4 py-2 font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" :disabled="fieldLogSaving" class="px-5 py-2.5 {{ $isBuckcrest ? 'bg-[#946E19] hover:bg-[#785712]' : 'bg-brand-600 hover:bg-brand-700' }} text-white font-bold rounded-xl shadow-md transition-all">
                        <span x-text="fieldLogSaving ? 'Saving...' : 'Save Weekly Field Log'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Ongoing Chat Catch-up Modal -->
    <div x-cloak x-show="dailyCatchupOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white dark:bg-dark-800 rounded-3xl max-w-lg w-full shadow-2xl p-6 md:p-8 space-y-5" @click.away="dailyCatchupOpen = false">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-dark-700">
                <div class="flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center font-bold">💬</span>
                    <div>
                        <h3 class="text-base font-bold text-dark-900 dark:text-white">Quick Chat / Call Catch-up</h3>
                        <p class="text-xs text-gray-500">Record fast WhatsApp or phone touches on active assigned leads</p>
                    </div>
                </div>
                <button @click="dailyCatchupOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="dailyPulseForm" onsubmit="handleDailyPulseSubmit(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Communication Channel</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center space-x-2 p-2.5 rounded-xl border border-gray-200 dark:border-dark-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-700">
                            <input type="radio" name="pulse_channel" value="whatsapp" checked class="text-brand-500 focus:ring-brand-500">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">WhatsApp Discussion</span>
                        </label>
                        <label class="flex items-center space-x-2 p-2.5 rounded-xl border border-gray-200 dark:border-dark-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-700">
                            <input type="radio" name="pulse_channel" value="call" class="text-brand-500 focus:ring-brand-500">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Phone Call / Discussion</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Select Client(s) You Spoke With Today</label>
                    @php
                        $myLeads = \App\Models\Lead::where(function($q) use ($currentUser) {
                            if ($currentUser->role === 'sales_executive' || $currentUser->role === 'sales_agent') {
                                $q->where('assigned_to', $currentUser->id);
                            }
                        })->whereNotIn('status', ['Closed Lost'])->orderBy('updated_at', 'desc')->limit(20)->get();
                    @endphp
                    <div class="max-h-48 overflow-y-auto space-y-1.5 border border-gray-200 dark:border-dark-700 p-2.5 rounded-xl bg-gray-50/50 dark:bg-dark-900 text-xs">
                        @forelse($myLeads as $lead)
                            <label class="flex items-center justify-between p-2 bg-white dark:bg-dark-800 rounded-lg border border-gray-150 dark:border-dark-700 hover:border-gray-300 cursor-pointer">
                                <div class="flex items-center space-x-2.5">
                                    <input type="checkbox" name="lead_ids[]" value="{{ $lead->id }}" class="rounded text-brand-500 focus:ring-brand-500">
                                    <span class="font-bold text-dark-900 dark:text-white">{{ $lead->full_name }}</span>
                                </div>
                                <span class="text-[10px] text-gray-400 font-mono">{{ $lead->phone_number }}</span>
                            </label>
                        @empty
                            <p class="text-center text-gray-400 py-3">No active leads assigned to you yet.</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Brief Discussion Note / Milestone</label>
                    <input type="text" name="pulse_summary" placeholder="e.g. Sent revised price list; client confirmed inspection for Friday" class="w-full px-3 py-2 text-xs border border-gray-250 dark:border-dark-600 rounded-xl focus:border-brand-500 outline-none dark:bg-dark-700 dark:text-white">
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-gray-100 dark:border-dark-700">
                    <button type="button" @click="dailyCatchupOpen = false" class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" id="pulseSubmitBtn" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                        Confirm Active Discussions
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function retailScorecardApp() {
    return {
        dailyCatchupOpen: false,
        drilldownModalOpen: false,
        drilldownLoading: false,
        drilldownTitle: '',
        drilldownItems: [],
        
        fieldLogModalOpen: false,
        fieldLogSaving: false,
        fieldLogData: {
            user_id: '{{ $currentUser->id }}',
            year: {{ $year }},
            month: {{ $month }},
            week_number: {{ $activeWeek === 'all' ? 1 : $activeWeek }},
            canvassing_locations: '',
            office_visits_count: 0,
            expected_payments_count: 0,
            expected_payments_notes: '',
            observations_recommendations: '',
            manager_feedback: ''
        },

        openDrilldown(userId, metric, userName) {
            this.drilldownModalOpen = true;
            this.drilldownLoading = true;
            this.drilldownItems = [];
            this.drilldownTitle = `${userName} — Auditing ${metric.toUpperCase()} Records`;

            const startDate = '{{ $startDate->toIso8601String() }}';
            const endDate = '{{ $endDate->toIso8601String() }}';

            fetch(`{{ route('reports.retail.drilldown') }}?user_id=${userId}&metric=${metric}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.drilldownLoading = false;
                if (data.success) {
                    this.drilldownTitle = data.title;
                    this.drilldownItems = data.items;
                } else {
                    alert(data.error || 'Failed to fetch drill-down records.');
                }
            })
            .catch(err => {
                this.drilldownLoading = false;
                console.error(err);
                alert('Network error while querying CRM records.');
            });
        },

        openFieldLogModal(prefill = null) {
            if (prefill) {
                this.fieldLogData.user_id = prefill.user_id;
                this.fieldLogData.canvassing_locations = prefill.canvassing_locations || '';
                this.fieldLogData.office_visits_count = prefill.office_visits_count || 0;
                this.fieldLogData.expected_payments_count = prefill.expected_payments_count || 0;
                this.fieldLogData.expected_payments_notes = prefill.expected_payments_notes || '';
                this.fieldLogData.observations_recommendations = prefill.observations_recommendations || '';
                this.fieldLogData.manager_feedback = prefill.manager_feedback || '';
            }
            this.fieldLogModalOpen = true;
        },

        submitFieldLog() {
            this.fieldLogSaving = true;
            fetch('{{ route('reports.retail.field-log') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.fieldLogData)
            })
            .then(res => res.json())
            .then(data => {
                this.fieldLogSaving = false;
                if (data.success) {
                    this.fieldLogModalOpen = false;
                    alert('Weekly field log and observations recorded successfully!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Error saving field activity log.');
                }
            })
            .catch(err => {
                this.fieldLogSaving = false;
                console.error(err);
                alert('Network error while saving field log.');
            });
        }
    };
}

async function handleDailyPulseSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('pulseSubmitBtn');
    const formData = new FormData(form);

    const leadIds = formData.getAll('lead_ids[]');
    if (!leadIds || leadIds.length === 0) {
        alert('Please select at least one client you spoke with.');
        return;
    }

    const payload = {
        lead_ids: leadIds,
        channel: formData.get('pulse_channel'),
        summary: formData.get('pulse_summary')
    };

    btn.disabled = true;
    btn.innerHTML = 'Saving...';

    try {
        const res = await fetch('{{ route('leads.daily-pulse') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (data.success) {
            alert(data.message || 'Activities logged successfully!');
            window.location.reload();
        } else {
            alert(data.error || 'Failed to record daily pulse.');
            btn.disabled = false;
            btn.innerHTML = 'Confirm Active Discussions';
        }
    } catch (err) {
        console.error(err);
        alert('Network error while recording activity.');
        btn.disabled = false;
        btn.innerHTML = 'Confirm Active Discussions';
    }
}
</script>
@endpush
@endsection
