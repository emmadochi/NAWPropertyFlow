@extends('supplier-portal.layout')

@section('title', 'PO ' . $purchaseOrder->ref_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                <a href="{{ route('supplier.purchase-orders.index') }}" class="hover:text-white">Purchase Orders</a>
                <span>/</span>
                <span class="text-brand-400 font-mono">{{ $purchaseOrder->ref_number }}</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight mt-1">{{ $purchaseOrder->ref_number }}</h1>
            <p class="text-xs text-slate-400">Delivery Site: <strong class="text-white">{{ $purchaseOrder->site?->name }}</strong> ({{ $purchaseOrder->site?->address }})</p>
        </div>
        <div>
            <a href="{{ route('supplier.invoices.create', ['purchase_order_id' => $purchaseOrder->id]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-600/30 transition-all">
                + Submit Invoice for this PO
            </a>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-slate-800">
            <h3 class="font-bold text-base text-white">Materials Supply Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-800/80 text-xs font-bold uppercase text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="py-4 px-6">Material SKU</th>
                        <th class="py-4 px-6 text-center">Qty Ordered</th>
                        <th class="py-4 px-6 text-center">Qty Delivered</th>
                        <th class="py-4 px-6 text-right">Agreed Unit Price</th>
                        <th class="py-4 px-6 text-right">Total Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($purchaseOrder->items as $item)
                        <tr class="hover:bg-slate-800/40">
                            <td class="py-4 px-6 font-semibold text-white">
                                {{ $item->material?->name }}
                                <span class="block text-xs font-mono text-slate-400">{{ $item->material?->code }} ({{ $item->material?->unit_of_measure }})</span>
                            </td>
                            <td class="py-4 px-6 text-center font-mono font-bold text-white">
                                {{ number_format($item->qty_ordered, 2) }}
                            </td>
                            <td class="py-4 px-6 text-center font-mono font-bold text-emerald-400">
                                {{ number_format($item->qty_delivered_cumulative, 2) }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono">
                                ₦{{ number_format($item->unit_price, 2) }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold text-white">
                                ₦{{ number_format($item->total_price, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-6 border-t border-slate-800 bg-slate-900/60 flex justify-between items-center">
            <span class="text-sm font-bold uppercase text-slate-400">Grand Total</span>
            <span class="text-2xl font-black font-mono text-white">₦{{ number_format($purchaseOrder->total_amount, 2) }}</span>
        </div>
    </div>
</div>
@endsection
