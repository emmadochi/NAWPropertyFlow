@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-dark-900">🎯 Sales Targets</h1>
            <p class="text-sm text-gray-500 mt-0.5">Set monthly targets for your sales team</p>
        </div>
        <a href="{{ route('hr.leaderboard') }}" class="text-sm text-gray-500 hover:text-brand-600 flex items-center gap-1">
            ← Back to Leaderboard
        </a>
    </div>

    {{-- Month/Year Filter --}}
    <form method="GET" action="{{ route('hr.targets') }}" class="flex items-center gap-2">
        <select name="month" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
            @endfor
        </select>
        <select name="year" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        <button type="submit" class="bg-brand-500 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-600 transition-colors">View</button>
    </form>

    {{-- Targets Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-dark-900">{{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }} — Team Targets</h2>
        </div>

        @if($staff->isEmpty())
            <div class="p-12 text-center text-gray-500">No sales executives found.</div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($staff as $member)
            @php $target = $targets->get($member->id); @endphp
            <div class="p-5 flex flex-col md:flex-row md:items-center gap-5" x-data="{ editing: false }">
                <div class="flex items-center space-x-3 min-w-0 w-48">
                    <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                        {{ strtoupper(substr($member->name, 0, 2)) }}
                    </div>
                    <div class="truncate">
                        <p class="font-semibold text-dark-900 truncate">{{ $member->name }}</p>
                        <p class="text-xs text-gray-400">{{ $member->branch?->name ?? 'No branch' }}</p>
                    </div>
                </div>

                <div class="flex-1 grid grid-cols-3 gap-4 text-center">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 mb-1">Leads Target</p>
                        <p class="text-xl font-bold text-dark-900">{{ $target?->leads_target ?? '—' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 mb-1">Sales Target</p>
                        <p class="text-xl font-bold text-dark-900">{{ $target?->sales_target ?? '—' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 mb-1">Revenue Target</p>
                        <p class="text-sm font-bold text-brand-600">{{ $target ? '₦'.number_format($target->revenue_target) : '—' }}</p>
                    </div>
                </div>

                <button @click="editing = !editing" class="text-brand-600 hover:text-brand-800 text-sm font-semibold flex-shrink-0 border border-brand-200 px-3 py-1.5 rounded-xl hover:bg-brand-50 transition-colors">
                    <span x-text="editing ? 'Cancel' : '{{ $target ? 'Edit' : 'Set' }} Target'"></span>
                </button>
            </div>

            {{-- Inline Edit Form --}}
            <div class="px-5 pb-5" x-data="{ editing: false }" x-show="false" :class="{ 'hidden': !editing }" x-cloak>
            </div>
            {{-- Separate x-data for edit form toggling --}}
            @endforeach
        </div>
        @endif
    </div>

    {{-- Set/Edit Target Form --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h2 class="font-semibold text-dark-900 mb-5">Set / Update Target</h2>
        <form method="POST" action="{{ route('hr.targets.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            @csrf
            <input type="hidden" name="target_month" value="{{ $month }}">
            <input type="hidden" name="target_year" value="{{ $year }}">

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Sales Executive</label>
                <select name="user_id" required class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select agent...</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Leads Target</label>
                <input type="number" name="leads_target" min="0" value="20" required class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Sales Target</label>
                <input type="number" name="sales_target" min="0" value="5" required class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Revenue Target (₦)</label>
                <input type="number" name="revenue_target" min="0" value="5000000" required class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-brand-500 text-white py-2.5 px-4 rounded-xl text-sm font-semibold hover:bg-brand-600 transition-colors">Save Target</button>
            </div>
        </form>
    </div>
</div>
@endsection
