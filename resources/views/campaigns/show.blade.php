@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between pb-4 border-b border-gray-150">
        <div>
            <a href="{{ route('campaigns.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Campaigns</span>
            </a>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">{{ $campaign->name }}</h1>
            <p class="text-xs text-gray-500 mt-1">Campaign Analytics &amp; Dispatch Console</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            @if($campaign->status === 'draft')
            <form action="{{ route('campaigns.send', $campaign) }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    <span>Dispatch Broadcast</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Analytics Dashboard Overview -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-white border border-gray-150 rounded-2xl p-5 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Target Audience</span>
            <span class="text-2xl font-black text-dark-950">{{ $analytics['audience_count'] }}</span>
            <span class="block text-[10px] text-gray-400 mt-1">matched segment</span>
        </div>
        <div class="bg-white border border-gray-150 rounded-2xl p-5 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Sent Status</span>
            <span class="text-2xl font-black text-brand-500">{{ $analytics['sent_count'] }}</span>
            <span class="block text-[10px] text-gray-450 mt-1">Delivery Rate: {{ $analytics['delivery_rate'] }}%</span>
        </div>
        <div class="bg-white border border-gray-150 rounded-2xl p-5 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Opened Count</span>
            <span class="text-2xl font-black text-emerald-600">{{ $analytics['opened_count'] }}</span>
            <span class="block text-[10px] text-gray-450 mt-1">Open Rate: {{ $analytics['open_rate'] }}%</span>
        </div>
        <div class="bg-white border border-gray-150 rounded-2xl p-5 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Clicked Link</span>
            <span class="text-2xl font-black text-blue-600">{{ $analytics['clicked_count'] }}</span>
            <span class="block text-[10px] text-gray-450 mt-1">Click Rate: {{ $analytics['click_rate'] }}%</span>
        </div>
        <div class="bg-white border border-gray-150 rounded-2xl p-5 shadow-sm">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Failed Count</span>
            <span class="text-2xl font-black text-rose-600">{{ $analytics['failed_count'] }}</span>
            <span class="block text-[10px] text-gray-450 mt-1">delivery drops</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Message details & Body -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-dark-900 border-b border-gray-100 pb-2">Campaign Specification</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Channel / Type</span>
                        <span class="font-semibold text-dark-900">{{ ucfirst($campaign->type) }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Current Status</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 uppercase mt-0.5">{{ $campaign->status }}</span>
                    </div>
                    @if($campaign->type === 'email')
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Subject</span>
                        <span class="font-semibold text-dark-900">{{ $campaign->subject }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Sender Address</span>
                        <span class="font-semibold text-dark-900">{{ $campaign->from_name }} &lt;{{ $campaign->from_email }}&gt;</span>
                    </div>
                    @endif
                </div>

                <div class="space-y-2 pt-4 border-t border-gray-100">
                    <span class="block text-[10px] font-bold text-gray-400 uppercase">Message Body Preview</span>
                    <div class="border rounded-2xl p-4 bg-gray-50 max-h-[300px] overflow-y-auto text-xs prose">
                        {!! $campaign->body !!}
                    </div>
                </div>
            </div>

            <!-- Contacts list -->
            <div class="bg-white rounded-3xl border border-gray-150 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-dark-900">Delivery Log &amp; Contacts</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-150">
                                <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase">Lead</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase">Contact Detail</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase">Status</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase">Opened At</th>
                                <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase">Clicked At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @forelse($campaign->contacts()->with('lead')->paginate(10) as $contact)
                            <tr>
                                <td class="px-6 py-3 font-medium text-dark-900">
                                    {{ $contact->lead?->full_name ?? 'Unknown Lead' }}
                                </td>
                                <td class="px-6 py-3 text-gray-600">
                                    {{ $campaign->type === 'email' ? $contact->lead?->email : $contact->lead?->phone_number }}
                                </td>
                                <td class="px-6 py-3">
                                    @if($contact->status === 'sent')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-blue-100 text-blue-800 uppercase">Sent</span>
                                    @elseif($contact->status === 'opened')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800 uppercase">Opened</span>
                                    @elseif($contact->status === 'clicked')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-purple-100 text-purple-800 uppercase">Clicked</span>
                                    @elseif($contact->status === 'failed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-rose-100 text-rose-800 uppercase" title="{{ $contact->failure_reason }}">Failed</span>
                                    @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-gray-100 text-gray-700 uppercase">Pending</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-gray-400">
                                    {{ $contact->opened_at ? $contact->opened_at->format('M d, H:i') : '-' }}
                                </td>
                                <td class="px-6 py-3 text-gray-400">
                                    {{ $contact->clicked_at ? $contact->clicked_at->format('M d, H:i') : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center text-gray-400">
                                    No contacts compiled yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Audience Filters Info -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-dark-900">Audience Targeting Segment</h3>
                <div class="space-y-3">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Target Rule</span>
                        <span class="text-xs font-semibold text-dark-900">{{ ucfirst($campaign->audience_segment) }}</span>
                    </div>
                    @if(!empty($campaign->audience_filters))
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Filters Applied</span>
                        <div class="space-y-1">
                            @foreach($campaign->audience_filters as $key => $val)
                            <div class="flex items-center justify-between text-xs bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">
                                <span class="font-bold text-gray-500 uppercase text-[9px]">{{ $key }}</span>
                                <span class="font-semibold text-dark-900">{{ $val }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
