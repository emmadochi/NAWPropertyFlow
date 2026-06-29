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
                        </td>
                        <td class="px-6 py-4 text-sm text-right space-x-2">
                            @if($milestone->amount_paid > 0 && $milestone->receipt_path)
                            <a href="{{ route('payments.download-receipt', $milestone->id) }}" target="_blank" class="inline-flex items-center text-brand-500 hover:text-brand-600 font-semibold transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>Receipt</span>
                            </a>
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

                    <!-- Amount Paid -->
                    <div>
                        <label for="amount_paid" class="block text-xs font-semibold text-gray-400 uppercase tracking-wide">Amount Paid (₦)</label>
                        <input type="number" name="amount_paid" id="amount_paid" :value="activeMilestone ? activeMilestone.amount_remaining : ''" required min="0.01" step="0.01" class="mt-2 block w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 py-3 px-4 border text-sm">
                    </div>

                    <!-- Bank Reference -->
                    <div>
                        <label for="bank_reference" class="block text-xs font-semibold text-gray-400 uppercase tracking-wide">Bank Reference / Narration</label>
                        <input type="text" name="bank_reference" id="bank_reference" placeholder="e.g. FBN/2026/0622/4119" class="mt-2 block w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 py-3 px-4 border text-sm">
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="modal_notes" class="block text-xs font-semibold text-gray-400 uppercase tracking-wide">Remarks</label>
                        <textarea name="notes" id="modal_notes" rows="2" class="mt-2 block w-full rounded-xl border-gray-300 focus:border-brand-500 focus:ring-brand-500 py-3 px-4 border text-sm" placeholder="Additional details..."></textarea>
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
