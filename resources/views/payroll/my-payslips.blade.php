@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-dark-900 dark:text-white tracking-tight">My Salary &amp; Payslip History</h1>
                    <p class="text-sm text-gray-500 dark:text-slate-400">View your salary structure, itemized deduction breakdown, and monthly payslips.</p>
                </div>
            </div>
        </div>

        @if($payslips->isNotEmpty())
        <div>
            <a href="{{ route('payroll.payslip.download', $payslips->first()->id) }}" target="_blank" class="inline-flex items-center space-x-2 bg-brand-500 hover:bg-brand-600 text-white px-4 py-2.5 rounded-xl font-bold text-xs shadow-md shadow-brand-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Latest Payslip (PDF)</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Salary Structure & Real-Time Balancing Cards -->
    @if($salaryStructure)
    @php
        $base = (float) $salaryStructure->base_salary;
        $allowances = (float) $salaryStructure->housing_allowance + (float) $salaryStructure->transport_allowance + (float) $salaryStructure->other_allowances;
        $finesSum = (float) $activeFines->sum('amount');
        $tax = round((($base + $allowances) * (float) $salaryStructure->tax_percent) / 100, 2);
        $pension = round((($base + (float) $salaryStructure->housing_allowance + (float) $salaryStructure->transport_allowance) * (float) $salaryStructure->pension_percent) / 100, 2);
        $estNet = max(0, ($base + $allowances) - ($tax + $pension + $finesSum));
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-gray-100 dark:border-slate-700/60 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wider">Base Salary</span>
                <span class="p-2 bg-blue-50 dark:bg-blue-950 text-blue-600 rounded-xl text-xs font-black">Monthly</span>
            </div>
            <p class="text-2xl font-black text-dark-900 dark:text-white">₦{{ number_format($base, 2) }}</p>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Guaranteed gross base pay</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-gray-100 dark:border-slate-700/60 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wider">Allowances</span>
                <span class="p-2 bg-emerald-50 dark:bg-emerald-950 text-emerald-600 rounded-xl text-xs font-black">Total</span>
            </div>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">₦{{ number_format($allowances, 2) }}</p>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Housing, Transport &amp; Utilities</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-gray-100 dark:border-slate-700/60 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wider">Active Fines / Deductions</span>
                <span class="p-2 {{ $finesSum > 0 ? 'bg-rose-50 text-rose-600 dark:bg-rose-950' : 'bg-gray-50 text-gray-500 dark:bg-slate-900' }} rounded-xl text-xs font-black">{{ now()->format('M Y') }}</span>
            </div>
            <p class="text-2xl font-black {{ $finesSum > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400' }}">₦{{ number_format($finesSum, 2) }}</p>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">{{ $activeFines->count() }} active deduction item(s)</p>
        </div>

        <div class="bg-gradient-to-br from-brand-500 to-amber-600 rounded-2xl p-5 text-white shadow-lg shadow-brand-500/20">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-brand-100 uppercase tracking-wider">Net Monthly Take-Home</span>
                <span class="px-2.5 py-1 bg-white/20 backdrop-blur-sm rounded-lg text-[10px] font-black uppercase tracking-wider">Estimated</span>
            </div>
            <p class="text-2xl font-black">₦{{ number_format($estNet, 2) }}</p>
            <p class="text-xs text-brand-100 mt-1">Disbursed to {{ $salaryStructure->bank_name ?? 'Bank Account' }}</p>
        </div>
    </div>
    @endif

    <!-- Active Deductions & Query Penalties Warning Banner -->
    @if($activeFines->isNotEmpty())
    <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl p-5">
        <div class="flex items-start space-x-3">
            <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900 text-rose-600 flex items-center justify-center font-bold flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-black text-rose-900 dark:text-rose-200 uppercase tracking-wide">Notice of Active Deductions &amp; Sanctions ({{ now()->format('F Y') }})</h3>
                <p class="text-xs text-rose-700 dark:text-rose-300 mt-0.5">The following items are scheduled to be balanced against your salary for this pay cycle:</p>
                <div class="mt-3 divide-y divide-rose-200/60 dark:divide-rose-800/50">
                    @foreach($activeFines as $fine)
                    <div class="py-2 flex items-center justify-between text-xs">
                        <span class="font-bold text-rose-900 dark:text-rose-200">• {{ $fine->title }} ({{ ucfirst(str_replace('_', ' ', $fine->deduction_type)) }})</span>
                        <span class="font-black text-rose-600 dark:text-rose-400">-₦{{ number_format($fine->amount, 2) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Payslip History Table -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700/60 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
            <div>
                <h3 class="font-black text-lg text-dark-900 dark:text-white">Monthly Payslip Archives</h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Official generated payroll batches and payment records.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-slate-900/50 text-[11px] font-black uppercase text-gray-400 dark:text-slate-400 border-b border-gray-100 dark:border-slate-700">
                        <th class="p-4 pl-6">Pay Period</th>
                        <th class="p-4">Base &amp; Allowances</th>
                        <th class="p-4">Commissions</th>
                        <th class="p-4">Gross Earnings</th>
                        <th class="p-4">Total Deductions</th>
                        <th class="p-4">Net Salary Paid</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 pr-6 text-right">Official Payslip</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-slate-700/50 text-sm">
                    @forelse($payslips as $p)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="p-4 pl-6 font-bold text-dark-900 dark:text-white">
                            {{ $p->payrollBatch->title }}
                        </td>
                        <td class="p-4 text-gray-600 dark:text-slate-300">
                            ₦{{ number_format($p->base_salary + $p->total_allowances, 2) }}
                        </td>
                        <td class="p-4 text-emerald-600 dark:text-emerald-400 font-semibold">
                            +₦{{ number_format($p->commission_amount, 2) }}
                        </td>
                        <td class="p-4 font-bold text-dark-900 dark:text-white">
                            ₦{{ number_format($p->gross_pay, 2) }}
                        </td>
                        <td class="p-4 text-rose-600 dark:text-rose-400 font-semibold">
                            -₦{{ number_format($p->total_deductions, 2) }}
                        </td>
                        <td class="p-4 font-black text-brand-600 dark:text-brand-400">
                            ₦{{ number_format($p->net_pay, 2) }}
                        </td>
                        <td class="p-4">
                            @if($p->status === 'paid')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Paid</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">{{ ucfirst($p->status) }}</span>
                            @endif
                        </td>
                        <td class="p-4 pr-6 text-right">
                            <a href="{{ route('payroll.payslip.download', $p->id) }}" target="_blank" class="inline-flex items-center space-x-1 text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 bg-brand-50 hover:bg-brand-100 dark:bg-brand-950/60 dark:hover:bg-brand-900/60 px-3 py-1.5 rounded-xl border border-brand-200 dark:border-brand-800 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>View Payslip</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-gray-400 dark:text-slate-500">
                            No archived payslips found yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payslips->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-slate-700">
            {{ $payslips->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
