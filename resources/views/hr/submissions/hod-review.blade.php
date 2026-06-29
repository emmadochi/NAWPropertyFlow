@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ rejectModalOpen: false, rejectActionUrl: '', rejectNotes: '' }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">📥 KPI Submissions Review</h1>
            <p class="text-xs text-gray-500 mt-1">Audit and approve manual performance logs from department staff members.</p>
        </div>
    </div>

    {{-- Month/Year Filter --}}
    <form method="GET" action="{{ route('hr.submissions.review') }}" class="flex items-center gap-2 bg-white p-3 rounded-2xl border border-gray-150 shadow-sm w-fit">
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
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h3 class="font-bold text-dark-900 text-sm">Review Queue</h3>
            <span class="text-[10px] font-bold text-brand-600 bg-brand-50 border border-brand-100 px-2.5 py-1 rounded-full uppercase tracking-wider">
                {{ \Carbon\Carbon::create()->month($month)->format('M') }} {{ $year }}
            </span>
        </div>

        @if($submissions->isEmpty())
            <div class="p-8 text-center text-xs text-gray-400 font-semibold">No submissions logged for review this month.</div>
        @else
            <div class="divide-y divide-gray-100 text-xs">
                @foreach($submissions as $sub)
                <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <span class="font-extrabold text-dark-900 text-sm">{{ $sub->user->name }}</span>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold border capitalize 
                                @if($sub->status === 'pending') bg-amber-50 text-amber-700 border-amber-200
                                @elseif($sub->status === 'approved') bg-emerald-50 text-emerald-700 border-emerald-200
                                @else bg-rose-50 text-rose-700 border-rose-200
                                @endif">
                                {{ $sub->status }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-2 text-[10px] font-bold text-gray-400">
                            <span>{{ $sub->department->name }} Department</span>
                            <span>•</span>
                            <span class="text-brand-600">{{ $sub->metric->label }}</span>
                        </div>
                        <p class="text-[11px] text-gray-500 max-w-lg mt-1 font-medium">{{ $sub->notes ?? 'No notes provided.' }}</p>
                    </div>

                    <div class="flex items-center space-x-6 text-right">
                        <div class="flex flex-col justify-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Reported Value</span>
                            <span class="text-base font-extrabold text-brand-600">
                                @if($sub->metric->unit === 'currency')
                                    ₦{{ number_format($sub->value, 2) }}
                                @else
                                    {{ number_format($sub->value, 0) }}
                                @endif
                            </span>
                            <span class="text-[10px] text-gray-400 mt-0.5">Submitted {{ $sub->created_at->format('M d, Y') }}</span>
                        </div>

                        @if($sub->status === 'pending')
                            <div class="flex items-center gap-1.5 pl-4 border-l border-gray-150">
                                <form method="POST" action="{{ route('hr.submissions.approve', $sub) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold px-3 py-2 rounded-xl transition-all shadow-md shadow-emerald-500/10">
                                        Approve
                                    </button>
                                </form>
                                <button @click="rejectActionUrl = '{{ route('hr.submissions.reject', $sub) }}'; rejectModalOpen = true" class="bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold px-3 py-2 rounded-xl transition-all border border-rose-200">
                                    Reject
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Reject Feedback Modal --}}
    <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-dark-900/40">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-xl border border-gray-150 space-y-4" @click.away="rejectModalOpen = false">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-bold text-dark-900">Reject KPI Submission</h3>
                <button @click="rejectModalOpen = false" class="text-gray-400 hover:text-gray-500">✕</button>
            </div>
            <form method="POST" :action="rejectActionUrl" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Rejection Reason / Feedback *</label>
                    <textarea name="notes" required rows="4" class="w-full px-3 py-2 border rounded-lg text-xs" placeholder="Explain why this submission is being rejected (e.g. incorrect counts, needs verification)..."></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 border rounded-lg text-xs font-bold text-gray-500">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold">Confirm Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
