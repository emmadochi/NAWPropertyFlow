@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">System Activity Logs</h1>
            <p class="text-sm text-gray-500 mt-1">Monitor all actions taken across the CRM by users.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-gray-600">Time</th>
                        <th class="px-6 py-4 font-semibold text-gray-600">User (Causer)</th>
                        <th class="px-6 py-4 font-semibold text-gray-600">Event Type</th>
                        <th class="px-6 py-4 font-semibold text-gray-600">Description</th>
                        <th class="px-6 py-4 font-semibold text-gray-600">Target (Subject)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 text-xs font-medium text-gray-500">
                            {{ $log->created_at->format('M d, Y h:ia') }}<br>
                            <span class="text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->causer)
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 rounded-full bg-brand-500 text-white flex items-center justify-center text-[10px] font-bold">
                                        {{ substr($log->causer->name, 0, 2) }}
                                    </div>
                                    <span class="font-semibold text-dark-900">{{ $log->causer->name }}</span>
                                </div>
                            @else
                                <span class="text-gray-400 italic">System Auto</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $badgeColor = match(true) {
                                    str_contains($log->description, 'created') => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                    str_contains($log->description, 'updated') => 'bg-blue-50 text-blue-600 border-blue-200',
                                    str_contains($log->description, 'deleted') => 'bg-rose-50 text-rose-600 border-rose-200',
                                    str_contains($log->description, 'login') => 'bg-purple-50 text-purple-600 border-purple-200',
                                    str_contains($log->description, 'logout') => 'bg-purple-50 text-purple-600 border-purple-200',
                                    default => 'bg-gray-100 text-gray-600 border-gray-200'
                                };
                            @endphp
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg border {{ $badgeColor }} uppercase tracking-wider">
                                {{ explode(' ', $log->description)[1] ?? 'Action' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-700">
                            {{ ucfirst($log->description) }}
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-xs font-mono bg-gray-50/50">
                            {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="text-sm font-medium">No activities recorded yet.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
