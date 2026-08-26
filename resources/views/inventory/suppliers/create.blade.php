@extends('layouts.app')

@section('title', 'Register New Material Supplier')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.suppliers.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Suppliers Directory</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Register Vendor</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Register Material Supplier</h1>
        </div>
        <a href="{{ route('inventory.suppliers.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-all">
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

    <form method="POST" action="{{ route('inventory.suppliers.store') }}" x-data="{ enablePortal: false }" class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-6 shadow-sm">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 border-b border-gray-100 dark:border-slate-800 pb-2">1. Company &amp; Contact Details</h3>
            </div>

            <!-- Supplier Name -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Company / Vendor Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Julius Berger Aggregates Ltd" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Supplier Code -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Supplier Code <span class="text-rose-500">*</span></label>
                <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. SUP-JB-001" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white uppercase font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Contact Person -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Primary Contact Person</label>
                <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="e.g. Alhaji Musa"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Phone -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. 08031234567"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Email -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="e.g. orders@supplier.ng"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Payment Terms Days -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Payment Terms (Net Days) <span class="text-rose-500">*</span></label>
                <input type="number" name="payment_terms_days" value="{{ old('payment_terms_days', 30) }}" min="0" max="180" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <p class="text-[11px] text-gray-400">Number of days allowed before invoice payment is due (e.g. 30 days).</p>
            </div>

            <div class="md:col-span-2">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 border-b border-gray-100 dark:border-slate-800 pb-2">2. Banking &amp; Statutory Info</h3>
            </div>

            <!-- Bank Name -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Bank Name</label>
                <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="e.g. Zenith Bank Plc"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Account Number -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">NUBAN Account Number</label>
                <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="e.g. 1012345678"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Account Name -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Account Name</label>
                <input type="text" name="bank_account_name" value="{{ old('bank_account_name') }}" placeholder="e.g. Julius Berger Aggregates Ltd"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- RC & TIN -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">CAC RC / Registration No.</label>
                <input type="text" name="rc_number" value="{{ old('rc_number') }}" placeholder="e.g. RC-123456"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white uppercase focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div class="md:col-span-2">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 border-b border-gray-100 dark:border-slate-800 pb-2">3. Supplier Portal Account (Optional)</h3>
            </div>

            <!-- Enable Portal User -->
            <div class="md:col-span-2 flex items-center gap-3">
                <input type="checkbox" id="create_portal_user" name="create_portal_user" value="1" x-model="enablePortal"
                       class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300 dark:border-slate-700">
                <label for="create_portal_user" class="text-sm font-bold text-slate-800 dark:text-slate-200">
                    Create Login for Supplier Self-Service Portal (Can view POs and submit invoices online)
                </label>
            </div>

            <template x-if="enablePortal">
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase text-purple-900 dark:text-purple-300">Portal User Name</label>
                        <input type="text" name="portal_user_name" value="{{ old('portal_user_name') }}" placeholder="Representative Name"
                               class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-purple-200 dark:border-purple-700 rounded-xl text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase text-purple-900 dark:text-purple-300">Portal Login Email</label>
                        <input type="email" name="portal_user_email" value="{{ old('portal_user_email') }}" placeholder="rep@supplier.com"
                               class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-purple-200 dark:border-purple-700 rounded-xl text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase text-purple-900 dark:text-purple-300">Temporary Password</label>
                        <input type="password" name="portal_user_password" placeholder="Min 8 characters"
                               class="w-full py-2 px-3 bg-white dark:bg-slate-800 border border-purple-200 dark:border-purple-700 rounded-xl text-sm">
                    </div>
                </div>
            </template>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-slate-800 flex justify-end gap-3">
            <a href="{{ route('inventory.suppliers.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-sm font-bold transition-all">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                Register Supplier
            </button>
        </div>
    </form>
</div>
@endsection
