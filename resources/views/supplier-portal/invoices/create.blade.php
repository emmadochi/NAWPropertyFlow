@extends('supplier-portal.layout')

@section('title', 'Submit Invoice')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-black text-white tracking-tight">Submit Digital Invoice</h1>
        <p class="text-xs text-slate-400 mt-1">Submit your billing against verified purchase orders for automated 3-Way matching.</p>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-950/40 border border-rose-800 text-rose-300 text-xs">
            <p class="font-bold mb-1">Please fix the following issues:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('supplier.invoices.store') }}" enctype="multipart/form-data" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-6 shadow-2xl">
        @csrf

        <div class="space-y-4">
            <!-- Purchase Order -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Target Purchase Order (PO) <span class="text-rose-500">*</span></label>
                <select name="purchase_order_id" required
                        class="w-full py-2.5 px-3 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select PO Reference</option>
                    @foreach($purchaseOrders as $po)
                        <option value="{{ $po->id }}" {{ (old('purchase_order_id', $selectedPo?->id) == $po->id) ? 'selected' : '' }}>
                            {{ $po->ref_number }} (Site: {{ $po->site?->name }} — PO Total: ₦{{ number_format($po->total_amount, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Invoice Number -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Your Invoice Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number') }}" placeholder="e.g. INV-99012" required
                           class="w-full py-2.5 px-3 bg-slate-800 border border-slate-700 rounded-xl text-sm font-mono text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Billed Amount -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Total Billed Amount (₦) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="billed_amount" value="{{ old('billed_amount', $selectedPo?->total_amount) }}" placeholder="0.00" required
                           class="w-full py-2.5 px-3 bg-slate-800 border border-slate-700 rounded-xl text-sm font-mono font-bold text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Invoice Date -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Invoice Issue Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required
                           class="w-full py-2.5 px-3 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <!-- Due Date -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Payment Due Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required
                           class="w-full py-2.5 px-3 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>

            <!-- Upload PDF/Image -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Upload PDF Invoice / Receipt</label>
                <input type="file" name="invoice_file" accept=".pdf,image/*"
                       class="w-full py-2 px-3 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white focus:outline-none">
            </div>
        </div>

        <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
            <a href="{{ route('supplier.invoices.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-600/30 transition-all">
                Submit Invoice
            </button>
        </div>
    </form>
</div>
@endsection
