@extends('layouts.app')

@section('content')
<div class="space-y-6" 
    x-data="{ 
        addFollowUpOpen: false, 
        completeFollowUpOpen: false, 
        selectedFollowUpId: null, 
        completionNotes: '', 
        activeView: localStorage.getItem('followUpsView') || 'table', 
        selectedDate: '',
        tableSearch: '',
        statusFilter: 'all',
        typeFilter: 'all'
    }" 
    x-init="$watch('activeView', v => localStorage.setItem('followUpsView', v))">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-gray-150 dark:border-dark-700">
        <div>
            <div class="flex items-center space-x-2 text-xs font-bold text-gray-400 mb-1">
                <a href="{{ route('leads.index') }}" class="hover:text-brand-600 transition-colors">Sales & CRM</a>
                <span>/</span>
                <span class="text-brand-600">Follow-Ups Management</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-black text-dark-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <span>⏱️ Follow-Ups & Client Engagements</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-gray-100 dark:bg-dark-700 text-gray-600 dark:text-gray-300">
                    {{ $allFollowUps->count() }} Tasks
                </span>
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Track, audit, and execute automated client touchpoints, calls, and meetings across the sales pipeline.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- View Toggle Buttons (Table, Queue, Calendar) -->
            <div class="flex items-center bg-gray-100 dark:bg-dark-700 rounded-xl p-1 shadow-inner">
                <!-- Table View (Requested Tabular Mode) -->
                <button type="button" @click="activeView = 'table'"
                    :class="activeView === 'table' ? 'bg-white dark:bg-dark-800 shadow-sm text-gray-900 dark:text-white font-extrabold' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 font-bold'"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 text-xs rounded-lg transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span>Table View</span>
                </button>

                <!-- Cards Queue View -->
                <button type="button" @click="activeView = 'queue'"
                    :class="activeView === 'queue' ? 'bg-white dark:bg-dark-800 shadow-sm text-gray-900 dark:text-white font-extrabold' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 font-bold'"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 text-xs rounded-lg transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span>Cards Queue</span>
                </button>

                <!-- Calendar View -->
                <button type="button" @click="activeView = 'calendar'"
                    :class="activeView === 'calendar' ? 'bg-white dark:bg-dark-800 shadow-sm text-gray-900 dark:text-white font-extrabold' : 'text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 font-bold'"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 text-xs rounded-lg transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Calendar</span>
                </button>
            </div>

            <!-- Schedule Task Button -->
            <button @click="addFollowUpOpen = true" class="inline-flex items-center space-x-1.5 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-md shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Schedule Follow-Up</span>
            </button>
        </div>
    </div>

    <!-- Quick Status KPI Ribbon (Clickable Filters) -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <!-- Total Tasks -->
        <button type="button" @click="statusFilter = 'all'; activeView = 'table'"
            :class="statusFilter === 'all' && activeView === 'table' ? 'ring-2 ring-brand-500 shadow-md' : ''"
            class="bg-white dark:bg-dark-800 p-4 rounded-2xl border border-gray-150 dark:border-dark-700 shadow-sm text-left transition-all hover:bg-gray-50 dark:hover:bg-dark-750">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">All Touchpoints</span>
            <span class="text-2xl font-black text-dark-900 dark:text-white mt-0.5 block">{{ $allFollowUps->count() }}</span>
            <span class="text-[10px] text-gray-500 font-medium">Total registered</span>
        </button>

        <!-- Overdue Tasks -->
        <button type="button" @click="statusFilter = 'overdue'; activeView = 'table'"
            :class="statusFilter === 'overdue' && activeView === 'table' ? 'ring-2 ring-rose-500 shadow-md' : ''"
            class="bg-rose-50/60 dark:bg-rose-950/20 p-4 rounded-2xl border border-rose-200 dark:border-rose-800/40 shadow-sm text-left transition-all hover:bg-rose-100/50">
            <span class="block text-[10px] font-bold text-rose-700 dark:text-rose-400 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                Overdue Tasks
            </span>
            <span class="text-2xl font-black text-rose-700 dark:text-rose-300 mt-0.5 block">{{ $overdue->count() }}</span>
            <span class="text-[10px] text-rose-600/80 font-medium">Action required immediately</span>
        </button>

        <!-- Due Today -->
        <button type="button" @click="statusFilter = 'today'; activeView = 'table'"
            :class="statusFilter === 'today' && activeView === 'table' ? 'ring-2 ring-amber-500 shadow-md' : ''"
            class="bg-amber-50/60 dark:bg-amber-950/20 p-4 rounded-2xl border border-amber-200 dark:border-amber-800/40 shadow-sm text-left transition-all hover:bg-amber-100/50">
            <span class="block text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                Due Today
            </span>
            <span class="text-2xl font-black text-amber-700 dark:text-amber-300 mt-0.5 block">{{ $dueToday->count() }}</span>
            <span class="text-[10px] text-amber-600/80 font-medium">To be completed today</span>
        </button>

        <!-- Due Tomorrow -->
        <button type="button" @click="statusFilter = 'tomorrow'; activeView = 'table'"
            :class="statusFilter === 'tomorrow' && activeView === 'table' ? 'ring-2 ring-blue-500 shadow-md' : ''"
            class="bg-blue-50/60 dark:bg-blue-950/20 p-4 rounded-2xl border border-blue-200 dark:border-blue-800/40 shadow-sm text-left transition-all hover:bg-blue-100/50">
            <span class="block text-[10px] font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                Due Tomorrow
            </span>
            <span class="text-2xl font-black text-blue-700 dark:text-blue-300 mt-0.5 block">{{ $dueTomorrow->count() }}</span>
            <span class="text-[10px] text-blue-600/80 font-medium">Upcoming pipeline</span>
        </button>

        <!-- Completed Tasks -->
        <button type="button" @click="statusFilter = 'completed'; activeView = 'table'"
            :class="statusFilter === 'completed' && activeView === 'table' ? 'ring-2 ring-emerald-500 shadow-md' : ''"
            class="col-span-2 md:col-span-1 bg-emerald-50/60 dark:bg-emerald-950/20 p-4 rounded-2xl border border-emerald-200 dark:border-emerald-800/40 shadow-sm text-left transition-all hover:bg-emerald-100/50">
            <span class="block text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Resolved / Closed
            </span>
            <span class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-0.5 block">{{ $completed->count() }}</span>
            <span class="text-[10px] text-emerald-600/80 font-medium">Successfully logged</span>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- 1. DETAILED TABULAR VIEW (Client Requested Detailed At-A-Glance Table)   -->
    <!-- ========================================================================= -->
    <div x-show="activeView === 'table'" class="space-y-4">
        
        <!-- Table Search & Filter Bar -->
        <div class="bg-white dark:bg-dark-800 rounded-2xl border border-gray-150 dark:border-dark-700 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-md">
                <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model="tableSearch" placeholder="Search client name, phone number, or notes..." 
                    class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-dark-750 border border-gray-250 dark:border-dark-600 rounded-xl text-xs font-medium text-dark-900 dark:text-white outline-none focus:border-brand-500">
            </div>

            <!-- Filter Status Badges -->
            <div class="flex items-center flex-wrap gap-1.5 text-xs">
                <button type="button" @click="statusFilter = 'all'"
                    :class="statusFilter === 'all' ? 'bg-dark-900 dark:bg-white text-white dark:text-dark-900 font-extrabold' : 'bg-gray-100 dark:bg-dark-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-lg font-bold transition-all">
                    All ({{ $allFollowUps->count() }})
                </button>
                <button type="button" @click="statusFilter = 'overdue'"
                    :class="statusFilter === 'overdue' ? 'bg-rose-600 text-white font-extrabold' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 hover:bg-rose-100'"
                    class="px-3 py-1.5 rounded-lg font-bold transition-all flex items-center space-x-1">
                    <span>⚠️ Overdue</span>
                    <span>({{ $overdue->count() }})</span>
                </button>
                <button type="button" @click="statusFilter = 'today'"
                    :class="statusFilter === 'today' ? 'bg-amber-600 text-white font-extrabold' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 hover:bg-amber-100'"
                    class="px-3 py-1.5 rounded-lg font-bold transition-all flex items-center space-x-1">
                    <span>⏳ Today</span>
                    <span>({{ $dueToday->count() }})</span>
                </button>
                <button type="button" @click="statusFilter = 'tomorrow'"
                    :class="statusFilter === 'tomorrow' ? 'bg-blue-600 text-white font-extrabold' : 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 hover:bg-blue-100'"
                    class="px-3 py-1.5 rounded-lg font-bold transition-all flex items-center space-x-1">
                    <span>📅 Tomorrow</span>
                    <span>({{ $dueTomorrow->count() }})</span>
                </button>
                <button type="button" @click="statusFilter = 'completed'"
                    :class="statusFilter === 'completed' ? 'bg-emerald-600 text-white font-extrabold' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100'"
                    class="px-3 py-1.5 rounded-lg font-bold transition-all flex items-center space-x-1">
                    <span>✓ Completed</span>
                    <span>({{ $completed->count() }})</span>
                </button>
            </div>
        </div>

        <!-- The Detailed Follow-Ups Table -->
        <div class="bg-white dark:bg-dark-800 rounded-3xl border border-gray-150 dark:border-dark-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50/80 dark:bg-dark-900 border-b border-gray-200 dark:border-dark-700 text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <th class="py-3 px-3 text-center w-10">S/N</th>
                            <th class="py-3 px-3 min-w-[180px]">Client / Prospect</th>
                            <th class="py-3 px-3 min-w-[170px]">Phone Number & Touch</th>
                            <th class="py-3 px-3 text-center min-w-[100px]">Type</th>
                            <th class="py-3 px-3 min-w-[150px]">Schedule & Status</th>
                            <th class="py-3 px-3 min-w-[200px]">Discussion Notes / Purpose</th>
                            <th class="py-3 px-3 min-w-[130px]">Assigned Officer</th>
                            <th class="py-3 px-3 text-center min-w-[100px]">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-dark-700">
                        @forelse($allFollowUps as $idx => $task)
                        @php
                            $lead = $task->lead;
                            $cleanPhone = $lead ? preg_replace('/[^0-9]/', '', $lead->phone_number ?? '') : '';
                            $isOverdue = ($task->status === 'Pending' && $task->due_date && $task->due_date->isPast());
                            $isToday = ($task->status === 'Pending' && $task->due_date && $task->due_date->isToday());
                            $isTomorrow = ($task->status === 'Pending' && $task->due_date && $task->due_date->isTomorrow());
                            $isCompleted = ($task->status === 'Completed');
                            
                            $statusCategory = $isCompleted ? 'completed' : ($isOverdue ? 'overdue' : ($isToday ? 'today' : ($isTomorrow ? 'tomorrow' : 'upcoming')));
                            $searchHaystack = strtolower(($lead ? $lead->full_name . ' ' . $lead->phone_number : '') . ' ' . $task->notes . ' ' . $task->type);
                        @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-dark-750 transition-colors {{ $isOverdue ? 'bg-rose-50/20' : ($isToday ? 'bg-amber-50/20' : '') }}"
                            x-show="(statusFilter === 'all' || statusFilter === '{{ $statusCategory }}') && (tableSearch === '' || '{{ addslashes($searchHaystack) }}'.includes(tableSearch.toLowerCase()))">
                            
                            <!-- S/N -->
                            <td class="py-3.5 px-3 text-center text-gray-400 font-bold text-[11px]">
                                {{ $idx + 1 }}
                            </td>

                            <!-- Client Name & Temperature -->
                            <td class="py-3.5 px-3">
                                @if($lead)
                                    <div class="flex items-center space-x-2.5">
                                        <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 dark:bg-brand-950 dark:text-brand-300 font-black text-xs flex items-center justify-center flex-shrink-0">
                                            {{ substr($lead->full_name, 0, 2) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('leads.show', $lead->id) }}" class="font-bold text-dark-900 dark:text-white hover:text-brand-600 transition-colors flex items-center gap-1.5">
                                                <span>{{ $lead->full_name }}</span>
                                                @if(strcasecmp($lead->lead_temperature ?? '', 'hot') === 0)
                                                    <span class="text-[9px] px-1.5 py-0.2 rounded font-bold bg-rose-100 text-rose-700">🔥 Hot</span>
                                                @endif
                                            </a>
                                            <span class="text-[10px] text-gray-400 block">{{ $lead->lead_source ?: 'Direct Prospect' }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">Unassigned Prospect</span>
                                @endif
                            </td>

                            <!-- Phone Number & 1-Click Call/WhatsApp Actions -->
                            <td class="py-3.5 px-3">
                                @if($lead && $lead->phone_number)
                                    <div class="space-y-1">
                                        <span class="font-mono text-xs font-bold text-dark-900 dark:text-gray-200 block">{{ $lead->phone_number }}</span>
                                        <div class="flex items-center space-x-1.5">
                                            <!-- One-Click Call Link -->
                                            <a href="tel:{{ $lead->phone_number }}" 
                                                class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 transition-all"
                                                title="Call {{ $lead->full_name }}">
                                                <span>📞</span>
                                                <span>Call</span>
                                            </a>

                                            <!-- One-Click WhatsApp Link -->
                                            @if($cleanPhone)
                                            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank"
                                                class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 transition-all"
                                                title="Send WhatsApp message to {{ $lead->full_name }}">
                                                <span>💬</span>
                                                <span>WhatsApp</span>
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-[11px]">—</span>
                                @endif
                            </td>

                            <!-- Follow-up Type -->
                            <td class="py-3.5 px-3 text-center">
                                @php
                                    $typeLower = strtolower($task->type);
                                @endphp
                                @if(str_contains($typeLower, 'call') || str_contains($typeLower, 'phone'))
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        <span>📞</span>
                                        <span>Phone Call</span>
                                    </span>
                                @elseif(str_contains($typeLower, 'meet') || str_contains($typeLower, 'inspect'))
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                        <span>🤝</span>
                                        <span>Direct Meeting</span>
                                    </span>
                                @elseif(str_contains($typeLower, 'whatsapp'))
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span>💬</span>
                                        <span>WhatsApp</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-gray-100 text-gray-700 dark:bg-dark-700 dark:text-gray-300 border border-gray-200 dark:border-dark-600">
                                        <span>📝</span>
                                        <span>{{ $task->type ?: 'Follow-Up' }}</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Schedule & Status -->
                            <td class="py-3.5 px-3">
                                <div class="space-y-0.5">
                                    @if($isCompleted)
                                        <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                            <span>✓</span>
                                            <span>Completed</span>
                                        </span>
                                    @elseif($isOverdue)
                                        <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300 animate-pulse">
                                            <span>⚠️</span>
                                            <span>LATE: {{ $task->due_date->diffForHumans() }}</span>
                                        </span>
                                    @elseif($isToday)
                                        <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                            <span>⏳</span>
                                            <span>Today at {{ $task->due_date->format('h:i A') }}</span>
                                        </span>
                                    @elseif($isTomorrow)
                                        <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                                            <span>Tomorrow at {{ $task->due_date->format('h:i A') }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 dark:bg-dark-700 dark:text-gray-300">
                                            <span>Due {{ $task->due_date ? $task->due_date->diffForHumans() : 'Soon' }}</span>
                                        </span>
                                    @endif

                                    <span class="text-[10px] text-gray-400 block font-mono">
                                        {{ $task->due_date ? $task->due_date->format('M d, Y h:i A') : 'No Date' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Discussion Notes / Purpose -->
                            <td class="py-3.5 px-3">
                                <p class="text-gray-700 dark:text-gray-300 line-clamp-2 leading-relaxed text-[11px]" title="{{ $task->notes }}">
                                    {{ $task->notes ?: 'Scheduled follow-up discussion.' }}
                                </p>
                            </td>

                            <!-- Assigned Officer -->
                            <td class="py-3.5 px-3">
                                @php
                                    $officer = $lead?->assignedOfficer;
                                @endphp
                                @if($officer)
                                    <div class="flex items-center space-x-1.5">
                                        <div class="w-5 h-5 rounded-full bg-gray-200 dark:bg-dark-700 text-dark-900 dark:text-gray-200 text-[9px] font-black flex items-center justify-center flex-shrink-0">
                                            {{ substr($officer->name, 0, 1) }}
                                        </div>
                                        <span class="font-bold text-dark-900 dark:text-gray-200 text-[11px] truncate max-w-[100px]">{{ $officer->name }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-[10px] italic">Unassigned</span>
                                @endif
                            </td>

                            <!-- Immediate Action Column -->
                            <td class="py-3.5 px-3 text-center">
                                @if(!$isCompleted)
                                    <button type="button" @click="selectedFollowUpId = {{ $task->id }}; completeFollowUpOpen = true"
                                        class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-xl font-bold text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 transition-all shadow-xs"
                                        title="Mark task as completed">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>Resolve</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center space-x-1 text-emerald-600 dark:text-emerald-400 font-bold text-[11px]">
                                        <span>✓</span>
                                        <span>Done</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-400 text-sm">
                                <span class="text-3xl block mb-2">📋</span>
                                <p class="font-bold text-dark-900 dark:text-white">No follow-up tasks found</p>
                                <p class="text-xs text-gray-400 mt-0.5">Use the "Schedule Follow-Up" button above to log your first touchpoint.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <!-- END TABULAR VIEW -->

    <!-- ========================================================================= -->
    <!-- 2. CARDS QUEUE VIEW (3 Columns: Overdue, Today, Tomorrow)                 -->
    <!-- ========================================================================= -->
    <div x-show="activeView === 'queue'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Column 1: Overdue -->
        <div class="bg-rose-50/50 dark:bg-rose-950/10 rounded-3xl p-5 border border-rose-100 dark:border-rose-900/30 flex flex-col h-[600px]">
            <div class="flex items-center justify-between pb-3 border-b border-rose-200 dark:border-rose-900/40 mb-4 flex-shrink-0">
                <h3 class="font-bold text-rose-800 dark:text-rose-300 text-sm flex items-center space-x-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-600 animate-pulse"></span>
                    <span>Overdue Task Reminders</span>
                </h3>
                <span class="px-2 py-0.5 text-xs font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 rounded-md">{{ $overdue->count() }}</span>
            </div>

            <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                @forelse($overdue as $task)
                <div class="bg-white dark:bg-dark-800 p-4 rounded-2xl border border-rose-150 dark:border-dark-700 shadow-sm flex flex-col justify-between space-y-3 hover:shadow-md transition-all">
                    <div class="space-y-1">
                        <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-wider text-rose-600">
                            <span>{{ $task->type }}</span>
                            <span class="bg-rose-50 dark:bg-rose-950 px-1.5 py-0.2 rounded-md">LATE: {{ $task->due_date->diffForHumans() }}</span>
                        </div>
                        <h4 class="font-bold text-dark-900 dark:text-white text-sm">
                            <a href="{{ route('leads.show', $task->lead_id) }}" class="hover:underline">{{ $task->lead ? $task->lead->full_name : 'Client' }}</a>
                        </h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">{{ $task->notes }}</p>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-50 dark:border-dark-700">
                        <span class="text-[10px] text-gray-400 font-semibold">{{ $task->due_date->format('M d, H:i A') }}</span>
                        <button @click="selectedFollowUpId = {{ $task->id }}; completeFollowUpOpen = true" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100 dark:border-emerald-900 transition-all flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Resolve</span>
                        </button>
                    </div>
                </div>
                @empty
                <div class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-400">
                    <h5 class="text-xs font-bold text-rose-800 dark:text-rose-400">No overdue tasks</h5>
                    <p class="text-[10px] text-gray-500 mt-1">Excellent job! All assignments are updated.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Column 2: Due Today -->
        <div class="bg-amber-50/50 dark:bg-amber-950/10 rounded-3xl p-5 border border-amber-100 dark:border-amber-900/30 flex flex-col h-[600px]">
            <div class="flex items-center justify-between pb-3 border-b border-amber-200 dark:border-amber-900/40 mb-4 flex-shrink-0">
                <h3 class="font-bold text-amber-800 dark:text-amber-300 text-sm flex items-center space-x-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span>Due Today</span>
                </h3>
                <span class="px-2 py-0.5 text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 rounded-md">{{ $dueToday->count() }}</span>
            </div>

            <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                @forelse($dueToday as $task)
                <div class="bg-white dark:bg-dark-800 p-4 rounded-2xl border border-amber-150 dark:border-dark-700 shadow-sm flex flex-col justify-between space-y-3 hover:shadow-md transition-all">
                    <div class="space-y-1">
                        <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-wider text-amber-600">
                            <span>{{ $task->type }}</span>
                            <span>{{ $task->due_date->format('H:i A') }}</span>
                        </div>
                        <h4 class="font-bold text-dark-900 dark:text-white text-sm">
                            <a href="{{ route('leads.show', $task->lead_id) }}" class="hover:underline">{{ $task->lead ? $task->lead->full_name : 'Client' }}</a>
                        </h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">{{ $task->notes }}</p>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-50 dark:border-dark-700">
                        <span class="text-[10px] text-gray-400 font-semibold">{{ $task->due_date->format('M d, H:i A') }}</span>
                        <button @click="selectedFollowUpId = {{ $task->id }}; completeFollowUpOpen = true" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100 dark:border-emerald-900 transition-all flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Resolve</span>
                        </button>
                    </div>
                </div>
                @empty
                <div class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-400">
                    <h5 class="text-xs font-bold text-amber-800 dark:text-amber-400">No tasks for today</h5>
                    <p class="text-[10px] text-gray-500 mt-1">Take this time to search or prospect new clients.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Column 3: Due Tomorrow -->
        <div class="bg-blue-50/50 dark:bg-blue-950/10 rounded-3xl p-5 border border-blue-100 dark:border-blue-900/30 flex flex-col h-[600px]">
            <div class="flex items-center justify-between pb-3 border-b border-blue-200 dark:border-blue-900/40 mb-4 flex-shrink-0">
                <h3 class="font-bold text-blue-800 dark:text-blue-300 text-sm flex items-center space-x-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <span>Due Tomorrow</span>
                </h3>
                <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 rounded-md">{{ $dueTomorrow->count() }}</span>
            </div>

            <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                @forelse($dueTomorrow as $task)
                <div class="bg-white dark:bg-dark-800 p-4 rounded-2xl border border-blue-150 dark:border-dark-700 shadow-sm flex flex-col justify-between space-y-3 hover:shadow-md transition-all">
                    <div class="space-y-1">
                        <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-wider text-blue-600">
                            <span>{{ $task->type }}</span>
                            <span>{{ $task->due_date->format('H:i A') }}</span>
                        </div>
                        <h4 class="font-bold text-dark-900 dark:text-white text-sm">
                            <a href="{{ route('leads.show', $task->lead_id) }}" class="hover:underline">{{ $task->lead ? $task->lead->full_name : 'Client' }}</a>
                        </h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">{{ $task->notes }}</p>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-50 dark:border-dark-700">
                        <span class="text-[10px] text-gray-400 font-semibold">{{ $task->due_date->format('M d, H:i A') }}</span>
                        <button @click="selectedFollowUpId = {{ $task->id }}; completeFollowUpOpen = true" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100 dark:border-emerald-900 transition-all flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Resolve</span>
                        </button>
                    </div>
                </div>
                @empty
                <div class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-400">
                    <h5 class="text-xs font-bold text-blue-800 dark:text-blue-400">No tasks for tomorrow</h5>
                    <p class="text-[10px] text-gray-500 mt-1">Excellent! Tomorrow is clear.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>
    <!-- END CARDS QUEUE VIEW -->

    <!-- ========================================================================= -->
    <!-- 3. CALENDAR VIEW                                                          -->
    <!-- ========================================================================= -->
    <div x-show="activeView === 'calendar'" class="bg-white dark:bg-dark-800 rounded-3xl border border-gray-150 dark:border-dark-700 shadow-sm overflow-hidden">
        
        <div class="p-6">
            <!-- Calendar Navigation Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-gray-100 dark:border-dark-700 gap-4">
                <div class="flex items-center space-x-3">
                    <button id="cal-prev" type="button" class="w-8 h-8 rounded-xl border border-gray-200 dark:border-dark-600 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-50 transition-all">&larr;</button>
                    <h2 id="cal-title" class="text-base font-extrabold text-dark-900 dark:text-white min-w-[140px] text-center"></h2>
                    <button id="cal-next" type="button" class="w-8 h-8 rounded-xl border border-gray-200 dark:border-dark-600 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-50 transition-all">&rarr;</button>
                    <button id="cal-today" type="button" class="px-3 py-1.5 text-xs font-bold rounded-xl border border-gray-200 dark:border-dark-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 transition-all">Today</button>
                </div>
                <!-- Legend -->
                <div class="hidden md:flex items-center space-x-4 text-xs font-semibold">
                    <span class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span><span class="text-gray-600 dark:text-gray-400">Overdue</span></span>
                    <span class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span><span class="text-gray-600 dark:text-gray-400">Today</span></span>
                    <span class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span><span class="text-gray-600 dark:text-gray-400">Upcoming</span></span>
                    <span class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span><span class="text-gray-600 dark:text-gray-400">Completed</span></span>
                </div>
            </div>

            <!-- Weekday Headers -->
            <div class="grid grid-cols-7 text-center text-[11px] font-extrabold text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-dark-700 bg-gray-50/50 dark:bg-dark-850">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                <div class="py-3 border-r border-gray-50 dark:border-dark-700 last:border-r-0">{{ $day }}</div>
                @endforeach
            </div>

            <!-- Day Cells Grid (rendered by JS) -->
            <div id="cal-grid" class="grid grid-cols-7" style="min-height: 520px;"></div>
        </div>

        <!-- JSON events payload for JS consumption -->
        <script id="followup-events-data" type="application/json">
            {!! json_encode($allTasks->map(function($t) {
                return [
                    'id'       => $t->id,
                    'lead'     => $t->lead ? $t->lead->full_name : 'Unknown',
                    'lead_id'  => $t->lead_id,
                    'type'     => $t->type,
                    'notes'    => $t->notes,
                    'status'   => $t->status,
                    'due_date' => $t->due_date ? \Carbon\Carbon::parse($t->due_date)->format('Y-m-d') : null,
                ];
            })) !!}
        </script>

    </div>
    <!-- END CALENDAR VIEW -->

    <!-- Add Follow-Up Modal -->
    <div x-cloak x-show="addFollowUpOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white dark:bg-dark-800 rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-6" @click.away="addFollowUpOpen = false">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-dark-700">
                <h3 class="text-lg font-bold text-dark-900 dark:text-white">Schedule Follow-Up Task</h3>
                <button @click="addFollowUpOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <form action="{{ route('follow-ups.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Select Lead Prospect *</label>
                    <select name="lead_id" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-dark-600 focus:border-brand-500 outline-none text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-dark-750">
                        <option value="">Choose client...</option>
                        @foreach($leads as $lead)
                        <option value="{{ $lead->id }}">{{ $lead->full_name }} ({{ $lead->phone_number }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Follow-Up Type *</label>
                    <select name="type" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-dark-600 focus:border-brand-500 outline-none text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-dark-750">
                        <option value="Call">Phone Call</option>
                        <option value="Meeting">Direct Meeting</option>
                        <option value="Note">Notes/Reminders</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Date & Time Due *</label>
                    <input type="datetime-local" name="due_date" required x-model="selectedDate"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-dark-600 focus:border-brand-500 outline-none text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-dark-750">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Notes / Discussion Goal</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-dark-600 focus:border-brand-500 outline-none text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-dark-750 resize-none"
                              placeholder="e.g. Confirm site inspection time and send estate brochure..."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-3 border-t border-gray-100 dark:border-dark-700">
                    <button type="button" @click="addFollowUpOpen = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-600/20">
                        Schedule Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Complete Follow-Up Modal -->
    <div x-cloak x-show="completeFollowUpOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white dark:bg-dark-800 rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-6" @click.away="completeFollowUpOpen = false">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100 dark:border-dark-700">
                <h3 class="text-lg font-bold text-dark-900 dark:text-white">Complete Follow-Up Touchpoint</h3>
                <button @click="completeFollowUpOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <form :action="'/follow-ups/' + selectedFollowUpId" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <input type="hidden" name="status" value="Completed">

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Completion Log Notes</label>
                    <textarea name="notes" rows="4" required x-model="completionNotes"
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-dark-600 focus:border-brand-500 outline-none text-sm text-gray-800 dark:text-gray-200 bg-white dark:bg-dark-750 resize-none"
                              placeholder="e.g. Client requested follow up inspection next week. Confirmed interested in 3-bedroom terrace."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100 dark:border-dark-700">
                    <button type="button" @click="completeFollowUpOpen = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-600/15">
                        Log as Completed
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
    const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const today = new Date();
    today.setHours(0,0,0,0);

    let currentYear  = today.getFullYear();
    let currentMonth = today.getMonth(); // 0-indexed

    // ─── Load events from embedded JSON ───────────────────────────────────────
    function loadEvents() {
        const el = document.getElementById('followup-events-data');
        if (!el) return [];
        try { return JSON.parse(el.textContent.trim()); } catch { return []; }
    }

    // ─── Render Calendar Grid ─────────────────────────────────────────────────
    function renderCalendar(year, month) {
        const grid     = document.getElementById('cal-grid');
        const titleEl  = document.getElementById('cal-title');
        if (!grid || !titleEl) return;

        titleEl.textContent = `${MONTHS[month]} ${year}`;

        const events   = loadEvents();
        const firstDay = new Date(year, month, 1).getDay(); // 0=Sun
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        grid.innerHTML = '';

        // Blank leading cells
        for (let b = 0; b < firstDay; b++) {
            const blank = document.createElement('div');
            blank.className = 'bg-gray-50/50 dark:bg-dark-850/50 border-r border-b border-gray-100 dark:border-dark-700 min-h-[90px]';
            grid.appendChild(blank);
        }

        // Day cells
        for (let d = 1; d <= daysInMonth; d++) {
            const cellDate   = new Date(year, month, d);
            cellDate.setHours(0,0,0,0);
            const dateStr    = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const isToday    = cellDate.getTime() === today.getTime();
            const isPast     = cellDate < today;
            const dayEvents  = events.filter(e => e.due_date === dateStr);

            const cell = document.createElement('div');
            cell.className = [
                'relative border-r border-b border-gray-100 dark:border-dark-700 min-h-[90px] p-2 cursor-pointer transition-colors hover:bg-blue-50/40 dark:hover:bg-dark-750 group flex flex-col',
                isToday ? 'bg-amber-50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-800' : '',
            ].join(' ');

            // Day number badge
            const dayNum = document.createElement('span');
            dayNum.className = [
                'text-xs font-extrabold w-7 h-7 flex items-center justify-center rounded-full mb-1 flex-shrink-0',
                isToday
                    ? 'bg-brand-500 text-white'
                    : isPast
                        ? 'text-gray-400'
                        : 'text-gray-700 dark:text-gray-300'
            ].join(' ');
            dayNum.textContent = d;
            cell.appendChild(dayNum);

            // Event chips
            const maxVisible = 3;
            dayEvents.slice(0, maxVisible).forEach(ev => {
                const evDate = new Date(ev.due_date + 'T00:00:00');
                const isOverdue = evDate < today && ev.status === 'Pending';

                const chip = document.createElement('div');
                chip.className = [
                    'text-[10px] font-bold px-1.5 py-0.5 rounded-md truncate mb-0.5 leading-snug',
                    ev.status === 'Completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' :
                    isToday && ev.status === 'Pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' :
                    isOverdue ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' :
                    'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                ].join(' ');
                chip.title = `${ev.type} — ${ev.lead}: ${ev.notes || ''}`;
                chip.textContent = `${ev.type === 'Call' ? '📞' : ev.type === 'Meeting' ? '🤝' : '📝'} ${ev.lead}`;
                cell.appendChild(chip);
            });

            // Overflow indicator
            if (dayEvents.length > maxVisible) {
                const more = document.createElement('span');
                more.className = 'text-[10px] text-gray-400 font-semibold';
                more.textContent = `+${dayEvents.length - maxVisible} more`;
                cell.appendChild(more);
            }

            // Click → open modal with prefilled date
            cell.addEventListener('click', () => {
                const timeStr = `${dateStr}T09:00`;
                const root = document.querySelector('[x-data]');
                if (root && root._x_dataStack) {
                    const alpineData = root._x_dataStack[0];
                    if (alpineData) {
                        alpineData.selectedDate = timeStr;
                        alpineData.addFollowUpOpen = true;
                    }
                }
            });

            grid.appendChild(cell);
        }

        // Trailing blank cells to complete the last row
        const totalCells = firstDay + daysInMonth;
        const remainder  = totalCells % 7;
        if (remainder !== 0) {
            for (let t = 0; t < 7 - remainder; t++) {
                const blank = document.createElement('div');
                blank.className = 'bg-gray-50/50 dark:bg-dark-850/50 border-r border-b border-gray-100 dark:border-dark-700 min-h-[90px]';
                grid.appendChild(blank);
            }
        }
    }

    // ─── Navigation ───────────────────────────────────────────────────────────
    function initCalendar() {
        const prevBtn  = document.getElementById('cal-prev');
        const nextBtn  = document.getElementById('cal-next');
        const todayBtn = document.getElementById('cal-today');
        if (!prevBtn) return;

        prevBtn.onclick = () => {
            currentMonth--;
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            renderCalendar(currentYear, currentMonth);
        };
        nextBtn.onclick = () => {
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            renderCalendar(currentYear, currentMonth);
        };
        todayBtn.onclick = () => {
            currentYear  = today.getFullYear();
            currentMonth = today.getMonth();
            renderCalendar(currentYear, currentMonth);
        };

        renderCalendar(currentYear, currentMonth);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCalendar);
    } else {
        initCalendar();
    }
    document.addEventListener('spa-load-complete', () => {
        currentYear  = today.getFullYear();
        currentMonth = today.getMonth();
        initCalendar();
    });
})();
</script>
@endpush
