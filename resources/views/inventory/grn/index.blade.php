@extends('layouts.app')

@section('title', 'Goods Received Notes (GRN)')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Dashboard</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Goods Received (GRN)</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Gate Deliveries &amp; Goods Received Notes (GRN)</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Log incoming deliveries with GPS geofence checks, waybills, driver details, and stock credits.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.grn.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Record Gate Delivery</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-gray-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">GRN Ref</th>
                        <th class="py-3.5 px-4">PO Reference</th>
                        <th class="py-3.5 px-4">Site &amp; Supplier</th>
                        <th class="py-3.5 px-4">Waybill &amp; Vehicle</th>
                        <th class="py-3.5 px-4 text-center">GPS Geofence</th>
                        <th class="py-3.5 px-4 text-center">Received By</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($notes as $grn)
                        <tr class="hover:bg-gray-50/75 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                <a href="{{ route('inventory.grn.show', $grn) }}" class="hover:text-brand-600 hover:underline">
                                    {{ $grn->ref_number }}
                                </a>
                                <span class="block text-[11px] text-gray-400 font-normal">{{ $grn->delivery_date->format('M d, Y') }} at {{ $grn->delivery_time }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-800 dark:text-slate-200">
                                {{ $grn->purchaseOrder?->ref_number }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $grn->purchaseOrder?->supplier?->name }}</div>
                                <span class="text-xs text-gray-400">Site: {{ $grn->site?->name }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div>WB: <strong>{{ $grn->waybill_number ?? 'N/A' }}</strong></div>
                                <span class="text-gray-400">{{ $grn->vehicle_plate ?? 'No plate' }} ({{ $grn->driver_name ?? 'Driver N/A' }})</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($grn->geofence_check_passed)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                        📍 On-Site Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                        ⚠️ Outside Geofence
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center text-xs">
                                {{ $grn->receiver?->name }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('inventory.grn.show', $grn) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    View GRN
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <p class="font-bold text-slate-600 dark:text-slate-300">No goods receipts logged yet.</p>
                                <a href="{{ route('inventory.grn.create') }}" class="mt-3 inline-block px-4 py-2 bg-brand-600 text-white rounded-xl text-xs font-bold">Record Gate Delivery</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($notes->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-slate-800">
                {{ $notes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
