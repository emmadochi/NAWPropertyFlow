@extends('layouts.app')

@section('title', 'Regional Market Price Benchmarks')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Dashboard</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Price Benchmarks</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Regional Construction Material Price Index</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Admin-recorded market prices across Lagos, Abuja, Port Harcourt and regional building material markets.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- New Benchmark Entry Form -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-4 shadow-sm">
            <div>
                <h3 class="font-bold text-base text-slate-900 dark:text-white">Record Market Price</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Log a new surveyed market price point.</p>
            </div>

            <form method="POST" action="{{ route('inventory.benchmarks.store') }}" class="space-y-4">
                @csrf

                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Material <span class="text-rose-500">*</span></label>
                    <select name="material_id" required class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <option value="">Select Material</option>
                        @foreach($materials as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->unit_of_measure }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Market City / Region <span class="text-rose-500">*</span></label>
                    <select name="city" required class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        @foreach($cities as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Surveyed Unit Price (₦) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="unit_price" placeholder="0.00" required
                           class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Source Market Name</label>
                    <input type="text" name="source_market_name" placeholder="e.g. Alaba Rago, Dei-Dei, Mile 2"
                           class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Survey Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="recorded_date" value="{{ date('Y-m-d') }}" required
                           class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <button type="submit" class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                    Save Benchmark Price
                </button>
            </form>
        </div>

        <!-- Recent Recorded Benchmarks Table -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-slate-800">
                <h3 class="font-bold text-base text-slate-900 dark:text-white">Surveyed Market Prices</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Latest recorded market benchmarks used for price variance detection on POs.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                    <thead class="bg-gray-50 dark:bg-slate-800/60 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-gray-200 dark:border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Material</th>
                            <th class="py-3 px-4">Market / Region</th>
                            <th class="py-3 px-4 text-right">Surveyed Price</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                        @forelse($benchmarks as $bm)
                            <tr class="hover:bg-gray-50/75 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">
                                    {{ $bm->material?->name }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-xs font-medium">{{ $cities[$bm->city] ?? ucfirst($bm->city) }}</span>
                                    @if($bm->source_market_name)
                                        <span class="block text-[11px] text-gray-400">{{ $bm->source_market_name }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                    ₦{{ number_format($bm->unit_price, 2) }}
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-500">
                                    {{ $bm->recorded_date->format('M d, Y') }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form method="POST" action="{{ route('inventory.benchmarks.destroy', $bm) }}" onsubmit="return confirm('Delete this price point?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-rose-500 hover:text-rose-700 font-semibold">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-sm">
                                    No market prices recorded yet. Add your first survey point on the left.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($benchmarks->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-slate-800">
                    {{ $benchmarks->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
