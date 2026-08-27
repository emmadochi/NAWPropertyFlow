@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto" x-data="planBuilder({{ $sale->deal_value }}, {{ json_encode($durations ?? []) }})">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-2 text-sm text-gray-400 mb-2">
            <a href="{{ route('leads.show', $sale->lead_id) }}" class="hover:text-brand-600">&larr; Back to Lead Profile</a>
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Build Structured Payment Plan</h1>
        <p class="text-gray-500 dark:text-slate-400 mt-1">Set up custom installment milestones and interest tenures for <strong>{{ $sale->lead->full_name }}</strong> purchasing <strong>{{ $sale->property->name }}</strong>.</p>
    </div>

    <!-- Sale Summary Card -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700 p-6 mb-8 shadow-sm">
        <h2 class="text-sm font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wider mb-4">Original Deal Summary</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <span class="text-xs text-gray-400 uppercase font-semibold">Client Name</span>
                <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">{{ $sale->lead->full_name }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-400 uppercase font-semibold">Property</span>
                <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">{{ $sale->property->name }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-400 uppercase font-semibold">Base Deal Value</span>
                <p class="text-base font-black text-brand-600 mt-1">₦{{ number_format($sale->deal_value, 2) }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-400 uppercase font-semibold">Closing Date</span>
                <p class="text-sm font-semibold text-gray-700 dark:text-slate-300 mt-1">{{ $sale->deal_closed_at ? $sale->deal_closed_at->format('M d, Y') : 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Plan Form -->
    <form action="{{ route('payments.store-plan', $sale->id) }}" method="POST" class="space-y-8">
        @csrf
        <input type="hidden" name="base_deal_value" :value="basePrice">
        <input type="hidden" name="payment_plan_duration_id" :value="selectedDurationId">
        <input type="hidden" name="duration_months" :value="durationMonths">
        <input type="hidden" name="interest_rate_pct" :value="interestRatePct">
        <input type="hidden" name="interest_amount" :value="interestAmount">
        <input type="hidden" name="total_amount" :value="totalAdjustedPrice">
        <input type="hidden" name="number_of_installments" :value="milestones.length">
        
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700 p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-slate-700 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Structured Plan &amp; Interest Tenure</h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Select a predefined duration or configure a bespoke installment spread.</p>
                </div>
                <a href="{{ route('settings.payment-plans.index') }}" target="_blank" class="text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 dark:bg-brand-900/30 px-3 py-1.5 rounded-xl transition-all">
                    ⚙️ Manage Durations &rarr;
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Duration Template Picker -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Select Duration Tenure &amp; Interest Rate</label>
                    <select x-model="selectedDurationId" @change="applyDurationTemplate()" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-brand-500 outline-none text-sm font-semibold">
                        <option value="">-- Custom Schedule (Manual) --</option>
                        <template x-for="d in durations" :key="d.id">
                            <option :value="d.id" x-text="d.name + ' (' + (d.interest_rate_pct > 0 ? '+' + d.interest_rate_pct + '% Interest' : '0% Interest') + ')'"></option>
                        </template>
                    </select>
                </div>

                <!-- Plan Type -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Plan Classification</label>
                    <select name="plan_type" x-model="planType" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-brand-500 outline-none text-sm">
                        <option value="installment">Standard Installment Spread</option>
                        <option value="outright">Outright Purchase (Single/Lump Sum)</option>
                        <option value="mortgage">Mortgage-Backed Plan</option>
                    </select>
                </div>
            </div>

            <!-- Pricing Breakdown Box -->
            <div class="bg-gradient-to-r from-gray-50 to-brand-50/40 dark:from-slate-900 dark:to-slate-900/60 p-5 rounded-2xl border border-brand-100 dark:border-slate-700 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                <div>
                    <span class="text-[10px] uppercase font-bold text-gray-400 block">Base Price</span>
                    <span class="text-sm font-black text-gray-800 dark:text-slate-200 mt-0.5 block" x-text="'₦' + formatCurrency(basePrice)"></span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-amber-600 block">Interest Surcharge</span>
                    <span class="text-sm font-black text-amber-600 mt-0.5 block" x-text="'+₦' + formatCurrency(interestAmount) + ' (' + interestRatePct + '%)'"></span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-brand-600 block">Total Deal Payable</span>
                    <span class="text-base font-black text-brand-600 mt-0.5 block" x-text="'₦' + formatCurrency(totalAdjustedPrice)"></span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-emerald-600 block">Initial Deposit</span>
                    <span class="text-sm font-black text-emerald-600 mt-0.5 block" x-text="'₦' + formatCurrency(initialDepositAmount) + ' (' + initialDepositPct + '%)'"></span>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Payment Terms &amp; Special Remarks</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-brand-500 outline-none text-sm" placeholder="Specify allocation conditions, grace period, or construction milestone tags..."></textarea>
            </div>
        </div>

        <!-- Milestones Area -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100 dark:border-slate-700">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Milestone Tranches &amp; Due Dates</h2>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Individual invoice installments issued to buyer over the payment tenure.</p>
                </div>
                <button type="button" @click="addMilestone()" class="px-4 py-2 bg-brand-50 dark:bg-brand-900/40 text-brand-600 dark:text-brand-400 hover:bg-brand-100 rounded-xl font-bold text-xs transition-colors flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Add Tranche</span>
                </button>
            </div>

            <!-- Milestones List -->
            <div class="space-y-3">
                <template x-for="(milestone, index) in milestones" :key="index">
                    <div class="flex flex-col md:flex-row items-start md:items-center space-y-3 md:space-y-0 md:space-x-4 p-4 bg-gray-50 dark:bg-slate-900/60 rounded-2xl border border-gray-100 dark:border-slate-700/60 relative">
                        <span class="w-6 h-6 rounded-full bg-brand-100 dark:bg-brand-900/60 text-brand-700 dark:text-brand-300 text-xs font-black flex items-center justify-center flex-shrink-0" x-text="index + 1"></span>
                        
                        <!-- Label -->
                        <div class="flex-1 w-full">
                            <label class="text-[10px] text-gray-400 uppercase font-bold block mb-1">Tranche Description</label>
                            <input type="text" :name="'milestones['+index+'][label]'" x-model="milestone.label" required class="block w-full rounded-xl border border-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white py-2 px-3 text-sm focus:border-brand-500 outline-none" placeholder="e.g. 1st Milestone (Ground Floor Slab)">
                        </div>

                        <!-- Amount Due -->
                        <div class="w-full md:w-48">
                            <label class="text-[10px] text-gray-400 uppercase font-bold block mb-1">Amount Due (₦)</label>
                            <input type="number" :name="'milestones['+index+'][amount_due]'" x-model.number="milestone.amount_due" required min="1" step="0.01" class="block w-full rounded-xl border border-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white py-2 px-3 text-sm font-bold text-gray-900 dark:text-slate-100 focus:border-brand-500 outline-none">
                        </div>

                        <!-- Due Date -->
                        <div class="w-full md:w-44">
                            <label class="text-[10px] text-gray-400 uppercase font-bold block mb-1">Due Date</label>
                            <input type="date" :name="'milestones['+index+'][due_date]'" x-model="milestone.due_date" required class="block w-full rounded-xl border border-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white py-2 px-3 text-sm focus:border-brand-500 outline-none">
                        </div>

                        <!-- Delete Button -->
                        <button type="button" @click="removeMilestone(index)" class="text-gray-400 hover:text-rose-600 p-2 rounded-xl hover:bg-rose-50 transition-colors" title="Delete Tranche">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Validation Summary Footer -->
            <div class="mt-8 border-t border-gray-100 dark:border-slate-700 pt-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-6">
                    <div>
                        <span class="text-xs text-gray-400 font-semibold block">Scheduled Total</span>
                        <p class="text-lg font-black" :class="isBalanced() ? 'text-emerald-600' : 'text-rose-600'">
                            ₦<span x-text="formatCurrency(runningTotal())"></span>
                        </p>
                    </div>
                    <div class="border-l border-gray-200 dark:border-slate-700 pl-6">
                        <span class="text-xs text-gray-400 font-semibold block">Target Total (Inc. Int.)</span>
                        <p class="text-lg font-black text-gray-900 dark:text-white">
                            ₦<span x-text="formatCurrency(totalAdjustedPrice)"></span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <span x-show="!isBalanced()" class="text-xs font-semibold text-rose-600 bg-rose-50 dark:bg-rose-900/30 px-3 py-1.5 rounded-xl border border-rose-200">
                        Variance: ₦<span x-text="formatCurrency(Math.abs(runningTotal() - totalAdjustedPrice))"></span>
                    </span>
                    
                    <button type="submit" :disabled="!isBalanced()" class="px-8 py-3 bg-brand-600 hover:bg-brand-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-sm rounded-xl transition-all shadow-lg flex items-center space-x-2">
                        <span>Save &amp; Activate Plan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function planBuilder(baseDealValue, durations) {
    return {
        basePrice: parseFloat(baseDealValue),
        durations: durations || [],
        selectedDurationId: '',
        durationMonths: 0,
        interestRatePct: 0.00,
        interestAmount: 0.00,
        totalAdjustedPrice: parseFloat(baseDealValue),
        initialDepositPct: 100.00,
        initialDepositAmount: parseFloat(baseDealValue),
        planType: 'outright',
        milestones: [
            { label: 'Outright Full Payment', amount_due: parseFloat(baseDealValue), due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0] }
        ],
        init() {
            // Auto-select first matching default or spread if available
            if (this.durations.length > 0) {
                const defaultPlan = this.durations.find(d => d.duration_months === 6) || this.durations[0];
                if (defaultPlan) {
                    this.selectedDurationId = defaultPlan.id;
                    this.applyDurationTemplate();
                }
            }
        },
        applyDurationTemplate() {
            const plan = this.durations.find(d => d.id == this.selectedDurationId);
            if (!plan) {
                this.durationMonths = 0;
                this.interestRatePct = 0.00;
                this.interestAmount = 0.00;
                this.totalAdjustedPrice = this.basePrice;
                this.initialDepositPct = 100.00;
                this.initialDepositAmount = this.basePrice;
                return;
            }

            this.durationMonths = parseInt(plan.duration_months);
            this.interestRatePct = parseFloat(plan.interest_rate_pct);
            this.interestAmount = Math.round(((this.basePrice * this.interestRatePct) / 100) * 100) / 100;
            this.totalAdjustedPrice = Math.round((this.basePrice + this.interestAmount) * 100) / 100;
            this.initialDepositPct = parseFloat(plan.initial_deposit_pct);
            this.initialDepositAmount = Math.round(((this.totalAdjustedPrice * this.initialDepositPct) / 100) * 100) / 100;

            const installmentsCount = parseInt(plan.number_of_installments) || (this.durationMonths > 0 ? this.durationMonths : 1);
            this.milestones = [];

            if (installmentsCount <= 1 || this.durationMonths === 0) {
                this.planType = 'outright';
                this.milestones.push({
                    label: 'Outright Full Purchase Payment',
                    amount_due: this.totalAdjustedPrice,
                    due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
                });
            } else {
                this.planType = 'installment';
                // 1. Initial Deposit
                this.milestones.push({
                    label: `Initial Commitment Deposit (${this.initialDepositPct}%)`,
                    amount_due: this.initialDepositAmount,
                    due_date: new Date().toISOString().split('T')[0]
                });

                // Remaining tranches
                const remainingAmount = Math.round((this.totalAdjustedPrice - this.initialDepositAmount) * 100) / 100;
                const remainingTranches = installmentsCount - 1;
                const trancheAmount = Math.floor((remainingAmount / remainingTranches) * 100) / 100;
                let runningAssigned = 0;

                for (let i = 1; i <= remainingTranches; i++) {
                    const daysOffset = Math.round((i * (this.durationMonths / remainingTranches)) * 30);
                    const trancheDate = new Date(Date.now() + daysOffset * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                    
                    const isLast = (i === remainingTranches);
                    const finalTrancheAmount = isLast ? Math.round((remainingAmount - runningAssigned) * 100) / 100 : trancheAmount;
                    runningAssigned += trancheAmount;

                    this.milestones.push({
                        label: `Installment Tranche #${i} (Month ${Math.min(this.durationMonths, Math.round(daysOffset/30))})`,
                        amount_due: finalTrancheAmount,
                        due_date: trancheDate
                    });
                }
            }
        },
        addMilestone() {
            const remaining = Math.max(0, Math.round((this.totalAdjustedPrice - this.runningTotal()) * 100) / 100);
            this.milestones.push({
                label: 'Milestone Tranche #' + (this.milestones.length + 1),
                amount_due: remaining,
                due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
            });
        },
        removeMilestone(index) {
            this.milestones.splice(index, 1);
        },
        runningTotal() {
            const sum = this.milestones.reduce((acc, item) => acc + (parseFloat(item.amount_due) || 0), 0);
            return Math.round(sum * 100) / 100;
        },
        isBalanced() {
            return Math.abs(this.runningTotal() - this.totalAdjustedPrice) < 0.05;
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
        }
    };
}
</script>
@endsection
