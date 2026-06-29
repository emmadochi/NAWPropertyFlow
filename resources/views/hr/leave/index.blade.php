@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-dark-900">📅 Leave Requests</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $isAdmin ? 'Manage all team leave requests' : 'Your leave request history' }}</p>
        </div>
        <a href="{{ route('hr.leave.create') }}" class="inline-flex items-center px-4 py-2.5 bg-brand-500 text-white rounded-xl text-sm font-semibold hover:bg-brand-600 transition-colors gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            New Request
        </a>
    </div>

    {{-- Status Filter --}}
    <div class="flex gap-2 flex-wrap">
        @foreach(['all' => 'All', 'pending' => '⏳ Pending', 'approved' => '✅ Approved', 'rejected' => '❌ Rejected'] as $val => $label)
        <a href="{{ route('hr.leave.index', array_merge(request()->query(), ['status' => $val === 'all' ? null : $val])) }}"
           class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition-colors {{ (request('status', 'all') === $val || ($val === 'all' && !request('status'))) ? 'bg-brand-500 text-white border-brand-500' : 'bg-white text-gray-600 border-gray-200 hover:border-brand-300' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Leaves Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        @if($leaves->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <p class="text-gray-500">No leave requests found.</p>
                <a href="{{ route('hr.leave.create') }}" class="mt-3 inline-block text-brand-600 text-sm font-medium hover:underline">Submit your first request →</a>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        @if($isAdmin)
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Staff Member</th>
                        @endif
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Period</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Days</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Submitted</th>
                        @if($isAdmin)
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($leaves as $leave)
                    <tr class="hover:bg-gray-50 transition-colors" x-data="{ reviewing: false }">
                        @if($isAdmin)
                        <td class="px-5 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($leave->user->name, 0, 2)) }}
                                </div>
                                <span class="font-medium text-dark-900">{{ $leave->user->name }}</span>
                            </div>
                        </td>
                        @endif
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold">
                                {{ \App\Models\LeaveRequest::TYPES[$leave->leave_type] ?? $leave->leave_type }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-700">
                            {{ $leave->start_date->format('d M Y') }} — {{ $leave->end_date->format('d M Y') }}
                        </td>
                        <td class="px-5 py-4 text-center font-semibold text-dark-900">{{ $leave->days_requested }}</td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $badgeClass = match($leave->status) {
                                    'approved' => 'bg-emerald-100 text-emerald-700',
                                    'rejected' => 'bg-rose-100 text-rose-700',
                                    default    => 'bg-amber-100 text-amber-700',
                                };
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                {{ ucfirst($leave->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-500 text-xs">{{ $leave->created_at->format('d M Y') }}</td>
                        @if($isAdmin)
                        <td class="px-5 py-4 text-center">
                            @if($leave->status === 'pending')
                            <button @click="reviewing = !reviewing" class="text-brand-600 hover:text-brand-800 text-xs font-semibold border border-brand-200 px-3 py-1 rounded-lg hover:bg-brand-50 transition-colors">
                                Review
                            </button>
                            @else
                            <span class="text-xs text-gray-400">{{ $leave->reviewer?->name ?? '—' }}</span>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @if($isAdmin && $leave->status === 'pending')
                    <tr x-data="{ reviewing: false }" x-show="reviewing" x-cloak class="bg-amber-50">
                        <td colspan="7" class="px-5 py-4">
                            <form method="POST" action="{{ route('hr.leave.review', $leave) }}" class="flex items-center gap-3">
                                @csrf @method('PATCH')
                                <input type="text" name="review_notes" placeholder="Optional review notes..." class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                <button type="submit" name="status" value="approved" class="bg-emerald-500 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-emerald-600 transition-colors">✓ Approve</button>
                                <button type="submit" name="status" value="rejected" class="bg-rose-500 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-rose-600 transition-colors">✕ Reject</button>
                            </form>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-50">
            {{ $leaves->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
