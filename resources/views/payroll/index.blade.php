@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showNewBatchModal: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-dark-900 dark:text-white tracking-tight">Payroll & Compensation</h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Manage staff salary structures, deal commissions, automated deductions, and monthly payroll batches.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('payroll.salaries') }}" 
               class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-700 dark:text-slate-200 font-bold text-xs shadow-sm transition-all flex items-center space-x-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                </svg>
                <span>Salary Structures</span>
            </a>
            <button @click="showNewBatchModal = true" 
                    class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs transition-all shadow-md shadow-brand-500/20 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Run Monthly Payroll</span>
            </button>
        </div>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wider">Total Disbursed (All Time)</p>
                <h3 class="text-2xl font-black text-dark-900 dark:text-white mt-1">₦{{ number_format($totalPaidOut, 2) }}</h3>
                <span class="text-[11px] text-emerald-500 font-semibold">Completed salary payouts</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 text-emerald-500 flex items-center justify-center font-bold text-xl flex-shrink-0">
                ₦
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wider">Total Commissions Paid</p>
                <h3 class="text-2xl font-black text-dark-900 dark:text-white mt-1">₦{{ number_format($totalCommissionsPaid, 2) }}</h3>
                <span class="text-[11px] text-brand-500 font-semibold">Aggregated from closed deals</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/30 text-brand-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wider">Active Staff on Payroll</p>
                <h3 class="text-2xl font-black text-dark-900 dark:text-white mt-1">{{ $staffCount }}</h3>
                <span class="text-[11px] text-blue-500 font-semibold">Eligible employees</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/30 text-blue-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Monthly Batches Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700/60 flex items-center justify-between">
            <h3 class="font-extrabold text-dark-900 dark:text-white text-sm uppercase tracking-wider">Payroll Batch History</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50/50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-slate-700/60 text-xs font-bold text-gray-500 dark:text-slate-400">
                    <tr>
                        <th class="px-6 py-3.5">Payroll Period</th>
                        <th class="px-6 py-3.5">Base Salaries</th>
                        <th class="px-6 py-3.5">Commissions</th>
                        <th class="px-6 py-3.5">Deductions</th>
                        <th class="px-6 py-3.5">Net Payout</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700/60">
                    @forelse($batches as $b)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-slate-700 text-brand-500 flex items-center justify-center font-bold text-xs">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <a href="{{ route('payroll.show', $b->id) }}" class="font-bold text-dark-900 dark:text-white hover:text-brand-500 transition-colors">
                                        {{ $b->title }}
                                    </a>
                                    <p class="text-xs text-gray-400 dark:text-slate-400">Created by {{ $b->creator->name ?? 'System' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-700 dark:text-slate-300">
                            ₦{{ number_format($b->total_base + $b->total_allowances, 2) }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-brand-600 dark:text-brand-400">
                            +₦{{ number_format($b->total_commissions, 2) }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-rose-500">
                            -₦{{ number_format($b->total_deductions, 2) }}
                        </td>
                        <td class="px-6 py-4 font-extrabold text-dark-900 dark:text-white">
                            ₦{{ number_format($b->total_net, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            @if($b->status === 'paid')
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 border border-emerald-200 dark:border-emerald-800">
                                    PAID
                                </span>
                            @elseif($b->status === 'approved')
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 border border-blue-200 dark:border-blue-800">
                                    APPROVED
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 border border-amber-200 dark:border-amber-800">
                                    DRAFT
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('payroll.show', $b->id) }}" class="inline-flex items-center space-x-1 text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 dark:bg-slate-700 dark:hover:bg-slate-600 px-3 py-1.5 rounded-lg transition-colors">
                                <span>View Details</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400 dark:text-slate-500 text-sm">
                            <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-slate-700 mx-auto flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            No payroll batches generated yet. Click <strong>Run Monthly Payroll</strong> to process the current month!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-700">
            {{ $batches->links() }}
        </div>
        @endif
    </div>

    <!-- Generate Monthly Payroll Modal -->
    <div x-show="showNewBatchModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-dark-900/50 backdrop-blur-sm" @click="showNewBatchModal = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-200 dark:border-slate-700">
            <h3 class="text-lg font-extrabold text-dark-900 dark:text-white">Run Monthly Payroll</h3>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Select the pay period. The engine will auto-calculate base salaries, deal commissions, and tax/pension/loan deductions.</p>

            <form action="{{ route('payroll.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase mb-1">Month</label>
                        <select name="month" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-sm font-semibold focus:border-brand-500 focus:outline-none">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase mb-1">Year</label>
                        <select name="year" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-sm font-semibold focus:border-brand-500 focus:outline-none">
                            @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end space-x-3">
                    <button type="button" @click="showNewBatchModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-100 dark:text-slate-300 dark:hover:bg-slate-700">Cancel</button>
                    <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2 rounded-xl text-xs font-bold shadow-md shadow-brand-500/20">Generate Payroll</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
