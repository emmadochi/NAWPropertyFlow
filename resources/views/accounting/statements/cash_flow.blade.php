@extends('layouts.app')

@section('title', 'Statement of Cash Flows')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-16">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-gray-200 dark:border-slate-800 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('accounting.dashboard') }}" class="text-xs text-brand-600 font-bold hover:underline">&larr; Financial Cockpit</a>
                <span class="text-slate-400">/</span>
                <span class="text-xs text-slate-500 font-bold uppercase">Statement of Cash Flows</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Cash Flow Statement</h1>
            <p class="text-xs text-slate-500">Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }} (Direct Method)</p>
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

    <!-- Cash Flow Statement -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6 print:p-0 print:border-none print:shadow-none">
        <div class="space-y-4">
            <h2 class="text-sm font-black uppercase tracking-wider text-brand-600 border-b border-brand-500/20 pb-2">
                Cash Flow from Operating Activities
            </h2>

            <div class="divide-y divide-gray-100 dark:divide-slate-800 text-xs">
                <div class="flex justify-between py-3">
                    <div>
                        <strong class="text-slate-900 dark:text-white block">Cash Inflows from Property Sales &amp; Collections</strong>
                        <span class="text-[11px] text-slate-500">Customer milestone deposits &amp; agency fees</span>
                    </div>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">
                        + ₦{{ number_format($cashFlow['cash_inflows'], 2) }}
                    </span>
                </div>

                <div class="flex justify-between py-3">
                    <div>
                        <strong class="text-slate-900 dark:text-white block">Cash Outflows for Materials, Payroll &amp; Overheads</strong>
                        <span class="text-[11px] text-slate-500">Supplier disbursements, site labor, rent, and utility expenses</span>
                    </div>
                    <span class="font-bold text-rose-600 dark:text-rose-400">
                        &minus; ₦{{ number_format($cashFlow['cash_outflows'], 2) }}
                    </span>
                </div>
            </div>

            <!-- Net Cash Flow -->
            <div class="flex justify-between p-5 rounded-2xl {{ $cashFlow['net_cash_flow'] >= 0 ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/15 border-rose-500/30 text-rose-600 dark:text-rose-400' }} border text-base font-black">
                <span>NET OPERATING CASH FLOW</span>
                <span>₦{{ number_format($cashFlow['net_cash_flow'], 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
