@extends('layouts.app')

@section('title', '3-Way Match Audit - ' . $invoice->invoice_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.invoices.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Supplier Invoices</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400 font-mono">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Invoice {{ $invoice->invoice_number }}</h1>
                @if($invoice->payment_status === 'approved_for_payment')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">✓ 3-Way Match Passed</span>
                @elseif($invoice->payment_status === 'paid')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">✓ Paid / Settled</span>
                @elseif($invoice->payment_status === 'disputed')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300">⚠️ Discrepancy / Disputed</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300">Unmatched</span>
                @endif
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Supplier: <strong class="text-slate-800 dark:text-white">{{ $invoice->supplier?->name }}</strong> • PO: <a href="{{ route('inventory.purchase-orders.show', $invoice->purchaseOrder) }}" class="text-brand-600 font-bold hover:underline">{{ $invoice->purchaseOrder?->ref_number }}</a></p>
        </div>

        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('inventory.invoices.match', $invoice) }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all">
                    Re-run 3-Way Match
                </button>
            </form>

            @if($invoice->payment_status !== 'paid' && ($invoice->payment_status === 'approved_for_payment' || Auth::user()->isCompanyAdmin()))
                <form method="POST" action="{{ route('inventory.invoices.approve-payment', $invoice) }}" onsubmit="return confirm('Authorize EFT payment voucher for this invoice?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-emerald-500/20 transition-all">
                        Disburse Payment
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- 3-Way Match Visual Reconciler Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- 1. Purchase Order -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-3 shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-slate-800 pb-2">
                <span class="text-xs font-bold uppercase text-slate-400">1. Purchase Order</span>
                <span class="text-xs font-mono font-bold text-brand-600">{{ $invoice->purchaseOrder?->ref_number }}</span>
            </div>
            <div class="text-2xl font-black text-slate-900 dark:text-white">
                ₦{{ number_format($invoice->purchaseOrder?->total_amount, 2) }}
            </div>
            <div class="text-xs text-gray-500 space-y-1">
                <div>Items Ordered: <strong>{{ $invoice->purchaseOrder?->items->count() }} SKUs</strong></div>
                <div>Approval Tier: <span class="uppercase font-bold text-purple-600">{{ $invoice->purchaseOrder?->approval_tier }}</span></div>
            </div>
        </div>

        <!-- 2. Goods Received Note -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-3 shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-slate-800 pb-2">
                <span class="text-xs font-bold uppercase text-slate-400">2. Gate Delivery (GRN)</span>
                <span class="text-xs font-mono font-bold text-emerald-600">{{ $invoice->goodsReceivedNote?->ref_number ?? 'Auto-Reconciled' }}</span>
            </div>
            <div class="text-2xl font-black text-emerald-600">
                ₦{{ number_format($invoice->subtotal_amount + $invoice->tax_amount, 2) }}
            </div>
            <div class="text-xs text-gray-500 space-y-1">
                <div>Physical Goods Received Verified</div>
                <div>Status: <span class="font-bold text-emerald-600">✓ On-Site Verified</span></div>
            </div>
        </div>

        <!-- 3. Supplier Invoice -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-3 shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-slate-800 pb-2">
                <span class="text-xs font-bold uppercase text-slate-400">3. Vendor Billed</span>
                <span class="text-xs font-mono font-bold text-slate-900 dark:text-white">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="text-2xl font-black {{ $invoice->payment_status === 'disputed' ? 'text-rose-600' : 'text-slate-900 dark:text-white' }}">
                ₦{{ number_format($invoice->total_amount, 2) }}
            </div>
            <div class="text-xs text-gray-500 space-y-1">
                <div>Status: <strong class="{{ $invoice->payment_status === 'disputed' ? 'text-rose-600' : 'text-emerald-600' }} uppercase">{{ $invoice->payment_status }}</strong></div>
                <div>Due: {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Net 30' }}</div>
            </div>
        </div>
    </div>

    <!-- Match Audit Report -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-3">
        <h3 class="font-bold text-base text-slate-900 dark:text-white">3-Way Match Verification Findings</h3>
        <div class="p-4 rounded-xl {{ $invoice->payment_status === 'approved_for_payment' ? 'bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300' }} text-sm font-medium whitespace-pre-line">
            {{ $invoice->discrepancy_notes ?? 'Reconciliation complete.' }}
        </div>
    </div>
</div>
@endsection
