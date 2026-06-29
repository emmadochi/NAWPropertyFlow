@extends('layouts.app')

@section('content')
<div class="space-y-8" x-data="{ bookInspectionOpen: false }">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Inspections</h1>
            <p class="text-sm text-gray-500 mt-1">Organize and monitor property site visits and client feedback.</p>
        </div>
        <div>
            <button @click="bookInspectionOpen = true" class="inline-flex items-center space-x-2 px-5 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/10 hover:shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>Book Inspection Tour</span>
            </button>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col md:flex-row gap-4">
        <form action="{{ route('inspections.index') }}" method="GET" class="w-full flex flex-col md:flex-row gap-4 items-center">
            
            <div class="w-full md:w-64">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Date Period</label>
                <select name="date_filter" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none text-sm text-gray-700 bg-white">
                    <option value="">All Dates</option>
                    <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="upcoming" {{ request('date_filter') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="past" {{ request('date_filter') === 'past' ? 'selected' : '' }}>Past / Completed</option>
                </select>
            </div>

            <div class="w-full md:w-64">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Tour Status</label>
                <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none text-sm text-gray-700 bg-white">
                    <option value="">All Statuses</option>
                    <option value="Scheduled" {{ request('status') === 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                    <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="Rescheduled" {{ request('status') === 'Rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                </select>
            </div>

            <div class="flex w-full md:w-auto space-x-2 pt-5">
                <button type="submit" class="flex-1 md:flex-initial px-6 py-2.5 bg-gray-900 hover:bg-gray-800 text-white font-bold text-sm rounded-xl transition-all">Filter</button>
                <a href="{{ route('inspections.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-sm rounded-xl text-center transition-all">Reset</a>
            </div>

        </form>
    </div>

    <!-- Inspections Table Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Prospect Client</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Property Interest</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Tour Date & Time</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Assigned Host</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($inspections as $ins)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-dark-900">
                                <a href="{{ route('leads.show', $ins->lead_id) }}" class="hover:underline">{{ $ins->lead->full_name }}</a>
                            </div>
                            <span class="text-xs text-gray-500">{{ $ins->lead->phone_number }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-800">{{ $ins->property->name }}</div>
                            <span class="text-xs text-gray-500">{{ $ins->property->location }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-dark-900">{{ $ins->inspection_date->format('M d, Y') }}</div>
                            <span class="text-xs text-brand-600 font-semibold">{{ $ins->inspection_date->format('h:i A') }}</span>
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-dark-700">
                            {{ $ins->assignedOfficer ? $ins->assignedOfficer->name : 'Unassigned' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-bold rounded-full leading-none
                                {{ $ins->status === 'Scheduled' ? 'bg-blue-50 text-blue-600 border border-blue-100' : '' }}
                                {{ $ins->status === 'Completed' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : '' }}
                                {{ $ins->status === 'Cancelled' ? 'bg-rose-50 text-rose-600 border border-rose-100' : '' }}
                                {{ $ins->status === 'Rescheduled' ? 'bg-purple-50 text-purple-600 border border-purple-100' : '' }}
                            ">
                                {{ $ins->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($ins->status === 'Scheduled')
                            <div class="inline-flex space-x-1.5">
                                <form action="{{ route('inspections.update', $ins->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="Completed">
                                    <button type="submit" class="px-3 py-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-150 text-emerald-600 text-xs font-bold rounded-xl shadow-sm transition-all">
                                        Log Completed
                                    </button>
                                </form>
                                <form action="{{ route('inspections.update', $ins->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="Cancelled">
                                    <button type="submit" class="px-3 py-2 bg-rose-50 hover:bg-rose-100 border border-rose-150 text-rose-600 text-xs font-bold rounded-xl shadow-sm transition-all">
                                        Cancel
                                    </button>
                                </form>
                            </div>
                            @else
                            <span class="text-xs text-gray-400 font-semibold">Archived</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <span class="p-4 bg-gray-50 text-gray-400 rounded-full inline-block mb-3">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </span>
                            <h4 class="text-sm font-bold text-dark-900">No inspections logged</h4>
                            <p class="text-xs text-gray-400 mt-1">Book new inspections using the button above.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inspections->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            {{ $inspections->links() }}
        </div>
        @endif
    </div>

    <!-- Book Inspection Modal -->
    <div x-cloak x-show="bookInspectionOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-6" @click.away="bookInspectionOpen = false">
            
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Book Property Inspection</h3>
                <button @click="bookInspectionOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <form action="{{ route('inspections.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select Lead Prospect *</label>
                    <select name="lead_id" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                        <option value="">Choose client...</option>
                        @foreach($leads as $lead)
                        <option value="{{ $lead->id }}">{{ $lead->full_name }} ({{ $lead->phone_number }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select Property *</label>
                    <select name="property_id" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                        <option value="">Choose layout...</option>
                        @foreach($properties as $property)
                        <option value="{{ $property->id }}">{{ $property->name }} ({{ $property->location }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Inspection Date & Time *</label>
                    <input type="datetime-local" name="inspection_date" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                </div>

                @if(Auth::user()->role !== 'sales_executive')
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Assign Host Sales Officer</label>
                    <select name="assigned_to"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                        <option value="">Use Lead Assigned Officer</option>
                        @foreach($officers as $officer)
                        <option value="{{ $officer->id }}">{{ $officer->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Inspection Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800 resize-none"
                              placeholder="Meeting instructions..."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="bookInspectionOpen = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl">
                        Schedule Tour
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
