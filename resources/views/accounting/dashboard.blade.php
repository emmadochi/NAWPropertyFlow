@extends('layouts.app')

@section('title', 'Financial Intelligence & Accounting Cockpit')

@section('content')
<div class="space-y-8 pb-16">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 p-6 sm:p-8 rounded-3xl border border-slate-800 shadow-2xl text-white">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-black uppercase tracking-wider">
                <span>🏛️ Enterprise IFRS General Ledger</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Financial Intelligence Cockpit</h1>
            <p class="text-xs sm:text-sm text-slate-400 max-w-2xl">
                Unified real-time financial reporting connecting Property Sales, Multi-Site Construction WIP, Office Overheads, and Bank Treasury.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('accounting.statements.balance-sheet') }}" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-2xl text-xs font-black uppercase tracking-wider shadow-lg shadow-brand-600/30 transition-transform hover:scale-105">
                Balance Sheet &rarr;
            </a>
            <a href="{{ route('accounting.statements.p-and-l') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-2xl text-xs font-black uppercase tracking-wider transition-all">
                P&amp;L Income &rarr;
            </a>
        </div>
    </div>

    <!-- Quick Navigation Hub -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <a href="{{ route('accounting.statements.balance-sheet') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 hover:border-brand-500/50 transition-all text-center space-y-1 shadow-sm group">
            <span class="text-2xl block group-hover:scale-110 transition-transform">⚖️</span>
            <strong class="text-xs font-bold text-slate-900 dark:text-white block">Balance Sheet</strong>
            <span class="text-[10px] text-slate-500 block">Assets vs Liabilities</span>
        </a>

        <a href="{{ route('accounting.statements.p-and-l') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 hover:border-brand-500/50 transition-all text-center space-y-1 shadow-sm group">
            <span class="text-2xl block group-hover:scale-110 transition-transform">📈</span>
            <strong class="text-xs font-bold text-slate-900 dark:text-white block">Profit &amp; Loss</strong>
            <span class="text-[10px] text-slate-500 block">Revenue &amp; Net Margin</span>
        </a>

        <a href="{{ route('accounting.statements.cash-flow') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 hover:border-brand-500/50 transition-all text-center space-y-1 shadow-sm group">
            <span class="text-2xl block group-hover:scale-110 transition-transform">💵</span>
            <strong class="text-xs font-bold text-slate-900 dark:text-white block">Cash Flow</strong>
            <span class="text-[10px] text-slate-500 block">Operating In/Outflow</span>
        </a>

        <a href="{{ route('accounting.treasury.index') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 hover:border-brand-500/50 transition-all text-center space-y-1 shadow-sm group">
            <span class="text-2xl block group-hover:scale-110 transition-transform">🏦</span>
            <strong class="text-xs font-bold text-slate-900 dark:text-white block">Multi-Bank Treasury</strong>
            <span class="text-[10px] text-slate-500 block">Reconcile Statements</span>
        </a>

        <a href="{{ route('accounting.reports.ar-aging') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 hover:border-brand-500/50 transition-all text-center space-y-1 shadow-sm group">
            <span class="text-2xl block group-hover:scale-110 transition-transform">⏳</span>
            <strong class="text-xs font-bold text-slate-900 dark:text-white block">AR / AP Aging</strong>
            <span class="text-[10px] text-slate-500 block">30/60/90+ Day Debts</span>
        </a>

        <a href="{{ route('accounting.tax.index') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 hover:border-brand-500/50 transition-all text-center space-y-1 shadow-sm group">
            <span class="text-2xl block group-hover:scale-110 transition-transform">📜</span>
            <strong class="text-xs font-bold text-slate-900 dark:text-white block">Tax &amp; FIRS Hub</strong>
            <span class="text-[10px] text-slate-500 block">5% WHT &amp; 7.5% VAT</span>
        </a>
    </div>

    <!-- Executive KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Assets -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span class="font-bold uppercase tracking-wider">Total Assets</span>
                <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-600 font-bold">Balance Sheet</span>
            </div>
            <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                ₦{{ number_format($balanceSheet['total_assets'], 2) }}
            </div>
            <p class="text-[11px] text-slate-500">
                Material Stock (₦{{ number_format(collect($balanceSheet['current_assets'])->where('code', '1300')->sum('balance'), 2) }}) + Bank Liquidity
            </p>
        </div>

        <!-- Total Payables (AP) -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span class="font-bold uppercase tracking-wider">Total Trade Payables</span>
                <span class="p-1.5 rounded-lg bg-rose-500/10 text-rose-600 font-bold">Creditors</span>
            </div>
            <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                ₦{{ number_format($balanceSheet['total_current_liabilities'], 2) }}
            </div>
            <p class="text-[11px] text-slate-500">
                Approved supplier liabilities &amp; unbilled GRNI deliveries
            </p>
        </div>

        <!-- Working Capital -->
        @php
            $workingCapital = $balanceSheet['total_current_assets'] - $balanceSheet['total_current_liabilities'];
        @endphp
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span class="font-bold uppercase tracking-wider">Net Working Capital</span>
                <span class="p-1.5 rounded-lg {{ $workingCapital >= 0 ? 'bg-blue-500/10 text-blue-600' : 'bg-rose-500/10 text-rose-600' }} font-bold">Liquidity</span>
            </div>
            <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                ₦{{ number_format($workingCapital, 2) }}
            </div>
            <p class="text-[11px] text-slate-500">
                Current Assets &minus; Current Liabilities
            </p>
        </div>

        <!-- Net Profit YTD -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span class="font-bold uppercase tracking-wider">Net Margin (YTD)</span>
                <span class="p-1.5 rounded-lg bg-brand-500/10 text-brand-600 font-bold">P&amp;L</span>
            </div>
            <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                ₦{{ number_format($pAndL['net_profit'], 2) }}
            </div>
            <p class="text-[11px] text-slate-500">
                Net Margin: <strong class="text-brand-600">{{ $pAndL['net_margin_pct'] }}%</strong> across operations
            </p>
        </div>
    </div>

    <!-- Two Column Deep Insights -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- 1. Balance Sheet Snapshot Matrix -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span>⚖️ Statement of Financial Position</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 text-[10px] font-bold">
                        {{ $balanceSheet['is_balanced'] ? '✓ Balanced' : 'Variance' }}
                    </span>
                </h3>
                <a href="{{ route('accounting.statements.balance-sheet') }}" class="text-xs text-brand-600 font-bold hover:underline">Full Statement &rarr;</a>
            </div>

            <div class="space-y-3 text-xs">
                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-200 dark:border-slate-700/60 space-y-2">
                    <div class="flex justify-between font-bold text-slate-900 dark:text-white">
                        <span>Total Assets</span>
                        <span>₦{{ number_format($balanceSheet['total_assets'], 2) }}</span>
                    </div>
                    <div class="pl-3 space-y-1 text-slate-500 border-l-2 border-brand-500">
                        <div class="flex justify-between">
                            <span>Current Assets (Cash, Stock, AR)</span>
                            <span>₦{{ number_format($balanceSheet['total_current_assets'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Non-Current &amp; Fixed Assets</span>
                            <span>₦{{ number_format($balanceSheet['total_non_current_assets'], 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-200 dark:border-slate-700/60 space-y-2">
                    <div class="flex justify-between font-bold text-slate-900 dark:text-white">
                        <span>Total Liabilities &amp; Equity</span>
                        <span>₦{{ number_format($balanceSheet['total_liabilities_and_equity'], 2) }}</span>
                    </div>
                    <div class="pl-3 space-y-1 text-slate-500 border-l-2 border-indigo-500">
                        <div class="flex justify-between">
                            <span>Total Liabilities (Trade AP, WHT, GRNI)</span>
                            <span>₦{{ number_format($balanceSheet['total_liabilities'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total Equity (Capital + Net Income)</span>
                            <span>₦{{ number_format($balanceSheet['total_equity'], 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Income & Construction Cost Breakdown -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span>📈 Profit &amp; Loss Highlights</span>
                </h3>
                <a href="{{ route('accounting.statements.p-and-l') }}" class="text-xs text-brand-600 font-bold hover:underline">Full Statement &rarr;</a>
            </div>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 font-bold text-slate-900 dark:text-white">
                    <span>Gross Operating Revenue</span>
                    <span class="text-emerald-600 dark:text-emerald-400">₦{{ number_format($pAndL['total_revenue'], 2) }}</span>
                </div>

                <div class="flex justify-between p-3 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 font-bold text-slate-900 dark:text-white">
                    <span>Direct Construction Costs (COGS / MIV)</span>
                    <span class="text-amber-600 dark:text-amber-400">&minus; ₦{{ number_format($pAndL['total_cogs'], 2) }}</span>
                </div>

                <div class="flex justify-between p-3 rounded-2xl bg-purple-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800/40 font-bold text-slate-900 dark:text-white">
                    <span>Gross Profit Margin</span>
                    <span class="text-purple-600 dark:text-purple-400">₦{{ number_format($pAndL['gross_profit'], 2) }} ({{ $pAndL['gross_margin_pct'] }}%)</span>
                </div>

                <div class="flex justify-between p-3 rounded-2xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 font-bold text-slate-900 dark:text-white">
                    <span>Operating Expenses (Salaries, Marketing, Rent)</span>
                    <span class="text-rose-600 dark:text-rose-400">&minus; ₦{{ number_format($pAndL['total_operating_expenses'], 2) }}</span>
                </div>

                <div class="flex justify-between p-4 rounded-2xl bg-brand-500/10 border border-brand-500/30 font-black text-sm text-brand-600 dark:text-brand-400">
                    <span>Net Operating Profit</span>
                    <span>₦{{ number_format($pAndL['net_profit'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
