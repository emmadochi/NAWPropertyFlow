@extends('layouts.app')

@section('title', 'Construction Inventory & Cost Valuation Executive Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Main CRM</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Inventory &amp; Materials Cockpit</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Executive Inventory &amp; Cost Cockpit</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Real-time FIFO stock valuation, project work-in-progress (WIP) material burn, and fraud radar intelligence.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.general-ledger.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Double-Entry General Ledger</span>
            </a>
            <a href="{{ route('inventory.requisitions.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-brand-500/30 transition-all">
                <span>+ Raise Site Requisition</span>
            </a>
        </div>
    </div>

    <!-- Executive Top 4 Financial Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- 1. Live Asset Valuation -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200/80 dark:border-slate-800 p-6 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs font-bold uppercase text-slate-400">
                <span>Site Stock Asset (FIFO)</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">📦</span>
            </div>
            <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                ₦{{ number_format($totalStockValuation, 2) }}
            </div>
            <div class="text-xs text-gray-500 flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Active physical stock on all yards</span>
            </div>
        </div>

        <!-- 2. Project WIP Material Cost -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200/80 dark:border-slate-800 p-6 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs font-bold uppercase text-slate-400">
                <span>Project WIP Material Cost</span>
                <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400">🏗️</span>
            </div>
            <div class="text-3xl font-black text-blue-600 dark:text-blue-400 tracking-tight">
                ₦{{ number_format($issuedStocks, 2) }}
            </div>
            <div class="text-xs text-gray-500 flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                <span>Materials issued (MIV) to building works</span>
            </div>
        </div>

        <!-- 3. Unbilled GRN Deliveries (GRNI) -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200/80 dark:border-slate-800 p-6 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs font-bold uppercase text-slate-400">
                <span>Pending Vendor Invoices</span>
                <span class="p-1.5 rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400">⏳</span>
            </div>
            <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                {{ $unbilledGrnCount }} Deliveries
            </div>
            <div class="text-xs text-gray-500 flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-purple-500"></span>
                <span>Gate deliveries awaiting 3-way match</span>
            </div>
        </div>

        <!-- 4. Fraud Risk Radar -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200/80 dark:border-slate-800 p-6 space-y-2 shadow-sm">
            <div class="flex items-center justify-between text-xs font-bold uppercase text-slate-400">
                <span>Fraud Radar Alert Index</span>
                <span class="p-1.5 rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400">🚨</span>
            </div>
            <div class="text-3xl font-black {{ $criticalAnomaliesCount > 0 ? 'text-rose-600' : 'text-slate-900 dark:text-white' }} tracking-tight">
                {{ $openAnomaliesCount }} Flags
            </div>
            <div class="text-xs text-gray-500 flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full {{ $criticalAnomaliesCount > 0 ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                <span>{{ $criticalAnomaliesCount }} Critical incidents requiring audit</span>
            </div>
        </div>
    </div>

    <!-- Stock Valuation by Site & Material Category Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Site Breakdown -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-slate-800 p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-base text-slate-900 dark:text-white">Material Valuation by Site Yard</h3>
                <a href="{{ route('inventory.sites.index') }}" class="text-xs font-bold text-brand-600 hover:underline">Manage Sites &rarr;</a>
            </div>

            <div class="space-y-3">
                @forelse($sitesBreakdown as $sb)
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $sb['name'] }}</div>
                            <span class="text-xs text-gray-400">{{ $sb['project'] }} • {{ $sb['total_skus'] }} Active SKUs</span>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold text-slate-900 dark:text-white text-sm">₦{{ number_format($sb['stock_value'], 2) }}</span>
                            <span class="block text-[11px] text-emerald-600 font-medium">On-Site Asset</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">No active site inventory yards found.</p>
                @endforelse
            </div>
        </div>

        <!-- Material Category Breakdown -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-slate-800 p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-base text-slate-900 dark:text-white">Stock Allocation by Category</h3>
                <a href="{{ route('inventory.catalogue.index') }}" class="text-xs font-bold text-brand-600 hover:underline">Material Catalogue &rarr;</a>
            </div>

            <div class="space-y-3">
                @forelse($categoriesBreakdown as $cb)
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-950/60 dark:text-brand-400 flex items-center justify-center text-xs font-bold">
                                🏗️
                            </div>
                            <div>
                                <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $cb['category'] }}</div>
                                <span class="text-xs text-gray-400">Live Inventory Asset</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold text-slate-900 dark:text-white text-sm">₦{{ number_format($cb['value'], 2) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">No material stock balances recorded.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Reorder Level Alerts & Recent Accounting Journals -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Low Stock Alerts -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-slate-800 p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-base text-slate-900 dark:text-white">Critical Reorder Alerts</h3>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300">Below Safety Buffer</span>
            </div>

            <div class="space-y-3">
                @forelse($lowStockWarnings as $warn)
                    <div class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-800/40 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $warn->material?->name }}</div>
                            <span class="text-xs text-gray-500">Site: {{ $warn->site?->name }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold text-rose-600 text-sm">{{ $warn->qty_on_hand }} {{ $warn->material?->unit_of_measure }}</span>
                            <span class="block text-[11px] text-gray-500">Reorder at {{ $warn->material?->reorder_level }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-emerald-600 font-semibold">
                        ✓ All site material buffers are healthy above safety stock levels.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Double-Entry Journal Postings -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-slate-800 p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-base text-slate-900 dark:text-white">Recent Double-Entry Journal Postings</h3>
                <a href="{{ route('inventory.general-ledger.index') }}" class="text-xs font-bold text-brand-600 hover:underline">Full General Ledger &rarr;</a>
            </div>

            <div class="space-y-3">
                @forelse($recentJournals as $j)
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold text-brand-600 text-xs">{{ $j->entry_number }}</span>
                            <div class="text-xs font-medium text-slate-800 dark:text-slate-200 truncate max-w-xs">{{ $j->description }}</div>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold text-slate-900 dark:text-white text-sm">₦{{ number_format($j->total_debit, 2) }}</span>
                            <span class="block text-[11px] text-gray-400">{{ $j->entry_date->format('M d, Y') }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">No general ledger journal postings yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
