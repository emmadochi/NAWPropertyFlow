@extends('layouts.app')

@section('title', 'Material Issue Voucher ' . $miv->ref_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.miv.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Material Issues</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400 font-mono">{{ $miv->ref_number }}</span>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $miv->ref_number }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Issued Complete</span>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Activity: <strong class="text-slate-800 dark:text-white">{{ $miv->activity_name }}</strong> • Site: <strong>{{ $miv->site?->name }}</strong></p>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Issue Date &amp; Time</div>
            <div class="text-lg font-black text-slate-900 dark:text-white mt-1">
                {{ $miv->created_at->format('M d, Y') }}
            </div>
            <div class="text-xs text-gray-400 mt-1">{{ $miv->created_at->format('H:i A') }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Storekeeper</div>
            <div class="text-lg font-black text-slate-900 dark:text-white mt-1">
                {{ $miv->issuer?->name }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Store Custodian</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Recipient Foreman</div>
            <div class="text-lg font-black text-slate-900 dark:text-white mt-1">
                {{ $miv->receiver?->name }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Workforce Lead</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Material Lines</div>
            <div class="text-lg font-black text-brand-600 dark:text-brand-400 mt-1">
                {{ $miv->items->count() }} SKUs
            </div>
            <div class="text-xs text-gray-400 mt-1">FIFO Stock Debited</div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-slate-800">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Disbursed Materials</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-gray-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Material SKU</th>
                        <th class="py-3 px-4 text-center">Batch Number</th>
                        <th class="py-3 px-4 text-center">Qty Issued</th>
                        <th class="py-3 px-4 text-right">Standard Cost Ext.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @foreach($miv->items as $item)
                        <tr class="hover:bg-gray-50/75 dark:hover:bg-slate-800/40">
                            <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">
                                {{ $item->material?->name }}
                                <span class="block text-xs font-mono text-gray-400">{{ $item->material?->code }} ({{ $item->material?->unit_of_measure }})</span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-xs">
                                {{ $item->batch?->batch_number ?? 'Auto FIFO' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-900 dark:text-white">
                                {{ number_format($item->qty_issued, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                ₦{{ number_format($item->qty_issued * ($item->material?->standard_unit_cost ?? 0), 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
