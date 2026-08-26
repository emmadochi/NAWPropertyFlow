@extends('layouts.app')

@section('title', $site->name . ' - Site Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.sites.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Inventory Sites</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400 font-mono">{{ $site->code }}</span>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $site->name }}</h1>
                @if($site->is_active)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Active</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 dark:bg-slate-800 dark:text-gray-400">Inactive</span>
                @endif
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Linked to Project: <strong>{{ $site->project?->name }}</strong> ({{ $site->project?->location }})</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.sites.edit', $site) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-50 hover:bg-brand-100 text-brand-700 dark:bg-brand-950 dark:text-brand-300 rounded-xl text-xs font-bold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>Edit Site Info</span>
            </a>
        </div>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Tracked Materials</div>
            <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $site->stock->count() }}</div>
            <div class="text-xs text-gray-400 mt-1">Unique catalogue SKUs on site</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Geofence Radius</div>
            <div class="text-2xl font-black text-brand-600 dark:text-brand-400 mt-1">{{ $site->geofence_radius_meters }}m</div>
            <div class="text-xs text-gray-400 mt-1">GPS Delivery validation zone</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Recent Deliveries</div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $site->goodsReceivedNotes->count() }}</div>
            <div class="text-xs text-gray-400 mt-1">Total GRNs recorded</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Material Requisitions</div>
            <div class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1">{{ $site->requisitions->count() }}</div>
            <div class="text-xs text-gray-400 mt-1">Site engineer MRFs</div>
        </div>
    </div>

    <!-- On-Site Stock Balance Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-slate-800 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-base text-slate-900 dark:text-white">Current Stock Balance on Site</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Real-time physical inventory levels and reorder alerts.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-gray-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Material Code &amp; Name</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4 text-right">On Hand</th>
                        <th class="py-3.5 px-4 text-right">Reserved (In-Transit)</th>
                        <th class="py-3.5 px-4 text-center">Reorder Level</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($site->stock as $stk)
                        @php
                            $mat = $stk->material;
                            $isCritical = $stk->qty_on_hand <= $mat->safety_stock_level;
                            $isReorder = $stk->qty_on_hand <= $mat->reorder_level && !$isCritical;
                        @endphp
                        <tr class="hover:bg-gray-50/75 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">
                                <div>{{ $mat->name }}</div>
                                <span class="text-xs font-mono text-gray-400">{{ $mat->code }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="capitalize text-xs px-2 py-0.5 rounded-lg bg-gray-100 text-gray-700 dark:bg-slate-800 dark:text-slate-300">{{ str_replace('_', ' ', $mat->category) }}</span>
                            </td>
                            <td class="py-3 px-4 text-right font-black font-mono {{ $isCritical ? 'text-rose-600' : ($isReorder ? 'text-amber-600' : 'text-slate-900 dark:text-white') }}">
                                {{ number_format($stk->qty_on_hand, 2) }} <span class="text-xs font-normal text-gray-400">{{ $mat->unit_of_measure }}</span>
                            </td>
                            <td class="py-3 px-4 text-right text-gray-500 font-mono text-xs">
                                {{ number_format($stk->qty_reserved, 2) }} {{ $mat->unit_of_measure }}
                            </td>
                            <td class="py-3 px-4 text-center text-xs text-gray-500">
                                {{ number_format($mat->reorder_level, 2) }} {{ $mat->unit_of_measure }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($isCritical)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                        🔴 Critical Low
                                    </span>
                                @elseif($isReorder)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                        🟡 Reorder Soon
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                        🟢 Healthy
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-sm">
                                No stock records logged on this site yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
