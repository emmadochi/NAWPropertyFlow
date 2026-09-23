@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between pb-4 border-b border-gray-150 dark:border-slate-800 gap-4">
        <div>
            <a href="{{ route('campaigns.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Campaigns</span>
            </a>
            <h1 class="text-2xl font-black text-dark-950 dark:text-white tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-brand-500/10 text-brand-600 dark:text-brand-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </span>
                Campaign Analytics &amp; ROI
            </h1>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Cross-channel marketing performance, delivery metrics, and audience engagement tracking.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('campaigns.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Create Campaign</span>
            </a>
        </div>
    </div>

    <!-- 5 KPI Cards Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- 1. Total Campaigns & Audience -->
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Total Campaigns</span>
                <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </span>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-3xl font-black text-dark-950 dark:text-white">{{ number_format($totalCampaigns) }}</span>
            </div>
            <span class="block text-[11px] text-gray-500 dark:text-slate-400 mt-1.5">
                Target audience: <strong class="text-dark-900 dark:text-white">{{ number_format($totalAudience) }}</strong>
            </span>
        </div>

        <!-- 2. Broadcasts Sent & Delivery Rate -->
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Dispatched Messages</span>
                <span class="p-1.5 rounded-lg bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </span>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-3xl font-black text-brand-600 dark:text-brand-400">{{ number_format($totalSent) }}</span>
            </div>
            <span class="block text-[11px] text-gray-500 dark:text-slate-400 mt-1.5">
                Delivery rate: <strong class="text-dark-900 dark:text-white">{{ $deliveryRate }}%</strong>
            </span>
        </div>

        <!-- 3. Open Rate & Total Opens -->
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Avg Open Rate</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </span>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $avgOpenRate }}%</span>
            </div>
            <span class="block text-[11px] text-gray-500 dark:text-slate-400 mt-1.5">
                <strong class="text-dark-900 dark:text-white">{{ number_format($totalOpened) }}</strong> total opens
            </span>
        </div>

        <!-- 4. Click Rate & Total Clicks -->
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Avg Click Rate</span>
                <span class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                </span>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ $avgClickRate }}%</span>
            </div>
            <span class="block text-[11px] text-gray-500 dark:text-slate-400 mt-1.5">
                <strong class="text-dark-900 dark:text-white">{{ number_format($totalClicked) }}</strong> click-throughs
            </span>
        </div>

        <!-- 5. Drops & Unsubscribes -->
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Drops &amp; Failures</span>
                <span class="p-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </span>
            </div>
            <div class="flex items-baseline space-x-2">
                <span class="text-3xl font-black text-rose-600 dark:text-rose-400">{{ number_format($failedCount) }}</span>
            </div>
            <span class="block text-[11px] text-gray-500 dark:text-slate-400 mt-1.5">
                <strong class="text-dark-900 dark:text-white">{{ number_format($totalUnsubscribed) }}</strong> unsubscribed
            </span>
        </div>
    </div>

    <!-- Engagement Funnel Card -->
    <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 pb-3 border-b border-gray-100 dark:border-slate-800 gap-2">
            <div>
                <h3 class="text-base font-extrabold text-dark-950 dark:text-white">Audience Engagement Conversion Funnel</h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Overall conversion efficiency from target reach to clicks</p>
            </div>
            <div class="flex items-center space-x-4 text-xs">
                <span class="inline-flex items-center text-gray-600 dark:text-slate-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400 mr-1.5"></span> Reach
                </span>
                <span class="inline-flex items-center text-gray-600 dark:text-slate-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-500 mr-1.5"></span> Dispatched
                </span>
                <span class="inline-flex items-center text-gray-600 dark:text-slate-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-1.5"></span> Opened
                </span>
                <span class="inline-flex items-center text-gray-600 dark:text-slate-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500 mr-1.5"></span> Clicked
                </span>
            </div>
        </div>

        @php
            $funnelBase = max($totalAudience, $totalSent, 1);
            $pSent = round(($totalSent / $funnelBase) * 100, 1);
            $pOpened = $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 1) : 0;
            $pClicked = $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 1) : 0;
        @endphp

        <div class="space-y-4">
            <!-- Step 1: Target Audience -->
            <div>
                <div class="flex justify-between items-center text-xs mb-1.5">
                    <span class="font-bold text-dark-900 dark:text-white flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 inline-flex items-center justify-center font-bold text-[10px]">1</span>
                        Targeted Audience Segments
                    </span>
                    <span class="font-black text-dark-950 dark:text-white">{{ number_format($totalAudience) }} leads (100%)</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-slate-800 rounded-full h-3.5 overflow-hidden p-0.5">
                    <div class="bg-slate-400 dark:bg-slate-500 h-2.5 rounded-full transition-all duration-500" style="width: 100%"></div>
                </div>
            </div>

            <!-- Step 2: Delivered Messages -->
            <div>
                <div class="flex justify-between items-center text-xs mb-1.5">
                    <span class="font-bold text-dark-900 dark:text-white flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-brand-50 dark:bg-brand-950 text-brand-600 dark:text-brand-400 inline-flex items-center justify-center font-bold text-[10px]">2</span>
                        Broadcasts Dispatched &amp; Delivered
                    </span>
                    <span class="font-black text-brand-600 dark:text-brand-400">{{ number_format($totalSent) }} sent ({{ $pSent }}%)</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-slate-800 rounded-full h-3.5 overflow-hidden p-0.5">
                    <div class="bg-brand-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ min(max($pSent, 1), 100) }}%"></div>
                </div>
            </div>

            <!-- Step 3: Opened Messages -->
            <div>
                <div class="flex justify-between items-center text-xs mb-1.5">
                    <span class="font-bold text-dark-900 dark:text-white flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 inline-flex items-center justify-center font-bold text-[10px]">3</span>
                        Opened / Read
                    </span>
                    <span class="font-black text-emerald-600 dark:text-emerald-400">{{ number_format($totalOpened) }} opened ({{ $pOpened }}% of sent)</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-slate-800 rounded-full h-3.5 overflow-hidden p-0.5">
                    <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ min(max($pOpened, 1), 100) }}%"></div>
                </div>
            </div>

            <!-- Step 4: Clicked Messages -->
            <div>
                <div class="flex justify-between items-center text-xs mb-1.5">
                    <span class="font-bold text-dark-900 dark:text-white flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 inline-flex items-center justify-center font-bold text-[10px]">4</span>
                        Clicked Call-To-Action Links
                    </span>
                    <span class="font-black text-blue-600 dark:text-blue-400">{{ number_format($totalClicked) }} clicked ({{ $pClicked }}% of sent)</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-slate-800 rounded-full h-3.5 overflow-hidden p-0.5">
                    <div class="bg-blue-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ min(max($pClicked, 1), 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1: Channel Breakdown & Status Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Channel Distribution Chart -->
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-extrabold text-dark-950 dark:text-white">Channel Distribution</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Campaigns split across Email, SMS, and WhatsApp</p>
                </div>
                <span class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Volume</span>
            </div>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="channelChart"></canvas>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-gray-100 dark:border-slate-800 text-center">
                @php
                    $emailItem = $channelCounts->get('email');
                    $smsItem = $channelCounts->get('sms');
                    $waItem = $channelCounts->get('whatsapp');
                @endphp
                <div class="p-2 rounded-xl bg-gray-50 dark:bg-slate-800/50">
                    <span class="text-[10px] font-bold text-gray-400 dark:text-slate-400 block uppercase">Email</span>
                    <span class="text-sm font-black text-dark-950 dark:text-white">{{ $emailItem?->count ?? 0 }} campaigns</span>
                    <span class="text-[10px] text-gray-500 block">{{ number_format($emailItem?->total_sent ?? 0) }} sent</span>
                </div>
                <div class="p-2 rounded-xl bg-gray-50 dark:bg-slate-800/50">
                    <span class="text-[10px] font-bold text-gray-400 dark:text-slate-400 block uppercase">SMS</span>
                    <span class="text-sm font-black text-dark-950 dark:text-white">{{ $smsItem?->count ?? 0 }} campaigns</span>
                    <span class="text-[10px] text-gray-500 block">{{ number_format($smsItem?->total_sent ?? 0) }} sent</span>
                </div>
                <div class="p-2 rounded-xl bg-gray-50 dark:bg-slate-800/50">
                    <span class="text-[10px] font-bold text-gray-400 dark:text-slate-400 block uppercase">WhatsApp</span>
                    <span class="text-sm font-black text-dark-950 dark:text-white">{{ $waItem?->count ?? 0 }} campaigns</span>
                    <span class="text-[10px] text-gray-500 block">{{ number_format($waItem?->total_sent ?? 0) }} sent</span>
                </div>
            </div>
        </div>

        <!-- Status Breakdown Chart -->
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-extrabold text-dark-950 dark:text-white">Status Breakdown</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Campaign lifecycle &amp; execution states</p>
                </div>
                <span class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">States</span>
            </div>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="grid grid-cols-4 gap-2 mt-4 pt-3 border-t border-gray-100 dark:border-slate-800 text-center">
                <div class="p-2 rounded-xl bg-gray-50 dark:bg-slate-800/50">
                    <span class="text-[10px] font-bold text-gray-400 dark:text-slate-400 block uppercase">Sent</span>
                    <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">{{ $statusCounts['sent'] ?? 0 }}</span>
                </div>
                <div class="p-2 rounded-xl bg-gray-50 dark:bg-slate-800/50">
                    <span class="text-[10px] font-bold text-gray-400 dark:text-slate-400 block uppercase">Draft</span>
                    <span class="text-sm font-black text-slate-600 dark:text-slate-300">{{ $statusCounts['draft'] ?? 0 }}</span>
                </div>
                <div class="p-2 rounded-xl bg-gray-50 dark:bg-slate-800/50">
                    <span class="text-[10px] font-bold text-gray-400 dark:text-slate-400 block uppercase">Active</span>
                    <span class="text-sm font-black text-amber-600 dark:text-amber-400">{{ ($statusCounts['sending'] ?? 0) + ($statusCounts['scheduled'] ?? 0) }}</span>
                </div>
                <div class="p-2 rounded-xl bg-gray-50 dark:bg-slate-800/50">
                    <span class="text-[10px] font-bold text-gray-400 dark:text-slate-400 block uppercase">Paused</span>
                    <span class="text-sm font-black text-rose-600 dark:text-rose-400">{{ ($statusCounts['paused'] ?? 0) + ($statusCounts['cancelled'] ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2: 6-Month Volume & Engagement Trends -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Dispatch Volume Chart -->
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-extrabold text-dark-950 dark:text-white">Monthly Dispatch Volume</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Campaigns created and messages sent over the last 6 months</p>
                </div>
                <span class="p-1 rounded bg-brand-50 dark:bg-brand-950 text-brand-600 dark:text-brand-400 text-[10px] font-extrabold">6 Months</span>
            </div>
            <div class="relative h-72">
                <canvas id="monthlyVolumeChart"></canvas>
            </div>
        </div>

        <!-- Monthly Engagement Trend Chart -->
        <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-extrabold text-dark-950 dark:text-white">Engagement Rates Trend</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Open vs Click performance progression</p>
                </div>
                <span class="p-1 rounded bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 text-[10px] font-extrabold">Rates %</span>
            </div>
            <div class="relative h-72">
                <canvas id="engagementTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables Row: Top Performing Campaigns & Recent Broadcasts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Performing Campaigns -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-150 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-gray-150 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-dark-950 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Top Performing Campaigns
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Ranked by highest open rate</p>
                </div>
            </div>
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-slate-800/50 border-b border-gray-150 dark:border-slate-800 text-[10px] font-extrabold text-gray-400 dark:text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Campaign</th>
                            <th class="py-3 px-4">Channel</th>
                            <th class="py-3 px-4 text-center">Sent</th>
                            <th class="py-3 px-4 text-center">Open Rate</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800 text-xs">
                        @forelse($topCampaigns as $c)
                            @php
                                $cOpenRate = $c->sent_count > 0 ? round(($c->opened_count / $c->sent_count) * 100, 1) : 0;
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-dark-900 dark:text-white block">{{ $c->name }}</span>
                                    <span class="text-[11px] text-gray-400 dark:text-slate-500">{{ $c->created_at->format('M d, Y') }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($c->type === 'email')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300">Email</span>
                                    @elseif($c->type === 'sms')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300">SMS</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">WhatsApp</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-dark-900 dark:text-white">
                                    {{ number_format($c->sent_count) }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex items-center space-x-1.5">
                                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400">{{ $cOpenRate }}%</span>
                                        <div class="w-12 bg-gray-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ min($cOpenRate, 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('campaigns.show', $c) }}" class="inline-flex items-center text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-400 dark:text-slate-500 text-xs">
                                    No dispatched campaigns found with open data yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Campaigns Activity -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-150 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-gray-150 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-dark-950 dark:text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                        Recent Campaign Activity
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Latest broadcasts created in CRM</p>
                </div>
                <a href="{{ route('campaigns.index') }}" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">
                    View All &rarr;
                </a>
            </div>
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-slate-800/50 border-b border-gray-150 dark:border-slate-800 text-[10px] font-extrabold text-gray-400 dark:text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">Campaign</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-center">Audience</th>
                            <th class="py-3 px-4 text-right">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800 text-xs">
                        @forelse($recentCampaigns as $rc)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('campaigns.show', $rc) }}" class="font-bold text-dark-900 dark:text-white hover:text-brand-600 block">
                                        {{ $rc->name }}
                                    </a>
                                    <span class="text-[11px] text-gray-400 dark:text-slate-500">By {{ $rc->creator?->name ?? 'System' }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $badgeColor = match($rc->status) {
                                            'sent' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
                                            'sending', 'scheduled' => 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
                                            'draft' => 'bg-gray-100 text-gray-700 dark:bg-slate-800 dark:text-slate-300',
                                            default => 'bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $badgeColor }}">
                                        {{ ucfirst($rc->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-semibold text-dark-900 dark:text-white">
                                    {{ number_format($rc->audience_count) }}
                                </td>
                                <td class="py-3.5 px-4 text-right text-gray-500 dark:text-slate-400 text-[11px]">
                                    {{ $rc->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-400 dark:text-slate-500 text-xs">
                                    No campaigns recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(51, 65, 85, 0.4)' : 'rgba(226, 232, 240, 0.8)';
    const brandColor = '#946E19'; // Buckcrest gold

    // 1. Channel Distribution Doughnut Chart
    const channelCtx = document.getElementById('channelChart');
    if (channelCtx) {
        const emailCount = {{ $channelCounts->get('email')?->count ?? 0 }};
        const smsCount = {{ $channelCounts->get('sms')?->count ?? 0 }};
        const waCount = {{ $channelCounts->get('whatsapp')?->count ?? 0 }};
        const totalChannels = emailCount + smsCount + waCount;

        new Chart(channelCtx, {
            type: 'doughnut',
            data: {
                labels: ['Email Broadcast', 'SMS Campaigns', 'WhatsApp Alerts'],
                datasets: [{
                    data: totalChannels > 0 ? [emailCount, smsCount, waCount] : [1, 0, 0],
                    backgroundColor: totalChannels > 0 ? ['#3b82f6', '#f59e0b', '#10b981'] : ['#cbd5e1'],
                    borderColor: isDark ? '#0f172a' : '#ffffff',
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
                            padding: 14
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (totalChannels === 0) return 'No campaigns recorded yet';
                                const val = context.raw || 0;
                                const pct = Math.round((val / totalChannels) * 100);
                                return ` ${context.label}: ${val} (${pct}%)`;
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }

    // 2. Status Breakdown Doughnut Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const sentCount = {{ $statusCounts['sent'] ?? 0 }};
        const draftCount = {{ $statusCounts['draft'] ?? 0 }};
        const activeCount = {{ ($statusCounts['sending'] ?? 0) + ($statusCounts['scheduled'] ?? 0) }};
        const otherCount = {{ ($statusCounts['paused'] ?? 0) + ($statusCounts['cancelled'] ?? 0) }};
        const totalStatus = sentCount + draftCount + activeCount + otherCount;

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Sent / Completed', 'Draft Mode', 'Active / In-Flight', 'Paused / Other'],
                datasets: [{
                    data: totalStatus > 0 ? [sentCount, draftCount, activeCount, otherCount] : [1, 0, 0, 0],
                    backgroundColor: totalStatus > 0 ? ['#10b981', '#94a3b8', '#f59e0b', '#f43f5e'] : ['#cbd5e1'],
                    borderColor: isDark ? '#0f172a' : '#ffffff',
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
                            padding: 14
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }

    // Monthly Data arrays
    @php
        $mLabels = $monthlyData->pluck('month_label')->toArray();
        $mCounts = $monthlyData->pluck('campaign_count')->toArray();
        $mSent = $monthlyData->pluck('total_sent')->toArray();
        $mOpened = $monthlyData->pluck('total_opened')->toArray();
        $mClicked = $monthlyData->pluck('total_clicked')->toArray();

        // Calculate open and click rates per month
        $mOpenRates = [];
        $mClickRates = [];
        foreach ($monthlyData as $md) {
            $s = (int) $md->total_sent;
            $mOpenRates[] = $s > 0 ? round(($md->total_opened / $s) * 100, 1) : 0;
            $mClickRates[] = $s > 0 ? round(($md->total_clicked / $s) * 100, 1) : 0;
        }

        // Fallback placeholder month labels if empty
        if (empty($mLabels)) {
            $mLabels = [now()->subMonths(2)->format('M Y'), now()->subMonths(1)->format('M Y'), now()->format('M Y')];
            $mCounts = [0, 0, 0];
            $mSent = [0, 0, 0];
            $mOpenRates = [0, 0, 0];
            $mClickRates = [0, 0, 0];
        }
    @endphp

    const monthlyLabels = {!! json_encode($mLabels) !!};
    const monthlyCounts = {!! json_encode($mCounts) !!};
    const monthlySent = {!! json_encode($mSent) !!};
    const monthlyOpenRates = {!! json_encode($mOpenRates) !!};
    const monthlyClickRates = {!! json_encode($mClickRates) !!};

    // 3. Monthly Dispatch Volume Bar & Line Chart
    const monthlyVolumeCtx = document.getElementById('monthlyVolumeChart');
    if (monthlyVolumeCtx) {
        new Chart(monthlyVolumeCtx, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Dispatched Messages',
                        data: monthlySent,
                        backgroundColor: 'rgba(148, 110, 25, 0.85)', // Brand gold
                        borderRadius: 8,
                        yAxisID: 'y'
                    },
                    {
                        type: 'line',
                        label: 'Campaigns Created',
                        data: monthlyCounts,
                        borderColor: '#3b82f6',
                        backgroundColor: '#3b82f6',
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: textColor,
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: 'bold' }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 10 } }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 10 } }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        ticks: { color: '#3b82f6', font: { family: 'Plus Jakarta Sans', size: 10 }, stepSize: 1 }
                    }
                }
            }
        });
    }

    // 4. Monthly Engagement Rates Line Chart
    const engagementCtx = document.getElementById('engagementTrendChart');
    if (engagementCtx) {
        new Chart(engagementCtx, {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [
                    {
                        label: 'Open Rate (%)',
                        data: monthlyOpenRates,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Click Rate (%)',
                        data: monthlyClickRates,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.08)',
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: textColor,
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.dataset.label}: ${context.raw}%`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: gridColor },
                        ticks: {
                            color: textColor,
                            font: { family: 'Plus Jakarta Sans', size: 10 },
                            callback: function(value) { return value + '%'; }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
