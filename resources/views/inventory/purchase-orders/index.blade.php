@extends('layouts.app')

@section('title', 'Purchase Orders (PO)')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Dashboard</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Purchase Orders (PO)</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Purchase Orders (PO)</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage 3-tier financial authorization, supplier order dispatch, and delivery fulfillment.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.purchase-orders.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Create Purchase Order</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-4">
        <form method="GET" action="{{ route('inventory.purchase-orders.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-5 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search PO ref or supplier..."
                       class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <div class="sm:col-span-4">
                <select name="status" class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="pending_t1" {{ request('status') === 'pending_t1' ? 'selected' : '' }}>Pending Tier 1 (PM)</option>
                    <option value="pending_t2" {{ request('status') === 'pending_t2' ? 'selected' : '' }}>Pending Tier 2 (MD)</option>
                    <option value="pending_t3" {{ request('status') === 'pending_t3' ? 'selected' : '' }}>Pending Tier 3 (Board)</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved &amp; Issued</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered Complete</option>
                    <option value="partially_delivered" {{ request('status') === 'partially_delivered' ? 'selected' : '' }}>Partially Delivered</option>
                </select>
            </div>
            <div class="sm:col-span-3 flex items-center gap-2">
                <button type="submit" class="w-full py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition-all">Filter</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('inventory.purchase-orders.index') }}" class="py-2 px-3 bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-300 rounded-xl text-sm font-semibold">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- PO Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-gray-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">PO Reference</th>
                        <th class="py-3.5 px-4">Supplier &amp; Site</th>
                        <th class="py-3.5 px-4 text-center">Auth Tier</th>
                        <th class="py-3.5 px-4 text-right">Total Amount</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($purchaseOrders as $po)
                        <tr class="hover:bg-gray-50/75 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                <a href="{{ route('inventory.purchase-orders.show', $po) }}" class="hover:text-brand-600 hover:underline">
                                    {{ $po->ref_number }}
                                </a>
                                <span class="block text-[11px] text-gray-400 font-normal">{{ $po->created_at->format('M d, Y') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $po->supplier?->name }}</div>
                                <span class="text-xs text-gray-400">Site: {{ $po->site?->name }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="text-xs px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 dark:bg-purple-950 dark:text-purple-300 font-bold uppercase font-mono">
                                    {{ $po->approval_tier }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-base text-slate-900 dark:text-white">
                                ₦{{ number_format($po->total_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($po->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">Approved</span>
                                @elseif($po->status === 'delivered')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Delivered</span>
                                @elseif(str_starts_with($po->status, 'pending'))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300">Pending Sign-off</span>
                                @elseif($po->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300">Rejected</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 dark:bg-slate-800 dark:text-gray-300">{{ ucfirst($po->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('inventory.purchase-orders.show', $po) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    View PO
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <p class="font-bold text-slate-600 dark:text-slate-300">No purchase orders created yet.</p>
                                <a href="{{ route('inventory.purchase-orders.create') }}" class="mt-3 inline-block px-4 py-2 bg-brand-600 text-white rounded-xl text-xs font-bold">Generate First PO</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchaseOrders->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-slate-800">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
