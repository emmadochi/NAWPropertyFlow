@extends('layouts.app')

@section('title', 'Anomaly Incident #' . $anomaly->id)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.anomalies.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Fraud Radar</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Incident #{{ $anomaly->id }}</span>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $anomaly->title }}</h1>
                @if($anomaly->severity === 'critical')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-200">🚨 Critical</span>
                @elseif($anomaly->severity === 'high')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300">High</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300">Medium</span>
                @endif
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Flag Type: <strong class="uppercase font-mono text-slate-800 dark:text-white">{{ str_replace('_', ' ', $anomaly->flag_type) }}</strong> • Site: <strong>{{ $anomaly->site?->name ?? 'Global' }}</strong></p>
        </div>
    </div>

    <!-- Details Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-4 shadow-sm">
        <h3 class="font-bold text-base text-slate-900 dark:text-white">Incident Trigger Details</h3>
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200 text-sm font-medium">
            {{ $anomaly->description }}
        </div>

        <div class="grid grid-cols-2 gap-4 text-xs text-gray-500 pt-2 border-t border-gray-100 dark:border-slate-800">
            <div>Detected: <strong>{{ $anomaly->created_at->format('M d, Y H:i:s') }}</strong></div>
            <div>Current Status: <strong class="uppercase text-slate-800 dark:text-white">{{ $anomaly->status }}</strong></div>
        </div>
    </div>

    <!-- Resolution Form -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-4 shadow-sm">
        <h3 class="font-bold text-base text-slate-900 dark:text-white">Management Resolution &amp; Corrective Action</h3>

        @if($anomaly->resolution_notes)
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-sm">
                <span class="text-xs font-bold uppercase text-gray-400 block">Existing Resolution Notes (by {{ $anomaly->resolver?->name ?? 'Admin' }} on {{ $anomaly->resolved_at?->format('M d, Y') }}):</span>
                <p class="mt-1 text-slate-800 dark:text-slate-200">{{ $anomaly->resolution_notes }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('inventory.anomalies.update-status', $anomaly) }}" class="space-y-4">
            @csrf

            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Update Incident Status <span class="text-rose-500">*</span></label>
                <select name="status" required class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="under_review" {{ $anomaly->status === 'under_review' ? 'selected' : '' }}>Under Review / Investigation</option>
                    <option value="resolved" {{ $anomaly->status === 'resolved' ? 'selected' : '' }}>Resolved (Legitimate Variance / Corrective Action Taken)</option>
                    <option value="dismissed" {{ $anomaly->status === 'dismissed' ? 'selected' : '' }}>Dismissed (False Positive)</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Investigation Findings &amp; Corrective Action Taken <span class="text-rose-500">*</span></label>
                <textarea name="resolution_notes" rows="4" placeholder="Detail interview with storekeeper, site engineer notes, or vendor credit note agreement..." required
                          class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('resolution_notes', $anomaly->resolution_notes) }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('inventory.anomalies.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-sm font-bold transition-all">
                    Back to Radar
                </a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                    Save Resolution
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
