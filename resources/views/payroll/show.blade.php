@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showDeductionModal: false, selectedUserId: null, selectedUserName: '' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('payroll.index') }}" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-white bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <h1 class="text-2xl font-extrabold text-dark-900 dark:text-white tracking-tight">{{ $batch->title }}</h1>
                @if($batch->status === 'paid')
                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 uppercase">PAID</span>
                @elseif($batch->status === 'approved')
                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-blue-50 text-blue-600 border border-blue-200 uppercase">APPROVED</span>
                @else
                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-amber-50 text-amber-600 border border-amber-200 uppercase">DRAFT</span>
                @endif
            </div>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Generated on {{ $batch->created_at->format('M d, Y') }} • {{ count($batch->payslips) }} Employee Payslips Processed</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Add Ad-hoc Deduction Button -->
            @if($batch->status === 'draft')
            <button @click="showDeductionModal = true" 
                    class="px-3.5 py-2 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-gray-50 text-gray-700 dark:text-slate-200 font-bold text-xs shadow-sm flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
                <span>Add Deduction</span>
            </button>
            @endif

            <!-- Export Bank CSV -->
            <a href="{{ route('payroll.export-bank', $batch->id) }}" 
               class="px-3.5 py-2 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-gray-50 text-gray-700 dark:text-slate-200 font-bold text-xs shadow-sm flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Export Bank Transfer CSV</span>
            </a>

            @if($batch->status === 'draft')
                <form action="{{ route('payroll.approve', $batch->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Approve this payroll batch?');" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-bold text-xs shadow-md shadow-blue-600/20">
                        Approve Payroll
                    </button>
                </form>
            @elseif($batch->status === 'approved')
                <form action="{{ route('payroll.mark-paid', $batch->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Mark all salaries and commissions in this batch as PAID?');" 
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl font-bold text-xs shadow-md shadow-emerald-600/20">
                        Mark as Disbursed (Paid)
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Batch Summary Financial Banner -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-gray-200 dark:border-slate-700">
            <p class="text-[11px] font-bold text-gray-400 uppercase">Base & Allowances</p>
            <p class="text-lg font-black text-dark-900 dark:text-white mt-0.5">₦{{ number_format($batch->total_base + $batch->total_allowances, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-gray-200 dark:border-slate-700">
            <p class="text-[11px] font-bold text-brand-500 uppercase">Earned Commissions</p>
            <p class="text-lg font-black text-brand-600 dark:text-brand-400 mt-0.5">+₦{{ number_format($batch->total_commissions, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-gray-200 dark:border-slate-700">
            <p class="text-[11px] font-bold text-rose-500 uppercase">Total Deductions</p>
            <p class="text-lg font-black text-rose-600 mt-0.5">-₦{{ number_format($batch->total_deductions, 2) }}</p>
        </div>
        <div class="bg-brand-50 dark:bg-slate-800 p-4 rounded-2xl border border-brand-200 dark:border-brand-500/30">
            <p class="text-[11px] font-bold text-brand-600 dark:text-brand-400 uppercase">Net Salary Payout</p>
            <p class="text-lg font-black text-brand-700 dark:text-white mt-0.5">₦{{ number_format($batch->total_net, 2) }}</p>
        </div>
    </div>

    <!-- Payslip Breakdown Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700 text-xs font-bold text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-6 py-3.5">Staff Member</th>
                        <th class="px-6 py-3.5">Base + Allowances</th>
                        <th class="px-6 py-3.5 text-brand-600">Commissions</th>
                        <th class="px-6 py-3.5">Gross Pay</th>
                        <th class="px-6 py-3.5 text-rose-500">Tax / Pension</th>
                        <th class="px-6 py-3.5 text-rose-500">Loans & Fines</th>
                        <th class="px-6 py-3.5 font-black text-dark-900 dark:text-white">Net Pay</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach($batch->payslips as $ps)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-slate-700 text-brand-600 flex items-center justify-center font-bold text-xs">
                                    {{ substr($ps->user->name, 0, 2) }}
                                </div>
                                <div>
                                    <p class="font-bold text-dark-900 dark:text-white">{{ $ps->user->name }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $ps->user->departmentRelation->name ?? ucfirst($ps->user->role) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-700 dark:text-slate-300 font-semibold">
                            ₦{{ number_format($ps->base_salary + $ps->total_allowances, 2) }}
                        </td>
                        <td class="px-6 py-4 text-brand-600 font-bold">
                            ₦{{ number_format($ps->commission_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 font-bold text-dark-900 dark:text-white">
                            ₦{{ number_format($ps->gross_pay, 2) }}
                        </td>
                        <td class="px-6 py-4 text-rose-500 font-medium text-xs">
                            -₦{{ number_format($ps->tax_deduction + $ps->pension_deduction, 2) }}
                        </td>
                        <td class="px-6 py-4 text-rose-600 font-medium text-xs">
                            -₦{{ number_format($ps->loan_deduction + $ps->other_deductions, 2) }}
                        </td>
                        <td class="px-6 py-4 font-black text-dark-900 dark:text-white text-base">
                            ₦{{ number_format($ps->net_pay, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('payroll.payslip.download', $ps->id) }}" 
                               target="_blank"
                               class="inline-flex items-center space-x-1 text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 dark:bg-slate-700 px-3 py-1.5 rounded-lg transition-colors"
                               title="Download Printable Payslip">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>Payslip PDF</span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Itemized Deduction Modal -->
    <div x-show="showDeductionModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-dark-900/50 backdrop-blur-sm" @click="showDeductionModal = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-200 dark:border-slate-700">
            <h3 class="text-lg font-extrabold text-dark-900 dark:text-white">Add Payroll Deduction</h3>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Record a loan repayment, fine, or custom deduction for this pay period.</p>

            <form action="{{ route('payroll.deductions.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="payroll_batch_id" value="{{ $batch->id }}">
                <input type="hidden" name="month" value="{{ $batch->month }}">
                <input type="hidden" name="year" value="{{ $batch->year }}">

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase mb-1">Staff Member *</label>
                    <select name="user_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-sm font-semibold focus:border-brand-500 focus:outline-none">
                        @foreach($staffMembers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase mb-1">Deduction Type *</label>
                        <select name="deduction_type" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-sm font-semibold focus:border-brand-500 focus:outline-none">
                            <option value="loan_repayment">Loan Repayment</option>
                            <option value="fine">Disciplinary Fine</option>
                            <option value="other">Other / Advance</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase mb-1">Amount (₦) *</label>
                        <input type="number" step="0.01" name="amount" required placeholder="50000"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-sm font-semibold focus:border-brand-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase mb-1">Reason / Description *</label>
                    <input type="text" name="title" required placeholder="e.g. Monthly Car Loan Repayment - Installment 2"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-sm font-semibold focus:border-brand-500 focus:outline-none">
                </div>

                <div class="pt-4 flex items-center justify-end space-x-3">
                    <button type="button" @click="showDeductionModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-100">Cancel</button>
                    <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white px-5 py-2 rounded-xl text-xs font-bold shadow-md shadow-rose-500/20">Apply Deduction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
