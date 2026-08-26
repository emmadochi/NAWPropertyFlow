@extends('layouts.app')

@section('title', 'Raise Material Requisition (MRF)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.requisitions.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Requisitions (MRF)</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">New Requisition</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Raise Material Requisition Form</h1>
        </div>
        <a href="{{ route('inventory.requisitions.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-all">
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

    <form method="POST" action="{{ route('inventory.requisitions.store') }}" x-data="requisitionForm()" class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-6 shadow-sm">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Target Site -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Site / Warehouse <span class="text-rose-500">*</span></label>
                <select name="site_id" required class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select Destination Site</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}" {{ (old('site_id', $selectedSite?->id) == $site->id) ? 'selected' : '' }}>
                            {{ $site->name }} ({{ $site->project?->name ?? 'No Project' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Need by Date -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Required On Site By <span class="text-rose-500">*</span></label>
                <input type="date" name="required_date" value="{{ old('required_date', date('Y-m-d', strtotime('+3 days'))) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Construction Activity -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Construction Work Activity <span class="text-rose-500">*</span></label>
                <input type="text" name="activity_name" x-model="activityName" @change="fetchBOMSuggestions()" placeholder="e.g. 1:2:4 Concrete Pour, 9-inch Block Laying" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Work Quantity / Volume -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Estimated Work Volume / Scope <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" name="work_quantity" x-model="workQuantity" @change="fetchBOMSuggestions()" placeholder="e.g. 50.00" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <!-- Materials Table -->
        <div class="space-y-3 pt-4 border-t border-gray-100 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Requested Building Materials</h3>
                    <p class="text-xs text-gray-400">Quantities will be auto-validated against standard consumption rates.</p>
                </div>
                <button type="button" @click="addItem()" class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 dark:bg-brand-950 dark:text-brand-300 rounded-lg text-xs font-bold transition-all">
                    + Add Item Row
                </button>
            </div>

            <div class="space-y-2">
                <template x-for="(item, index) in items" :key="index">
                    <div class="flex flex-col sm:flex-row items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-slate-800/60 border border-gray-200 dark:border-slate-700">
                        <div class="flex-1 w-full">
                            <select :name="'items[' + index + '][material_id]'" x-model="item.material_id" required
                                    class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm">
                                <option value="">Select Material</option>
                                @foreach($materials as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->unit_of_measure }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full sm:w-40">
                            <input type="number" step="0.001" :name="'items[' + index + '][qty_requested]'" x-model="item.qty_requested" placeholder="Qty Requested" required
                                   class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-mono">
                        </div>
                        <button type="button" @click="removeItem(index)" class="p-2 text-rose-500 hover:text-rose-700" :disabled="items.length === 1">
                            ✕
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Notes -->
        <div class="space-y-1">
            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Site Engineer Remarks / Specifications</label>
            <textarea name="notes" rows="3" placeholder="Specific delivery instructions or structural mix details..."
                      class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"></textarea>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-slate-800 flex justify-end gap-3">
            <a href="{{ route('inventory.requisitions.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-sm font-bold transition-all">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                Submit Requisition
            </button>
        </div>
    </form>
</div>

<script>
function requisitionForm() {
    return {
        activityName: '',
        workQuantity: 1,
        items: [
            { material_id: '', qty_requested: '' }
        ],
        addItem() {
            this.items.push({ material_id: '', qty_requested: '' });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        fetchBOMSuggestions() {
            if (!this.activityName || !this.workQuantity || this.workQuantity <= 0) return;
            fetch(`{{ route('inventory.bom.suggest-qty') }}?activity_name=${encodeURIComponent(this.activityName)}&work_quantity=${this.workQuantity}`)
                .then(res => res.json())
                .then(data => {
                    if (data.materials && data.materials.length > 0) {
                        this.items = data.materials.map(m => ({
                            material_id: m.material_id,
                            qty_requested: m.expected_qty
                        }));
                    }
                })
                .catch(() => {});
        }
    }
}
</script>
@endsection
