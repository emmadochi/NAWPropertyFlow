@extends('layouts.app')

@section('title', 'Record Goods Received Note (GRN)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.grn.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Goods Received (GRN)</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">New Gate Delivery</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Record Gate Delivery &amp; Issue GRN</h1>
        </div>
        <a href="{{ route('inventory.grn.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-all">
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

    <form method="POST" action="{{ route('inventory.grn.store') }}" enctype="multipart/form-data" x-data="grnForm()" class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-6 shadow-sm">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Purchase Order Selection -->
            <div class="space-y-1 md:col-span-2">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Purchase Order (PO) Reference <span class="text-rose-500">*</span></label>
                <select name="purchase_order_id" x-model="selectedPoId" @change="onPoSelect()" required
                        class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select Approved Purchase Order</option>
                    @foreach($purchaseOrders as $po)
                        <option value="{{ $po->id }}" {{ (old('purchase_order_id', $selectedPo?->id) == $po->id) ? 'selected' : '' }}>
                            {{ $po->ref_number }} — {{ $po->supplier?->name }} (Site: {{ $po->site?->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Delivery Date & Time -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Delivery Date <span class="text-rose-500">*</span></label>
                <input type="date" name="delivery_date" value="{{ old('delivery_date', date('Y-m-d')) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Arrival Time <span class="text-rose-500">*</span></label>
                <input type="time" name="delivery_time" value="{{ old('delivery_time', date('H:i')) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Waybill & Driver Info -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Supplier Waybill / Delivery Note Number</label>
                <input type="text" name="waybill_number" value="{{ old('waybill_number') }}" placeholder="e.g. WB-992140"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Vehicle Registration Number</label>
                <input type="text" name="vehicle_plate" value="{{ old('vehicle_plate') }}" placeholder="e.g. KSF-123-XY"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm uppercase font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Driver Name</label>
                <input type="text" name="driver_name" value="{{ old('driver_name') }}" placeholder="e.g. Musa Ibrahim"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Driver Phone</label>
                <input type="text" name="driver_phone" value="{{ old('driver_phone') }}" placeholder="e.g. 08031234567"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <!-- GPS Geolocation Capture -->
        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-gray-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase text-slate-700 dark:text-slate-200 block">Gate Delivery GPS Verification</span>
                <p class="text-xs text-gray-400">Capture device GPS coordinates to verify physical on-site arrival within the site boundary radius.</p>
                <div class="text-xs font-mono mt-1 text-slate-600 dark:text-slate-400" x-show="lat">
                    Lat: <span x-text="lat"></span>, Lng: <span x-text="lng"></span>
                </div>
            </div>
            <button type="button" @click="captureGps()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shrink-0">
                📍 Capture Device GPS
            </button>
            <input type="hidden" name="delivery_gps_lat" :value="lat">
            <input type="hidden" name="delivery_gps_lng" :value="lng">
        </div>

        <!-- Items Received Table -->
        <div class="space-y-3 pt-4 border-t border-gray-100 dark:border-slate-800">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Delivered Material Physical Inspection</h3>
            
            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-slate-800/60 border border-gray-200 dark:border-slate-700 space-y-3">
                        <input type="hidden" :name="'items[' + index + '][po_item_id]'" :value="item.po_item_id">
                        <input type="hidden" :name="'items[' + index + '][material_id]'" :value="item.material_id">

                        <div class="flex items-center justify-between">
                            <span class="font-bold text-sm text-slate-900 dark:text-white" x-text="item.material_name"></span>
                            <span class="text-xs font-mono text-gray-400">Ordered: <strong x-text="item.qty_ordered"></strong></span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-500">Qty Accepted (Good)</label>
                                <input type="number" step="0.001" :name="'items[' + index + '][qty_received]'" x-model="item.qty_received" required
                                       class="w-full py-1.5 px-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg text-sm font-mono font-bold text-emerald-600">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-500">Qty Rejected (Damaged)</label>
                                <input type="number" step="0.001" :name="'items[' + index + '][qty_rejected]'" x-model="item.qty_rejected"
                                       class="w-full py-1.5 px-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg text-sm font-mono font-bold text-rose-600">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-500">Batch / Heat No.</label>
                                <input type="text" :name="'items[' + index + '][batch_number]'" placeholder="e.g. BATCH-2026-01"
                                       class="w-full py-1.5 px-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-500">Expiry Date (Cement/Chemicals)</label>
                                <input type="date" :name="'items[' + index + '][expiry_date]'"
                                       class="w-full py-1.5 px-3 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Photo Evidence Upload -->
        <div class="space-y-1">
            <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Upload Offloading Photos &amp; Waybill Proof</label>
            <input type="file" name="photo_evidence[]" multiple accept="image/*"
                   class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none">
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-slate-800 flex justify-end gap-3">
            <a href="{{ route('inventory.grn.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-sm font-bold transition-all">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                Accept Delivery &amp; Credit Stock
            </button>
        </div>
    </form>
</div>

<script>
function grnForm() {
    return {
        selectedPoId: '{{ $selectedPo?->id ?? '' }}',
        lat: '',
        lng: '',
        items: [
            @if($selectedPo)
                @foreach($selectedPo->items as $item)
                    {
                        po_item_id: '{{ $item->id }}',
                        material_id: '{{ $item->material_id }}',
                        material_name: '{{ $item->material->name }} ({{ $item->material->unit_of_measure }})',
                        qty_ordered: {{ $item->qty_ordered }},
                        qty_received: {{ $item->qty_ordered - $item->qty_delivered_cumulative }},
                        qty_rejected: 0
                    },
                @endforeach
            @endif
        ],
        onPoSelect() {
            if (!this.selectedPoId) return;
            window.location.href = `{{ route('inventory.grn.create') }}?purchase_order_id=${this.selectedPoId}`;
        },
        captureGps() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((pos) => {
                    this.lat = pos.coords.latitude.toFixed(7);
                    this.lng = pos.coords.longitude.toFixed(7);
                }, (err) => {
                    alert('Geolocation error: ' + err.message);
                });
            } else {
                alert('Geolocation is not supported by your browser.');
            }
        }
    }
}
</script>
@endsection
