@extends('layouts.app')

@section('title', 'Multi-Bank Treasury & Automated Bank Reconciliation')

@section('content')
<div class="space-y-8 pb-16">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-slate-800 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <a href="{{ route('accounting.dashboard') }}" class="text-xs text-brand-600 font-bold hover:underline">&larr; Financial Cockpit</a>
                <span class="text-slate-400">/</span>
                <span class="text-xs text-slate-500 font-bold uppercase">Treasury Management</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Multi-Bank Treasury &amp; Reconciliation</h1>
            <p class="text-xs text-slate-500">Manage corporate bank accounts, import statements (CSV), and match transactions automatically.</p>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('modal-add-bank').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-2xl text-xs font-black uppercase tracking-wider shadow-md shadow-brand-600/20">
                + Add Bank Account
            </button>
            <button onclick="document.getElementById('modal-import-statement').classList.remove('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-2xl text-xs font-black uppercase tracking-wider">
                📥 Import Statement (CSV)
            </button>
        </div>
    </div>

    <!-- Corporate Bank Account Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse($bankAccounts as $acc)
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-2xl">🏦</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 text-[10px] font-bold">
                        {{ $acc->gl_account_code }}
                    </span>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white">{{ $acc->account_name }}</h3>
                    <p class="text-xs text-slate-400 font-mono">{{ $acc->bank_name }} &bull; {{ $acc->account_number }}</p>
                </div>
                <div class="pt-2 border-t border-gray-100 dark:border-slate-800">
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider block">Live Book Balance</span>
                    <span class="text-lg font-black text-brand-600 dark:text-brand-400">{{ $acc->formatted_balance }}</span>
                </div>
            </div>
        @empty
            <div class="sm:col-span-4 p-8 text-center bg-gray-50 dark:bg-slate-900/40 rounded-3xl border border-dashed border-gray-300 dark:border-slate-800">
                <span class="text-3xl block mb-2">🏦</span>
                <p class="text-sm font-bold text-slate-600 dark:text-slate-300">No corporate bank accounts registered yet.</p>
                <button onclick="document.getElementById('modal-add-bank').classList.remove('hidden')" class="mt-3 text-xs text-brand-600 font-bold hover:underline">+ Add First Bank Account</button>
            </div>
        @endforelse
    </div>

    <!-- Unreconciled Bank Lines Queue -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">Pending Bank Statement Lines for Reconciliation</h3>
                <p class="text-xs text-slate-400">Statement lines awaiting automated or manual match against receipts and vendor vouchers.</p>
            </div>
            <span class="px-3 py-1 bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 rounded-full text-xs font-bold">
                {{ $unreconciledTransactions->count() }} Unreconciled
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-slate-800/80 uppercase tracking-wider text-[10px] font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Account</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Reference &amp; Narration</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($unreconciledTransactions as $tx)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-800/40">
                            <td class="py-3 px-4 whitespace-nowrap">{{ $tx->transaction_date->format('M d, Y') }}</td>
                            <td class="py-3 px-4 font-bold">{{ $tx->bankAccount->account_name }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tx->type === 'credit' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400' }}">
                                    {{ strtoupper($tx->type) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <strong class="block font-medium text-slate-900 dark:text-white">{{ $tx->reference ?: 'No Ref' }}</strong>
                                <span class="text-[11px] text-slate-400">{{ $tx->narration }}</span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold {{ $tx->type === 'credit' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $tx->type === 'credit' ? '+' : '-' }} ₦{{ number_format($tx->amount, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="text-amber-500 font-bold text-[11px]">Pending Match</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 italic">All bank statement transactions are 100% reconciled!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Bank Account -->
<div id="modal-add-bank" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 p-6 rounded-3xl shadow-2xl space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-black text-slate-900 dark:text-white">Add Corporate Bank Account</h3>
            <button onclick="document.getElementById('modal-add-bank').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('accounting.treasury.store-account') }}" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Account Display Name</label>
                <input type="text" name="account_name" required placeholder="e.g. Zenith Main Operations"
                       class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Bank Name</label>
                <input type="text" name="bank_name" required placeholder="e.g. Zenith Bank Plc"
                       class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Account Number (NUBAN)</label>
                <input type="text" name="account_number" required placeholder="1012345678"
                       class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Currency</label>
                    <select name="currency" class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white">
                        <option value="NGN">NGN (₦)</option>
                        <option value="USD">USD ($)</option>
                        <option value="GBP">GBP (£)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">GL Account Code</label>
                    <select name="gl_account_code" class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white">
                        @foreach($glAccounts as $gl)
                            <option value="{{ $gl->account_code }}">{{ $gl->account_code }} - {{ $gl->account_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Opening Balance (₦)</label>
                <input type="number" step="0.01" name="opening_balance" value="0.00" required
                       class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white">
            </div>
            <div class="pt-2">
                <button type="submit" class="w-full py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl font-bold shadow-lg shadow-brand-600/30">
                    Save Bank Account
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Import CSV Statement -->
<div id="modal-import-statement" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur z-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 p-6 rounded-3xl shadow-2xl space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-black text-slate-900 dark:text-white">Import Bank Statement (CSV)</h3>
            <button onclick="document.getElementById('modal-import-statement').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('accounting.treasury.import-statement') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Target Bank Account</label>
                <select name="bank_account_id" required class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white">
                    @foreach($bankAccounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->account_name }} ({{ $acc->bank_name }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">CSV File</label>
                <input type="file" name="statement_file" accept=".csv,.txt" required
                       class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brand-600 file:text-white">
                <span class="text-[11px] text-slate-400 mt-1 block">Expected CSV Columns: Date, Reference, Narration, Debit, Credit</span>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl font-bold shadow-lg shadow-brand-600/30">
                    Upload &amp; Auto-Reconcile
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
