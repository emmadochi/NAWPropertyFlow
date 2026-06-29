@extends('layouts.app')

@section('content')
<div class="space-y-8" x-data="{ addLeadOpen: false, importLeadsOpen: false, activeView: localStorage.getItem('leadsView') || 'table' }" x-init="$watch('activeView', v => localStorage.setItem('leadsView', v))">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Leads Pipeline</h1>
            <p class="text-sm text-gray-500 mt-1">Capture, distribute, and nurture sales prospects.</p>
        </div>
        <div class="flex items-center space-x-2">
            <!-- View Toggle Buttons -->
            <div class="flex items-center bg-gray-100 rounded-xl p-1">
                <button @click="activeView = 'table'"
                    :class="activeView === 'table' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 text-xs font-bold rounded-lg transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg>
                    <span>Table</span>
                </button>
                <button @click="activeView = 'board'"
                    :class="activeView === 'board' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 text-xs font-bold rounded-lg transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    <span>Board</span>
                </button>
            </div>
            <button @click="importLeadsOpen = true" class="inline-flex items-center space-x-2 px-5 py-3 bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 font-bold text-sm rounded-xl shadow-sm transition-all">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Import Leads (CSV)</span>
            </button>
            <button @click="addLeadOpen = true" class="inline-flex items-center space-x-2 px-5 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/10 hover:shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Add Lead Prospect</span>
            </button>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col md:flex-row gap-4">
        <form action="{{ route('leads.index') }}" method="GET" class="w-full flex flex-col md:flex-row gap-4 items-center">
            
            <!-- Search -->
            <div class="w-full md:flex-1 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none text-sm text-gray-800"
                       placeholder="Search by name, phone, email...">
            </div>

            <!-- Status Filter -->
            <div class="w-full md:w-48">
                <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none text-sm text-gray-700 bg-white">
                    <option value="">All Statuses</option>
                    <option value="New" {{ request('status') === 'New' ? 'selected' : '' }}>New</option>
                    <option value="Contacted" {{ request('status') === 'Contacted' ? 'selected' : '' }}>Contacted</option>
                    <option value="Follow Up" {{ request('status') === 'Follow Up' ? 'selected' : '' }}>Follow Up</option>
                    <option value="Inspection Scheduled" {{ request('status') === 'Inspection Scheduled' ? 'selected' : '' }}>Inspection Scheduled</option>
                    <option value="Negotiation" {{ request('status') === 'Negotiation' ? 'selected' : '' }}>Negotiation</option>
                    <option value="Payment Processing" {{ request('status') === 'Payment Processing' ? 'selected' : '' }}>Payment Processing</option>
                    <option value="Closed Won" {{ request('status') === 'Closed Won' ? 'selected' : '' }}>Closed Won</option>
                    <option value="Closed Lost" {{ request('status') === 'Closed Lost' ? 'selected' : '' }}>Closed Lost</option>
                </select>
            </div>

            <!-- Source Filter -->
            <div class="w-full md:w-48">
                <select name="source" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none text-sm text-gray-700 bg-white">
                    <option value="">All Sources</option>
                    <option value="Website" {{ request('source') === 'Website' ? 'selected' : '' }}>Website</option>
                    <option value="Referral" {{ request('source') === 'Referral' ? 'selected' : '' }}>Referral</option>
                    <option value="Social Media" {{ request('source') === 'Social Media' ? 'selected' : '' }}>Social Media</option>
                    <option value="WhatsApp" {{ request('source') === 'WhatsApp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="Cold Call" {{ request('source') === 'Cold Call' ? 'selected' : '' }}>Cold Call</option>
                </select>
            </div>

            <!-- Assignee Filter (Admins/Managers only) -->
            @if(Auth::user()->role !== 'sales_executive')
            <div class="w-full md:w-48">
                <select name="assigned_to" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none text-sm text-gray-700 bg-white">
                    <option value="">All Officers</option>
                    @foreach($officers as $officer)
                    <option value="{{ $officer->id }}" {{ request('assigned_to') == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="flex w-full md:w-auto space-x-2">
                <button type="submit" class="flex-1 md:flex-initial px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white font-bold text-sm rounded-xl transition-all">Filter</button>
                <a href="{{ route('leads.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-sm rounded-xl text-center transition-all">Reset</a>
            </div>

        </form>
    </div>

    <!-- ================================================================
         KANBAN BOARD VIEW
         ================================================================ -->
    <div x-show="activeView === 'board'" x-cloak>
        <div id="kanban-board" class="flex gap-4 overflow-x-auto pb-6">

            @php
            $statuses = [
                'New'                  => ['color' => '#FEA500', 'bg' => '#FFF7E6', 'icon' => '🆕'],
                'Contacted'            => ['color' => '#3B82F6', 'bg' => '#EFF6FF', 'icon' => '📞'],
                'Follow Up'            => ['color' => '#F59E0B', 'bg' => '#FFFBEB', 'icon' => '🔄'],
                'Inspection Scheduled' => ['color' => '#8B5CF6', 'bg' => '#F5F3FF', 'icon' => '🏡'],
                'Negotiation'          => ['color' => '#6366F1', 'bg' => '#EEF2FF', 'icon' => '🤝'],
                'Payment Processing'   => ['color' => '#F59E0B', 'bg' => '#FFFBEB', 'icon' => '💳'],
                'Closed Won'           => ['color' => '#10B981', 'bg' => '#ECFDF5', 'icon' => '✅'],
                'Closed Lost'          => ['color' => '#EF4444', 'bg' => '#FEF2F2', 'icon' => '❌'],
            ];

            $leadsAll = \App\Models\Lead::with(['assignedOfficer','propertyInterest'])
                ->when(Auth::user()->role === 'sales_executive', fn($q) => $q->where('assigned_to', Auth::id()))
                ->when(request('search'), fn($q, $s) => $q->search($s))
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('status');
            @endphp

            @foreach($statuses as $status => $style)
            @php $columnLeads = $leadsAll->get($status, collect()); @endphp
            <div class="kanban-column flex-shrink-0 w-72 flex flex-col rounded-2xl overflow-hidden"
                 style="background: {{ $style['bg'] }}; border: 1.5px solid {{ $style['color'] }}22;">

                <!-- Column Header -->
                <div class="px-4 py-3 flex items-center justify-between"
                     style="border-bottom: 2px solid {{ $style['color'] }}33;">
                    <div class="flex items-center space-x-2">
                        <span class="text-base">{{ $style['icon'] }}</span>
                        <h3 class="text-sm font-extrabold text-gray-800">{{ $status }}</h3>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full text-white"
                          style="background: {{ $style['color'] }};">
                        {{ $columnLeads->count() }}
                    </span>
                </div>

                <!-- Drop Zone -->
                <div class="kanban-drop-zone flex-1 p-3 space-y-3 min-h-[120px] transition-colors duration-200"
                     data-status="{{ $status }}">

                    @foreach($columnLeads as $lead)
                    <div class="kanban-card bg-white rounded-xl p-4 shadow-sm border border-gray-100
                                hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-grab active:cursor-grabbing"
                         draggable="true"
                         data-lead-id="{{ $lead->id }}"
                         data-lead-name="{{ $lead->full_name }}">

                        <div class="flex items-start justify-between mb-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-extrabold flex-shrink-0"
                                 style="background: {{ $style['color'] }};">
                                {{ strtoupper(substr($lead->full_name, 0, 1)) }}
                            </div>
                            <a href="{{ route('leads.show', $lead->id) }}"
                               class="text-gray-300 hover:text-brand-500 transition-colors"
                               title="View Profile" onclick="event.stopPropagation()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>

                        <h4 class="font-bold text-sm text-gray-900 leading-snug">{{ $lead->full_name }}</h4>

                        @if($lead->propertyInterest)
                        <p class="text-xs text-gray-500 mt-0.5 truncate">🏠 {{ $lead->propertyInterest->name }}</p>
                        @endif

                        <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-50">
                            <span class="text-xs font-semibold text-gray-600">{{ $lead->budget_range ?? 'N/A' }}</span>
                            @if($lead->assignedOfficer)
                            <span class="text-xs text-gray-400 truncate max-w-[80px]" title="{{ $lead->assignedOfficer->name }}">{{ $lead->assignedOfficer->name }}</span>
                            @else
                            <span class="text-xs text-gray-300 italic">Unassigned</span>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    <!-- Empty column placeholder -->
                    @if($columnLeads->isEmpty())
                    <div class="kanban-empty-placeholder flex items-center justify-center h-20 border-2 border-dashed rounded-xl"
                         style="border-color: {{ $style['color'] }}44;">
                        <p class="text-xs font-semibold" style="color: {{ $style['color'] }}99;">Drop here</p>
                    </div>
                    @endif

                </div>
            </div>
            @endforeach

        </div>
    </div>
    <!-- END KANBAN BOARD -->

    <!-- Data Table Card -->
    <div x-show="activeView === 'table'" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Prospect Details</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Property Interest</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Budget / Location</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Assigned Officer</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($leads as $lead)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-dark-900">{{ $lead->full_name }}</div>
                            <div class="text-xs text-gray-500 space-y-0.5 mt-0.5">
                                <div>Source: {{ $lead->lead_source }}</div>
                                @if($lead->whatsapp_number)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->whatsapp_number) }}" target="_blank" class="text-brand-600 hover:underline flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5 text-brand-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.458L0 24zm6.59-4.846c1.6.95 3.197 1.45 4.817 1.453 5.461 0 9.898-4.432 9.9-9.893.002-2.646-1.01-5.132-2.85-6.974S14.653 1.082 12.01 1.08c-5.468 0-9.91 4.436-9.912 9.898-.001 1.83.486 3.62 1.411 5.2l-.994 3.628 3.722-.972zm11.233-7.502c-.3-.15-1.77-.875-2.045-.975s-.475-.15-.675.15-.775.975-.95 1.175-.35.225-.65.075c-.3-.15-1.265-.467-2.41-1.485-.89-.79-1.49-1.77-1.665-2.07s-.018-.462.13-.61c.135-.133.3-.35.45-.525.15-.175.2-.3.3-.5s.05-.375-.025-.525-.675-1.625-.925-2.225c-.244-.589-.491-.51-.675-.52-.175-.01-.375-.01-.575-.01s-.525.075-.8.375c-.275.3-1.05 1.025-1.05 2.5s1.075 2.9 1.225 3.1c.15.2 2.11 3.225 5.11 4.525.714.31 1.27.495 1.7.635.717.227 1.37.195 1.885.118.574-.085 1.77-.725 2.02-1.39s.25-1.235.175-1.39-.275-.25-.575-.4z"/>
                                    </svg>
                                    <span>WhatsApp Link</span>
                                </a>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($lead->propertyInterest)
                            <div class="font-semibold text-gray-800">{{ $lead->propertyInterest->name }}</div>
                            <div class="text-xs text-gray-500">Type: {{ $lead->propertyInterest->property_type }}</div>
                            @else
                            <span class="text-xs text-gray-400">General Inquiry</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-dark-900">{{ $lead->budget_range ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500 flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>{{ $lead->preferred_location ?? 'Any' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if(Auth::user()->role !== 'sales_executive')
                            <form action="{{ route('leads.assign', $lead->id) }}" method="POST" class="inline-block">
                                @csrf
                                <select name="assigned_to" onchange="this.form.submit()" 
                                        class="text-xs bg-gray-50 border border-gray-200 rounded-lg p-1.5 focus:outline-none focus:ring-1 focus:ring-brand-500 font-medium">
                                    <option value="">Unassigned</option>
                                    @foreach($officers as $officer)
                                    <option value="{{ $officer->id }}" {{ $lead->assigned_to == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                            @else
                            <span class="text-xs font-semibold text-dark-700 bg-gray-100 px-2.5 py-1 rounded-lg">
                                {{ $lead->assignedOfficer ? $lead->assignedOfficer->name : 'Unassigned' }}
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-bold rounded-full leading-none
                                {{ $lead->status === 'New' ? 'bg-orange-50 text-orange-600 border border-orange-100' : '' }}
                                {{ $lead->status === 'Contacted' ? 'bg-blue-50 text-blue-600 border border-blue-100' : '' }}
                                {{ $lead->status === 'Follow Up' ? 'bg-amber-50 text-amber-600 border border-amber-100' : '' }}
                                {{ $lead->status === 'Inspection Scheduled' ? 'bg-purple-50 text-purple-600 border border-purple-100' : '' }}
                                {{ $lead->status === 'Negotiation' ? 'bg-indigo-50 text-indigo-600 border border-indigo-100' : '' }}
                                {{ $lead->status === 'Payment Processing' ? 'bg-yellow-50 text-yellow-600 border border-yellow-100' : '' }}
                                {{ $lead->status === 'Closed Won' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : '' }}
                                {{ $lead->status === 'Closed Lost' ? 'bg-rose-50 text-rose-600 border border-rose-100' : '' }}
                            ">
                                {{ $lead->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('leads.show', $lead->id) }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100/70 border border-brand-100 px-3 py-2 rounded-xl transition-all">
                                <span>Profile Details</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <span class="p-4 bg-gray-50 text-gray-400 rounded-full inline-block mb-3">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </span>
                            <h4 class="text-sm font-bold text-dark-900">No leads found</h4>
                            <p class="text-xs text-gray-400 mt-1">Try resetting the filter criteria or add a new lead prospect.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            {{ $leads->links() }}
        </div>
        @endif
    </div>

    <!-- Capture Lead Modal -->
    <div x-cloak x-show="addLeadOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl p-6 md:p-8 space-y-6 my-8" @click.away="addLeadOpen = false">
            
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-xl font-bold text-dark-900">Capture New Lead Prospect</h3>
                <button @click="addLeadOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('leads.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Full Name *</label>
                        <input type="text" name="full_name" required
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800"
                               placeholder="Chinedu Okafor">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Email Address</label>
                        <input type="email" name="email"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800"
                               placeholder="client@example.com">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Phone Number *</label>
                        <input type="text" name="phone_number" required
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800"
                               placeholder="+23480...">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800"
                               placeholder="+23480...">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Budget Range *</label>
                        <select name="budget_range" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                            <option value="₦10M - ₦30M">₦10M - ₦30M</option>
                            <option value="₦30M - ₦60M">₦30M - ₦60M</option>
                            <option value="₦60M - ₦100M">₦60M - ₦100M</option>
                            <option value="₦100M+">₦100M+</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Preferred Location</label>
                        <input type="text" name="preferred_location"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800"
                               placeholder="Lekki, Epe, Ikeja...">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Property Interest</label>
                        <select name="property_interest_id"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                            <option value="">General inquiry</option>
                            @foreach($properties as $property)
                            <option value="{{ $property->id }}">{{ $property->name }} - ₦{{ number_format($property->price, 0) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Lead Source *</label>
                        <select name="lead_source" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                            <option value="Website">Website</option>
                            <option value="Referral">Referral</option>
                            <option value="Social Media">Social Media</option>
                            <option value="WhatsApp">WhatsApp</option>
                            <option value="Cold Call">Cold Call</option>
                        </select>
                    </div>

                    @if(Auth::user()->role !== 'sales_executive')
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Assign Sales Officer</label>
                        <select name="assigned_to"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                            <option value="">Unassigned</option>
                            @foreach($officers as $officer)
                            <option value="{{ $officer->id }}">{{ $officer->name }} ({{ str_replace('_', ' ', $officer->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <input type="hidden" name="assigned_to" value="{{ Auth::id() }}">
                    @endif

                    @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin())
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Assign Branch Office</label>
                        <select name="branch_id"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white cursor-pointer">
                            <option value="">Unassigned (Corporate Head Office)</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->city }})</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Initial Status *</label>
                        <select name="status" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                            <option value="New">New</option>
                            <option value="Contacted">Contacted</option>
                            <option value="Follow Up">Follow Up</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Introductory / Activity Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800 resize-none"
                              placeholder="Add description of their property preferences or discussion outcomes."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="addLeadOpen = false" class="px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/10">
                        Create Lead
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Leads Modal -->
    <div x-cloak x-show="importLeadsOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-6" @click.away="importLeadsOpen = false">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Import Leads from CSV</h3>
                <button @click="importLeadsOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('leads.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">CSV File *</label>
                    <input type="file" name="csv_file" required accept=".csv,.txt" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                    <p class="text-[10px] text-gray-400 mt-1">Upload a CSV file containing at least <strong>full_name</strong> and <strong>phone_number</strong> columns.</p>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 text-[11px] text-gray-500 space-y-2">
                    <span class="font-bold text-gray-700 block">Available Columns:</span>
                    <p><code>full_name</code>, <code>phone_number</code>, <code>whatsapp_number</code>, <code>email</code>, <code>budget_range</code>, <code>preferred_location</code>, <code>lead_source</code>, <code>notes</code>, <code>status</code></p>
                    <a href="{{ route('leads.import-template') }}" class="inline-flex items-center space-x-1 text-brand-600 hover:text-brand-700 font-bold mt-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span>Download Sample Template</span>
                    </a>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="importLeadsOpen = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl">
                        Import Leads
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function initKanban() {
        const board = document.getElementById('kanban-board');
        if (!board) return;

        let draggedCard = null;
        let sourceZone = null;
        let ghostEl = null;

        // ─── DRAG START ───────────────────────────────────────────────────────
        board.addEventListener('dragstart', (e) => {
            const card = e.target.closest('.kanban-card');
            if (!card) return;
            draggedCard = card;
            sourceZone = card.closest('.kanban-drop-zone');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', card.dataset.leadId);

            // Visual ghost style
            setTimeout(() => card.classList.add('opacity-30', 'scale-95'), 0);
        });

        board.addEventListener('dragend', (e) => {
            const card = e.target.closest('.kanban-card');
            if (card) card.classList.remove('opacity-30', 'scale-95');
            clearDropHighlights();
            draggedCard = null;
            sourceZone = null;
        });

        // ─── DRAG OVER ────────────────────────────────────────────────────────
        board.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const zone = e.target.closest('.kanban-drop-zone');
            if (zone && zone !== sourceZone) {
                clearDropHighlights();
                zone.classList.add('bg-black/5', 'ring-2', 'ring-inset', 'ring-current');
            }
        });

        board.addEventListener('dragleave', (e) => {
            const zone = e.target.closest('.kanban-drop-zone');
            if (zone) zone.classList.remove('bg-black/5', 'ring-2', 'ring-inset', 'ring-current');
        });

        // ─── DROP ─────────────────────────────────────────────────────────────
        board.addEventListener('drop', async (e) => {
            e.preventDefault();
            const zone = e.target.closest('.kanban-drop-zone');
            if (!zone || !draggedCard) return;

            const newStatus = zone.dataset.status;
            const oldZone   = sourceZone;

            if (newStatus === oldZone?.dataset.status) return;

            const leadId = draggedCard.dataset.leadId;
            const cardElement = draggedCard; // Store reference to prevent race condition with dragend setting draggedCard to null

            // Optimistically move card
            const placeholder = zone.querySelector('.kanban-empty-placeholder');
            if (placeholder) placeholder.remove();
            zone.appendChild(cardElement);
            cardElement.classList.remove('opacity-30', 'scale-95');
            clearDropHighlights();

            // Update column counters
            updateCounter(oldZone, -1);
            updateCounter(zone, +1);

            // Fetch fresh CSRF from page header each time to prevent 419 errors
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // AJAX persist to server
            try {
                const res = await fetch(`/leads/${leadId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status: newStatus }),
                });

                if (!res.ok) throw new Error('Server error');

                showToast(`✅ Lead moved to <strong>${newStatus}</strong>`, 'success');
            } catch (err) {
                // Rollback: move card back
                if (oldZone && cardElement) {
                    oldZone.appendChild(cardElement);
                    updateCounter(oldZone, +1);
                    updateCounter(zone, -1);
                }
                showToast('❌ Failed to update status. Please try again.', 'error');
            }
        });

        function updateCounter(zone, delta) {
            if (!zone) return;
            const col = zone.closest('.kanban-column');
            const badge = col?.querySelector('.kanban-column > div > span:last-child');
            if (badge) {
                const current = parseInt(badge.textContent, 10) || 0;
                badge.textContent = Math.max(0, current + delta);
            }
        }

        function clearDropHighlights() {
            document.querySelectorAll('.kanban-drop-zone').forEach(z => {
                z.classList.remove('bg-black/5', 'ring-2', 'ring-inset', 'ring-current');
            });
        }
    }

    // ─── TOAST NOTIFICATION ───────────────────────────────────────────────────
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container') || createToastContainer();
        const toast = document.createElement('div');
        toast.className = [
            'flex items-center space-x-2 px-4 py-3 rounded-xl shadow-lg text-sm font-semibold',
            'transform translate-y-4 opacity-0 transition-all duration-300',
            type === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800'
                               : 'bg-rose-50 border border-rose-200 text-rose-800'
        ].join(' ');
        toast.innerHTML = message;
        container.appendChild(toast);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-4', 'opacity-0');
            });
        });

        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-4');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    function createToastContainer() {
        const el = document.createElement('div');
        el.id = 'toast-container';
        el.className = 'fixed bottom-6 right-6 z-[9999] flex flex-col gap-2';
        document.body.appendChild(el);
        return el;
    }

    // Trigger on SPA transitions and first load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initKanban);
    } else {
        initKanban();
    }
    document.addEventListener('spa-load-complete', initKanban);
})();
</script>
@endpush
