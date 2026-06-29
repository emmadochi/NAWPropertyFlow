@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">📝 My KPI Logs</h1>
            <p class="text-xs text-gray-500 mt-1">Submit your department's manual performance metrics for HOD validation.</p>
        </div>
        @if($department && $department->hod)
            <div class="text-right">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Department Head</span>
                <span class="text-xs font-extrabold text-brand-600 bg-brand-50 border border-brand-100 px-3 py-1 rounded-full">{{ $department->hod->name }}</span>
            </div>
        @endif
    </div>

    @if(!$department)
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-3xl p-6 text-center text-xs font-semibold">
            ⚠️ You are not assigned to a department yet. Please contact your administrator to assign you to a department in Team Settings.
        </div>
    @elseif($manualMetrics->isEmpty())
        <div class="bg-gray-50 border border-gray-150 rounded-3xl p-8 text-center text-xs font-semibold text-gray-500">
            🏢 The <strong>{{ $department->name }}</strong> department does not require any manual KPI reporting at this time. All targets are computed automatically by the CRM system.
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Submission Form (Right Column) --}}
            <div class="lg:col-order-last">
                <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm space-y-4">
                    <div>
                        <h2 class="font-bold text-dark-900 text-sm">Submit Performance Entry</h2>
                        <p class="text-[11px] text-gray-400 mt-1">Report a new manual metric achievement. It will go to your department head for review.</p>
                    </div>

                    <form method="POST" action="{{ route('hr.submissions.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="submission_month" value="{{ $month }}">
                        <input type="hidden" name="submission_year" value="{{ $year }}">

                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Metric *</label>
                            <select name="department_metric_id" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white text-gray-700 font-semibold">
                                @foreach($manualMetrics as $m)
                                    <option value="{{ $m->id }}">{{ $m->label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Achieved Value / Count *</label>
                            <input type="number" step="any" name="value" required min="0.01" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white font-semibold text-gray-800" placeholder="e.g. 5">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Submission Notes / Details</label>
                            <textarea name="notes" rows="3" class="w-full px-3 py-2 border rounded-lg text-xs" placeholder="e.g. Completed YouTube video shoot for Lekki Palms Estate..."></textarea>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md shadow-brand-500/10 transition-all pt-3">
                            Submit Log Entry
                        </button>
                    </form>
                </div>
            </div>

            {{-- History List (Left 2 Columns) --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Date Filter --}}
                <form method="GET" action="{{ route('hr.submissions.index') }}" class="flex items-center gap-2 bg-white p-3 rounded-2xl border border-gray-150 shadow-sm w-fit">
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
                    <button type="submit" class="bg-brand-500 text-white px-4 py-1.5 rounded-xl text-xs font-bold hover:bg-brand-600 shadow-md shadow-brand-500/10 transition-all">Filter</button>
                </form>

                <div class="bg-white rounded-3xl border border-gray-150 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-dark-900 text-sm">Submission History ({{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }})</h3>
                    </div>

                    @if($submissions->isEmpty())
                        <div class="p-8 text-center text-xs text-gray-400 font-semibold">No performance logs recorded for this month. Use the right form to log achievements.</div>
                    @else
                        <div class="divide-y divide-gray-100 text-xs">
                            @foreach($submissions as $sub)
                            <div class="p-5 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-bold text-dark-900 text-sm capitalize">{{ $sub->metric->label }}</span>
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold border capitalize 
                                            @if($sub->status === 'pending') bg-amber-50 text-amber-700 border-amber-200
                                            @elseif($sub->status === 'approved') bg-emerald-50 text-emerald-700 border-emerald-200
                                            @else bg-rose-50 text-rose-700 border-rose-200
                                            @endif">
                                            {{ $sub->status }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 max-w-lg">{{ $sub->notes ?? 'No notes added.' }}</p>
                                    @if($sub->status === 'rejected' && $sub->notes)
                                        <div class="mt-2 bg-rose-50 border border-rose-100 rounded-xl p-2.5 text-[10px] text-rose-800">
                                            <strong>HOD Feedback:</strong> {{ $sub->notes }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right flex flex-col justify-center">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Reported Value</span>
                                    <span class="text-base font-extrabold text-brand-600">
                                        @if($sub->metric->unit === 'currency')
                                            ₦{{ number_format($sub->value, 2) }}
                                        @else
                                            {{ number_format($sub->value, 0) }}
                                        @endif
                                    </span>
                                    <span class="text-[10px] text-gray-400 mt-0.5">Submitted on {{ $sub->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
