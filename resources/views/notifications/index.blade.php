@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2.5">
                <span class="text-2xl">🔔</span>
                <h1 class="text-2xl md:text-3xl font-black text-dark-900 dark:text-white tracking-tight">Notification Center</h1>
            </div>
            <p class="text-xs md:text-sm text-gray-500 dark:text-slate-400 mt-1">
                Real-time operational alerts across buyer payment proofs, lead inquiries, scheduled tours, and approvals.
            </p>
        </div>

        <div class="flex items-center space-x-2">
            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-slate-700">
                {{ $unreadCount }} Active Alert{{ $unreadCount === 1 ? '' : 's' }}
            </span>
        </div>
    </div>

    {{-- Category Filters --}}
    <div class="flex items-center space-x-2 overflow-x-auto pb-2">
        <a href="{{ route('notifications.index', ['category' => 'all']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $category === 'all' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-300 hover:bg-gray-50 border border-gray-200 dark:border-slate-700' }}">
            All Notifications
        </a>
        <a href="{{ route('notifications.index', ['category' => 'payments']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $category === 'payments' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-300 hover:bg-gray-50 border border-gray-200 dark:border-slate-700' }}">
            💳 Payment Proofs
        </a>
        <a href="{{ route('notifications.index', ['category' => 'leads']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $category === 'leads' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-300 hover:bg-gray-50 border border-gray-200 dark:border-slate-700' }}">
            🆕 Leads & Follow-ups
        </a>
        <a href="{{ route('notifications.index', ['category' => 'inspections']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $category === 'inspections' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-300 hover:bg-gray-50 border border-gray-200 dark:border-slate-700' }}">
            🏡 Site Tours
        </a>
        <a href="{{ route('notifications.index', ['category' => 'hr']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $category === 'hr' ? 'bg-brand-500 text-white shadow-md shadow-brand-500/20' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-300 hover:bg-gray-50 border border-gray-200 dark:border-slate-700' }}">
            📝 HR & Approvals
        </a>
    </div>

    {{-- Notification List --}}
    <div class="bg-white dark:bg-slate-900 border border-gray-150 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
        @if(empty($allNotifications))
            <div class="p-12 text-center space-y-3">
                <span class="text-4xl block">🎉</span>
                <h3 class="text-base font-extrabold text-dark-900 dark:text-white">All Caught Up!</h3>
                <p class="text-xs text-gray-400 max-w-sm mx-auto">There are no pending alerts or required actions in this category right now.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100 dark:divide-slate-800">
                @foreach($allNotifications as $n)
                <a href="{{ $n['url'] }}" class="p-5 md:p-6 flex items-start space-x-4 hover:bg-gray-50/80 dark:hover:bg-slate-800/50 transition-colors group block">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl flex-shrink-0 
                        @if($n['category'] === 'payments') bg-emerald-50 text-emerald-600 border border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-800
                        @elseif($n['category'] === 'leads') bg-orange-50 text-orange-600 border border-orange-200 dark:bg-orange-950/40 dark:border-orange-800
                        @elseif($n['category'] === 'inspections') bg-blue-50 text-blue-600 border border-blue-200 dark:bg-blue-950/40 dark:border-blue-800
                        @else bg-purple-50 text-purple-600 border border-purple-200 dark:bg-purple-950/40 dark:border-purple-800
                        @endif">
                        <span>{{ $n['icon'] }}</span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                            <h4 class="text-sm font-extrabold text-dark-900 dark:text-white group-hover:text-brand-600 transition-colors">
                                {{ $n['title'] }}
                            </h4>
                            <span class="text-[11px] font-semibold text-gray-400 dark:text-slate-500">
                                {{ $n['time'] }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-slate-300 mt-1 leading-relaxed">
                            {{ $n['description'] }}
                        </p>
                        <div class="mt-2.5 flex items-center space-x-2">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-md 
                                @if($n['category'] === 'payments') bg-emerald-100/70 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300
                                @elseif($n['category'] === 'leads') bg-orange-100/70 text-orange-800 dark:bg-orange-900 dark:text-orange-300
                                @elseif($n['category'] === 'inspections') bg-blue-100/70 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                @else bg-purple-100/70 text-purple-800 dark:bg-purple-900 dark:text-purple-300
                                @endif">
                                {{ ucfirst($n['category']) }}
                            </span>
                            <span class="text-xs font-bold text-brand-600 dark:text-brand-400 group-hover:translate-x-1 transition-transform inline-flex items-center space-x-1">
                                <span>Take Action</span>
                                <span>&rarr;</span>
                            </span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
