@extends('layouts.app')

@section('title', 'Construction Inventory General Ledger')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.dashboard') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Inventory Cockpit</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">General Ledger</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Construction Double-Entry General Ledger</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Automated double-entry journals for gate delivery asset capitalization, WIP job costing, and 3-way match tax postings.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.dashboard') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-all">
                &larr; Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Chart of Accounts Summary -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-slate-800 p-6 space-y-4 shadow-sm">
        <h3 class="font-bold text-base text-slate-900 dark:text-white">Chart of Accounts Matrix</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($accounts as $acc)
                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-800">
                    <div class="flex items-center justify-between">
                        <span class="font-mono font-bold text-brand-600 text-sm">{{ $acc->account_code }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $acc->account_type === 'asset' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : ($acc->account_type === 'liability' ? 'bg-purple-50 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300') }}">
                            {{ $acc->account_type }}
                        </span>
                    </div>
                    <div class="font-bold text-slate-900 dark:text-white text-xs mt-1.5">{{ $acc->account_name }}</div>
                    <p class="text-[11px] text-gray-400 mt-1 line-clamp-2">{{ $acc->description }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- General Ledger Journal Entries Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-slate-800 overflow-hidden shadow-sm space-y-4">
        <div class="p-6 border-b border-gray-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Journal Entries Ledger</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-gray-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-6">Entry No &amp; Date</th>
                        <th class="py-3.5 px-6">Site Yard &amp; Project</th>
                        <th class="py-3.5 px-6">Narration / Description</th>
                        <th class="py-3.5 px-6">Account Postings (Debits &amp; Credits)</th>
                        <th class="py-3.5 px-6 text-right">Entry Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($journals as $je)
                        <tr class="hover:bg-gray-50/75 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900 dark:text-white">
                                {{ $je->entry_number }}
                                <span class="block text-xs font-normal text-gray-400">{{ $je->entry_date->format('M d, Y') }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $je->site?->name ?? 'Company Head Office' }}</div>
                                <span class="text-xs text-gray-400">{{ $je->project?->name ?? 'General Inventory' }}</span>
                            </td>
                            <td class="py-4 px-6 text-xs max-w-xs font-medium text-slate-800 dark:text-slate-200">
                                {{ $je->description }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="space-y-1.5 text-xs font-mono">
                                    @foreach($je->items as $item)
                                        <div class="flex items-center justify-between gap-4">
                                            <span class="{{ $item->entry_type === 'debit' ? 'text-blue-600 font-bold' : 'text-purple-600 pl-3 font-semibold' }}">
                                                {{ $item->entry_type === 'debit' ? 'DR' : 'CR' }} {{ $item->account_code }} - {{ $item->account?->account_name }}
                                            </span>
                                            <span class="font-bold text-slate-900 dark:text-white">
                                                ₦{{ number_format($item->amount, 2) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold text-base text-slate-900 dark:text-white">
                                ₦{{ number_format($je->total_debit, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                No double-entry journals recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($journals->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-slate-800">
                {{ $journals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
