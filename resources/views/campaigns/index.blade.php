@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between pb-4 border-b border-gray-150">
        <div>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">Marketing Campaigns</h1>
            <p class="text-xs text-gray-500 mt-1">Broadcast emails, SMS, and WhatsApp alerts to targeted lead segments.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('campaigns.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Create Campaign</span>
            </a>
        </div>
    </div>

    <!-- Stats summary grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-150 rounded-2xl p-5 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Campaigns</span>
            <div class="flex items-baseline space-x-2">
                <span class="text-2xl font-black text-dark-950">{{ $campaigns->total() }}</span>
            </div>
        </div>
        <div class="bg-white border border-gray-150 rounded-2xl p-5 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Active / Sending</span>
            <div class="flex items-baseline space-x-2">
                <span class="text-2xl font-black text-amber-600">{{ \App\Models\Campaign::whereIn('status', ['sending', 'scheduled'])->count() }}</span>
            </div>
        </div>
        <div class="bg-white border border-gray-150 rounded-2xl p-5 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Broadcasts Sent</span>
            <div class="flex items-baseline space-x-2">
                <span class="text-2xl font-black text-brand-500">{{ \App\Models\Campaign::sum('sent_count') }}</span>
            </div>
        </div>
        <div class="bg-white border border-gray-150 rounded-2xl p-5 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Average Open Rate</span>
            <div class="flex items-baseline space-x-2">
                @php
                    $totalSent = \App\Models\Campaign::sum('sent_count');
                    $totalOpened = \App\Models\Campaign::sum('opened_count');
                    $avgOpen = $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 1) : 0;
                @endphp
                <span class="text-2xl font-black text-emerald-600">{{ $avgOpen }}%</span>
            </div>
        </div>
    </div>

    <!-- Campaigns list -->
    <div class="bg-white rounded-3xl border border-gray-150 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-150">
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Campaign</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Audience Segment</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Engagement</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse($campaigns as $campaign)
                    <tr class="hover:bg-gray-55/40 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-dark-900">{{ $campaign->name }}</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">Created {{ $campaign->created_at->diffForHumans() }} by {{ $campaign->creator?->name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center space-x-1 font-semibold">
                                @if($campaign->type === 'email')
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <span>Email</span>
                                @elseif($campaign->type === 'sms')
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                <span>SMS</span>
                                @else
                                <svg class="w-4 h-4 text-teal-550" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.517 2.266 2.27 3.507 5.289 3.507 8.491-.005 6.66-5.341 11.997-11.953 11.997-2.005-.001-3.973-.502-5.717-1.454L0 24zm6.59-4.846c1.6.95 3.197 1.451 4.962 1.452 5.4 0 9.794-4.402 9.797-9.814.001-2.621-1.02-5.087-2.877-6.948S14.2 1.021 11.583 1.021C6.183 1.021 1.79 5.424 1.787 10.838c-.001 1.894.498 3.738 1.448 5.34l-1.023 3.733 3.827-.999z"/></svg>
                                <span>WhatsApp</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($campaign->status === 'draft')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 uppercase">Draft</span>
                            @elseif($campaign->status === 'sending')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 uppercase animate-pulse">Sending</span>
                            @elseif($campaign->status === 'sent')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">Sent</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 uppercase">{{ $campaign->status }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-700">
                            {{ ucfirst($campaign->audience_segment) }} ({{ $campaign->audience_count }} leads)
                        </td>
                        <td class="px-6 py-4">
                            @if($campaign->status === 'sent')
                            <div class="space-y-1">
                                <div class="flex justify-between text-[10px] text-gray-500 font-semibold">
                                    <span>Open Rate: {{ $campaign->openRate() }}%</span>
                                    <span>Click Rate: {{ $campaign->clickRate() }}%</span>
                                </div>
                                <div class="w-24 bg-gray-100 h-1.5 rounded-full overflow-hidden flex">
                                    <div class="bg-blue-500 h-full" style="width: {{ $campaign->openRate() }}%"></div>
                                    <div class="bg-brand-500 h-full" style="width: {{ $campaign->clickRate() }}%"></div>
                                </div>
                            </div>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('campaigns.show', $campaign) }}" class="p-1 text-gray-400 hover:text-brand-500 transition-colors" title="View / Dispatch">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                @if($campaign->status === 'draft')
                                <form action="{{ route('campaigns.destroy', $campaign) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this campaign?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-gray-400 hover:text-rose-500 transition-colors" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                            No campaigns created yet. Click "Create Campaign" to get started.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($campaigns->hasPages())
        <div class="px-6 py-4 border-t border-gray-150">
            {{ $campaigns->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
