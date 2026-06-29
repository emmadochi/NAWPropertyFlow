@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between pb-4 border-b border-gray-150">
        <div>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">Drip Automation Sequences</h1>
            <p class="text-xs text-gray-500 mt-1">Configure step-by-step automated follow-up messages triggered by user/lead pipeline events.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('drip-sequences.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Create Drip Sequence</span>
            </a>
        </div>
    </div>

    <!-- Sequences list -->
    <div class="bg-white rounded-3xl border border-gray-150 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-150">
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sequence Name</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Trigger Event</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Steps Count</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Active Enrollees</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse($sequences as $seq)
                    <tr class="hover:bg-gray-55/40 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-dark-900">{{ $seq->name }}</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">{{ $seq->description ?? 'No description provided' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-50 border border-gray-150 text-dark-800">
                                {{ \App\Models\DripSequence::TRIGGERS[$seq->trigger_event] ?? $seq->trigger_event }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-semibold text-dark-950">
                            {{ $seq->steps_count }} steps
                        </td>
                        <td class="px-6 py-4 font-semibold text-brand-500">
                            {{ $seq->enrollments_count }} leads enrolled
                        </td>
                        <td class="px-6 py-4">
                            @if($seq->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">Active</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 uppercase">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('drip-sequences.show', $seq) }}" class="p-1 text-gray-400 hover:text-brand-500 transition-colors" title="Manage Steps &amp; Enrollments">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                </a>
                                <form action="{{ route('drip-sequences.toggle', $seq) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1 text-gray-400 hover:text-amber-500 transition-colors" title="{{ $seq->is_active ? 'Deactivate' : 'Activate' }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                                    </button>
                                </form>
                                <form action="{{ route('drip-sequences.destroy', $seq) }}" method="POST" onsubmit="return confirm('Delete this sequence and all steps?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-gray-400 hover:text-rose-500 transition-colors" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                            No drip sequences created yet. Click "Create Drip Sequence" to configure.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sequences->hasPages())
        <div class="px-6 py-4 border-t border-gray-150">
            {{ $sequences->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
