@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto" x-data="{ activeMilestone: null }">
    <!-- Header/Navigation -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-dark-900">Installment Schedule</h1>
            <p class="text-gray-500 mt-2">
                Sale: <strong>{{ $paymentPlan->sale->property->name }}</strong> bought by 
                <a href="{{ route('leads.show', $paymentPlan->sale->lead_id) }}" class="text-brand-500 hover:text-brand-600 font-semibold">{{ $paymentPlan->sale->lead->full_name }}</a>
            </p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <a href="{{ route('leads.show', $paymentPlan->sale->lead_id) }}" class="px-5 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-dark-700 font-semibold text-sm rounded-xl transition-colors">
                Back to Lead Profile
            </a>
            @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'sales_manager']))
            <a href="{{ route('payments.create-plan', $paymentPlan->sale_id) }}" class="px-5 py-2.5 bg-brand-50 hover:bg-brand-100 text-brand-600 font-semibold text-sm rounded-xl transition-colors">
                Rebuild Plan
            </a>
            @endif
        </div>
    </div>

    <!-- Core Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Total Deal Value</span>
            <span class="text-2xl font-bold text-dark-800 mt-2">₦{{ number_format($paymentPlan->total_amount, 2) }}</span>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Total Paid</span>
            <span class="text-2xl font-bold text-emerald-600 mt-2">₦{{ number_format($paymentPlan->amount_paid, 2) }}</span>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Outstanding Balance</span>
            <span class="text-2xl font-bold text-brand-500 mt-2">₦{{ number_format($paymentPlan->balance, 2) }}</span>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Plan Status</span>
            <div class="mt-2">
                @if($paymentPlan->status === 'completed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        Completed
                    </span>
                @elseif($paymentPlan->status === 'defaulted')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                        Defaulted
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                        Active Installments
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Milestones Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-dark-900">Milestone Tranches</h2>
            <span class="text-xs text-gray-400 font-semibold">{{ $paymentPlan->milestones->count() }} Payments Scheduled</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Tranche Label</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Due Date</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 text-right">Amount Due</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 text-right">Amount Paid</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($paymentPlan->milestones as $milestone)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-bold text-dark-800 text-sm block">{{ $milestone->label }}</span>
                            @if($milestone->bank_reference)
                            <span class="text-xs text-gray-400 mt-1 block">Ref: {{ $milestone->bank_reference }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-dark-600">
                            {{ $milestone->due_date->format('M d, Y') }}
                            @if($milestone->status !== 'paid' && $milestone->due_date->isPast())
                                <span class="text-xs font-bold text-rose-500 block mt-1">Overdue</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-dark-800 text-right">
                            ₦{{ number_format($milestone->amount_due, 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-emerald-600 text-right">
                            ₦{{ number_format($milestone->amount_paid, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center space-y-1">
                                @if($milestone->status === 'paid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        Paid
                                    </span>
                                @elseif($milestone->status === 'partial')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        Partial
                                    </span>
                                @elseif($milestone->status === 'overdue')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        Overdue
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                        Pending
                                    </span>
                                @endif

                                @if($milestone->amount_paid > 0)
                                    @if($milestone->verified_at)
                                        <span class="inline-flex items-center space-x-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200" title="Verified by {{ $milestone->verifier?->name ?? 'Admin' }} on {{ $milestone->verified_at->format('M d, Y') }}">
                                            <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                            <span>Verified</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center space-x-1 text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                                            <span>Pending Audit</span>
                                        </span>
                                    @endif
                                @endif
                            </div>
                        <td class="px-6 py-4 text-sm text-right space-x-2">
                            @if($milestone->amount_paid > 0)
                            <a href="{{ route('payments.download-receipt', $milestone->id) }}" target="_blank" class="inline-flex items-center text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 font-bold text-xs px-3 py-1.5 rounded-lg shadow-sm transition-colors">
                                <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>Official Receipt</span>
                            </a>
                            @endif

                            {{-- View Uploaded Proof of Payment if attached by buyer --}}
                            @if($milestone->proof_of_payment)
                            <a href="{{ asset('storage/' . $milestone->proof_of_payment) }}" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold text-xs transition-colors bg-blue-50 border border-blue-200 px-2 py-1 rounded-lg">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <span>View Client POP</span>
                            </a>
                            @endif

                            {{-- Admin Verification Action --}}
                            @if(in_array(Auth::user()->role, ['super_admin', 'company_admin']) && $milestone->amount_paid > 0 && !$milestone->verified_at)
                            <form action="{{ route('payments.verify-payment', $milestone->id) }}" method="POST" class="inline" onsubmit="return confirm('Verify this payment of ₦{{ number_format($milestone->amount_paid, 2) }}? This will approve marketer commissions for payroll.')">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 font-bold text-xs rounded-lg transition-colors shadow-sm">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    <span>Verify Payment</span>
                                </button>
                            </form>
                            @endif

                            @if($milestone->status !== 'paid')
                            <button type="button" 
                                    @click="activeMilestone = {
                                        id: {{ $milestone->id }},
                                        label: '{{ $milestone->label }}',
                                        amount_remaining: {{ max(0, $milestone->amount_due - $milestone->amount_paid) }}
                                    }" 
                                    class="inline-flex items-center px-3 py-1.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-xs rounded-lg transition-colors">
                                Record Payment
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Record Payment Modal (Alpine.js controlled) -->
    <div x-cloak x-show="activeMilestone !== null" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-dark-900/40 backdrop-blur-sm transition-opacity" x-transition>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-2xl w-full max-w-md overflow-hidden" @click.away="activeMilestone = null">
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-dark-900">Record Payment</h3>
                <button type="button" @click="activeMilestone = null" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Form -->
            <form :action="'/payments/milestones/' + (activeMilestone ? activeMilestone.id : '') + '/payments'" method="POST">
                @csrf
                <div class="p-6 space-y-5">
                    <div class="bg-brand-50 rounded-xl p-4 border border-brand-100 text-sm text-brand-700">
                        Milestone: <strong x-text="activeMilestone ? activeMilestone.label : ''"></strong><br>
                        Remaining Due: <strong x-text="'₦' + (activeMilestone ? new Intl.NumberFormat('en-NG').format(activeMilestone.amount_remaining) : '')"></strong>
                    </div>

                    <!-- Amount Paid & Payment Date -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="amount_paid" class="block text-xs font-semibold text-gray-400 uppercase tracking-wide">Amount Paid (₦) *</label>
                            <input type="number" name="amount_paid" id="amount_paid" :value="activeMilestone ? activeMilestone.amount_remaining : ''" required min="0.01" step="0.01" class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 py-2.5 px-3.5 border text-sm font-bold text-dark-800">
                        </div>
                        <div>
                            <label for="payment_date" class="block text-xs font-semibold text-gray-400 uppercase tracking-wide">Payment Date *</label>
                            <input type="date" name="payment_date" id="payment_date" value="{{ date('Y-m-d') }}" required class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 py-2.5 px-3.5 border text-sm font-medium text-dark-800">
                        </div>
                    </div>

                    <!-- Bank Reference -->
                    <div>
                        <label for="bank_reference" class="block text-xs font-semibold text-gray-400 uppercase tracking-wide">Bank Reference / Narration</label>
                        <input type="text" name="bank_reference" id="bank_reference" placeholder="e.g. FBN/2026/0622/4119" class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 py-2.5 px-3.5 border text-sm">
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="modal_notes" class="block text-xs font-semibold text-gray-400 uppercase tracking-wide">Remarks</label>
                        <textarea name="notes" id="modal_notes" rows="2" class="mt-1.5 block w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 py-2 px-3.5 border text-sm" placeholder="Additional details..."></textarea>
                    </div>

                    <!-- Email Receipt Toggle -->
                    <div class="bg-blue-50/70 rounded-xl p-3.5 border border-blue-100">
                        <label class="flex items-start space-x-2.5 cursor-pointer">
                            <input type="checkbox" name="send_receipt_email" value="1" checked class="mt-0.5 rounded text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="text-xs font-bold text-slate-800 block">Email Official Payment Receipt to Client</span>
                                <span class="text-[11px] text-slate-500 block mt-0.5">Uncheck this if you are recording a historical or backdated payment to avoid emailing the buyer.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end space-x-3">
                    <button type="button" @click="activeMilestone = null" class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-dark-700 font-semibold text-sm rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-lg transition-all shadow-md">
                        Confirm Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
