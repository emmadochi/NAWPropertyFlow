@extends('layouts.app')

@section('title', 'Goods Received Note ' . $grn->ref_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.grn.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Goods Received</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400 font-mono">{{ $grn->ref_number }}</span>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $grn->ref_number }}</h1>
                @if($grn->geofence_check_passed)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">📍 On-Site Geofence Verified</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300">⚠️ Outside Geofence Boundary</span>
                @endif
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">PO: <a href="{{ route('inventory.purchase-orders.show', $grn->purchaseOrder) }}" class="text-brand-600 font-bold hover:underline">{{ $grn->purchaseOrder?->ref_number }}</a> • Site: <strong>{{ $grn->site?->name }}</strong></p>
        </div>
    </div>

    <!-- Delivery Detail Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Delivery Time</div>
            <div class="text-lg font-black text-slate-900 dark:text-white mt-1">
                {{ $grn->delivery_date->format('M d, Y') }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Time: {{ $grn->delivery_time }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Waybill &amp; Vehicle</div>
            <div class="text-lg font-black text-slate-900 dark:text-white mt-1">
                {{ $grn->waybill_number ?? 'N/A' }}
            </div>
            <div class="text-xs font-mono text-gray-400 mt-1">Plate: {{ $grn->vehicle_plate ?? 'N/A' }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Driver Contact</div>
            <div class="text-lg font-black text-slate-900 dark:text-white mt-1">
                {{ $grn->driver_name ?? 'N/A' }}
            </div>
            <div class="text-xs text-gray-400 mt-1">{{ $grn->driver_phone ?? 'No phone' }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Storekeeper</div>
            <div class="text-lg font-black text-slate-900 dark:text-white mt-1">
                {{ $grn->receiver?->name }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Stock balance credited</div>
        </div>
    </div>

    <!-- Delivered Items Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-slate-800">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Delivered Building Materials &amp; QC Status</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-gray-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Material</th>
                        <th class="py-3 px-4 text-center">Batch Number</th>
                        <th class="py-3 px-4 text-center">Qty Accepted</th>
                        <th class="py-3 px-4 text-center">Qty Rejected</th>
                        <th class="py-3 px-4 text-right">Expiry Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @foreach($grn->items as $item)
                        <tr class="hover:bg-gray-50/75 dark:hover:bg-slate-800/40">
                            <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">
                                {{ $item->material?->name }}
                                <span class="block text-xs font-mono text-gray-400">{{ $item->material?->code }} ({{ $item->material?->unit_of_measure }})</span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-xs">
                                {{ $item->batch_number ?? 'No batch code' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-600">
                                {{ number_format($item->qty_received, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-rose-600">
                                {{ number_format($item->qty_rejected, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right text-xs text-gray-500">
                                {{ $item->expiry_date ? $item->expiry_date->format('M d, Y') : 'N/A' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
