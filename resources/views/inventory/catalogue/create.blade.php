@extends('layouts.app')

@section('title', 'Add Material to Catalogue')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.catalogue.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Material Catalogue</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Add Material</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Add Building Material SKU</h1>
        </div>
        <a href="{{ route('inventory.catalogue.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-all">
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

    <form method="POST" action="{{ route('inventory.catalogue.store') }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-6 shadow-sm">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Material Name -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Material Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Dangote Falcon Cement 50kg" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Material Code -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Material Code <span class="text-rose-500">*</span></label>
                <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. CEM-DNG-50" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white uppercase font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Category -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Category <span class="text-rose-500">*</span></label>
                <select name="category" required class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select Category</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Unit of Measure -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Unit of Measure (UoM) <span class="text-rose-500">*</span></label>
                <input type="text" name="unit_of_measure" value="{{ old('unit_of_measure') }}" placeholder="e.g. bags, tonnes, m3, litres, pieces" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Standard Unit Cost -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Standard Estimated Cost (₦) <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" name="standard_unit_cost" value="{{ old('standard_unit_cost') }}" placeholder="0.00" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Reorder Level -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Reorder Threshold Level <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" name="reorder_level" value="{{ old('reorder_level', 0) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <p class="text-[11px] text-gray-400">Triggers reorder warnings when site stock drops below this.</p>
            </div>

            <!-- Safety Stock Level -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Safety Buffer Level <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" name="safety_stock_level" value="{{ old('safety_stock_level', 0) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <p class="text-[11px] text-gray-400">Critical minimum buffer before site halt risk.</p>
            </div>

            <!-- Shelf Life Days -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Shelf Life (Days - Optional)</label>
                <input type="number" name="shelf_life_days" value="{{ old('shelf_life_days') }}" placeholder="e.g. 90 for cement"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Batch Tracking Checkbox -->
            <div class="md:col-span-2 flex items-center gap-3 pt-2">
                <input type="checkbox" id="is_trackable_by_batch" name="is_trackable_by_batch" value="1" {{ old('is_trackable_by_batch') ? 'checked' : '' }}
                       class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300 dark:border-slate-700">
                <label for="is_trackable_by_batch" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    Enable Batch / Lot Number Tracking (Recommended for Cement, Chemical additives, Paint)
                </label>
            </div>

            <!-- Notes -->
            <div class="md:col-span-2 space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Specifications &amp; Storage Notes</label>
                <textarea name="notes" rows="3" placeholder="Handling instructions, required certifications, moisture protection..."
                          class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-slate-800 flex justify-end gap-3">
            <a href="{{ route('inventory.catalogue.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-sm font-bold transition-all">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                Save Material
            </button>
        </div>
    </form>
</div>
@endsection
