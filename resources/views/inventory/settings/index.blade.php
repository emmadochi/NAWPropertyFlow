@extends('layouts.app')

@section('title', 'Construction Inventory Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Dashboard</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Inventory Setup</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Construction Inventory Settings</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Configure PO authorization thresholds, delivery geofence rules, and fraud detection multipliers.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300 text-sm">
            <p class="font-bold mb-1">Please fix the following errors:</p>
            <ul class="list-disc pl-5 space-y-0.5 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.settings.update') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-6 shadow-sm">
        @csrf
        @method('PUT')

        <!-- PO Approval Thresholds -->
        <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 border-b border-gray-100 dark:border-slate-800 pb-2">
                1. Purchase Order (PO) Approval Tiers
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">
                        Tier 1 Max Threshold (Project Manager Limit) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-sm font-bold text-gray-400">₦</span>
                        <input type="number" step="1000" name="po_tier1_max" value="{{ old('po_tier1_max', $settings->po_tier1_max) }}" required
                               class="w-full pl-8 pr-3 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-mono font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <p class="text-[11px] text-gray-400">POs up to this amount can be approved by the Site / Project Manager alone.</p>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">
                        Tier 2 Max Threshold (Managing Director / MD Limit) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-sm font-bold text-gray-400">₦</span>
                        <input type="number" step="1000" name="po_tier2_max" value="{{ old('po_tier2_max', $settings->po_tier2_max) }}" required
                               class="w-full pl-8 pr-3 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-mono font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <p class="text-[11px] text-gray-400">POs exceeding this amount escalate to Board of Directors / Super Admin sign-off.</p>
                </div>
            </div>
        </div>

        <!-- Geofencing & Delivery Gate Rules -->
        <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 border-b border-gray-100 dark:border-slate-800 pb-2">
                2. Gate Delivery &amp; Geofencing Rules
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">After-Hours Start Time</label>
                    <input type="time" name="after_hours_start" value="{{ old('after_hours_start', substr($settings->after_hours_start, 0, 5)) }}" required
                           class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">After-Hours End Time</label>
                    <input type="time" name="after_hours_end" value="{{ old('after_hours_end', substr($settings->after_hours_end, 0, 5)) }}" required
                           class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Cement Shelf Life (Days)</label>
                    <input type="number" name="cement_shelf_life_days" value="{{ old('cement_shelf_life_days', $settings->cement_shelf_life_days) }}" min="30" max="365" required
                           class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div class="md:col-span-3 flex items-center gap-3 pt-2">
                    <input type="checkbox" id="grn_geofence_strict" name="grn_geofence_strict" value="1" {{ old('grn_geofence_strict', $settings->grn_geofence_strict) ? 'checked' : '' }}
                           class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300 dark:border-slate-700">
                    <label for="grn_geofence_strict" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Enforce Strict GPS Geofencing on Gate Deliveries (Flag deliveries recorded outside site radius)
                    </label>
                </div>
            </div>
        </div>

        <!-- Anomaly & Fraud Detection Limits -->
        <div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 border-b border-gray-100 dark:border-slate-800 pb-2">
                3. Anomaly &amp; Fraud Detection Sensitivity
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Waste Spike Multiplier</label>
                    <input type="number" step="0.1" name="waste_alert_multiplier" value="{{ old('waste_alert_multiplier', $settings->waste_alert_multiplier) }}" min="1" max="10" required
                           class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <p class="text-[11px] text-gray-400">Flag if waste rate exceeds X times 30-day baseline (e.g. 1.5x).</p>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Consecutive Exact Match Limit</label>
                    <input type="number" name="perfect_match_consecutive_limit" value="{{ old('perfect_match_consecutive_limit', $settings->perfect_match_consecutive_limit) }}" min="2" max="20" required
                           class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <p class="text-[11px] text-gray-400">Flag suspected uninspected deliveries if GRN matches PO exactly N times.</p>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Price Variance Alert (%)</label>
                    <input type="number" step="0.1" name="price_variance_alert_pct" value="{{ old('price_variance_alert_pct', $settings->price_variance_alert_pct) }}" min="1" max="100" required
                           class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <p class="text-[11px] text-gray-400">Alert if quoted purchase price exceeds regional benchmark by this %.</p>
                </div>

                <div class="space-y-1 md:col-span-3">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Vendor-Storekeeper Pairing Threshold (Monthly)</label>
                    <input type="number" name="staff_pairing_occurrences_limit" value="{{ old('staff_pairing_occurrences_limit', $settings->staff_pairing_occurrences_limit) }}" min="2" max="20" required
                           class="w-full max-w-xs py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <p class="text-[11px] text-gray-400">Alert for potential collusion if the same storekeeper signs for the same vendor N times in 30 days.</p>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-slate-800 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                Save Inventory Settings
            </button>
        </div>
    </form>
</div>
@endsection
