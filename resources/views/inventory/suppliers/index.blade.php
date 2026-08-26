@extends('layouts.app')

@section('title', 'Suppliers & Vendor Directory')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Dashboard</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Suppliers Directory</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Vendor &amp; Supplier Directory</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Approved construction material suppliers, payment terms, performance scores, and portal accounts.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.suppliers.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Register Supplier</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300 text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-4">
        <form method="GET" action="{{ route('inventory.suppliers.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search supplier name, code, contact or phone..."
                       class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <div class="sm:col-span-4">
                <select name="status" class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Approved Suppliers</option>
                    <option value="blacklisted" {{ request('status') === 'blacklisted' ? 'selected' : '' }}>Blacklisted Vendors</option>
                </select>
            </div>
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition-all">Filter</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('inventory.suppliers.index') }}" class="py-2 px-3 bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-300 rounded-xl text-sm font-semibold">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Suppliers Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-gray-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Supplier Name &amp; Code</th>
                        <th class="py-3.5 px-4">Contact Info</th>
                        <th class="py-3.5 px-4 text-center">Payment Terms</th>
                        <th class="py-3.5 px-4 text-center">Performance Score</th>
                        <th class="py-3.5 px-4 text-center">Portal User</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($suppliers as $sup)
                        <tr class="hover:bg-gray-50/75 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                                        🏢
                                    </span>
                                    <div>
                                        <a href="{{ route('inventory.suppliers.show', $sup) }}" class="hover:text-brand-600 hover:underline">
                                            {{ $sup->name }}
                                        </a>
                                        <span class="block text-xs font-mono text-gray-400">{{ $sup->code }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-800 dark:text-slate-200">{{ $sup->contact_person ?? 'N/A' }}</div>
                                <span class="block text-xs text-gray-400">{{ $sup->phone ?? $sup->email ?? 'No contact info' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="text-xs px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold font-mono">
                                    Net {{ $sup->payment_terms_days }} Days
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <span class="font-bold font-mono text-sm {{ $sup->performance_score >= 80 ? 'text-emerald-600' : ($sup->performance_score >= 60 ? 'text-amber-600' : 'text-rose-600') }}">
                                        {{ number_format($sup->performance_score, 1) }}%
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($sup->users_count > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 dark:bg-purple-950 dark:text-purple-300">
                                        🔐 Enabled
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">None</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($sup->is_blacklisted)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300">
                                        🚫 Blacklisted
                                    </span>
                                @elseif($sup->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 dark:bg-slate-800 dark:text-gray-400">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-2">
                                <a href="{{ route('inventory.suppliers.show', $sup) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    View
                                </a>
                                <a href="{{ route('inventory.suppliers.edit', $sup) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-700 dark:bg-brand-950 dark:text-brand-300">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <p class="font-bold text-slate-600 dark:text-slate-300">No suppliers registered.</p>
                                <a href="{{ route('inventory.suppliers.create') }}" class="mt-3 inline-block px-4 py-2 bg-brand-600 text-white rounded-xl text-xs font-bold">Register First Vendor</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-slate-800">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
