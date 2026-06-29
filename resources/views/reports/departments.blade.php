@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">📊 Departmental Performance Reports</h1>
            <p class="text-xs text-gray-500 mt-1">Cross-department progress audit comparing actual metrics against established monthly goals.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('reports.index') }}" class="text-xs font-bold text-gray-500 hover:text-brand-600">
                General Reports
            </a>
            <span class="text-gray-300">|</span>
            <a href="{{ route('hr.department-targets.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 border border-brand-100 px-3 py-1.5 rounded-xl">
                Configure Targets
            </a>
        </div>
    </div>

    {{-- Month/Year Filter --}}
    <form method="GET" action="{{ route('reports.departments.index') }}" class="flex items-center gap-2 bg-white p-3 rounded-2xl border border-gray-150 shadow-sm w-fit">
        <select name="month" class="bg-gray-50 border border-gray-250 rounded-xl px-3 py-1.5 text-xs focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none font-bold text-gray-600">
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
            @endfor
        </select>
        <select name="year" class="bg-gray-50 border border-gray-250 rounded-xl px-3 py-1.5 text-xs focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none font-bold text-gray-600">
            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        <button type="submit" class="bg-brand-500 text-white px-4 py-1.5 rounded-xl text-xs font-bold hover:bg-brand-600 shadow-md shadow-brand-500/10 transition-all font-bold">Filter</button>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($reports as $dept => $data)
        <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm space-y-6">
            <div class="flex items-center space-x-3 pb-3 border-b border-gray-100">
                <span class="text-2xl">{{ $data['icon'] }}</span>
                <div>
                    <h3 class="font-black text-dark-900 text-base">{{ $dept }} Department</h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Performance Audit</p>
                </div>
            </div>

            <div class="space-y-6">
                @foreach($data['metrics'] as $key => $m)
                @php
                    $percent = null;
                    if ($m['target'] !== null && $m['target'] > 0) {
                        $percent = min(100, round(($m['actual'] / $m['target']) * 100));
                    }
                @endphp

                <div class="space-y-2">
                    <div class="flex justify-between items-end text-xs">
                        <span class="font-bold text-gray-600">{{ $m['label'] }}</span>
                        <div class="text-right">
                            <span class="font-extrabold text-dark-900">
                                @if($m['unit'] === 'currency')
                                    ₦{{ number_format($m['actual'], 2) }}
                                @else
                                    {{ number_format($m['actual'], 0) }}
                                @endif
                            </span>
                            @if($m['target'] !== null)
                                <span class="text-gray-400 font-medium"> / 
                                    @if($m['unit'] === 'currency')
                                        ₦{{ number_format($m['target'], 0) }}
                                    @else
                                        {{ number_format($m['target'], 0) }}
                                    @endif
                                </span>
                            @else
                                <span class="text-gray-400 font-medium"> (No target)</span>
                            @endif
                        </div>
                    </div>

                    @if($percent !== null)
                    <div class="relative w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="absolute top-0 left-0 h-full rounded-full transition-all duration-500 @if($percent >= 100) bg-emerald-500 @elseif($percent >= 50) bg-brand-500 @else bg-amber-500 @endif" style="width: {{ $percent }}%;"></div>
                    </div>
                    <div class="flex justify-between items-center text-[10px] font-bold text-gray-400">
                        <span>Completion Rate</span>
                        <span class="@if($percent >= 100) text-emerald-600 @elseif($percent >= 50) text-brand-600 @else text-amber-600 @endif">{{ $percent }}%</span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
