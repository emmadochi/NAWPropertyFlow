@extends('layouts.app')

@section('title', 'Profit & Loss Statement (Income Statement)')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-16">
    <!-- Header with Date Filters & PDF Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-gray-200 dark:border-slate-800 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('accounting.dashboard') }}" class="text-xs text-brand-600 font-bold hover:underline">&larr; Financial Cockpit</a>
                <span class="text-slate-400">/</span>
                <span class="text-xs text-slate-500 font-bold uppercase">Income Statement</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Profit &amp; Loss Statement</h1>
            <p class="text-xs text-slate-500">Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
        </div>

        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none">
                <span class="text-slate-400 text-xs">to</span>
                <input type="date" name="end_date" value="{{ $endDate }}" onchange="this.form.submit()"
                       class="py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none">
            </form>
            <button onclick="window.print()" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-brand-600/20 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print / PDF</span>
            </button>
        </div>
    </div>

    <!-- P&L Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-8 print:p-0 print:border-none print:shadow-none">
        <!-- 1. REVENUE -->
        <div class="space-y-3">
            <h2 class="text-sm font-black uppercase tracking-wider text-emerald-600 border-b border-emerald-500/20 pb-2">
                1. Operating Revenue
            </h2>
            <div class="divide-y divide-gray-100 dark:divide-slate-800 text-xs">
                @forelse($pAndL['revenues'] as $item)
                    <div class="flex justify-between py-2">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">[{{ $item['code'] }}] {{ $item['name'] }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">₦{{ number_format($item['amount'], 2) }}</span>
                    </div>
                @empty
                    <div class="py-2 text-slate-400 italic">No revenue transactions in this period.</div>
                @endforelse
                <div class="flex justify-between py-2 font-bold text-slate-900 dark:text-white bg-emerald-50 dark:bg-emerald-950/30 px-3 rounded-xl mt-1">
                    <span>Total Operating Revenue</span>
                    <span class="text-emerald-600">₦{{ number_format($pAndL['total_revenue'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- 2. COST OF CONSTRUCTION (COGS) -->
        <div class="space-y-3">
            <h2 class="text-sm font-black uppercase tracking-wider text-amber-600 border-b border-amber-500/20 pb-2">
                2. Cost of Construction &amp; Direct Materials (COGS / WIP)
            </h2>
            <div class="divide-y divide-gray-100 dark:divide-slate-800 text-xs">
                @forelse($pAndL['cogs'] as $item)
                    <div class="flex justify-between py-2">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">[{{ $item['code'] }}] {{ $item['name'] }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">₦{{ number_format($item['amount'], 2) }}</span>
                    </div>
                @empty
                    <div class="py-2 text-slate-400 italic">No direct construction costs incurred in this period.</div>
                @endforelse
                <div class="flex justify-between py-2 font-bold text-slate-900 dark:text-white bg-amber-50 dark:bg-amber-950/30 px-3 rounded-xl mt-1">
                    <span>Total Cost of Construction</span>
                    <span class="text-amber-600">&minus; ₦{{ number_format($pAndL['total_cogs'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- GROSS PROFIT -->
        <div class="flex justify-between p-4 rounded-2xl bg-purple-500/10 border border-purple-500/30 text-sm font-black text-purple-600 dark:text-purple-400">
            <span>GROSS PROFIT (Margin: {{ $pAndL['gross_margin_pct'] }}%)</span>
            <span>₦{{ number_format($pAndL['gross_profit'], 2) }}</span>
        </div>

        <!-- 3. OPERATING EXPENSES -->
        <div class="space-y-3">
            <h2 class="text-sm font-black uppercase tracking-wider text-rose-600 border-b border-rose-500/20 pb-2">
                3. Operating &amp; Administrative Overheads
            </h2>
            <div class="divide-y divide-gray-100 dark:divide-slate-800 text-xs">
                @forelse($pAndL['operating_expenses'] as $item)
                    <div class="flex justify-between py-2">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">[{{ $item['code'] }}] {{ $item['name'] }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">₦{{ number_format($item['amount'], 2) }}</span>
                    </div>
                @empty
                    <div class="py-2 text-slate-400 italic">No operating overheads in this period.</div>
                @endforelse
                <div class="flex justify-between py-2 font-bold text-slate-900 dark:text-white bg-rose-50 dark:bg-rose-950/30 px-3 rounded-xl mt-1">
                    <span>Total Operating Expenses</span>
                    <span class="text-rose-600">&minus; ₦{{ number_format($pAndL['total_operating_expenses'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- NET OPERATING PROFIT -->
        <div class="flex justify-between p-5 rounded-2xl {{ $pAndL['net_profit'] >= 0 ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/15 border-rose-500/30 text-rose-600 dark:text-rose-400' }} border text-base font-black">
            <div class="space-y-0.5">
                <div>NET PROFIT FOR THE PERIOD</div>
                <div class="text-xs font-normal opacity-80">Net Margin: {{ $pAndL['net_margin_pct'] }}%</div>
            </div>
            <div>₦{{ number_format($pAndL['net_profit'], 2) }}</div>
        </div>
    </div>
</div>
@endsection
