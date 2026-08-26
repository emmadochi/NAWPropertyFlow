@extends('layouts.app')

@section('title', 'Record Supplier Invoice')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.invoices.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Supplier Invoices</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Record Invoice</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Record Supplier Invoice &amp; Run 3-Way Match</h1>
        </div>
        <a href="{{ route('inventory.invoices.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-all">
            Cancel
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300 text-sm">
            <p class="font-bold mb-1">Please fix the following errors:</p>
            <ul class="list-disc pl-5 space-y-0.5 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.invoices.store') }}" enctype="multipart/form-data" class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-6 shadow-sm">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Purchase Order -->
            <div class="space-y-1 md:col-span-2">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Purchase Order (PO) Reference <span class="text-rose-500">*</span></label>
                <select name="purchase_order_id" required
                        class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select Purchase Order</option>
                    @foreach($purchaseOrders as $po)
                        <option value="{{ $po->id }}" {{ (old('purchase_order_id', $selectedPo?->id) == $po->id) ? 'selected' : '' }}>
                            {{ $po->ref_number }} — {{ $po->supplier?->name }} (PO Amount: ₦{{ number_format($po->total_amount, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Supplier -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Vendor / Supplier <span class="text-rose-500">*</span></label>
                <select name="supplier_id" required
                        class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select Supplier</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ (old('supplier_id', $selectedPo?->supplier_id) == $s->id) ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Invoice Number -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Vendor Invoice Number <span class="text-rose-500">*</span></label>
                <input type="text" name="invoice_number" value="{{ old('invoice_number') }}" placeholder="e.g. INV-2026-9901" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Invoice Date -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Invoice Date <span class="text-rose-500">*</span></label>
                <input type="date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Due Date -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Payment Due Date <span class="text-rose-500">*</span></label>
                <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Billed Amount -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Billed Grand Total (₦) <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" name="billed_amount" value="{{ old('billed_amount', $selectedPo?->total_amount) }}" placeholder="0.00" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-mono font-bold focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Upload PDF/Image -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Upload Scanned Invoice (PDF / Photo)</label>
                <input type="file" name="invoice_file" accept=".pdf,image/*"
                       class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none">
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-slate-800 flex justify-end gap-3">
            <a href="{{ route('inventory.invoices.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-sm font-bold transition-all">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                Save &amp; Run 3-Way Match
            </button>
        </div>
    </form>
</div>
@endsection
