@extends('supplier-portal.layout')

@section('title', 'My Invoices')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Vendor Invoices</h1>
            <p class="text-sm text-slate-400 mt-1">Track invoice match status, payment approvals, and bank disbursement dates.</p>
        </div>
        <div>
            <a href="{{ route('supplier.invoices.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-600/30 transition-all">
                + Submit Digital Invoice
            </a>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-800/80 text-xs font-bold uppercase text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="py-4 px-6">Invoice Number</th>
                        <th class="py-4 px-6">PO Reference</th>
                        <th class="py-4 px-6 text-right">Billed Amount</th>
                        <th class="py-4 px-6 text-center">3-Way Match</th>
                        <th class="py-4 px-6 text-center">Payment Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-white">
                                {{ $inv->invoice_number }}
                                <span class="block text-xs text-slate-400 font-normal">Date: {{ $inv->invoice_date->format('M d, Y') }}</span>
                            </td>
                            <td class="py-4 px-6 font-mono text-slate-300">
                                {{ $inv->purchaseOrder?->ref_number }}
                            </td>
                            <td class="py-4 px-6 text-right font-mono font-bold text-base text-white">
                                ₦{{ number_format($inv->billed_amount, 2) }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($inv->match_status === 'passed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-950 text-emerald-300">
                                        ✓ Reconciled OK
                                    </span>
                                @elseif($inv->match_status === 'variance_detected')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-950 text-rose-300">
                                        ⚠️ Variance Review
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-800 text-slate-400">
                                        Processing
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($inv->payment_status === 'paid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-950 text-emerald-300">
                                        Paid (Ref: {{ $inv->payment_reference }})
                                    </span>
                                @elseif($inv->payment_status === 'approved_for_payment')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-950 text-blue-300">
                                        Approved for EFT
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-950 text-amber-300">
                                        Pending Review
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-500">
                                No invoices submitted yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
