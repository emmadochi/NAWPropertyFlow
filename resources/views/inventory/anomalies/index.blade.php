@extends('layouts.app')

@section('title', 'AI & Algorithmic Fraud Anomaly Radar')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Dashboard</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Fraud &amp; Anomaly Engine</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Algorithmic Fraud &amp; Loss Radar</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Automated detection of ghost deliveries, staff-vendor collusion, price benchmark spikes, and waste spikes.</p>
        </div>
    </div>

    <!-- Stat Alert Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Open Incidents</div>
            <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">
                {{ $openCount }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Requiring management review</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Critical Red Flags</div>
            <div class="text-2xl font-black text-rose-600 mt-1">
                {{ $criticalCount }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Geofence breaches &amp; massive spikes</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Collusion Radar</div>
            <div class="text-2xl font-black text-amber-600 mt-1">
                Active
            </div>
            <div class="text-xs text-gray-400 mt-1">Storekeeper-Supplier frequency check</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Price Benchmark Alert</div>
            <div class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1">
                Monitored
            </div>
            <div class="text-xs text-gray-400 mt-1">Regional cost index comparison</div>
        </div>
    </div>

    <!-- Anomaly Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-gray-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Date Flagged</th>
                        <th class="py-3.5 px-4">Site Location</th>
                        <th class="py-3.5 px-4">Flag Type</th>
                        <th class="py-3.5 px-4 text-center">Severity</th>
                        <th class="py-3.5 px-4">Title &amp; Trigger Details</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($anomalies as $flag)
                        <tr class="hover:bg-gray-50/75 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 text-xs font-mono">
                                {{ $flag->created_at->format('M d, Y') }}
                                <span class="block text-[11px] text-gray-400">{{ $flag->created_at->format('H:i') }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">
                                {{ $flag->site?->name ?? 'Global' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-xs uppercase">
                                {{ str_replace('_', ' ', $flag->flag_type) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($flag->severity === 'critical')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-200">
                                        🚨 Critical
                                    </span>
                                @elseif($flag->severity === 'high')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                        High
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                        Medium
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-800 dark:text-slate-200 max-w-xs">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $flag->title }}</div>
                                <span class="text-gray-500 block truncate">{{ $flag->description }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($flag->status === 'open')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300">Open</span>
                                @elseif($flag->status === 'under_review')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300">Under Review</span>
                                @elseif($flag->status === 'resolved')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Resolved</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-800 text-white dark:bg-slate-700">{{ ucfirst($flag->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('inventory.anomalies.show', $flag) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    Investigate
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <p class="font-bold text-slate-600 dark:text-slate-300">✓ No fraud or loss anomalies detected. All operations normal.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($anomalies->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-slate-800">
                {{ $anomalies->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
