@extends('layouts.app')

@section('title', 'Statement of Financial Position (Balance Sheet)')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-16">
    <!-- Header with Date Filter & PDF Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-gray-200 dark:border-slate-800 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('accounting.dashboard') }}" class="text-xs text-brand-600 font-bold hover:underline">&larr; Financial Cockpit</a>
                <span class="text-slate-400">/</span>
                <span class="text-xs text-slate-500 font-bold uppercase">Statement of Financial Position</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Balance Sheet</h1>
            <p class="text-xs text-slate-500">As of {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }} (IFRS Standard)</p>
        </div>

        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="as_of_date" value="{{ $asOfDate }}" onchange="this.form.submit()"
                       class="py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none">
            </form>
            <button onclick="window.print()" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-brand-600/20 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print / PDF</span>
            </button>
        </div>
    </div>

    <!-- Balance Sheet Statement Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-8 print:p-0 print:border-none print:shadow-none">
        <!-- 1. ASSETS SECTION -->
        <div class="space-y-4">
            <h2 class="text-sm font-black uppercase tracking-wider text-brand-600 border-b border-brand-500/20 pb-2">
                1. Assets
            </h2>

            <!-- Current Assets -->
            <div class="space-y-2">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Current Assets</h3>
                <div class="divide-y divide-gray-100 dark:divide-slate-800 text-xs">
                    @forelse($balanceSheet['current_assets'] as $item)
                        <div class="flex justify-between py-2">
                            <span class="text-slate-700 dark:text-slate-300 font-medium">[{{ $item['code'] }}] {{ $item['name'] }}</span>
                            <span class="font-bold text-slate-900 dark:text-white">₦{{ number_format($item['balance'], 2) }}</span>
                        </div>
                    @empty
                        <div class="py-2 text-slate-400 italic">No current asset balances recorded.</div>
                    @endforelse
                    <div class="flex justify-between py-2 font-bold text-slate-900 dark:text-white bg-gray-50 dark:bg-slate-800/60 px-3 rounded-xl mt-1">
                        <span>Total Current Assets</span>
                        <span class="text-brand-600">₦{{ number_format($balanceSheet['total_current_assets'], 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Non-Current Assets -->
            @if(count($balanceSheet['non_current_assets']) > 0)
            <div class="space-y-2 pt-2">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Non-Current &amp; Fixed Assets</h3>
                <div class="divide-y divide-gray-100 dark:divide-slate-800 text-xs">
                    @foreach($balanceSheet['non_current_assets'] as $item)
                        <div class="flex justify-between py-2">
                            <span class="text-slate-700 dark:text-slate-300 font-medium">[{{ $item['code'] }}] {{ $item['name'] }}</span>
                            <span class="font-bold text-slate-900 dark:text-white">₦{{ number_format($item['balance'], 2) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between py-2 font-bold text-slate-900 dark:text-white bg-gray-50 dark:bg-slate-800/60 px-3 rounded-xl mt-1">
                        <span>Total Non-Current Assets</span>
                        <span>₦{{ number_format($balanceSheet['total_non_current_assets'], 2) }}</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- TOTAL ASSETS -->
            <div class="flex justify-between p-4 rounded-2xl bg-brand-500/10 border border-brand-500/30 text-sm font-black text-brand-600 dark:text-brand-400">
                <span class="uppercase tracking-wider">TOTAL ASSETS</span>
                <span>₦{{ number_format($balanceSheet['total_assets'], 2) }}</span>
            </div>
        </div>

        <!-- 2. LIABILITIES SECTION -->
        <div class="space-y-4">
            <h2 class="text-sm font-black uppercase tracking-wider text-rose-600 border-b border-rose-500/20 pb-2">
                2. Liabilities
            </h2>

            <!-- Current Liabilities -->
            <div class="space-y-2">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Current Liabilities</h3>
                <div class="divide-y divide-gray-100 dark:divide-slate-800 text-xs">
                    @forelse($balanceSheet['current_liabilities'] as $item)
                        <div class="flex justify-between py-2">
                            <span class="text-slate-700 dark:text-slate-300 font-medium">[{{ $item['code'] }}] {{ $item['name'] }}</span>
                            <span class="font-bold text-slate-900 dark:text-white">₦{{ number_format($item['balance'], 2) }}</span>
                        </div>
                    @empty
                        <div class="py-2 text-slate-400 italic">No current liability balances recorded.</div>
                    @endforelse
                    <div class="flex justify-between py-2 font-bold text-slate-900 dark:text-white bg-gray-50 dark:bg-slate-800/60 px-3 rounded-xl mt-1">
                        <span>Total Current Liabilities</span>
                        <span class="text-rose-600">₦{{ number_format($balanceSheet['total_current_liabilities'], 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- TOTAL LIABILITIES -->
            <div class="flex justify-between p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-sm font-black text-rose-600 dark:text-rose-400">
                <span class="uppercase tracking-wider">TOTAL LIABILITIES</span>
                <span>₦{{ number_format($balanceSheet['total_liabilities'], 2) }}</span>
            </div>
        </div>

        <!-- 3. EQUITY SECTION -->
        <div class="space-y-4">
            <h2 class="text-sm font-black uppercase tracking-wider text-indigo-600 border-b border-indigo-500/20 pb-2">
                3. Equity &amp; Retained Earnings
            </h2>

            <div class="divide-y divide-gray-100 dark:divide-slate-800 text-xs">
                @foreach($balanceSheet['equity_accounts'] as $item)
                    <div class="flex justify-between py-2">
                        <span class="text-slate-700 dark:text-slate-300 font-medium">[{{ $item['code'] }}] {{ $item['name'] }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">₦{{ number_format($item['balance'], 2) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between py-2">
                    <span class="text-slate-700 dark:text-slate-300 font-medium">Current Period Net Income (P&amp;L)</span>
                    <span class="font-bold {{ $balanceSheet['current_period_net_income'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        ₦{{ number_format($balanceSheet['current_period_net_income'], 2) }}
                    </span>
                </div>
                <div class="flex justify-between py-2 font-bold text-slate-900 dark:text-white bg-gray-50 dark:bg-slate-800/60 px-3 rounded-xl mt-1">
                    <span>Total Equity</span>
                    <span class="text-indigo-600">₦{{ number_format($balanceSheet['total_equity'], 2) }}</span>
                </div>
            </div>

            <!-- TOTAL LIABILITIES & EQUITY -->
            <div class="flex justify-between p-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/30 text-sm font-black text-indigo-600 dark:text-indigo-400">
                <span class="uppercase tracking-wider">TOTAL LIABILITIES &amp; EQUITY</span>
                <span>₦{{ number_format($balanceSheet['total_liabilities_and_equity'], 2) }}</span>
            </div>
        </div>

        <!-- Verification Seal -->
        <div class="p-4 rounded-2xl {{ $balanceSheet['is_balanced'] ? 'bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/40 text-rose-700 dark:text-rose-300' }} flex items-center justify-between text-xs font-bold">
            <div class="flex items-center gap-2">
                <span>{{ $balanceSheet['is_balanced'] ? '✓' : '⚠️' }}</span>
                <span>{{ $balanceSheet['is_balanced'] ? 'Statement of Financial Position is 100% Balanced (Assets = Liabilities + Equity)' : 'Unbalanced Variance: ₦' . number_format($balanceSheet['variance'], 2) }}</span>
            </div>
            <span>IFRS Standard</span>
        </div>
    </div>
</div>
@endsection
