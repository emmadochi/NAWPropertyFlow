@extends('supplier-portal.layout')

@section('title', 'Purchase Orders')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-black text-white tracking-tight">Purchase Orders</h1>
        <p class="text-sm text-slate-400 mt-1">Official materials supply purchase orders issued by NAW PropertyFlow.</p>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-800/80 text-xs font-bold uppercase text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="py-4 px-6">PO Reference</th>
                        <th class="py-4 px-6">Delivery Site</th>
                        <th class="py-4 px-6 text-right">Order Value</th>
                        <th class="py-4 px-6 text-center">Fulfillment Status</th>
                        <th class="py-4 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($purchaseOrders as $po)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-white">
                                <a href="{{ route('supplier.purchase-orders.show', $po) }}" class="hover:text-brand-400 hover:underline">
                                    {{ $po->ref_number }}
                                </a>
                                <span class="block text-xs text-slate-400 font-normal">{{ $po->created_at->format('M d, Y') }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-white font-medium">{{ $po->site?->name }}</div>
                                <span class="text-xs text-slate-400">{{ $po->site?->address }}</span>
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold text-base text-white">
                                ₦{{ number_format($po->total_amount, 2) }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($po->status === 'delivered')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-950 text-emerald-300">Delivered</span>
                                @elseif($po->status === 'partially_delivered')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-950 text-amber-300">Partially Delivered</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-950 text-blue-300">Approved / In Transit</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <a href="{{ route('supplier.purchase-orders.show', $po) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-white">
                                    View PO
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500">
                                No purchase orders assigned to your account.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchaseOrders->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
