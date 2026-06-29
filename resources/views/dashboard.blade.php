@extends('layouts.app')

@section('content')
<div class="space-y-8" x-data="{ completeFollowUpOpen: false, selectedFollowUpId: null, completionNotes: '' }">
    {{-- Onboarding Checklist Welcome Card --}}
    @if(isset($onboardingTasks) && $onboardingTasks->isNotEmpty() && Auth::user()->onboardingPercentage() < 100)
    <div class="bg-gradient-to-r from-brand-500 to-brand-600 rounded-3xl p-6 md:p-8 text-white shadow-xl relative overflow-hidden">
        {{-- Decorative circles --}}
        <div class="absolute right-0 top-0 w-64 h-64 bg-white/5 rounded-full -mr-20 -mt-20"></div>
        <div class="absolute right-12 bottom-0 w-32 h-32 bg-white/5 rounded-full -mr-10 -mb-10"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-3 max-w-xl">
                <span class="px-3 py-1 bg-white/20 text-white rounded-full text-xs font-bold uppercase tracking-wider">Welcome On Board!</span>
                <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight font-display">Hello, {{ Auth::user()->name }}!</h2>
                <p class="text-white/80 text-sm leading-relaxed">
                    Welcome to the <strong class="text-white font-bold">{{ Auth::user()->departmentRelation->name ?? Auth::user()->department ?? 'Sales' }}</strong> department at our <strong class="text-white font-bold">{{ Auth::user()->branch->name ?? 'Corporate' }}</strong> branch. 
                    @if(Auth::user()->commission_rate)
                    Your personalized sales commission rate is set at <strong class="text-white font-bold">{{ Auth::user()->commission_rate }}%</strong>.
                    @endif
                    Please complete your onboarding checklist to get fully set up in the system.
                </p>
                
                {{-- Progress Bar --}}
                <div class="space-y-1.5 pt-2">
                    <div class="flex justify-between text-xs font-bold text-white/90">
                        <span>Onboarding Progress</span>
                        <span>{{ Auth::user()->onboardingPercentage() }}%</span>
                    </div>
                    <div class="w-full bg-white/20 rounded-full h-2">
                        <div class="bg-white h-2 rounded-full transition-all duration-300" style="width: {{ Auth::user()->onboardingPercentage() }}%"></div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-5 text-dark-900 w-full lg:max-w-md shadow-lg space-y-4">
                <h3 class="font-bold text-sm text-gray-800 border-b border-gray-100 pb-2">Pending Onboarding Tasks</h3>
                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    @foreach($onboardingTasks as $task)
                    <div class="flex items-center justify-between gap-3 text-xs p-2 bg-gray-50 rounded-xl hover:bg-gray-100/70 transition-all">
                        <div class="flex items-center gap-2">
                            <form action="{{ route('hr.staff.onboarding.toggle', $task->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-5 h-5 rounded-md border {{ $task->is_completed ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-gray-300 hover:border-brand-500' }} flex items-center justify-center transition-all">
                                    @if($task->is_completed)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    @endif
                                </button>
                            </form>
                            <span class="{{ $task->is_completed ? 'text-gray-400 line-through' : 'text-gray-700 font-medium' }}">{{ $task->task_name }}</span>
                        </div>
                        @if($task->due_date)
                        <span class="text-[10px] text-gray-400 flex-shrink-0">Due: {{ $task->due_date->format('d M') }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($is_media_dashboard) && $is_media_dashboard)
    <!-- Media Dashboard Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Media & Marketing Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Track campaign engagement, content production, and lead acquisition.</p>
        </div>
        <div class="flex space-x-3">
            <a href="#" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/10 hover:shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>New Campaign</span>
            </a>
        </div>
    </div>

    <!-- Media Stats Counters -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Campaigns</span>
                <h3 class="text-3xl font-extrabold text-dark-900 mt-1">{{ number_format($metrics['total_campaigns']) }}</h3>
                <span class="text-xs text-gray-500 block mt-1">Created & Scheduled</span>
            </div>
            <span class="p-4 bg-brand-50 text-brand-500 rounded-2xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </span>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Sends</span>
                <h3 class="text-3xl font-extrabold text-dark-900 mt-1">{{ number_format($metrics['total_emails_sent']) }}</h3>
                <span class="text-xs text-blue-600 block mt-1 font-semibold">Emails & SMS Delivered</span>
            </div>
            <span class="p-4 bg-blue-50 text-blue-500 rounded-2xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </span>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Avg Open Rate</span>
                <h3 class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $metrics['avg_open_rate'] }}%</h3>
                <span class="text-xs text-emerald-600 block mt-1 font-semibold">Audience Engagement</span>
            </div>
            <span class="p-4 bg-emerald-50 text-emerald-500 rounded-2xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </span>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Avg Click Rate</span>
                <h3 class="text-3xl font-extrabold text-amber-600 mt-1">{{ $metrics['avg_click_rate'] }}%</h3>
                <span class="text-xs text-amber-600 block mt-1 font-semibold">Call to Action clicks</span>
            </div>
            <span class="p-4 bg-amber-50 text-amber-500 rounded-2xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Media Department KPIs -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-dark-900">Content Production Targets (This Month)</h3>
                    <p class="text-xs text-gray-500">Progress towards your department's KPI submissions.</p>
                </div>
            </div>
            <div class="space-y-4 mt-4">
                @forelse($media_targets as $target)
                <div>
                    <div class="flex justify-between text-xs font-bold mb-1">
                        <span class="text-dark-900">{{ $target->metric }}</span>
                        <span class="text-gray-500">{{ $target->actual_value }} / {{ $target->target_value }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                        <div class="bg-brand-500 h-2.5 rounded-full" style="width: {{ $target->target_value > 0 ? min(100, ($target->actual_value / $target->target_value) * 100) : 0 }}%"></div>
                    </div>
                </div>
                @empty
                <div class="p-4 bg-gray-50 rounded-xl text-center">
                    <p class="text-sm text-gray-500">No active targets set for the Media department this month.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Lead Source Performance Chart -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div>
                <h3 class="text-lg font-bold text-dark-900">Lead Acquisition Sources</h3>
                <p class="text-xs text-gray-500">Where are your leads coming from?</p>
            </div>
            <div class="h-64 relative flex items-center justify-center">
                <canvas id="leadSourceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Campaigns -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
            <div>
                <h3 class="text-lg font-bold text-dark-900">Recent Marketing Campaigns</h3>
                <p class="text-xs text-gray-500">Latest email and SMS blasts sent to prospects.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-4 py-3 font-bold text-gray-500">Campaign Name</th>
                        <th class="px-4 py-3 font-bold text-gray-500">Type</th>
                        <th class="px-4 py-3 font-bold text-gray-500">Sent</th>
                        <th class="px-4 py-3 font-bold text-gray-500">Opens</th>
                        <th class="px-4 py-3 font-bold text-gray-500">Clicks</th>
                        <th class="px-4 py-3 font-bold text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recent_campaigns as $campaign)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 font-bold text-dark-900">{{ $campaign->name }}</td>
                        <td class="px-4 py-3 uppercase tracking-wider text-[10px] font-bold text-gray-500">{{ $campaign->type }}</td>
                        <td class="px-4 py-3">{{ number_format($campaign->sent_count) }}</td>
                        <td class="px-4 py-3 text-emerald-600 font-bold">{{ number_format($campaign->opened_count) }}</td>
                        <td class="px-4 py-3 text-amber-600 font-bold">{{ number_format($campaign->clicked_count) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-[10px] font-bold rounded-md bg-blue-50 text-blue-600">{{ $campaign->status }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No recent campaigns found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">CRM Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Real-time leads tracking, conversions metrics, and performance dashboard.</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('leads.index') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/10 hover:shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Add New Lead</span>
            </a>
        </div>
    </div>

    <!-- Stats Counters Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card: Total Leads -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Leads</span>
                <h3 class="text-3xl font-extrabold text-dark-900 mt-1">{{ number_format($metrics['total_leads']) }}</h3>
                <span class="text-xs text-gray-500 block mt-1">Registered leads</span>
            </div>
            <span class="p-4 bg-brand-50 text-brand-500 rounded-2xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </span>
        </div>

        <!-- Card: Follow Ups Due -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Follow Ups Due</span>
                <h3 class="text-3xl font-extrabold text-dark-900 mt-1">{{ number_format($metrics['follow_ups_due']) }}</h3>
                <span class="text-xs text-brand-600 block mt-1 font-semibold">Today & Tomorrow</span>
            </div>
            <span class="p-4 bg-amber-50 text-amber-500 rounded-2xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </span>
        </div>

        <!-- Card: Inspections Scheduled -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Inspections</span>
                <h3 class="text-3xl font-extrabold text-dark-900 mt-1">{{ number_format($metrics['scheduled_inspections']) }}</h3>
                <span class="text-xs text-blue-600 block mt-1 font-semibold">Active Schedules</span>
            </div>
            <span class="p-4 bg-blue-50 text-blue-500 rounded-2xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </span>
        </div>

        <!-- Card: Closed Revenue -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Revenue</span>
                <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">₦{{ number_format($metrics['total_revenue'], 2) }}</h3>
                <span class="text-xs text-emerald-600 block mt-1 font-semibold">{{ $metrics['closed_deals'] }} Deals Won ({{ $metrics['conversion_rate'] }}%)</span>
            </div>
            <span class="p-4 bg-emerald-50 text-emerald-500 rounded-2xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1M10 6h4a2 2 0 110 4h-4V6z"></path>
                </svg>
            </span>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Monthly Performance Chart (Leads vs Sales) -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-dark-900">Sales and Leads Monthly Growth</h3>
                    <p class="text-xs text-gray-500">6-month trends overview.</p>
                </div>
            </div>
            <div class="h-80 relative">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>

        <!-- Lead Source Performance Chart -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div>
                <h3 class="text-lg font-bold text-dark-900">Lead Source Channels</h3>
                <p class="text-xs text-gray-500">Breakdown of acquisition channels.</p>
            </div>
            <div class="h-64 relative flex items-center justify-center">
                <canvas id="leadSourceChart"></canvas>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center text-xs pt-2">
                @foreach($source_performance->take(3) as $source)
                <div class="p-2 bg-gray-50 rounded-xl">
                    <span class="text-gray-500 block truncate">{{ $source->lead_source }}</span>
                    <strong class="text-dark-900 font-bold text-sm">{{ $source->count }}</strong>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Bottom Lists Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left: Pending Follow-Ups -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col h-[500px]">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4 flex-shrink-0">
                <div>
                    <h3 class="text-lg font-bold text-dark-900">Pending Follow-Ups</h3>
                    <p class="text-xs text-gray-500">Logs waiting for call/meeting completions.</p>
                </div>
                <a href="{{ route('follow-ups.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">View All</a>
            </div>

            <div class="flex-1 overflow-y-auto space-y-3 pr-2">
                @forelse($pending_follow_ups as $followUp)
                <div class="p-4 bg-gray-50 hover:bg-gray-100/70 border border-gray-100 rounded-2xl flex justify-between items-start transition-all">
                    <div class="space-y-1 overflow-hidden pr-4">
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-0.5 text-[10px] font-bold tracking-wide uppercase rounded-md 
                                {{ $followUp->type === 'Call' ? 'bg-orange-100 text-orange-700' : '' }}
                                {{ $followUp->type === 'Meeting' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $followUp->type === 'Note' ? 'bg-gray-200 text-gray-700' : '' }}
                            ">
                                {{ $followUp->type }}
                            </span>
                            <span class="text-xs text-gray-500 font-medium">Due: {{ $followUp->due_date->format('M d, h:i A') }}</span>
                            @if($followUp->due_date->isPast())
                            <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.2 rounded-md">OVERDUE</span>
                            @endif
                        </div>
                        <h4 class="text-sm font-bold text-dark-900 truncate">
                            <a href="{{ route('leads.show', $followUp->lead_id) }}" class="hover:underline">{{ $followUp->lead->full_name }}</a>
                        </h4>
                        <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed">{{ $followUp->notes }}</p>
                    </div>
                    <button @click="selectedFollowUpId = {{ $followUp->id }}; completeFollowUpOpen = true" 
                            class="p-2 bg-white text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 border border-gray-200 rounded-xl shadow-sm hover:border-emerald-200 transition-all flex-shrink-0"
                            title="Mark as Completed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </button>
                </div>
                @empty
                <div class="h-full flex flex-col items-center justify-center text-center p-6">
                    <span class="p-4 bg-gray-50 text-gray-400 rounded-full mb-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </span>
                    <h5 class="text-sm font-bold text-dark-900">No follow-ups due</h5>
                    <p class="text-xs text-gray-500 mt-1">Excellent! All logged tasks are caught up.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Upcoming Inspections -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-col h-[500px]">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4 flex-shrink-0">
                <div>
                    <h3 class="text-lg font-bold text-dark-900">Upcoming Inspections</h3>
                    <p class="text-xs text-gray-500">Site property tours with prospects.</p>
                </div>
                <a href="{{ route('inspections.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">View All</a>
            </div>

            <div class="flex-1 overflow-y-auto space-y-3 pr-2">
                @forelse($upcoming_inspections as $inspection)
                <div class="p-4 bg-gray-50 hover:bg-gray-100/70 border border-gray-100 rounded-2xl flex items-start space-x-3">
                    <span class="p-3 bg-blue-50 text-blue-500 rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </span>
                    <div class="flex-1 space-y-1 overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-blue-600 font-semibold">{{ $inspection->inspection_date->format('M d, h:i A') }}</span>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-100 text-blue-700">{{ $inspection->status }}</span>
                        </div>
                        <h4 class="text-sm font-bold text-dark-900 truncate">
                            <a href="{{ route('leads.show', $inspection->lead_id) }}" class="hover:underline">{{ $inspection->lead->full_name }}</a>
                        </h4>
                        <p class="text-xs text-gray-500 font-semibold truncate">{{ $inspection->property->name }} ({{ $inspection->property->location }})</p>
                        <p class="text-xs text-gray-600 line-clamp-1 leading-relaxed mt-0.5">{{ $inspection->notes }}</p>
                    </div>
                </div>
                @empty
                <div class="h-full flex flex-col items-center justify-center text-center p-6">
                    <span class="p-4 bg-gray-50 text-gray-400 rounded-full mb-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                    <h5 class="text-sm font-bold text-dark-900">No scheduled inspections</h5>
                    <p class="text-xs text-gray-500 mt-1">Book site tours to help close more sales.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Complete Follow-Up Modal -->
    <div x-cloak x-show="completeFollowUpOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-6" @click.away="completeFollowUpOpen = false">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Complete Follow-Up Task</h3>
                <button @click="completeFollowUpOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form :action="'/follow-ups/' + selectedFollowUpId" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="Completed">

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Completion Log Notes</label>
                    <textarea name="notes" rows="4" required x-model="completionNotes"
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none text-sm text-gray-800 resize-none"
                              placeholder="Describe outcome: e.g. Client agreed to schedule inspection next week."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="completeFollowUpOpen = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-600/15">
                        Log as Completed
                    </button>
                </div>
            </form>
        </div>
    </div>

    @endif
</div>

<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const initCharts = () => {
        const isMediaDashboard = @json(isset($is_media_dashboard) && $is_media_dashboard);
        const sourceData = @json($source_performance ?? []);

        // 1. Lead Source Doughnut Chart (Shared by both dashboards)
        const ctx2Element = document.getElementById('leadSourceChart');
        if (ctx2Element) {
            const sourceLabels = sourceData.map(d => d.lead_source);
            const sourceCounts = sourceData.map(d => d.count);
            const ctx2 = ctx2Element.getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: sourceLabels,
                    datasets: [{
                        data: sourceCounts,
                        backgroundColor: [
                            '#FEA500', // Brand Orange
                            '#3b82f6', // Blue
                            '#10b981', // Emerald
                            '#8b5cf6', // Violet
                            '#ec4899', // Pink
                            '#64748b'  // Slate
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    cutout: '70%'
                }
            });
        }

        // 2. Sales vs Leads Bar/Line Chart (Only on Main Dashboard)
        if (!isMediaDashboard) {
            const leadsData = @json($leads_by_month ?? []);
            const salesData = @json($sales_by_month ?? []);
            
            const trendLabels = leadsData.map(d => d.month_name);
            const leadsCount = leadsData.map(d => d.count);
            
            const salesTotals = trendLabels.map(label => {
                const match = salesData.find(s => s.month_name === label);
                return match ? parseFloat(match.total) : 0;
            });

            const ctx1Element = document.getElementById('monthlyTrendChart');
            if (ctx1Element) {
                const ctx1 = ctx1Element.getContext('2d');
                
                // Create Premium Linear Gradients
                const orangeGradient = ctx1.createLinearGradient(0, 0, 0, 300);
                orangeGradient.addColorStop(0, 'rgba(254, 165, 0, 0.35)');
                orangeGradient.addColorStop(1, 'rgba(254, 165, 0, 0.02)');

                const emeraldGradient = ctx1.createLinearGradient(0, 0, 0, 300);
                emeraldGradient.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
                emeraldGradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

                new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: trendLabels,
                        datasets: [
                            {
                                label: 'Leads Count',
                                data: leadsCount,
                                backgroundColor: orangeGradient,
                                borderColor: '#FEA500',
                                borderWidth: 2,
                                borderRadius: 8,
                                yAxisID: 'y_leads',
                                barPercentage: 0.55
                            },
                            {
                                label: 'Sales Volume (₦)',
                                data: salesTotals,
                                type: 'line',
                                borderColor: '#10b981',
                                backgroundColor: emeraldGradient,
                                borderWidth: 3,
                                pointBackgroundColor: '#10b981',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1.5,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                fill: true,
                                tension: 0.35,
                                yAxisID: 'y_sales'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { font: { family: 'Plus Jakarta Sans', size: 12, weight: '600' }, color: '#475569' }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } } },
                            y_leads: { 
                                type: 'linear', 
                                position: 'left', 
                                title: { display: true, text: 'Leads Registered', font: { family: 'Plus Jakarta Sans', size: 12, weight: '700' } }, 
                                grid: { borderDash: [5, 5], drawBorder: false }, 
                                ticks: { stepSize: 1, font: { family: 'Plus Jakarta Sans' } } 
                            },
                            y_sales: { 
                                type: 'linear', 
                                position: 'right', 
                                title: { display: true, text: 'Revenue (₦)', font: { family: 'Plus Jakarta Sans', size: 12, weight: '700' } }, 
                                grid: { display: false } 
                            }
                        }
                    }
                });
            }
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCharts);
    } else {
        initCharts();
    }
})();
</script>

@endsection
