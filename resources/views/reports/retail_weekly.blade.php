@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ dailyCatchupOpen: false, selectedLeads: [], pulseChannel: 'whatsapp', pulseSummary: '' }">

    <!-- Top Breadcrumb & Actions Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-4 border-b border-gray-150">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold text-gray-400 mb-1">
                <a href="{{ route('reports.index') }}" class="hover:text-brand-600 transition-colors">Reports</a>
                <span>/</span>
                <span class="text-brand-600">Retail Team Performance</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-black text-dark-900 tracking-tight flex items-center gap-3">
                <span>📊 Retail Team Performance Scorecard</span>
                <span class="text-xs px-2.5 py-1 rounded-full font-bold bg-brand-50 text-brand-600 border border-brand-100">
                    {{ $periodLabel }}
                </span>
            </h1>
            <p class="text-xs text-gray-500 mt-1">Verified weekly and monthly audit of sales outreaches, client engagements, site inspections, and cash collections.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button @click="dailyCatchupOpen = true" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-xl border border-emerald-200 transition-all shadow-sm">
                <span>💬</span>
                <span>Quick Chat / Call Catch-up</span>
            </button>

            <a href="{{ route('reports.retail.export', request()->all()) }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-white text-gray-700 hover:bg-gray-50 border border-gray-250 font-bold text-xs rounded-xl shadow-sm transition-all">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Export to Excel / CSV</span>
            </a>

            <a href="{{ route('leads.index') }}" class="inline-flex items-center space-x-1 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md shadow-brand-500/20 transition-all">
                <span>View Leads Pipeline &rarr;</span>
            </a>
        </div>
    </div>

    <!-- Period Filter Bar -->
    <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-sm">
        <form method="GET" action="{{ route('reports.retail.index') }}" class="flex flex-wrap items-center gap-3">
            <!-- Period Type Toggle -->
            <div class="flex items-center bg-gray-100 p-1 rounded-xl">
                <button type="button" onclick="document.getElementById('period_type_input').value='weekly'; this.form.submit();"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodType === 'weekly' ? 'bg-white text-dark-900 shadow-sm' : 'text-gray-500 hover:text-dark-900' }}">
                    Weekly Sprint
                </button>
                <button type="button" onclick="document.getElementById('period_type_input').value='monthly'; this.form.submit();"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodType === 'monthly' ? 'bg-white text-dark-900 shadow-sm' : 'text-gray-500 hover:text-dark-900' }}">
                    Monthly Overview
                </button>
            </div>
            <input type="hidden" id="period_type_input" name="period_type" value="{{ $periodType }}">

            @if($periodType === 'weekly')
            <!-- Week Selector -->
            <div class="flex items-center space-x-2">
                <label class="text-xs font-bold text-gray-500">Week:</label>
                <select name="week" onchange="this.form.submit()" class="bg-gray-50 border border-gray-250 rounded-xl px-3 py-1.5 text-xs font-bold text-gray-700 focus:border-brand-500 outline-none">
                    @for($w = 1; $w <= 52; $w++)
                        @php
                            $wStart = \Carbon\Carbon::now()->setISODate($year, $w)->startOfWeek();
                            $wEnd = \Carbon\Carbon::now()->setISODate($year, $w)->endOfWeek();
                        @endphp
                        <option value="{{ $w }}" {{ $w == $week ? 'selected' : '' }}>
                            Week {{ $w }} ({{ $wStart->format('M d') }} - {{ $wEnd->format('M d') }})
                        </option>
                    @endfor
                </select>
            </div>
            @else
            <!-- Month Selector -->
            <div class="flex items-center space-x-2">
                <label class="text-xs font-bold text-gray-500">Month:</label>
                <select name="month" onchange="this.form.submit()" class="bg-gray-50 border border-gray-250 rounded-xl px-3 py-1.5 text-xs font-bold text-gray-700 focus:border-brand-500 outline-none">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            @endif

            <!-- Year Selector -->
            <div class="flex items-center space-x-2">
                <label class="text-xs font-bold text-gray-500">Year:</label>
                <select name="year" onchange="this.form.submit()" class="bg-gray-50 border border-gray-250 rounded-xl px-3 py-1.5 text-xs font-bold text-gray-700 focus:border-brand-500 outline-none">
                    @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <!-- Branch Filter (if applicable) -->
            @if($branches->count() > 1)
            <div class="flex items-center space-x-2">
                <label class="text-xs font-bold text-gray-500">Branch:</label>
                <select name="branch_id" onchange="this.form.submit()" class="bg-gray-50 border border-gray-250 rounded-xl px-3 py-1.5 text-xs font-bold text-gray-700 focus:border-brand-500 outline-none">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <noscript>
                <button type="submit" class="px-3 py-1.5 bg-brand-500 text-white rounded-xl text-xs font-bold">Filter</button>
            </noscript>
        </form>
    </div>

    <!-- Verified Team Aggregates KPI Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Leads Captured</span>
            <span class="text-xl lg:text-2xl font-black text-dark-900 mt-1 block">{{ number_format($aggregates['leads_captured']) }}</span>
            <span class="text-[10px] text-emerald-600 font-bold mt-0.5 block">New pipeline</span>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Verified Calls</span>
            <span class="text-xl lg:text-2xl font-black text-blue-600 mt-1 block">{{ number_format($aggregates['calls_logged']) }}</span>
            <span class="text-[10px] text-gray-400 font-medium mt-0.5 block">Logged on leads</span>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">WhatsApp Chats</span>
            <span class="text-xl lg:text-2xl font-black text-emerald-600 mt-1 block">{{ number_format($aggregates['whatsapp_logged']) }}</span>
            <span class="text-[10px] text-gray-400 font-medium mt-0.5 block">Ongoing discussions</span>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Site Inspections</span>
            <div class="flex items-baseline space-x-1 mt-1">
                <span class="text-xl lg:text-2xl font-black text-purple-600">{{ $aggregates['inspections_completed'] }}</span>
                <span class="text-xs text-gray-400 font-bold">/ {{ $aggregates['inspections_scheduled'] }}</span>
            </div>
            <span class="text-[10px] text-purple-600 font-bold mt-0.5 block">Done / Booked</span>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Office Visits</span>
            <span class="text-xl lg:text-2xl font-black text-indigo-600 mt-1 block">{{ number_format($aggregates['office_visits']) }}</span>
            <span class="text-[10px] text-gray-400 font-medium mt-0.5 block">Corporate walk-ins</span>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Deals Closed</span>
            <span class="text-xl lg:text-2xl font-black text-amber-600 mt-1 block">{{ number_format($aggregates['new_sales_count']) }}</span>
            <span class="text-[10px] text-amber-700 font-bold mt-0.5 block">₦{{ number_format($aggregates['new_sales_value'], 0) }}</span>
        </div>

        <div class="bg-white rounded-2xl border border-emerald-150 bg-gradient-to-br from-emerald-50/50 to-white p-4 shadow-sm">
            <span class="block text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Total Cash Inflow</span>
            <span class="text-lg lg:text-xl font-black text-emerald-700 mt-1 block">₦{{ number_format($aggregates['new_sales_value'] + $aggregates['milestone_collections'], 0) }}</span>
            <span class="text-[10px] text-gray-500 font-medium mt-0.5 block">Sales + Top-ups</span>
        </div>
    </div>

    <!-- Main Performance Scorecard Table -->
    <div class="bg-white rounded-3xl border border-gray-150 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-dark-900 text-base">Sales Consultant League Table</h3>
                <p class="text-xs text-gray-400">Ranked by revenue generation and genuine customer engagement</p>
            </div>
            <span class="text-xs font-bold text-gray-500 bg-gray-50 px-3 py-1 rounded-xl border border-gray-200">
                {{ count($scorecard) }} Sales Consultants Active
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-150 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Sales Consultant</th>
                        <th class="py-3.5 px-3">Field Outreaches</th>
                        <th class="py-3.5 px-3 text-center">Leads</th>
                        <th class="py-3.5 px-3 text-center">Daily Rhythm (Mon–Sun)</th>
                        <th class="py-3.5 px-3 text-center">Calls</th>
                        <th class="py-3.5 px-3 text-center">WhatsApp</th>
                        <th class="py-3.5 px-3 text-center">Inspections</th>
                        <th class="py-3.5 px-3 text-center">Office Visits</th>
                        <th class="py-3.5 px-3 text-right">New Sales (₦)</th>
                        <th class="py-3.5 px-3 text-right">Top-ups (₦)</th>
                        <th class="py-3.5 px-3 text-right">Expected Inflow (₦)</th>
                        <th class="py-3.5 px-4 text-right">Total Inflow (₦)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse($scorecard as $row)
                    @php
                        $user = $row['user'];
                        $isMe = ($currentUser->id === $user->id);
                    @endphp
                    <tr class="hover:bg-gray-50/80 transition-colors {{ $isMe ? 'bg-brand-50/30' : '' }}">
                        <!-- Consultant Identity -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 font-black text-xs flex items-center justify-center flex-shrink-0">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                                <div>
                                    <div class="flex items-center space-x-1.5">
                                        <span class="font-bold text-dark-900">{{ $user->name }}</span>
                                        @if($isMe)
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-brand-100 text-brand-700">You</span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-gray-400">{{ $user->branch ? $user->branch->name : 'Main Branch' }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Outreach Locations -->
                        <td class="py-3.5 px-3 max-w-[180px]">
                            @if(!empty($row['outreach_locations']))
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice($row['outreach_locations'], 0, 2) as $loc)
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded-md text-[10px] font-medium truncate max-w-[120px]" title="{{ $loc }}">
                                            📍 {{ $loc }}
                                        </span>
                                    @endforeach
                                    @if(count($row['outreach_locations']) > 2)
                                        <span class="px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded text-[9px] font-bold">+{{ count($row['outreach_locations']) - 2 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-300 text-[11px]">—</span>
                            @endif
                        </td>

                        <!-- Leads Captured -->
                        <td class="py-3.5 px-3 text-center">
                            <span class="font-black text-dark-900 {{ $row['leads_captured'] > 0 ? 'text-dark-900' : 'text-gray-300' }}">
                                {{ $row['leads_captured'] }}
                            </span>
                        </td>

                        <!-- Daily Rhythm (Mon–Sun) -->
                        <td class="py-3.5 px-3">
                            <div class="flex items-center justify-center space-x-1">
                                @foreach(['Mon' => 'M', 'Tue' => 'T', 'Wed' => 'W', 'Thu' => 'T', 'Fri' => 'F', 'Sat' => 'S', 'Sun' => 'S'] as $dayKey => $dayLabel)
                                    @php $cnt = $row['daily_lead_counts'][$dayKey] ?? 0; @endphp
                                    <div class="flex flex-col items-center" title="{{ $dayKey }}: {{ $cnt }} leads uploaded">
                                        <span class="text-[8px] font-bold {{ $cnt > 0 ? 'text-emerald-700 font-extrabold' : 'text-gray-400' }}">{{ $dayLabel }}</span>
                                        <span class="w-5 h-5 flex items-center justify-center text-[10px] font-bold rounded-md transition-all {{ $cnt > 0 ? 'bg-emerald-100 text-emerald-800 font-black ring-1 ring-emerald-300' : 'bg-gray-100 text-gray-300' }}">
                                            {{ $cnt }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </td>

                        <!-- Calls Logged -->
                        <td class="py-3.5 px-3 text-center">
                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-lg font-bold text-xs {{ $row['calls_logged'] > 0 ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'text-gray-300' }}">
                                {{ $row['calls_logged'] }}
                            </span>
                        </td>

                        <!-- WhatsApp Chats -->
                        <td class="py-3.5 px-3 text-center">
                            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-lg font-bold text-xs {{ $row['whatsapp_logged'] > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'text-gray-300' }}">
                                {{ $row['whatsapp_logged'] }}
                            </span>
                        </td>

                        <!-- Inspections -->
                        <td class="py-3.5 px-3 text-center">
                            @if($row['inspections_scheduled'] > 0)
                                <span class="px-2 py-0.5 rounded-lg text-xs font-bold {{ $row['inspections_completed'] > 0 ? 'bg-purple-50 text-purple-700 border border-purple-100' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $row['inspections_completed'] }} / {{ $row['inspections_scheduled'] }}
                                </span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        <!-- Office Visits -->
                        <td class="py-3.5 px-3 text-center">
                            <span class="font-bold text-gray-600 {{ $row['office_visits'] > 0 ? 'text-indigo-600 font-extrabold' : 'text-gray-300' }}">
                                {{ $row['office_visits'] }}
                            </span>
                        </td>

                        <!-- New Sales Value -->
                        <td class="py-3.5 px-3 text-right">
                            @if($row['new_sales_count'] > 0)
                                <span class="font-bold text-amber-700 block">₦{{ number_format($row['new_sales_value'], 0) }}</span>
                                <span class="text-[10px] text-gray-400">({{ $row['new_sales_count'] }} deal{{ $row['new_sales_count'] > 1 ? 's' : '' }})</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        <!-- Milestone Collections (Top-ups) -->
                        <td class="py-3.5 px-3 text-right">
                            @if($row['milestone_collections'] > 0)
                                <span class="font-bold text-emerald-700">₦{{ number_format($row['milestone_collections'], 0) }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        <!-- Expected Collections -->
                        <td class="py-3.5 px-3 text-right">
                            @if($row['expected_collections'] > 0)
                                <span class="font-bold text-gray-500">₦{{ number_format($row['expected_collections'], 0) }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        <!-- Total Cash Inflow -->
                        <td class="py-3.5 px-4 text-right">
                            <span class="font-black text-dark-900 text-sm {{ $row['total_revenue'] > 0 ? 'text-emerald-700' : 'text-gray-400' }}">
                                ₦{{ number_format($row['total_revenue'], 0) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="py-12 text-center text-gray-400 text-sm">
                            No sales consultants found for the selected criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <!-- Aggregates Footer Row -->
                @if(count($scorecard) > 0)
                <tfoot>
                    <tr class="bg-gray-100/80 border-t-2 border-gray-200 text-xs font-black text-dark-900">
                        <td class="py-4 px-4 text-dark-900 uppercase tracking-wider">
                            TOTAL TEAM AGGREGATE
                        </td>
                        <td class="py-4 px-3 text-[10px] text-gray-400 font-bold uppercase">All Outreaches</td>
                        <td class="py-4 px-3 text-center text-sm font-black">{{ number_format($aggregates['leads_captured']) }}</td>
                        <td class="py-4 px-3 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                @foreach(['Mon' => 'M', 'Tue' => 'T', 'Wed' => 'W', 'Thu' => 'T', 'Fri' => 'F', 'Sat' => 'S', 'Sun' => 'S'] as $dayKey => $dayLabel)
                                    @php $tot = $aggregates['daily_leads'][$dayKey] ?? 0; @endphp
                                    <div class="flex flex-col items-center" title="Total {{ $dayKey }}: {{ $tot }} leads">
                                        <span class="text-[8px] font-bold text-gray-500">{{ $dayLabel }}</span>
                                        <span class="text-[10px] font-black {{ $tot > 0 ? 'text-emerald-700' : 'text-gray-400' }}">{{ $tot }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="py-4 px-3 text-center text-sm font-black text-blue-700">{{ number_format($aggregates['calls_logged']) }}</td>
                        <td class="py-4 px-3 text-center text-sm font-black text-emerald-700">{{ number_format($aggregates['whatsapp_logged']) }}</td>
                        <td class="py-4 px-3 text-center text-sm font-black text-purple-700">{{ $aggregates['inspections_completed'] }} / {{ $aggregates['inspections_scheduled'] }}</td>
                        <td class="py-4 px-3 text-center text-sm font-black text-indigo-700">{{ number_format($aggregates['office_visits']) }}</td>
                        <td class="py-4 px-3 text-right text-sm font-black text-amber-700">
                            ₦{{ number_format($aggregates['new_sales_value'], 0) }}
                            <span class="text-[10px] block font-medium text-gray-500">({{ $aggregates['new_sales_count'] }} deals)</span>
                        </td>
                        <td class="py-4 px-3 text-right text-sm font-black text-emerald-700">₦{{ number_format($aggregates['milestone_collections'], 0) }}</td>
                        <td class="py-4 px-3 text-right text-sm font-black text-gray-600">₦{{ number_format($aggregates['expected_collections'], 0) }}</td>
                        <td class="py-4 px-4 text-right text-base font-black text-emerald-800">
                            ₦{{ number_format($aggregates['new_sales_value'] + $aggregates['milestone_collections'], 0) }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Weekly Review & Consistency Insights Panel -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Daily Upload Rhythm Audit -->
        <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-2 mb-3">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">📅</span>
                    <div>
                        <h4 class="font-bold text-dark-900 text-sm">Daily Upload Cadence</h4>
                        <p class="text-[11px] text-gray-400">Team prospect ingestion rhythm (Mon–Sun)</p>
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-1.5 py-4 border-y border-gray-100 my-3 text-center">
                    @foreach(['Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday', 'Sun' => 'Sunday'] as $dK => $dLong)
                        @php $dayTotal = $aggregates['daily_leads'][$dK] ?? 0; @endphp
                        <div class="p-2 rounded-xl {{ $dayTotal > 0 ? 'bg-emerald-50 border border-emerald-100' : 'bg-gray-50 border border-gray-100' }}">
                            <span class="block text-[9px] font-extrabold {{ $dayTotal > 0 ? 'text-emerald-700' : 'text-gray-400' }} uppercase">{{ substr($dK, 0, 3) }}</span>
                            <span class="block text-base font-black {{ $dayTotal > 0 ? 'text-emerald-800' : 'text-gray-300' }} mt-0.5">{{ $dayTotal }}</span>
                        </div>
                    @endforeach
                </div>

                <p class="text-xs text-gray-500 leading-relaxed">
                    Consistent daily prospecting ensures active follow-ups and prevents weekend pipeline bunching before Monday executive meetings.
                </p>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                <span class="text-xs text-gray-400 font-medium">Daily Leads Portal:</span>
                <a href="{{ route('leads.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 inline-flex items-center space-x-1">
                    <span>Upload Today's Leads</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>

        <!-- Weekly Meeting Agenda & Audit Checklist -->
        <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm lg:col-span-2">
            <div class="flex items-center space-x-2 mb-3">
                <span class="w-8 h-8 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-sm">📋</span>
                <div>
                    <h4 class="font-bold text-dark-900 text-sm">Weekly Sales Review & Audit Framework</h4>
                    <p class="text-[11px] text-gray-400">Standard operating checklist for Monday pipeline reviews and 1-on-1s</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 text-xs">
                <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 flex items-start space-x-3">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-black text-[11px] flex items-center justify-center flex-shrink-0 mt-0.5">1</span>
                    <div>
                        <span class="font-bold text-dark-900 block">Daily Ingestion Check</span>
                        <p class="text-[11px] text-gray-500 mt-0.5">Audit consultant chips (M–S). Reps must upload daily regardless of channel or area.</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 flex items-start space-x-3">
                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-800 font-black text-[11px] flex items-center justify-center flex-shrink-0 mt-0.5">2</span>
                    <div>
                        <span class="font-bold text-dark-900 block">Engagement Conversion</span>
                        <p class="text-[11px] text-gray-500 mt-0.5">Ensure uploaded leads are immediately contacted via verified phone calls and WhatsApp.</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 flex items-start space-x-3">
                    <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-800 font-black text-[11px] flex items-center justify-center flex-shrink-0 mt-0.5">3</span>
                    <div>
                        <span class="font-bold text-dark-900 block">Inspection Follow-through</span>
                        <p class="text-[11px] text-gray-500 mt-0.5">Compare scheduled vs completed site inspections to prevent prospect drop-off.</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-100 flex items-start space-x-3">
                    <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-800 font-black text-[11px] flex items-center justify-center flex-shrink-0 mt-0.5">4</span>
                    <div>
                        <span class="font-bold text-dark-900 block">Revenue & Top-ups Audit</span>
                        <p class="text-[11px] text-gray-500 mt-0.5">Review closed deals, pending milestone installments, and overdue customer collections.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Ongoing Chat Catch-up Modal -->
    <div x-cloak x-show="dailyCatchupOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl p-6 md:p-8 space-y-5" @click.away="dailyCatchupOpen = false">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <div class="flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">💬</span>
                    <div>
                        <h3 class="text-base font-bold text-dark-900">Log Ongoing Conversations</h3>
                        <p class="text-xs text-gray-500">Record active WhatsApp/call touches without opening individual tabs</p>
                    </div>
                </div>
                <button @click="dailyCatchupOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <p class="text-xs text-gray-600 bg-emerald-50/50 p-3 rounded-xl border border-emerald-100 leading-relaxed">
                Had back-and-forth chats on your phone or WhatsApp Web today? Confirm your active contacts here so your scorecard updates instantly.
            </p>

            <form id="dailyPulseForm" onsubmit="handleDailyPulseSubmit(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Communication Channel</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center space-x-2 p-2.5 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="pulse_channel" value="whatsapp" checked class="text-brand-500 focus:ring-brand-500">
                            <span class="text-xs font-bold text-gray-700">WhatsApp Discussion</span>
                        </label>
                        <label class="flex items-center space-x-2 p-2.5 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="pulse_channel" value="call" class="text-brand-500 focus:ring-brand-500">
                            <span class="text-xs font-bold text-gray-700">Phone Call / Discussion</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Select Client(s) You Spoke With Today</label>
                    @php
                        $myLeads = \App\Models\Lead::where(function($q) use ($currentUser) {
                            if ($currentUser->role === 'sales_executive') {
                                $q->where('assigned_to', $currentUser->id);
                            }
                        })->whereNotIn('status', ['Closed Lost'])->orderBy('updated_at', 'desc')->limit(20)->get();
                    @endphp
                    <div class="max-h-48 overflow-y-auto space-y-1.5 border border-gray-200 p-2.5 rounded-xl bg-gray-50/50 text-xs">
                        @forelse($myLeads as $lead)
                            <label class="flex items-center justify-between p-2 bg-white rounded-lg border border-gray-100 hover:border-gray-300 cursor-pointer">
                                <div class="flex items-center space-x-2.5">
                                    <input type="checkbox" name="lead_ids[]" value="{{ $lead->id }}" class="rounded text-brand-500 focus:ring-brand-500">
                                    <span class="font-bold text-dark-900">{{ $lead->full_name }}</span>
                                </div>
                                <span class="text-[10px] text-gray-400 font-mono">{{ $lead->phone_number }}</span>
                            </label>
                        @empty
                            <p class="text-center text-gray-400 py-3">No active leads assigned to you yet.</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Brief Discussion Note / Milestone</label>
                    <input type="text" name="pulse_summary" placeholder="e.g. Sent revised price list; client confirmed interest" class="w-full px-3 py-2 text-xs border border-gray-250 rounded-xl focus:border-brand-500 outline-none">
                </div>

                <div class="flex justify-end space-x-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="dailyCatchupOpen = false" class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" id="pulseSubmitBtn" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition-all">
                        Confirm Active Discussions
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
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
