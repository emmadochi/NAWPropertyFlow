@extends('layouts.app')

@section('title', 'Tax & Statutory Compliance Hub (FIRS & State BIR)')

@section('content')
<div class="space-y-8 pb-16">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-slate-800 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('accounting.dashboard') }}" class="text-xs text-brand-600 font-bold hover:underline">&larr; Financial Cockpit</a>
                <span class="text-slate-400">/</span>
                <span class="text-xs text-slate-500 font-bold uppercase">Statutory Compliance</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Tax Compliance &amp; FIRS Hub</h1>
            <p class="text-xs text-slate-500">Automated 5% Withholding Tax (WHT) deductions and 7.5% Value Added Tax (VAT) schedules.</p>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-brand-600/20 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print / PDF</span>
            </button>
        </div>
    </div>

    <!-- VAT Overview Card -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span class="font-bold uppercase tracking-wider">Output VAT (7.5%)</span>
                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 font-bold text-[10px]">Sales</span>
            </div>
            <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                ₦{{ number_format($vatSummary['output_vat'], 2) }}
            </div>
            <p class="text-[11px] text-slate-400">VAT collected on commercial properties &amp; FM</p>
        </div>

        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span class="font-bold uppercase tracking-wider">Input VAT (7.5%)</span>
                <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 font-bold text-[10px]">Purchases</span>
            </div>
            <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                ₦{{ number_format($vatSummary['input_vat'], 2) }}
            </div>
            <p class="text-[11px] text-slate-400">Recoverable VAT on materials &amp; equipment</p>
        </div>

        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span class="font-bold uppercase tracking-wider">Net VAT Remittance</span>
                <span class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-400 font-bold text-[10px]">FIRS</span>
            </div>
            <div class="text-2xl font-black text-brand-600 dark:text-brand-400 tracking-tight">
                ₦{{ number_format($vatSummary['net_vat_payable'], 2) }}
            </div>
            <p class="text-[11px] text-slate-400">Due by 21st of current month</p>
        </div>
    </div>

    <!-- Withholding Tax (WHT 5%) Schedule Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">Withholding Tax (WHT 5%) Deduction Schedule</h3>
                <p class="text-xs text-slate-400">Tax deducted at source on building materials and construction subcontractors for FIRS credit note filing.</p>
            </div>
            <span class="px-3 py-1 bg-brand-500/10 text-brand-600 rounded-full text-xs font-bold">
                Total WHT: ₦{{ number_format($whtSchedule['total_wht'], 2) }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-slate-800/80 uppercase tracking-wider text-[10px] font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Beneficiary (Vendor)</th>
                        <th class="py-3 px-4">TIN / Code</th>
                        <th class="py-3 px-4">Invoice Ref</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right">Gross Amount</th>
                        <th class="py-3 px-4 text-right">WHT (5%)</th>
                        <th class="py-3 px-4 text-right">Net Paid</th>
                        <th class="py-3 px-4 text-center">FIRS Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($whtSchedule['rows'] as $wht)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-800/40">
                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">{{ $wht['beneficiary'] }}</td>
                            <td class="py-3 px-4 font-mono">{{ $wht['beneficiary_tin'] }}</td>
                            <td class="py-3 px-4 font-mono text-brand-600">{{ $wht['reference'] }}</td>
                            <td class="py-3 px-4">{{ \Carbon\Carbon::parse($wht['date'])->format('M d, Y') }}</td>
                            <td class="py-3 px-4 text-right font-mono">₦{{ number_format($wht['gross_amount'], 2) }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-rose-600">₦{{ number_format($wht['wht_amount'], 2) }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">₦{{ number_format($wht['net_payable'], 2) }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $wht['status'] === 'remitted' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400' }}">
                                    {{ strtoupper($wht['status']) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 italic">No WHT deductions recorded in this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 dark:bg-slate-800/90 font-black text-xs text-slate-900 dark:text-white border-t border-gray-200 dark:border-slate-700">
                    <tr>
                        <td colspan="4" class="py-4 px-4 uppercase tracking-wider">Total Cumulative Deductions</td>
                        <td class="py-4 px-4 text-right font-mono">₦{{ number_format($whtSchedule['total_gross'], 2) }}</td>
                        <td class="py-4 px-4 text-right font-mono text-rose-600">₦{{ number_format($whtSchedule['total_wht'], 2) }}</td>
                        <td class="py-4 px-4 text-right font-mono text-emerald-600">₦{{ number_format($whtSchedule['total_gross'] - $whtSchedule['total_wht'], 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
