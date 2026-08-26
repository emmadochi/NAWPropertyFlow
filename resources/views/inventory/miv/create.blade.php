@extends('layouts.app')

@section('title', 'Issue Materials (MIV)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.miv.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Material Issues (MIV)</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">New Issue Voucher</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Issue Materials from Site Store</h1>
        </div>
        <a href="{{ route('inventory.miv.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-all">
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

    <form method="POST" action="{{ route('inventory.miv.store') }}" x-data="mivForm()" class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-6 shadow-sm">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Source Site -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Site / Warehouse Store <span class="text-rose-500">*</span></label>
                <select name="site_id" x-model="selectedSiteId" @change="onSiteSelect()" required
                        class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select Source Site</option>
                    @foreach($sites as $s)
                        <option value="{{ $s->id }}" {{ (old('site_id', $selectedSite?->id) == $s->id) ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->project?->name ?? 'No Project' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Recipient Engineer / Foreman -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Issued To (Site Engineer / Foreman) <span class="text-rose-500">*</span></label>
                <select name="received_by_user_id" required
                        class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select Recipient</option>
                    @foreach($receivers as $u)
                        <option value="{{ $u->id }}" {{ old('received_by_user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ ucfirst($u->role) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Construction Activity -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Construction Work Activity <span class="text-rose-500">*</span></label>
                <input type="text" name="activity_name" value="{{ old('activity_name') }}" placeholder="e.g. Ground Floor Slab Casting, 9-inch Block Work" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Work Quantity / Scope -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Estimated Work Volume</label>
                <input type="number" step="0.01" name="work_quantity" value="{{ old('work_quantity') }}" placeholder="e.g. 45.00"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <!-- Issue Line Items -->
        <div class="space-y-3 pt-4 border-t border-gray-100 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Materials Being Issued</h3>
                    <p class="text-xs text-gray-400">Stock will be debited using FIFO batch lot deduction.</p>
                </div>
                <button type="button" @click="addItem()" class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 dark:bg-brand-950 dark:text-brand-300 rounded-lg text-xs font-bold transition-all">
                    + Add Item
                </button>
            </div>

            <div class="space-y-2">
                <template x-for="(item, index) in items" :key="index">
                    <div class="flex flex-col sm:flex-row items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-slate-800/60 border border-gray-200 dark:border-slate-700">
                        <div class="flex-1 w-full">
                            <select :name="'items[' + index + '][material_id]'" x-model="item.material_id" required
                                    class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm">
                                <option value="">Select On-Hand Material</option>
                                @if($selectedSite && $selectedSite->stock)
                                    @foreach($selectedSite->stock as $stk)
                                        <option value="{{ $stk->material_id }}">
                                            {{ $stk->material?->name }} (Available: {{ number_format($stk->qty_on_hand, 2) }} {{ $stk->material?->unit_of_measure }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="w-full sm:w-44">
                            <input type="number" step="0.001" :name="'items[' + index + '][qty_issued]'" x-model="item.qty_issued" placeholder="Qty Issued" required
                                   class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-mono font-bold">
                        </div>
                        <button type="button" @click="removeItem(index)" class="p-2 text-rose-500 hover:text-rose-700" :disabled="items.length === 1">✕</button>
                    </div>
                </template>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-slate-800 flex justify-end gap-3">
            <a href="{{ route('inventory.miv.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-sm font-bold transition-all">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                Disburse &amp; Debit Stock
            </button>
        </div>
    </form>
</div>

<script>
function mivForm() {
    return {
        selectedSiteId: '{{ $selectedSite?->id ?? '' }}',
        items: [
            { material_id: '', qty_issued: '' }
        ],
        addItem() {
            this.items.push({ material_id: '', qty_issued: '' });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        onSiteSelect() {
            if (this.selectedSiteId) {
                window.location.href = `{{ route('inventory.miv.create') }}?site_id=${this.selectedSiteId}`;
            }
        }
    }
}
</script>
@endsection
