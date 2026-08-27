@extends('layouts.app')

@section('title', 'Audited Trial Balance Matrix')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-16">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-gray-200 dark:border-slate-800 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('accounting.dashboard') }}" class="text-xs text-brand-600 font-bold hover:underline">&larr; Financial Cockpit</a>
                <span class="text-slate-400">/</span>
                <span class="text-xs text-slate-500 font-bold uppercase">Audited General Ledger</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Trial Balance Matrix</h1>
            <p class="text-xs text-slate-500">As of {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }} (Double-Entry Balanced)</p>
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

    <!-- Trial Balance Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-slate-800/80 uppercase tracking-wider text-[10px] font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4">Account Title</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4 text-right">Debit (DR)</th>
                        <th class="py-3 px-4 text-right">Credit (CR)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($trialBalance['rows'] as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-800/40">
                            <td class="py-3 px-4 font-mono font-bold text-brand-600">{{ $row['code'] }}</td>
                            <td class="py-3 px-4 font-medium text-slate-900 dark:text-white">{{ $row['name'] }}</td>
                            <td class="py-3 px-4 uppercase text-[10px] text-slate-400">{{ $row['type'] }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                {{ $row['debit'] > 0 ? '₦' . number_format($row['debit'], 2) : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                {{ $row['credit'] > 0 ? '₦' . number_format($row['credit'], 2) : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 italic">No ledger activity found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 dark:bg-slate-800/90 font-black text-xs text-slate-900 dark:text-white border-t border-gray-200 dark:border-slate-700">
                    <tr>
                        <td colspan="3" class="py-4 px-4 uppercase tracking-wider">Total Balanced Ledger</td>
                        <td class="py-4 px-4 text-right font-mono text-brand-600">₦{{ number_format($trialBalance['total_debit'], 2) }}</td>
                        <td class="py-4 px-4 text-right font-mono text-brand-600">₦{{ number_format($trialBalance['total_credit'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
