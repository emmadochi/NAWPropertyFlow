@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto" x-data="planBuilder({{ $sale->deal_value }})">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-dark-900">Build Payment Plan</h1>
        <p class="text-gray-500 mt-2">Set up installment milestones for <strong>{{ $sale->lead->full_name }}</strong> purchasing <strong>{{ $sale->property->name }}</strong>.</p>
    </div>

    <!-- Sale Summary -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-8 shadow-sm">
        <h2 class="text-lg font-semibold text-dark-900 mb-4">Sale Summary</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <span class="text-xs text-gray-400 uppercase font-semibold">Client Name</span>
                <p class="text-sm font-semibold text-dark-800 mt-1">{{ $sale->lead->full_name }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-400 uppercase font-semibold">Property</span>
                <p class="text-sm font-semibold text-dark-800 mt-1">{{ $sale->property->name }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-400 uppercase font-semibold">Deal Value</span>
                <p class="text-sm font-bold text-brand-600 mt-1">₦{{ number_format($sale->deal_value, 2) }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-400 uppercase font-semibold">Closing Date</span>
                <p class="text-sm font-semibold text-dark-800 mt-1">{{ $sale->deal_closed_at ? $sale->deal_closed_at->format('M d, Y') : 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Plan Form -->
    <form action="{{ route('payments.store-plan', $sale->id) }}" method="POST" class="space-y-8">
        @csrf
        
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-dark-900 mb-6">Plan Settings</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Plan Type -->
                <div>
                    <label for="plan_type" class="block text-sm font-medium text-dark-700">Payment Plan Type</label>
                    <select name="plan_type" id="plan_type" x-model="planType" @change="adjustDefaultMilestones()" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 py-3 px-4 border">
                        <option value="outright">Outright Purchase</option>
                        <option value="installment">Installment Schedule</option>
                        <option value="mortgage">Mortgage-Backed Plan</option>
                    </select>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-dark-700">Notes / Terms</label>
                    <textarea name="notes" id="notes" rows="1" class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 py-3 px-4 border" placeholder="Internal remarks or custom payment conditions..."></textarea>
                </div>
            </div>
        </div>

        <!-- Milestones Area -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-dark-900">Milestone Tranches</h2>
                    <p class="text-xs text-gray-500 mt-1">Split the total price into individual payments</p>
                </div>
                <button type="button" @click="addMilestone()" class="px-4 py-2 bg-brand-50 text-brand-600 hover:bg-brand-100 rounded-xl font-semibold text-xs transition-colors flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Milestone</span>
                </button>
            </div>

            <!-- Milestones List -->
            <div class="space-y-4">
                <template x-for="(milestone, index) in milestones" :key="index">
                    <div class="flex flex-col md:flex-row items-start md:items-center space-y-3 md:space-y-0 md:space-x-4 p-4 bg-gray-50 rounded-xl border border-gray-100 relative">
                        <!-- Label -->
                        <div class="flex-1 w-full">
                            <label class="text-xs text-gray-400 font-semibold block mb-1">Label</label>
                            <input type="text" :name="'milestones['+index+'][label]'" x-model="milestone.label" required class="block w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500 py-2 px-3 border text-sm" placeholder="e.g. Initial 30% Deposit">
                        </div>

                        <!-- Amount Due -->
                        <div class="w-full md:w-48">
                            <label class="text-xs text-gray-400 font-semibold block mb-1">Amount Due (₦)</label>
                            <input type="number" :name="'milestones['+index+'][amount_due]'" x-model.number="milestone.amount_due" required min="1" step="0.01" class="block w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500 py-2 px-3 border text-sm" placeholder="Amount Due">
                        </div>

                        <!-- Due Date -->
                        <div class="w-full md:w-44">
                            <label class="text-xs text-gray-400 font-semibold block mb-1">Due Date</label>
                            <input type="date" :name="'milestones['+index+'][due_date]'" x-model="milestone.due_date" required class="block w-full rounded-lg border-gray-300 focus:border-brand-500 focus:ring-brand-500 py-2 px-3 border text-sm">
                        </div>

                        <!-- Delete Button -->
                        <button type="button" @click="removeMilestone(index)" class="absolute top-2 right-2 md:static text-gray-400 hover:text-rose-600 transition-colors p-1.5 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Validation summary footer -->
            <div class="mt-8 border-t border-gray-100 pt-6 flex flex-col md:flex-row items-center justify-between">
                <div class="flex items-center space-x-6 mb-4 md:mb-0">
                    <div>
                        <span class="text-xs text-gray-400 font-semibold">Running Total</span>
                        <p class="text-lg font-bold" :class="runningTotal() === dealValue ? 'text-emerald-600' : 'text-rose-600'">
                            ₦<span x-text="formatCurrency(runningTotal())"></span>
                        </p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 font-semibold">Deal Value</span>
                        <p class="text-lg font-bold text-dark-800">
                            ₦<span x-text="formatCurrency(dealValue)"></span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <span x-show="runningTotal() !== dealValue" class="text-xs font-semibold text-rose-600 flex items-center space-x-1 bg-rose-50 px-3 py-1.5 rounded-lg border border-rose-100">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <span>Total amounts must equal deal value!</span>
                    </span>
                    
                    <button type="submit" :disabled="runningTotal() !== dealValue" class="px-6 py-3 bg-brand-500 hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg flex items-center space-x-2">
                        <span>Save Payment Plan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function planBuilder(dealValue) {
        return {
            dealValue: dealValue,
            planType: 'outright',
            milestones: [
                { label: 'Outright Payment', amount_due: dealValue, due_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0] }
            ],
            adjustDefaultMilestones() {
                if (this.planType === 'outright') {
                    this.milestones = [
                        { label: 'Outright Payment', amount_due: this.dealValue, due_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0] }
                    ];
                } else if (this.planType === 'installment') {
                    const split = Math.round(this.dealValue / 3 * 100) / 100;
                    const diff = this.dealValue - (split * 3);
                    this.milestones = [
                        { label: '30% Down Payment', amount_due: split, due_date: new Date().toISOString().split('T')[0] },
                        { label: 'Second Installment', amount_due: split, due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0] },
                        { label: 'Final Installment', amount_due: Math.round((split + diff) * 100) / 100, due_date: new Date(Date.now() + 60 * 24 * 60 * 60 * 1000).toISOString().split('T')[0] }
                    ];
                } else {
                    const deposit = Math.round(this.dealValue * 0.2 * 100) / 100;
                    this.milestones = [
                        { label: '20% Minimum Equity Contribution', amount_due: deposit, due_date: new Date().toISOString().split('T')[0] },
                        { label: '80% Mortgage Disbursement', amount_due: Math.round((this.dealValue - deposit) * 100) / 100, due_date: new Date(Date.now() + 45 * 24 * 60 * 60 * 1000).toISOString().split('T')[0] }
                    ];
                }
            },
            addMilestone() {
                const remaining = Math.max(0, this.dealValue - this.runningTotal());
                this.milestones.push({
                    label: 'Milestone #' + (this.milestones.length + 1),
                    amount_due: remaining,
                    due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
                });
            },
            removeMilestone(index) {
                this.milestones.splice(index, 1);
            },
            runningTotal() {
                return this.milestones.reduce((sum, item) => sum + (parseFloat(item.amount_due) || 0), 0);
            },
            formatCurrency(value) {
                return new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
            }
        };
    }
</script>
@endsection
