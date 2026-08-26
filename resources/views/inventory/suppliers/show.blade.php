@extends('layouts.app')

@section('title', $supplier->name . ' - Vendor Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.suppliers.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Suppliers Directory</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400 font-mono">{{ $supplier->code }}</span>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $supplier->name }}</h1>
                @if($supplier->is_blacklisted)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300">🚫 Blacklisted</span>
                @elseif($supplier->is_active)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Active</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 dark:bg-slate-800 dark:text-gray-400">Inactive</span>
                @endif
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $supplier->contact_person ? 'Contact: ' . $supplier->contact_person . ' (' . $supplier->phone . ')' : $supplier->phone }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.suppliers.edit', $supplier) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-50 hover:bg-brand-100 text-brand-700 dark:bg-brand-950 dark:text-brand-300 rounded-xl text-xs font-bold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>Edit Vendor</span>
            </a>

            @if($supplier->is_blacklisted)
                <form method="POST" action="{{ route('inventory.suppliers.blacklist', $supplier) }}" onsubmit="return confirm('Restore this supplier to active status?')">
                    @csrf
                    <input type="hidden" name="action" value="unblacklist">
                    <button type="submit" class="px-4 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-xl text-xs font-bold transition-all">
                        Restore Supplier
                    </button>
                </form>
            @else
                <button type="button" onclick="let r = prompt('Reason for blacklisting supplier:'); if(r){ document.getElementById('bl-reason').value = r; document.getElementById('bl-form').submit(); }"
                        class="px-4 py-2 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-xl text-xs font-bold transition-all">
                    Blacklist Vendor
                </button>
                <form id="bl-form" method="POST" action="{{ route('inventory.suppliers.blacklist', $supplier) }}" class="hidden">
                    @csrf
                    <input type="hidden" name="action" value="blacklist">
                    <input type="hidden" id="bl-reason" name="blacklist_reason" value="">
                </form>
            @endif
        </div>
    </div>

    @if($supplier->is_blacklisted && $supplier->blacklist_reason)
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300 text-sm">
            <span class="font-bold">Blacklist Notice:</span> {{ $supplier->blacklist_reason }}
        </div>
    @endif

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Performance Score</div>
            <div class="text-2xl font-black {{ $supplier->performance_score >= 80 ? 'text-emerald-600' : 'text-amber-600' }} mt-1">
                {{ number_format($supplier->performance_score, 1) }}%
            </div>
            <div class="text-xs text-gray-400 mt-1">Delivery time &amp; quality rank</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Total Orders (POs)</div>
            <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">
                ₦{{ number_format($totalOrdered, 2) }}
            </div>
            <div class="text-xs text-gray-400 mt-1">{{ $supplier->purchaseOrders->count() }} Purchase Orders</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Invoiced &amp; Matched</div>
            <div class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1">
                ₦{{ number_format($totalInvoiced, 2) }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Total billed to date</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-5">
            <div class="text-xs font-bold uppercase text-slate-400">Total Paid Out</div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                ₦{{ number_format($totalPaid, 2) }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Cleared disbursements</div>
        </div>
    </div>

    <!-- Vendor Details & Banking Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-4">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Vendor Details</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-xs text-gray-400 uppercase font-semibold block">Address</span>
                    <span class="text-slate-800 dark:text-slate-200">{{ $supplier->address ?? 'Not recorded' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 uppercase font-semibold block">Payment Terms</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 font-mono">Net {{ $supplier->payment_terms_days }} Days</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 uppercase font-semibold block">CAC Registration / RC No.</span>
                    <span class="text-slate-800 dark:text-slate-200 font-mono">{{ $supplier->rc_number ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 uppercase font-semibold block">Tax Identification No. (TIN)</span>
                    <span class="text-slate-800 dark:text-slate-200 font-mono">{{ $supplier->tin ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-4">
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Settlement Banking</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <span class="text-xs text-gray-400 uppercase font-semibold block">Bank</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $supplier->bank_name ?? 'Not configured' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 uppercase font-semibold block">Account Number</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white text-base">{{ $supplier->bank_account_number ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 uppercase font-semibold block">Account Name</span>
                    <span class="text-slate-800 dark:text-slate-200">{{ $supplier->bank_account_name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
