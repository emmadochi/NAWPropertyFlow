@extends('layouts.app')

@section('title', 'Edit Supplier - ' . $supplier->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.suppliers.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Suppliers Directory</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Edit Vendor</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Edit Supplier: {{ $supplier->name }}</h1>
        </div>
        <a href="{{ route('inventory.suppliers.show', $supplier) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-all">
            View Details
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

    <form method="POST" action="{{ route('inventory.suppliers.update', $supplier) }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Supplier Name -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Company / Vendor Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Supplier Code -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Supplier Code <span class="text-rose-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $supplier->code) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white uppercase font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Contact Person -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Primary Contact Person</label>
                <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Phone -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Email -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Payment Terms Days -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Payment Terms (Net Days) <span class="text-rose-500">*</span></label>
                <input type="number" name="payment_terms_days" value="{{ old('payment_terms_days', $supplier->payment_terms_days) }}" min="0" max="180" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Bank Name -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Bank Name</label>
                <input type="text" name="bank_name" value="{{ old('bank_name', $supplier->bank_name) }}"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Account Number -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">NUBAN Account Number</label>
                <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $supplier->bank_account_number) }}"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Account Name -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Account Name</label>
                <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $supplier->bank_account_name) }}"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- RC & TIN -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">CAC RC / Registration No.</label>
                <input type="text" name="rc_number" value="{{ old('rc_number', $supplier->rc_number) }}"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white uppercase focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-slate-800 flex justify-end gap-3">
            <a href="{{ route('inventory.suppliers.show', $supplier) }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-sm font-bold transition-all">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
