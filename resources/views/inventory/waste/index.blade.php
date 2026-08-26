@extends('layouts.app')

@section('title', 'Material Waste & Loss Registry')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Dashboard</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Waste &amp; Loss Logs</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Material Waste &amp; Loss Registry</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Classify avoidable/unavoidable site losses, subcontractor damages, and weather destruction.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.waste.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Record Waste Incident</span>
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
                        <th class="py-3.5 px-4">Date Logged</th>
                        <th class="py-3.5 px-4">Site &amp; Material</th>
                        <th class="py-3.5 px-4 text-center">Classification</th>
                        <th class="py-3.5 px-4 text-center">Wasted Qty</th>
                        <th class="py-3.5 px-4">Cause Description</th>
                        <th class="py-3.5 px-4 text-right">Logged By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/75 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 text-xs font-mono">
                                {{ $log->created_at->format('M d, Y') }}
                                <span class="block text-[11px] text-gray-400">{{ $log->created_at->format('H:i') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $log->material?->name }}</div>
                                <span class="text-xs text-gray-400">Site: {{ $log->site?->name }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($log->waste_type === 'avoidable')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                        Avoidable Waste
                                    </span>
                                @elseif($log->waste_type === 'theft_suspected')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                        🚨 Suspected Theft
                                    </span>
                                @elseif($log->waste_type === 'loss')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-orange-50 text-orange-700 dark:bg-orange-950 dark:text-orange-300">
                                        Damage / Loss
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 dark:bg-slate-800 dark:text-gray-300">
                                        Unavoidable
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-rose-600">
                                {{ number_format($log->qty, 2) }} {{ $log->material?->unit_of_measure }}
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <div class="text-slate-800 dark:text-slate-200">{{ $log->description }}</div>
                                @if($log->responsible_team)
                                    <span class="text-gray-400">Subcontractor: {{ $log->responsible_team }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right text-xs">
                                {{ $log->logger?->name }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <p class="font-bold text-slate-600 dark:text-slate-300">No waste or damage incidents recorded.</p>
                                <a href="{{ route('inventory.waste.create') }}" class="mt-3 inline-block px-4 py-2 bg-brand-600 text-white rounded-xl text-xs font-bold">Record Incident</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-slate-800">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
