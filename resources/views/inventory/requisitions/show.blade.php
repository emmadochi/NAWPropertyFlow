@extends('layouts.app')

@section('title', 'Requisition ' . $requisition->ref_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.requisitions.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Requisitions</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400 font-mono">{{ $requisition->ref_number }}</span>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $requisition->ref_number }}</h1>
                @if($requisition->status === 'approved')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Approved</span>
                @elseif($requisition->status === 'pending')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300">Pending Review</span>
                @elseif($requisition->status === 'rejected')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300">Rejected</span>
                @endif
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Activity: <strong class="text-slate-800 dark:text-white">{{ $requisition->activity_name }}</strong> • Site: <strong>{{ $requisition->site?->name }}</strong></p>
        </div>

        <div class="flex items-center gap-3">
            @if($requisition->status === 'pending' && (Auth::user()->hasPermission('inventory.approve_mrf') || Auth::user()->isCompanyAdmin()))
                <form method="POST" action="{{ route('inventory.requisitions.approve', $requisition) }}" onsubmit="return confirm('Approve this material requisition?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-emerald-500/20 transition-all">
                        Approve MRF
                    </button>
                </form>

                <button type="button" onclick="let r = prompt('Reason for rejection:'); if(r){ document.getElementById('rej-reason').value = r; document.getElementById('rej-form').submit(); }"
                        class="px-4 py-2 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-xl text-xs font-bold transition-all">
                    Reject MRF
                </button>
                <form id="rej-form" method="POST" action="{{ route('inventory.requisitions.reject', $requisition) }}" class="hidden">
                    @csrf
                    <input type="hidden" id="rej-reason" name="rejection_reason" value="">
                </form>
            @endif

            @if($requisition->status === 'approved' && !$requisition->purchaseOrder && (Auth::user()->hasPermission('inventory.create_po') || Auth::user()->isCompanyAdmin()))
                <a href="{{ route('inventory.purchase-orders.create', ['requisition_id' => $requisition->id]) }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-brand-500/30 transition-all">
                    Generate Purchase Order (PO)
                </a>
            @endif
        </div>
    </div>

    @if($requisition->rejection_reason)
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300 text-sm">
            <strong>Rejection Reason:</strong> {{ $requisition->rejection_reason }}
        </div>
    @endif

    <!-- Items & BOM Verification Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-slate-800">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Requisitioned Materials &amp; Consumption Audit</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Comparison against Quantity Surveyor (QS) BOM benchmarks.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-gray-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Material SKU</th>
                        <th class="py-3 px-4 text-center">BOM Expected</th>
                        <th class="py-3 px-4 text-center">Qty Requested</th>
                        <th class="py-3 px-4 text-center">Qty Approved</th>
                        <th class="py-3 px-4 text-right">Variance Analysis</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @foreach($requisition->items as $item)
                        <tr class="hover:bg-gray-50/75 dark:hover:bg-slate-800/40">
                            <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">
                                {{ $item->material?->name }}
                                <span class="block text-xs font-mono text-gray-400">{{ $item->material?->code }} ({{ $item->material?->unit_of_measure }})</span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                {{ $item->bom_expected_qty ? number_format($item->bom_expected_qty, 2) : 'N/A' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-900 dark:text-white">
                                {{ number_format($item->qty_requested, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-600">
                                {{ number_format($item->qty_approved, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right text-xs">
                                @if($item->variance_flag)
                                    <span class="inline-flex items-center gap-1 font-bold text-rose-600">
                                        ⚠️ {{ $item->variance_reason }}
                                    </span>
                                @else
                                    <span class="text-emerald-600 font-semibold">✓ Within standard tolerance</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
