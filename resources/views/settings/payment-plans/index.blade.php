@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-8" x-data="paymentPlanManager()">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <span class="p-2.5 bg-brand-50 text-brand-600 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Payment Plan Durations &amp; Interest Rates</h1>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">Configure structured installment tenures (6M, 9M, 12M, 24M) and interest surcharges for buyers.</p>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-3">
            <button @click="openAddModal()" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm rounded-xl transition-all shadow-md hover:shadow-lg flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Add New Duration Plan</span>
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-gray-100 dark:border-slate-700/60 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Active Plan Tenures</span>
            <span class="text-2xl font-black text-gray-900 dark:text-white mt-1 block">{{ $durations->where('is_active', true)->count() }}</span>
            <span class="text-xs text-emerald-600 font-semibold mt-1 inline-block">Available at Checkout</span>
        </div>

        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-gray-100 dark:border-slate-700/60 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Longest Tenure</span>
            <span class="text-2xl font-black text-brand-600 mt-1 block">{{ $durations->max('duration_months') ?? 0 }} Months</span>
            <span class="text-xs text-gray-500 font-semibold mt-1 inline-block">Diaspora &amp; Extended Spread</span>
        </div>

        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-gray-100 dark:border-slate-700/60 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Avg. Interest Rate</span>
            <span class="text-2xl font-black text-amber-500 mt-1 block">{{ number_format($durations->avg('interest_rate_pct'), 1) }}%</span>
            <span class="text-xs text-gray-500 font-semibold mt-1 inline-block">Across all spread plans</span>
        </div>

        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-gray-100 dark:border-slate-700/60 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Min. Initial Deposit</span>
            <span class="text-2xl font-black text-indigo-600 mt-1 block">{{ number_format($durations->min('initial_deposit_pct'), 0) }}%</span>
            <span class="text-xs text-gray-500 font-semibold mt-1 inline-block">Lowest entry threshold</span>
        </div>
    </div>

    <!-- Live Calculator Simulator -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white rounded-3xl p-6 md:p-8 shadow-xl border border-slate-700/60 relative overflow-hidden">
        <div class="relative z-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-700/80 gap-4">
                <div>
                    <span class="text-xs font-bold text-amber-400 uppercase tracking-widest block">Interactive Pricing Engine</span>
                    <h2 class="text-xl font-bold text-white mt-1">Live Deal &amp; Interest Simulator</h2>
                </div>
                <div class="flex items-center space-x-3">
                    <label class="text-xs text-slate-300 font-semibold">Test Property Price (₦):</label>
                    <input type="number" x-model.number="simPrice" step="1000000" min="1000000" class="bg-slate-950/80 border border-slate-600 text-amber-400 font-bold px-4 py-2 rounded-xl text-sm w-44 focus:ring-2 focus:ring-amber-400 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                <template x-for="plan in durationsList.filter(p => p.is_active)" :key="plan.id">
                    <div class="bg-slate-800/80 border border-slate-700 p-4 rounded-2xl hover:border-amber-400/60 transition-all">
                        <div class="flex justify-between items-start">
                            <span class="text-xs font-bold text-slate-300" x-text="plan.name"></span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-400/20 text-amber-300" x-text="plan.interest_rate_pct > 0 ? '+' + plan.interest_rate_pct + '% Int.' : '0% Int.'"></span>
                        </div>
                        <div class="mt-3">
                            <span class="text-[10px] uppercase tracking-wider text-slate-400 block">Total Payable:</span>
                            <span class="text-base font-black text-amber-300" x-text="'₦' + formatNumber(calculateTotal(plan))"></span>
                        </div>
                        <div class="mt-2 pt-2 border-t border-slate-700/60 flex justify-between text-xs text-slate-400">
                            <span>Dep (<span x-text="plan.initial_deposit_pct"></span>%): <strong class="text-white" x-text="'₦' + formatNumber(calculateDeposit(plan))"></strong></span>
                            <span>Tranches: <strong class="text-white" x-text="plan.number_of_installments + 'x'"></strong></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Durations Table -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Configured Payment Plan Durations</h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">These plans automatically populate milestone structures when closing property sales.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-700 dark:text-slate-300">
                <thead class="bg-gray-50 dark:bg-slate-900/60 text-[11px] uppercase font-bold text-gray-400 dark:text-slate-400 border-b border-gray-100 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4">Plan Name &amp; Description</th>
                        <th class="px-6 py-4">Duration (Tenure)</th>
                        <th class="px-6 py-4">Interest Surcharge</th>
                        <th class="px-6 py-4">Required Deposit</th>
                        <th class="px-6 py-4">Installments</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700/60">
                    @forelse($durations as $plan)
                    <tr class="hover:bg-gray-50/70 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900 dark:text-white">{{ $plan->name }}</div>
                            @if($plan->description)
                            <div class="text-xs text-gray-500 dark:text-slate-400 mt-0.5 max-w-sm line-clamp-1">{{ $plan->description }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                @if($plan->duration_months == 0)
                                    Outright (30 Days)
                                @else
                                    {{ $plan->duration_months }} Months
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($plan->interest_rate_pct > 0)
                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                                +{{ number_format($plan->interest_rate_pct, 2) }}% Interest
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                                0.00% (Interest-Free)
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                            {{ number_format($plan->initial_deposit_pct, 1) }}%
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-700 dark:text-slate-300">
                            {{ $plan->number_of_installments }} Payments
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('settings.payment-plans.toggle', $plan->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold cursor-pointer transition-all {{ $plan->is_active ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400' : 'bg-gray-100 dark:bg-slate-700 text-gray-500' }}">
                                    {{ $plan->is_active ? '● Active' : '○ Disabled' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center space-x-2">
                                <button @click="openEditModal({{ json_encode($plan) }})" class="p-2 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-xl transition-all" title="Edit Plan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <form action="{{ route('settings.payment-plans.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this payment plan duration?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-500 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Delete Plan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                            No payment plan durations configured yet. Click "Add New Duration Plan" to create one.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Add / Edit Payment Plan Duration -->
    <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm transition-opacity overflow-y-auto">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full shadow-2xl p-6 md:p-8 space-y-6 my-8 border border-gray-100 dark:border-slate-700" @click.away="modalOpen = false">
            
            <div class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-slate-700">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white" x-text="isEdit ? 'Edit Payment Plan Duration' : 'Create Payment Plan Duration'"></h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Define tenure, interest surcharge %, and required deposit.</p>
                </div>
                <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form :action="isEdit ? updateUrl : '{{ route('settings.payment-plans.store') }}'" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Plan Name *</label>
                    <input type="text" name="name" x-model="formData.name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-brand-500 outline-none text-sm" placeholder="e.g. 6 Months Standard Spread">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Duration (Months) *</label>
                        <input type="number" name="duration_months" x-model.number="formData.duration_months" required min="0" max="120" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-brand-500 outline-none text-sm" placeholder="6">
                        <span class="text-[10px] text-gray-400 mt-1 block">Set 0 for Outright</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Interest Rate (%) *</label>
                        <input type="number" name="interest_rate_pct" x-model.number="formData.interest_rate_pct" required min="0" max="100" step="0.01" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-brand-500 outline-none text-sm" placeholder="5.00">
                        <span class="text-[10px] text-gray-400 mt-1 block">e.g. 5.00 for 5% surcharge</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Initial Deposit (%) *</label>
                        <input type="number" name="initial_deposit_pct" x-model.number="formData.initial_deposit_pct" required min="1" max="100" step="0.01" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-brand-500 outline-none text-sm" placeholder="30.00">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Installment Tranches *</label>
                        <input type="number" name="number_of_installments" x-model.number="formData.number_of_installments" required min="1" max="120" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-brand-500 outline-none text-sm" placeholder="6">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Description / Terms</label>
                    <textarea name="description" x-model="formData.description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-brand-500 outline-none text-sm" placeholder="e.g. 30% initial commitment deposit followed by 5 equal monthly milestones."></textarea>
                </div>

                <div class="flex items-center space-x-3 pt-2">
                    <input type="checkbox" name="is_active" id="is_active" x-model="formData.is_active" value="1" class="w-4 h-4 text-brand-600 rounded border-gray-300 focus:ring-brand-500">
                    <label for="is_active" class="text-xs font-bold text-gray-700 dark:text-slate-300">Active (Visible for Sales &amp; Deals)</label>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-slate-700">
                    <button type="button" @click="modalOpen = false" class="px-5 py-2.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-300 font-bold text-xs rounded-xl hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl transition-all shadow-md">
                        <span x-text="isEdit ? 'Update Plan' : 'Save Plan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function paymentPlanManager() {
    return {
        simPrice: 50000000,
        durationsList: @json($durations),
        modalOpen: false,
        isEdit: false,
        updateUrl: '',
        formData: {
            name: '',
            duration_months: 6,
            interest_rate_pct: 5.00,
            initial_deposit_pct: 30.00,
            number_of_installments: 6,
            description: '',
            is_active: true
        },
        calculateTotal(plan) {
            let interest = (this.simPrice * parseFloat(plan.interest_rate_pct || 0)) / 100;
            return this.simPrice + interest;
        },
        calculateDeposit(plan) {
            let total = this.calculateTotal(plan);
            return (total * parseFloat(plan.initial_deposit_pct || 0)) / 100;
        },
        formatNumber(num) {
            return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(num);
        },
        openAddModal() {
            this.isEdit = false;
            this.formData = {
                name: '',
                duration_months: 6,
                interest_rate_pct: 5.00,
                initial_deposit_pct: 30.00,
                number_of_installments: 6,
                description: '',
                is_active: true
            };
            this.modalOpen = true;
        },
        openEditModal(plan) {
            this.isEdit = true;
            this.updateUrl = '/settings/payment-plans/' + plan.id;
            this.formData = {
                name: plan.name,
                duration_months: plan.duration_months,
                interest_rate_pct: parseFloat(plan.interest_rate_pct),
                initial_deposit_pct: parseFloat(plan.initial_deposit_pct),
                number_of_installments: plan.number_of_installments,
                description: plan.description,
                is_active: Boolean(plan.is_active)
            };
            this.modalOpen = true;
        }
    }
}
</script>
@endsection
