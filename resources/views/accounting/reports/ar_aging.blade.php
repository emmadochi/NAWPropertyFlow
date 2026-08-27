@extends('layouts.app')

@section('title', 'Accounts Receivable (AR) Aging Analysis')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 pb-16">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-gray-200 dark:border-slate-800 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('accounting.dashboard') }}" class="text-xs text-brand-600 font-bold hover:underline">&larr; Financial Cockpit</a>
                <span class="text-slate-400">/</span>
                <span class="text-xs text-slate-500 font-bold uppercase">Receivables Aging Matrix</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Accounts Receivable (AR) Aging</h1>
            <p class="text-xs text-slate-500">Property buyer payment installments broken down into 30 / 60 / 90+ days aging buckets.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('accounting.reports.ap-aging') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl text-xs font-bold transition-all">
                Switch to AP Payables &rarr;
            </a>
            <button onclick="window.print()" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-brand-600/20 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print / PDF</span>
            </button>
        </div>
    </div>

    <!-- 4 Aging Bucket Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($report['buckets'] as $key => $bucket)
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 shadow-sm space-y-2">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span class="font-bold uppercase tracking-wider">{{ $bucket['label'] }}</span>
                    <span class="px-2 py-0.5 rounded-full {{ $key === 'over_90' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400' : 'bg-gray-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }} text-[10px] font-bold">
                        {{ $bucket['count'] }} Debts
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    ₦{{ number_format($bucket['total'], 2) }}
                </div>
                <p class="text-[11px] text-slate-400">
                    {{ $report['total_receivables'] > 0 ? round(($bucket['total'] / $report['total_receivables']) * 100, 1) : 0 }}% of total receivables
                </p>
            </div>
        @endforeach
    </div>

    <!-- Detailed Debtor Installments Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-4">
        <h3 class="text-base font-black text-slate-900 dark:text-white">Outstanding Buyer Installment Schedules</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-slate-800/80 uppercase tracking-wider text-[10px] font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Buyer Name</th>
                        <th class="py-3 px-4">Property Unit</th>
                        <th class="py-3 px-4">Milestone</th>
                        <th class="py-3 px-4">Due Date</th>
                        <th class="py-3 px-4">Days Overdue</th>
                        <th class="py-3 px-4 text-right">Amount Due</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @php $allInstallments = collect($report['buckets'])->pluck('items')->flatten(1); @endphp
                    @forelse($allInstallments as $item)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-800/40">
                            <td class="py-3 px-4">
                                <strong class="text-slate-900 dark:text-white block">{{ $item['customer_name'] }}</strong>
                                <span class="text-[11px] text-slate-400">{{ $item['customer_phone'] }}</span>
                            </td>
                            <td class="py-3 px-4">{{ $item['property_title'] }}</td>
                            <td class="py-3 px-4">{{ $item['title'] }}</td>
                            <td class="py-3 px-4">{{ \Carbon\Carbon::parse($item['due_date'])->format('M d, Y') }}</td>
                            <td class="py-3 px-4">
                                @if($item['days_overdue'] > 90)
                                    <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 font-bold text-[10px]">
                                        {{ $item['days_overdue'] }} Days (Critical)
                                    </span>
                                @elseif($item['days_overdue'] > 30)
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 font-bold text-[10px]">
                                        {{ $item['days_overdue'] }} Days
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 font-bold text-[10px]">
                                        Current
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                ₦{{ number_format($item['amount'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 italic">No outstanding buyer receivables. All installments up to date!</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 dark:bg-slate-800/90 font-black text-xs text-slate-900 dark:text-white border-t border-gray-200 dark:border-slate-700">
                    <tr>
                        <td colspan="5" class="py-4 px-4 uppercase tracking-wider">Total Accounts Receivable</td>
                        <td class="py-4 px-4 text-right font-mono text-brand-600">₦{{ number_format($report['total_receivables'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
