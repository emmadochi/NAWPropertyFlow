@extends('layouts.app')

@section('title', 'Define BOM Consumption Benchmark')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.bom.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Bill of Materials</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">New Rate</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Define Standard Consumption Rate</h1>
        </div>
        <a href="{{ route('inventory.bom.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-all">
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

    <form method="POST" action="{{ route('inventory.bom.store') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-6 shadow-sm">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Construction Activity -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Construction Work Activity <span class="text-rose-500">*</span></label>
                <input type="text" name="activity_name" value="{{ old('activity_name') }}" placeholder="e.g. 1:2:4 Concrete Pour, 9-inch Block Laying, Floor Tiling" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Material Selection -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Required Material <span class="text-rose-500">*</span></label>
                <select name="material_id" required class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select Material SKU</option>
                    @foreach($materials as $m)
                        <option value="{{ $m->id }}" {{ old('material_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->name }} ({{ $m->code }} - {{ $m->unit_of_measure }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Qty Per Unit -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Standard Quantity Needed <span class="text-rose-500">*</span></label>
                <input type="number" step="0.0001" name="qty_per_unit" value="{{ old('qty_per_unit') }}" placeholder="e.g. 6.0000" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <p class="text-[11px] text-gray-400">Standard quantity consumed for each 1 unit of work completed.</p>
            </div>

            <!-- Unit of Work -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Unit of Finished Work <span class="text-rose-500">*</span></label>
                <input type="text" name="unit_of_work" value="{{ old('unit_of_work', 'm3') }}" placeholder="e.g. m3, m2, linear meters, floor, block" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Allowable Variance % -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Allowable Variance Tolerance (±%) <span class="text-rose-500">*</span></label>
                <input type="number" step="0.1" name="allowable_variance_pct" value="{{ old('allowable_variance_pct', 10.0) }}" min="0" max="100" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <p class="text-[11px] text-gray-400">Issues exceeding this % above standard will trigger an over-consumption warning.</p>
            </div>

            <!-- Project Scope (Optional) -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Project Scope (Leave empty for Global)</label>
                <select name="project_id" class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">🌐 Global Company-Wide Standard</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>
                            Specific to Project: {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-slate-800 flex justify-end gap-3">
            <a href="{{ route('inventory.bom.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-sm font-bold transition-all">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                Save Benchmark
            </button>
        </div>
    </form>
</div>
@endsection
