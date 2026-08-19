@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-4xl">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-dark-900 dark:text-white tracking-tight">Company Settings</h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Configure company identity, branding logo, official contact info, and letterheads.</p>
        </div>
    </div>

    <!-- Company Settings Form Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 shadow-sm overflow-hidden p-6 md:p-8">
        <form action="{{ route('settings.company.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Logo & Brand Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-4 sm:space-y-0 sm:space-x-6 pb-6 border-b border-gray-100 dark:border-slate-800">
                <div class="relative group">
                    @if($settings->logo_path)
                        <img src="{{ asset('storage/' . $settings->logo_path) }}" 
                             alt="Company Logo" 
                             class="w-24 h-24 rounded-2xl object-contain bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 p-2 shadow-sm">
                    @else
                        <div class="w-24 h-24 rounded-2xl bg-brand-50 dark:bg-slate-800 border border-brand-200 dark:border-slate-700 flex items-center justify-center text-brand-500 font-bold text-2xl">
                            {{ substr($settings->company_name ?? 'CO', 0, 2) }}
                        </div>
                    @endif
                </div>

                <div class="flex-1">
                    <label class="block text-sm font-bold text-gray-700 dark:text-slate-200 mb-1">Company Brand Logo</label>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-3">Upload your official high-resolution PNG or SVG logo for the navbar, emails, and PDFs.</p>
                    <input type="file" name="logo" accept="image/*"
                           class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100 cursor-pointer">
                </div>
            </div>

            <!-- Basic Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Company Name *</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $settings->company_name) }}" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Official Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $settings->email) }}"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $settings->phone) }}"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Active License Tier</label>
                    <input type="text" value="{{ ucfirst($settings->package_tier ?? 'enterprise') }}" disabled
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 text-gray-500 dark:text-slate-400 text-sm font-bold uppercase tracking-wider cursor-not-allowed">
                </div>
            </div>

            <!-- Address -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider mb-2">Head Office Address</label>
                <textarea name="address" rows="2"
                          class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none text-sm">{{ old('address', $settings->address) }}</textarea>
            </div>

            <!-- Letterhead Customization -->
            <div class="pt-4 border-t border-gray-100 dark:border-slate-800 space-y-4">
                <h3 class="text-sm font-bold text-dark-900 dark:text-white uppercase tracking-wider">Document & Receipt Customization</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-1">Receipt / Invoice Header Note</label>
                        <textarea name="letterhead_header" rows="2" placeholder="e.g. Official Receipt of Ricaf Nigeria Limited"
                                  class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none text-sm">{{ old('letterhead_header', $settings->letterhead_header) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-1">Receipt / Letterhead Footer Note</label>
                        <textarea name="letterhead_footer" rows="2" placeholder="e.g. Thank you for doing business with us. Valid without signature."
                                  class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none text-sm">{{ old('letterhead_footer', $settings->letterhead_footer) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 border-t border-gray-100 dark:border-slate-800 flex justify-end">
                <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-brand-500/20 hover:shadow-brand-600/30 transition-all text-sm">
                    Save Company Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
