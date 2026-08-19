@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showEditModal: false, activeStaff: null }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('payroll.index') }}" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-white bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <h1 class="text-2xl font-extrabold text-dark-900 dark:text-white tracking-tight">Staff Salary Structures</h1>
            </div>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Configure baseline salary, housing, transport allowances, tax rates, and bank disbursement accounts.</p>
        </div>
    </div>

    <!-- Staff Salary Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700 text-xs font-bold text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-6 py-3.5">Staff Member</th>
                        <th class="px-6 py-3.5">Base Salary</th>
                        <th class="px-6 py-3.5">Total Allowances</th>
                        <th class="px-6 py-3.5">Gross Base</th>
                        <th class="px-6 py-3.5">PAYE Tax / Pension</th>
                        <th class="px-6 py-3.5">Disbursement Bank</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @foreach($staff as $u)
                    @php $s = $u->salaryStructure; @endphp
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-slate-700 text-brand-600 flex items-center justify-center font-bold text-xs">
                                    {{ substr($u->name, 0, 2) }}
                                </div>
                                <div>
                                    <p class="font-bold text-dark-900 dark:text-white">{{ $u->name }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $u->departmentRelation->name ?? ucfirst($u->role) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-bold text-dark-900 dark:text-white">
                            ₦{{ number_format($s?->base_salary ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 text-gray-700 dark:text-slate-300 font-semibold">
                            ₦{{ number_format($s?->total_allowances ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 font-extrabold text-brand-600">
                            ₦{{ number_format($s?->total_fixed_gross ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-gray-500">
                            Tax: {{ $s?->tax_percent ?? 0 }}% | Pen: {{ $s?->pension_percent ?? 0 }}%
                        </td>
                        <td class="px-6 py-4 text-xs">
                            @if($s?->bank_name)
                                <span class="font-bold text-dark-900 dark:text-white">{{ $s->bank_name }}</span>
                                <span class="text-gray-400 block">{{ $s->account_number }}</span>
                            @else
                                <span class="text-amber-500 font-medium">Not set</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button @click="activeStaff = {{ json_encode([
                                        'id' => $u->id,
                                        'name' => $u->name,
                                        'base_salary' => $s?->base_salary ?? 0,
                                        'housing_allowance' => $s?->housing_allowance ?? 0,
                                        'transport_allowance' => $s?->transport_allowance ?? 0,
                                        'other_allowances' => $s?->other_allowances ?? 0,
                                        'tax_percent' => $s?->tax_percent ?? 0,
                                        'pension_percent' => $s?->pension_percent ?? 0,
                                        'bank_name' => $s?->bank_name ?? '',
                                        'account_number' => $s?->account_number ?? '',
                                        'account_name' => $s?->account_name ?? '',
                                    ]) }}; showEditModal = true" 
                                    class="inline-flex items-center space-x-1 text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 dark:bg-slate-700 px-3 py-1.5 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                <span>Edit Structure</span>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($staff->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-700">
            {{ $staff->links() }}
        </div>
        @endif
    </div>

    <!-- Edit Salary Structure Modal -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-dark-900/50 backdrop-blur-sm" @click="showEditModal = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-200 dark:border-slate-700">
            <h3 class="text-lg font-extrabold text-dark-900 dark:text-white">Edit Salary Structure</h3>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Configuring compensation for <strong class="text-dark-900 dark:text-white" x-text="activeStaff?.name"></strong></p>

            <form :action="'/payroll/salaries/' + activeStaff?.id" method="POST" class="mt-5 space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase mb-1">Base Monthly Pay (₦) *</label>
                        <input type="number" step="0.01" name="base_salary" :value="activeStaff?.base_salary" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-sm font-semibold focus:border-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase mb-1">Housing Allowance (₦)</label>
                        <input type="number" step="0.01" name="housing_allowance" :value="activeStaff?.housing_allowance"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-sm font-semibold focus:border-brand-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase mb-1">Transport Allowance (₦)</label>
                        <input type="number" step="0.01" name="transport_allowance" :value="activeStaff?.transport_allowance"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-sm font-semibold focus:border-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase mb-1">Other Allowances (₦)</label>
                        <input type="number" step="0.01" name="other_allowances" :value="activeStaff?.other_allowances"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-sm font-semibold focus:border-brand-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase mb-1">PAYE Tax (%)</label>
                        <input type="number" step="0.01" name="tax_percent" :value="activeStaff?.tax_percent" placeholder="e.g. 7.5"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-sm font-semibold focus:border-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase mb-1">Pension (%)</label>
                        <input type="number" step="0.01" name="pension_percent" :value="activeStaff?.pension_percent" placeholder="e.g. 8"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-sm font-semibold focus:border-brand-500 focus:outline-none">
                    </div>
                </div>

                <!-- Banking details -->
                <div class="pt-2 border-t border-gray-100 dark:border-slate-700">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Disbursement Bank Account</p>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 dark:text-slate-400 mb-1">Bank Name</label>
                            <input type="text" name="bank_name" :value="activeStaff?.bank_name" placeholder="GTBank"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 dark:text-slate-400 mb-1">Account Number</label>
                            <input type="text" name="account_number" :value="activeStaff?.account_number" placeholder="0123456789"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 dark:text-slate-400 mb-1">Account Name</label>
                            <input type="text" name="account_name" :value="activeStaff?.account_name" placeholder="Full Account Name"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end space-x-3">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-100">Cancel</button>
                    <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2 rounded-xl text-xs font-bold shadow-md shadow-brand-500/20">Save Structure</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
