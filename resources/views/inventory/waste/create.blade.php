@extends('layouts.app')

@section('title', 'Record Material Waste / Loss')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.waste.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Waste Registry</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Log Incident</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Record Material Waste / Damage Incident</h1>
        </div>
        <a href="{{ route('inventory.waste.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-all">
            Cancel
        </a>
    </div>

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

    <form method="POST" action="{{ route('inventory.waste.store') }}" enctype="multipart/form-data" class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-6 shadow-sm">
        @csrf

        @if($selectedMiv)
            <input type="hidden" name="miv_id" value="{{ $selectedMiv->id }}">
            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-sm flex items-center justify-between">
                <div>
                    <span class="font-bold text-amber-900 dark:text-amber-200">Linked to MIV: {{ $selectedMiv->ref_number }}</span>
                    <p class="text-xs text-amber-700 dark:text-amber-300">Activity: {{ $selectedMiv->activity_name }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Site -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Site Location <span class="text-rose-500">*</span></label>
                <select name="site_id" required class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select Site</option>
                    @foreach($sites as $s)
                        <option value="{{ $s->id }}" {{ (old('site_id', $selectedMiv?->site_id) == $s->id) ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Material -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Wasted / Damaged Material <span class="text-rose-500">*</span></label>
                <select name="material_id" required class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select Material</option>
                    @foreach($materials as $m)
                        <option value="{{ $m->id }}" {{ old('material_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->name }} ({{ $m->unit_of_measure }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Quantity -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Quantity Lost / Damaged <span class="text-rose-500">*</span></label>
                <input type="number" step="0.001" name="qty" value="{{ old('qty') }}" placeholder="e.g. 15.00" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Classification -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Incident Classification <span class="text-rose-500">*</span></label>
                <select name="waste_type" required class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="avoidable">Avoidable Waste (Handling error / poor storage)</option>
                    <option value="unavoidable">Unavoidable Waste (Normal cut-offs / minor spillage)</option>
                    <option value="loss">Damage / Environmental Loss (Rain / flood / collapsed formwork)</option>
                    <option value="theft_suspected">Suspected Theft / Pilferage</option>
                </select>
            </div>

            <!-- Subcontractor / Team -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Responsible Gang / Subcontractor Team</label>
                <input type="text" name="responsible_team" value="{{ old('responsible_team') }}" placeholder="e.g. Tiling Gang 2, Blocklaying Gang"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Weather -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Weather Condition</label>
                <input type="text" name="weather_condition" value="{{ old('weather_condition') }}" placeholder="e.g. Heavy rainfall, High humidity"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <!-- Description -->
        <div class="space-y-1">
            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Root Cause &amp; Description <span class="text-rose-500">*</span></label>
            <textarea name="description" rows="3" placeholder="Detailed explanation of how the material damage or loss occurred..." required
                      class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('description') }}</textarea>
        </div>

        <!-- Toggles -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
            <div class="flex items-center gap-3">
                <input type="checkbox" id="deduct_from_stock" name="deduct_from_stock" value="1" {{ old('deduct_from_stock', '1') ? 'checked' : '' }}
                       class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300 dark:border-slate-700">
                <label for="deduct_from_stock" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                    Debit lost quantity from site store balance immediately
                </label>
            </div>
            <div class="flex items-center gap-3">
                <input type="checkbox" id="insurance_claim_raised" name="insurance_claim_raised" value="1" {{ old('insurance_claim_raised') ? 'checked' : '' }}
                       class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300 dark:border-slate-700">
                <label for="insurance_claim_raised" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                    Escalate incident for Contractor All-Risk (CAR) Insurance Claim
                </label>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-slate-800 flex justify-end gap-3">
            <a href="{{ route('inventory.waste.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-sm font-bold transition-all">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                Save Incident Log
            </button>
        </div>
    </form>
</div>
@endsection
