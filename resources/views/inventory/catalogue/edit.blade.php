@extends('layouts.app')

@section('title', 'Edit Material - ' . $material->name)

@section('content')
<div x-data="{
    categoryModalOpen: false,
    newCatName: '',
    newCatDesc: '',
    isCreatingCat: false,
    catError: null,
    categories: @js($categories),
    selectedCategory: '{{ old('category', $material->category) }}',
    async submitNewCategory() {
        if (!this.newCatName.trim()) return;
        this.isCreatingCat = true;
        this.catError = null;

        try {
            const res = await fetch('{{ route('inventory.categories.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: this.newCatName,
                    description: this.newCatDesc,
                    is_active: true
                })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                this.categories[data.category.slug] = data.category.name;
                this.selectedCategory = data.category.slug;
                this.newCatName = '';
                this.newCatDesc = '';
                this.categoryModalOpen = false;
            } else {
                this.catError = data.message || 'Failed to add category. Please check input.';
            }
        } catch (e) {
            this.catError = 'An error occurred while creating the category.';
        } finally {
            this.isCreatingCat = false;
        }
    }
}" class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.catalogue.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Material Catalogue</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Edit Material</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Edit Material: {{ $material->name }}</h1>
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

    <form method="POST" action="{{ route('inventory.catalogue.update', $material) }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Material Name -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Material Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $material->name) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Material Code -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Material Code <span class="text-rose-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $material->code) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white uppercase font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Category -->
            <div class="space-y-1">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Category <span class="text-rose-500">*</span></label>
                    <button type="button" @click="categoryModalOpen = true" class="text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 flex items-center gap-1 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>New Category</span>
                    </button>
                </div>
                <select name="category" x-model="selectedCategory" required class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select Category</option>
                    <template x-for="(label, key) in categories" :key="key">
                        <option :value="key" x-text="label" :selected="selectedCategory === key"></option>
                    </template>
                </select>
            </div>

            <!-- Unit of Measure -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Unit of Measure (UoM) <span class="text-rose-500">*</span></label>
                <input type="text" name="unit_of_measure" value="{{ old('unit_of_measure', $material->unit_of_measure) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Standard Unit Cost -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Standard Estimated Cost (₦) <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" name="standard_unit_cost" value="{{ old('standard_unit_cost', $material->standard_unit_cost) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Reorder Level -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Reorder Threshold Level <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" name="reorder_level" value="{{ old('reorder_level', $material->reorder_level) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Safety Stock Level -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Safety Buffer Level <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" name="safety_stock_level" value="{{ old('safety_stock_level', $material->safety_stock_level) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Shelf Life Days -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Shelf Life (Days)</label>
                <input type="number" name="shelf_life_days" value="{{ old('shelf_life_days', $material->shelf_life_days) }}"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Batch Tracking Checkbox -->
            <div class="md:col-span-2 flex items-center gap-3 pt-2">
                <input type="checkbox" id="is_trackable_by_batch" name="is_trackable_by_batch" value="1" {{ old('is_trackable_by_batch', $material->is_trackable_by_batch) ? 'checked' : '' }}
                       class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300 dark:border-slate-700">
                <label for="is_trackable_by_batch" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    Enable Batch / Lot Number Tracking
                </label>
            </div>

            <!-- Notes -->
            <div class="md:col-span-2 space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Specifications &amp; Notes</label>
                <textarea name="notes" rows="3"
                          class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('notes', $material->notes) }}</textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-slate-800 flex justify-between items-center">
            <button type="button" onclick="if(confirm('Are you sure you want to remove this material?')) document.getElementById('delete-material-form').submit();"
                    class="text-xs font-bold text-rose-600 hover:text-rose-700">
                Delete Material
            </button>
            <div class="flex gap-3">
                <a href="{{ route('inventory.catalogue.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-sm font-bold transition-all">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                    Save Changes
                </button>
            </div>
        </div>
    </form>

    <!-- Quick Add Category Modal -->
    <div x-show="categoryModalOpen" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div @click.away="categoryModalOpen = false" class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-slate-800 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Add New Material Category</h3>
                <button type="button" @click="categoryModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">&times;</button>
            </div>

            <template x-if="catError">
                <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold" x-text="catError"></div>
            </template>

            <form @submit.prevent="submitNewCategory" class="space-y-4">
                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Category Name <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="newCatName" placeholder="e.g. Roofing & Waterproofing" required
                           class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Description / Sub-Items (Optional)</label>
                    <textarea x-model="newCatDesc" rows="2" placeholder="e.g. Zinc sheets, bitumen felt, aluminium roofing caps..."
                              class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-slate-800">
                    <button type="button" @click="categoryModalOpen = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-all">Cancel</button>
                    <button type="submit" :disabled="isCreatingCat" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-sm shadow-brand-500/30 transition-all">
                        <span x-show="!isCreatingCat">Create Category</span>
                        <span x-show="isCreatingCat">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <form id="delete-material-form" method="POST" action="{{ route('inventory.catalogue.destroy', $material) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
