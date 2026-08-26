@extends('layouts.app')

@section('title', 'Create Purchase Order (PO)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.purchase-orders.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Purchase Orders</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">New Order</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Generate Purchase Order (PO)</h1>
        </div>
        <a href="{{ route('inventory.purchase-orders.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-all">
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

    <form method="POST" action="{{ route('inventory.purchase-orders.store') }}" x-data="poForm()" class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-6 shadow-sm">
        @csrf

        @if($requisition)
            <input type="hidden" name="requisition_id" value="{{ $requisition->id }}">
            <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-sm flex items-center justify-between">
                <div>
                    <span class="font-bold text-blue-900 dark:text-blue-200">Generated from MRF: {{ $requisition->ref_number }}</span>
                    <p class="text-xs text-blue-700 dark:text-blue-300">Activity: {{ $requisition->activity_name }}</p>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-lg bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 font-bold">Linked</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Supplier -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Select Approved Supplier <span class="text-rose-500">*</span></label>
                <select name="supplier_id" required class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select Vendor</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->name }} ({{ $sup->code }} - Net {{ $sup->payment_terms_days }}d)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Destination Site -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Delivery Destination Site <span class="text-rose-500">*</span></label>
                <select name="site_id" required class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select Site</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}" {{ (old('site_id', $requisition?->site_id) == $site->id) ? 'selected' : '' }}>
                            {{ $site->name }} ({{ $site->project?->name ?? 'Global' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Expected Delivery Date -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Expected Delivery Date</label>
                <input type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date', date('Y-m-d', strtotime('+3 days'))) }}"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Expiry / Cancellation Date -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">PO Expiry / Cancellation Date</label>
                <input type="date" name="expiry_date" value="{{ old('expiry_date', date('Y-m-d', strtotime('+14 days'))) }}"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <!-- Materials Line Items -->
        <div class="space-y-3 pt-4 border-t border-gray-100 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Order Line Items</h3>
                    <p class="text-xs text-gray-400">Specify material quantities and negotiated unit supply prices.</p>
                </div>
                <button type="button" @click="addItem()" class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 dark:bg-brand-950 dark:text-brand-300 rounded-lg text-xs font-bold transition-all">
                    + Add Item
                </button>
            </div>

            <div class="space-y-2">
                <template x-for="(item, index) in items" :key="index">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 p-3 rounded-xl bg-gray-50 dark:bg-slate-800/60 border border-gray-200 dark:border-slate-700 items-center">
                        <div class="sm:col-span-6">
                            <select :name="'items[' + index + '][material_id]'" x-model="item.material_id" @change="onMaterialChange(index)" required
                                    class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm">
                                <option value="">Select Material</option>
                                @foreach($materials as $m)
                                    <option value="{{ $m->id }}" data-cost="{{ $m->standard_unit_cost }}">{{ $m->name }} ({{ $m->unit_of_measure }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <input type="number" step="0.001" :name="'items[' + index + '][qty_ordered]'" x-model.number="item.qty_ordered" placeholder="Qty" required
                                   class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-mono">
                        </div>
                        <div class="sm:col-span-3">
                            <input type="number" step="0.01" :name="'items[' + index + '][unit_price]'" x-model.number="item.unit_price" placeholder="Unit Price ₦" required
                                   class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-mono">
                        </div>
                        <div class="sm:col-span-1 text-center">
                            <button type="button" @click="removeItem(index)" class="text-rose-500 hover:text-rose-700" :disabled="items.length === 1">✕</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Cost Breakdown & Summary -->
        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-gray-200 dark:border-slate-700 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Delivery / Logistics Fee (₦)</label>
                <input type="number" step="0.01" name="delivery_fee" x-model.number="deliveryFee" placeholder="0.00"
                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-mono">
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">VAT / Tax Amount (₦)</label>
                <input type="number" step="0.01" name="tax_amount" x-model.number="taxAmount" placeholder="0.00"
                       class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-mono">
            </div>
            <div class="space-y-1 text-right">
                <span class="text-xs font-bold uppercase text-slate-400 block">Grand Total</span>
                <span class="text-2xl font-black font-mono text-slate-900 dark:text-white block mt-1">
                    ₦<span x-text="calculateGrandTotal().toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                </span>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-slate-800 flex justify-end gap-3">
            <a href="{{ route('inventory.purchase-orders.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-sm font-bold transition-all">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                Generate Purchase Order
            </button>
        </div>
    </form>
</div>

<script>
function poForm() {
    return {
        deliveryFee: 0,
        taxAmount: 0,
        items: [
            @if($requisition && $requisition->items->count() > 0)
                @foreach($requisition->items as $item)
                    {
                        material_id: '{{ $item->material_id }}',
                        qty_ordered: {{ $item->qty_approved > 0 ? $item->qty_approved : $item->qty_requested }},
                        unit_price: {{ $item->material->standard_unit_cost ?? 0 }}
                    },
                @endforeach
            @else
                { material_id: '', qty_ordered: 1, unit_price: 0 }
            @endif
        ],
        addItem() {
            this.items.push({ material_id: '', qty_ordered: 1, unit_price: 0 });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        onMaterialChange(index) {
            const select = document.querySelectorAll('select[name^="items"]')[index];
            const opt = select.options[select.selectedIndex];
            if (opt && opt.dataset.cost) {
                this.items[index].unit_price = parseFloat(opt.dataset.cost);
            }
        },
        calculateGrandTotal() {
            let subtotal = this.items.reduce((sum, item) => {
                let qty = parseFloat(item.qty_ordered) || 0;
                let price = parseFloat(item.unit_price) || 0;
                return sum + (qty * price);
            }, 0);
            return subtotal + (parseFloat(this.deliveryFee) || 0) + (parseFloat(this.taxAmount) || 0);
        }
    }
}
</script>
@endsection
