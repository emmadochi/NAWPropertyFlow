@extends('layouts.app')

@section('title', 'Expense Management & Financial Ledger')

@section('content')
<div class="space-y-6" x-data="{ addExpenseModal: false, selectedReceipt: null }">

    <!-- Top Action & Navigation Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-brand-500/10 text-brand-500 border border-brand-500/30">
                    Finance &amp; Accounting
                </span>
                <span class="text-xs font-semibold text-gray-400">Ledger &amp; OPEX</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-dark-900 dark:text-white tracking-tight mt-1">
                Operating Expenses &amp; Cost Control
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-slate-400">
                Track project development outlays, site operations, marketing spend, and corporate P&amp;L.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button @click="addExpenseModal = true" class="inline-flex items-center space-x-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-lg shadow-brand-500/20 transition-all transform hover:scale-105 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Log New Expense</span>
            </button>
        </div>
    </div>

    <!-- P&L and Financial Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Inflow -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-400">Total Verified Inflow</span>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 text-xs font-black">💰 Inflow</span>
            </div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">
                ₦{{ number_format($totalVerifiedInflow, 2) }}
            </div>
            <p class="text-[11px] text-gray-400 mt-1">From client property payments</p>
        </div>

        <!-- Total OPEX Expenses -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-400">Total Operating Expenses</span>
                <span class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 text-xs font-black">📉 OPEX</span>
            </div>
            <div class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-2">
                ₦{{ number_format($totalExpensesAllTime, 2) }}
            </div>
            <p class="text-[11px] text-gray-400 mt-1">This month: ₦{{ number_format($thisMonthExpenses, 2) }}</p>
        </div>

        <!-- Total Payroll Outflows -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-400">Payroll &amp; Salaries</span>
                <span class="p-2 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 text-xs font-black">👥 Staff</span>
            </div>
            <div class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-2">
                ₦{{ number_format($totalPayrollPaid, 2) }}
            </div>
            <p class="text-[11px] text-gray-400 mt-1">Salaries &amp; paid commissions</p>
        </div>

        <!-- Net Operating Profit -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-950 p-5 rounded-2xl border border-brand-500/30 text-white shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-brand-400">Net Operating Position</span>
                <span class="p-1.5 rounded-lg bg-brand-500/20 text-brand-400 text-xs font-bold">📊 P&amp;L</span>
            </div>
            <div class="text-2xl font-black {{ $netOperatingProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }} mt-2">
                ₦{{ number_format($netOperatingProfit, 2) }}
            </div>
            <p class="text-[11px] text-gray-400 mt-1">
                {{ $netOperatingProfit >= 0 ? 'Net positive liquidity' : 'Net deficit balance' }}
            </p>
        </div>

    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-sm">
        <form action="{{ route('accounting.expenses.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Expense Category</label>
                <select name="category" onchange="this.form.submit()" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-800 dark:text-slate-200 rounded-xl px-3 py-2 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                    <option value="">All Categories</option>
                    @foreach($categories as $catKey => $catLabel)
                        <option value="{{ $catKey }}" {{ request('category') == $catKey ? 'selected' : '' }}>{{ $catLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Approval Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-800 dark:text-slate-200 rounded-xl px-3 py-2 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Pending Audit ({{ $pendingApprovalCount }})</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>✓ Approved</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>💳 Paid / Disbursed</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>✕ Rejected</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Linked Project / Estate</label>
                <select name="property_id" onchange="this.form.submit()" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-800 dark:text-slate-200 rounded-xl px-3 py-2 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                    <option value="">All Projects</option>
                    @foreach($properties as $prop)
                        <option value="{{ $prop->id }}" {{ request('property_id') == $prop->id ? 'selected' : '' }}>{{ $prop->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" onchange="this.form.submit()"
                       class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-800 dark:text-slate-200 rounded-xl px-3 py-1.5 text-xs font-semibold focus:border-brand-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" onchange="this.form.submit()"
                       class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-800 dark:text-slate-200 rounded-xl px-3 py-1.5 text-xs font-semibold focus:border-brand-500 focus:outline-none">
            </div>
        </form>
    </div>

    <!-- Expenses Ledger Table -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-dark-900 dark:text-white uppercase tracking-wider">
                Financial Outflows Ledger
            </h3>
            <span class="text-xs font-bold text-gray-400">{{ $expenses->total() }} recorded entries</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-slate-900/60 text-gray-400 uppercase text-[10px] font-extrabold border-b border-gray-100 dark:border-slate-700">
                    <tr>
                        <th class="py-3.5 px-5">Ref / Title</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">Project / Estate</th>
                        <th class="py-3.5 px-4">Amount</th>
                        <th class="py-3.5 px-4">Logged By</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700 font-medium">
                    @forelse($expenses as $item)
                    <tr class="hover:bg-gray-50/70 dark:hover:bg-slate-750 transition-colors">
                        <!-- Ref / Title -->
                        <td class="py-4 px-5">
                            <div class="font-extrabold text-dark-900 dark:text-white">{{ $item->title }}</div>
                            <div class="text-[11px] text-gray-400 font-mono mt-0.5">{{ $item->reference_number }} {{ $item->vendor_name ? '• ' . $item->vendor_name : '' }}</div>
                        </td>

                        <!-- Category -->
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                {{ $item->category }}
                            </span>
                        </td>

                        <!-- Project -->
                        <td class="py-4 px-4">
                            @if($item->property)
                                <span class="font-bold text-gray-700 dark:text-slate-300">{{ $item->property->name }}</span>
                            @else
                                <span class="text-gray-400 text-[11px]">General OPEX</span>
                            @endif
                        </td>

                        <!-- Amount -->
                        <td class="py-4 px-4">
                            <span class="font-black text-sm text-dark-900 dark:text-white">
                                ₦{{ number_format($item->amount, 2) }}
                            </span>
                            <div class="text-[10px] text-gray-400">{{ $item->payment_method }}</div>
                        </td>

                        <!-- Logged By -->
                        <td class="py-4 px-4">
                            <div class="font-bold text-gray-800 dark:text-slate-200">{{ $item->user->name ?? 'System' }}</div>
                            <div class="text-[10px] text-gray-400 capitalize">{{ str_replace('_', ' ', $item->user->role ?? '') }}</div>
                        </td>

                        <!-- Date -->
                        <td class="py-4 px-4 text-gray-500 dark:text-slate-400">
                            {{ $item->expense_date->format('d M Y') }}
                        </td>

                        <!-- Status Pill -->
                        <td class="py-4 px-4">
                            @if($item->status === 'paid')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-500/20">
                                    ✓ Disbursed
                                </span>
                            @elseif($item->status === 'approved')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-500/20">
                                    ✓ Approved
                                </span>
                            @elseif($item->status === 'pending')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-500/20">
                                    ⏳ Pending Audit
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-500/20">
                                    ✕ Rejected
                                </span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-5 text-right space-x-1.5">
                            @if($item->receipt_file)
                                <a href="{{ asset('storage/' . $item->receipt_file) }}" target="_blank" 
                                   class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 text-gray-700 dark:text-slate-200 text-[11px] font-bold"
                                   title="View Attachment">
                                    📎 Receipt
                                </a>
                            @endif

                            @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'accountant', 'finance_manager']))
                                @if($item->status === 'pending')
                                    <form action="{{ route('accounting.expenses.status', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold shadow-sm">
                                            Approve
                                        </button>
                                    </form>
                                @endif

                                @if($item->status === 'approved')
                                    <form action="{{ route('accounting.expenses.status', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="paid">
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-[11px] font-bold shadow-sm">
                                            Mark Paid
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-gray-400 text-xs">
                            No expense records found matching current criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-700">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>

    <!-- Modal: Log New Expense -->
    <div x-cloak x-show="addExpenseModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 backdrop-blur-sm overflow-y-auto">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-gray-200 dark:border-slate-700 space-y-4 my-8"
             @click.away="addExpenseModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-slate-700">
                <div class="flex items-center space-x-2.5">
                    <span class="p-2 rounded-xl bg-brand-500/10 text-brand-500 font-bold text-base">💸</span>
                    <div>
                        <h3 class="text-base font-extrabold text-dark-900 dark:text-white">Log Operational Expense</h3>
                        <p class="text-[11px] text-gray-400">Record an outflow for approval and financial audit.</p>
                    </div>
                </div>
                <button @click="addExpenseModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('accounting.expenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Expense Title / Description *</label>
                    <input type="text" name="title" required placeholder="e.g. Site Generator Diesel - Hutu Prestige"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-semibold text-gray-800 dark:text-white focus:border-brand-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Category *</label>
                        <select name="category" required class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-semibold text-gray-800 dark:text-white focus:border-brand-500 focus:outline-none">
                            @foreach($categories as $catKey => $catLabel)
                                <option value="{{ $catKey }}">{{ $catLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Amount (₦) *</label>
                        <input type="number" name="amount" step="0.01" min="0.01" required placeholder="e.g. 250000"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-black text-rose-600 focus:border-brand-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Expense Date *</label>
                        <input type="date" name="expense_date" value="{{ now()->format('Y-m-d') }}" required
                               class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-semibold text-gray-800 dark:text-white focus:border-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Linked Project (Optional)</label>
                        <select name="property_id" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-semibold text-gray-800 dark:text-white focus:border-brand-500 focus:outline-none">
                            <option value="">-- General Office OPEX --</option>
                            @foreach($properties as $prop)
                                <option value="{{ $prop->id }}">{{ $prop->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Vendor / Payee Name</label>
                        <input type="text" name="vendor_name" placeholder="e.g. NNPC Mega Station"
                               class="w-full px-3.5 py-2 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-semibold text-gray-800 dark:text-white focus:border-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-semibold text-gray-800 dark:text-white focus:border-brand-500 focus:outline-none">
                            <option value="Bank Transfer">Bank Transfer (EFT/NIP)</option>
                            <option value="Petty Cash">Office Petty Cash</option>
                            <option value="Corporate Card">Corporate Debit Card</option>
                            <option value="Cheque">Bank Cheque</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Attach Receipt / Invoice (PDF / Image)</label>
                    <input type="file" name="receipt_file" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-orange-50 file:text-brand-600 hover:file:bg-orange-100 cursor-pointer">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-1">Audit Notes / Comments</label>
                    <textarea name="notes" rows="2" placeholder="Provide extra context for the finance audit desk..."
                              class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-xs font-semibold text-gray-800 dark:text-white focus:border-brand-500 focus:outline-none resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-gray-100 dark:border-slate-700">
                    <button type="button" @click="addExpenseModal = false" class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white font-bold px-5 py-2.5 rounded-xl text-xs shadow-md shadow-brand-500/20">
                        Submit Expense
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
