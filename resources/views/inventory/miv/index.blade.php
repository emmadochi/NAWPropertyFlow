@extends('layouts.app')

@section('title', 'Material Issue Vouchers (MIV)')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Dashboard</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Material Issues (MIV)</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Material Issue Vouchers (MIV)</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Track store disbursements to site engineers, activity scopes, and FIFO batch deductions.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.miv.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Issue Materials</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-gray-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">MIV Reference</th>
                        <th class="py-3.5 px-4">Site &amp; Activity</th>
                        <th class="py-3.5 px-4">Issued By</th>
                        <th class="py-3.5 px-4">Received By (Foreman/Engr)</th>
                        <th class="py-3.5 px-4 text-center">Items Count</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($vouchers as $miv)
                        <tr class="hover:bg-gray-50/75 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                <a href="{{ route('inventory.miv.show', $miv) }}" class="hover:text-brand-600 hover:underline">
                                    {{ $miv->ref_number }}
                                </a>
                                <span class="block text-[11px] text-gray-400 font-normal">{{ $miv->created_at->format('M d, Y H:i') }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $miv->activity_name }}</div>
                                <span class="text-xs text-gray-400">Site: {{ $miv->site?->name }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                {{ $miv->issuer?->name }}
                            </td>
                            <td class="py-3.5 px-4 text-xs font-medium text-slate-800 dark:text-slate-200">
                                {{ $miv->receiver?->name }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                {{ $miv->items->count() }} items
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('inventory.miv.show', $miv) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    View MIV
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <p class="font-bold text-slate-600 dark:text-slate-300">No material issue vouchers created yet.</p>
                                <a href="{{ route('inventory.miv.create') }}" class="mt-3 inline-block px-4 py-2 bg-brand-600 text-white rounded-xl text-xs font-bold">Issue First Material Batch</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vouchers->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-slate-800">
                {{ $vouchers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
