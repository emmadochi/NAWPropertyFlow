@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-dark-900">🏆 Sales Leaderboard</h1>
            <p class="text-sm text-gray-500 mt-0.5">Team performance for {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</p>
        </div>

        {{-- Month/Year Filter --}}
        <form method="GET" action="{{ route('hr.leaderboard') }}" class="flex items-center gap-2 flex-wrap">
            <select name="branch_id" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                <option value="">All Branches</option>
                @foreach($branches as $br)
                    <option value="{{ $br->id }}" {{ $branchId == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                @endforeach
            </select>
            <select name="month" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                @endfor
            </select>
            <select name="year" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="bg-brand-500 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-600 transition-colors">Filter</button>
        </form>
    </div>

    @if($leaderboard->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <p class="text-gray-500">No sales executives found for this period.</p>
        </div>
    @else
    {{-- Podium Top 3 --}}
    @if($leaderboard->count() >= 1)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($leaderboard->take(3) as $i => $exec)
        @php
            $medals  = ['🥇', '🥈', '🥉'];
            $colors  = ['bg-amber-50 border-amber-200', 'bg-slate-50 border-slate-200', 'bg-orange-50 border-orange-200'];
            $accents = ['text-amber-600', 'text-slate-500', 'text-orange-500'];
        @endphp
        <div class="bg-white rounded-2xl border {{ $colors[$i] ?? 'border-gray-100' }} p-6 text-center relative overflow-hidden {{ $i === 0 ? 'md:col-span-1 md:order-2' : ($i === 1 ? 'md:order-1' : 'md:order-3') }}">
            <div class="absolute top-3 left-3 text-2xl">{{ $medals[$i] }}</div>
            <div class="w-16 h-16 rounded-full bg-brand-500 text-white flex items-center justify-center text-xl font-bold mx-auto mb-3">
                {{ strtoupper(substr($exec->name, 0, 2)) }}
            </div>
            <h3 class="font-bold text-dark-900 text-lg">{{ $exec->name }}</h3>
            <p class="text-xs text-gray-500 mb-4">{{ $exec->branch?->name ?? 'No Branch' }}</p>

            <div class="grid grid-cols-3 gap-3 text-center">
                <div>
                    <p class="text-2xl font-extrabold {{ $accents[$i] ?? 'text-brand-600' }}">{{ $exec->sales_count }}</p>
                    <p class="text-xs text-gray-500">Sales</p>
                </div>
                <div>
                    <p class="text-2xl font-extrabold {{ $accents[$i] ?? 'text-brand-600' }}">{{ $exec->leads_count }}</p>
                    <p class="text-xs text-gray-500">Leads</p>
                </div>
                <div>
                    <p class="text-sm font-extrabold {{ $accents[$i] ?? 'text-brand-600' }}">₦{{ number_format($exec->revenue_total / 1000000, 1) }}M</p>
                    <p class="text-xs text-gray-500">Revenue</p>
                </div>
            </div>

            @if($exec->target)
            <div class="mt-4 space-y-2">
                @foreach(['sales_pct' => 'Sales Target', 'revenue_pct' => 'Revenue Target'] as $pct => $label)
                @if($exec->$pct !== null)
                <div>
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>{{ $label }}</span>
                        <span class="font-semibold {{ $exec->$pct >= 100 ? 'text-emerald-600' : ($exec->$pct >= 50 ? 'text-amber-600' : 'text-rose-600') }}">{{ $exec->$pct }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full {{ $exec->$pct >= 100 ? 'bg-emerald-500' : ($exec->$pct >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ min($exec->$pct, 100) }}%"></div>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Full Leaderboard Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="font-semibold text-dark-900">Full Rankings — {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Agent</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Leads</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Sales</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Conv. Rate</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($leaderboard as $rank => $exec)
                    @php
                        $conv = $exec->leads_count > 0 ? round(($exec->sales_count / $exec->leads_count) * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-4">
                            <span class="text-lg">
                                @if($rank === 0) 🥇
                                @elseif($rank === 1) 🥈
                                @elseif($rank === 2) 🥉
                                @else <span class="font-bold text-gray-500">#{{ $rank + 1 }}</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    {{ strtoupper(substr($exec->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-dark-900">{{ $exec->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $exec->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-600">{{ $exec->branch?->name ?? '—' }}</td>
                        <td class="px-5 py-4 text-right font-semibold text-dark-900">{{ $exec->leads_count }}</td>
                        <td class="px-5 py-4 text-right font-semibold text-dark-900">{{ $exec->sales_count }}</td>
                        <td class="px-5 py-4 text-right font-bold text-brand-600">₦{{ number_format($exec->revenue_total) }}</td>
                        <td class="px-5 py-4 text-right">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $conv >= 50 ? 'bg-emerald-100 text-emerald-700' : ($conv >= 25 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ $conv }}%
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <a href="{{ route('hr.staff.show', $exec) }}" class="text-brand-600 hover:text-brand-800 font-medium text-xs">View Profile</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="flex gap-3">
        <a href="{{ route('hr.targets', ['month' => $month, 'year' => $year]) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors gap-2">
            🎯 Set Sales Targets
        </a>
        <a href="{{ route('hr.department-targets.index', ['month' => $month, 'year' => $year]) }}" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-xl text-sm font-medium hover:bg-brand-600 transition-colors gap-2">
            🎯 Departmental Goals
        </a>
    </div>
    @endif

</div>
@endsection
