@extends('supplier-portal.layout')

@section('title', 'Supplier Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Welcome, {{ $supplier->name }}</h1>
            <p class="text-sm text-slate-400 mt-1">Vendor Code: <strong class="font-mono text-brand-400">{{ $supplier->code }}</strong> • Terms: <strong>Net {{ $supplier->payment_terms_days }} Days</strong></p>
        </div>
        <div>
            <a href="{{ route('supplier.invoices.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-600/30 transition-all">
                + Submit Digital Invoice
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6">
            <div class="text-xs font-bold uppercase text-slate-400">Open Purchase Orders</div>
            <div class="text-3xl font-black text-white mt-1">{{ $openPosCount }}</div>
            <div class="text-xs text-slate-400 mt-1">Awaiting or fulfilling delivery</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6">
            <div class="text-xs font-bold uppercase text-slate-400">Total Invoiced</div>
            <div class="text-3xl font-black text-blue-400 mt-1">₦{{ number_format($invoicedTotal, 2) }}</div>
            <div class="text-xs text-slate-400 mt-1">Total claims submitted</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6">
            <div class="text-xs font-bold uppercase text-slate-400">Cleared Payments</div>
            <div class="text-3xl font-black text-emerald-400 mt-1">₦{{ number_format($paidTotal, 2) }}</div>
            <div class="text-xs text-slate-400 mt-1">Disbursed EFT settlements</div>
        </div>
    </div>

    <!-- Recent Orders & Invoices Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent POs -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-base text-white">Recent Purchase Orders</h3>
                <a href="{{ route('supplier.purchase-orders.index') }}" class="text-xs text-brand-400 hover:underline">View All</a>
            </div>
            <div class="space-y-3">
                @forelse($recentPos as $po)
                    <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700/60 flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold text-white text-sm">{{ $po->ref_number }}</span>
                            <span class="block text-xs text-slate-400">Site: {{ $po->site?->name }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold text-white text-sm">₦{{ number_format($po->total_amount, 2) }}</span>
                            <a href="{{ route('supplier.purchase-orders.show', $po) }}" class="block text-xs text-brand-400 hover:underline">Details &rarr;</a>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-4">No purchase orders assigned.</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Invoices -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-base text-white">Submitted Invoices</h3>
                <a href="{{ route('supplier.invoices.index') }}" class="text-xs text-brand-400 hover:underline">View All</a>
            </div>
            <div class="space-y-3">
                @forelse($recentInvoices as $inv)
                    <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700/60 flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold text-white text-sm">{{ $inv->invoice_number }}</span>
                            <span class="block text-xs text-slate-400">{{ $inv->invoice_date->format('M d, Y') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold text-white text-sm">₦{{ number_format($inv->billed_amount, 2) }}</span>
                            <span class="block text-[11px] font-bold uppercase {{ $inv->match_status === 'passed' ? 'text-emerald-400' : 'text-amber-400' }}">
                                {{ $inv->match_status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-4">No invoices submitted yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
