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

    {{-- 2. Dynamic Modular Top Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin())
                <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Executive Control Center</h1>
                <p class="text-sm text-gray-500 mt-1">Cross-department operations, real estate inventory, financial inflows, and workforce status.</p>
            @elseif(Auth::user()->hasPermission('leads.view_all') || Auth::user()->hasPermission('leads.view_own'))
                <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Sales &amp; Deals Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Real-time leads tracking, client inspections, pipeline tasks, and commission targets.</p>
            @elseif(Auth::user()->hasPermission('finance.view_ledger'))
                <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Financial &amp; Accounting Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Verified cash inflows, client payment audits, OPEX disbursements, and company P&amp;L.</p>
            @elseif(Auth::user()->hasPermission('hr.manage_users') || Auth::user()->hasPermission('hr.manage_targets') || Auth::user()->hasPermission('hr.approve_leaves'))
                <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Human Resources Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Staff directory, leave approvals, daily attendance KPI compliance, and onboarding pipeline.</p>
            @elseif(Auth::user()->hasPermission('media.manage_production') || Auth::user()->hasPermission('marketing.view') || Auth::user()->hasPermission('marketing.send_broadcast'))
                <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Media &amp; Marketing Operations</h1>
                <p class="text-sm text-gray-500 mt-1">Creative asset shoots, promotional campaigns, audience reach, and lead conversion channels.</p>
            @else
                <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Operations Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Real-time company metrics, project statuses, and task tracking.</p>
            @endif
        </div>
        <div class="flex space-x-3">
            @if(Auth::user()->hasPermission('leads.create'))
            <a href="{{ route('leads.index') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/10 hover:shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Add New Lead</span>
            </a>
            @elseif(Auth::user()->hasPermission('hr.manage_users'))
            <a href="{{ route('settings.index') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/10 hover:shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                <span>Add Staff Member</span>
            </a>
            @elseif(Auth::user()->hasPermission('finance.log_expenses'))
            <a href="{{ route('accounting.expenses.index') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/10 hover:shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Log New Expense</span>
            </a>
            @endif
        </div>
    </div>

    {{-- 3. Capability-Driven Dynamic Counter Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Finance & Accounting Counters --}}
        @if(!empty($finance_data) && (Auth::user()->hasPermission('finance.view_ledger') && !Auth::user()->hasPermission('leads.view_own') && !Auth::user()->hasPermission('leads.view_all')))
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Monthly Inflows</span>
                    <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">₦{{ number_format($finance_data['monthly_inflows'], 2) }}</h3>
                    <span class="text-xs text-emerald-600 block mt-1 font-semibold">Verified Client Inflows</span>
                </div>
                <span class="p-4 bg-emerald-50 text-emerald-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1M10 6h4a2 2 0 110 4h-4V6z"></path></svg>
                </span>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending POPs</span>
                    <h3 class="text-3xl font-extrabold {{ $finance_data['pending_pop_count'] > 0 ? 'text-amber-600' : 'text-dark-900' }} mt-1">{{ number_format($finance_data['pending_pop_count']) }}</h3>
                    <span class="text-xs text-amber-600 block mt-1 font-semibold">Payment Receipts to Audit</span>
                </div>
                <span class="p-4 bg-amber-50 text-amber-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending OPEX Claims</span>
                    <h3 class="text-3xl font-extrabold {{ $finance_data['pending_expenses_count'] > 0 ? 'text-rose-600' : 'text-dark-900' }} mt-1">{{ number_format($finance_data['pending_expenses_count']) }}</h3>
                    <span class="text-xs text-rose-600 block mt-1 font-semibold">Awaiting CFO Approval</span>
                </div>
                <span class="p-4 bg-rose-50 text-rose-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </span>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Net Operating Margin</span>
                    <h3 class="text-2xl font-extrabold {{ $finance_data['net_profit'] >= 0 ? 'text-blue-600' : 'text-rose-600' }} mt-1">₦{{ number_format($finance_data['net_profit'], 2) }}</h3>
                    <span class="text-xs text-gray-500 block mt-1">Inflows minus OPEX</span>
                </div>
                <span class="p-4 bg-blue-50 text-blue-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                </span>
            </div>

        {{-- HR Counters --}}
        @elseif(!empty($hr_data) && (Auth::user()->hasPermission('hr.manage_users') || Auth::user()->hasPermission('hr.manage_targets') || Auth::user()->hasPermission('hr.approve_leaves')) && !Auth::user()->hasPermission('leads.view_own') && !Auth::user()->hasPermission('leads.view_all') && !Auth::user()->hasPermission('finance.view_ledger'))
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Staff</span>
                    <h3 class="text-3xl font-extrabold text-dark-900 mt-1">{{ number_format($hr_data['total_staff']) }}</h3>
                    <span class="text-xs text-gray-500 block mt-1">Across all branches</span>
                </div>
                <span class="p-4 bg-purple-50 text-purple-600 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending Leaves</span>
                    <h3 class="text-3xl font-extrabold {{ $hr_data['pending_leaves_count'] > 0 ? 'text-amber-600' : 'text-dark-900' }} mt-1">{{ number_format($hr_data['pending_leaves_count']) }}</h3>
                    <span class="text-xs text-amber-600 block mt-1 font-semibold">Requires Approval</span>
                </div>
                <span class="p-4 bg-amber-50 text-amber-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Today's Submissions</span>
                    <h3 class="text-3xl font-extrabold text-emerald-600 mt-1">{{ number_format($hr_data['today_submissions_count']) }}</h3>
                    <span class="text-xs text-emerald-600 block mt-1 font-semibold">Daily KPI Logs Received</span>
                </div>
                <span class="p-4 bg-emerald-50 text-emerald-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending Onboardings</span>
                    <h3 class="text-3xl font-extrabold text-blue-600 mt-1">{{ number_format($hr_data['pending_onboardings_count']) }}</h3>
                    <span class="text-xs text-blue-600 block mt-1 font-semibold">Staff Setup Checklist</span>
                </div>
                <span class="p-4 bg-blue-50 text-blue-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </span>
            </div>

        {{-- Marketing & Media Counters --}}
        @elseif(!empty($marketing_data) && (Auth::user()->hasPermission('marketing.view') && !Auth::user()->hasPermission('leads.view_own')))
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Campaigns</span>
                    <h3 class="text-3xl font-extrabold text-dark-900 mt-1">{{ number_format($marketing_data['total_campaigns']) }}</h3>
                    <span class="text-xs text-gray-500 block mt-1">Created &amp; Dispatched</span>
                </div>
                <span class="p-4 bg-brand-50 text-brand-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </span>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Broadcasts</span>
                    <h3 class="text-3xl font-extrabold text-blue-600 mt-1">{{ number_format($marketing_data['total_emails_sent']) }}</h3>
                    <span class="text-xs text-blue-600 block mt-1 font-semibold">Emails &amp; SMS Sent</span>
                </div>
                <span class="p-4 bg-blue-50 text-blue-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </span>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Avg Open Rate</span>
                    <h3 class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $marketing_data['avg_open_rate'] }}%</h3>
                    <span class="text-xs text-emerald-600 block mt-1 font-semibold">Audience Engagement</span>
                </div>
                <span class="p-4 bg-emerald-50 text-emerald-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </span>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Stored Media Assets</span>
                    <h3 class="text-3xl font-extrabold text-amber-600 mt-1">{{ number_format($media_data['media_storage_count'] ?? 0) }}</h3>
                    <span class="text-xs text-amber-600 block mt-1 font-semibold">Photos &amp; Drone Shoots</span>
                </div>
                <span class="p-4 bg-amber-50 text-amber-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>

        {{-- Sales & General CRM Counters (Default) --}}
        @else
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Leads</span>
                    <h3 class="text-3xl font-extrabold text-dark-900 mt-1">{{ number_format($metrics['total_leads']) }}</h3>
                    <span class="text-xs text-gray-500 block mt-1">Assigned leads</span>
                </div>
                <span class="p-4 bg-brand-50 text-brand-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Follow Ups Due</span>
                    <h3 class="text-3xl font-extrabold text-dark-900 mt-1">{{ number_format($metrics['follow_ups_due']) }}</h3>
                    <span class="text-xs text-brand-600 block mt-1 font-semibold">Today &amp; Tomorrow</span>
                </div>
                <span class="p-4 bg-amber-50 text-amber-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Inspections</span>
                    <h3 class="text-3xl font-extrabold text-dark-900 mt-1">{{ number_format($metrics['scheduled_inspections']) }}</h3>
                    <span class="text-xs text-blue-600 block mt-1 font-semibold">Active Schedules</span>
                </div>
                <span class="p-4 bg-blue-50 text-blue-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    @if(Auth::user()->role === 'sales_executive')
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">My Closed Sales</span>
                        <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">₦{{ number_format($metrics['total_revenue'], 2) }}</h3>
                        @php
                            $myCommissions = \App\Models\Commission::where('user_id', Auth::id())->whereIn('status', ['approved', 'paid'])->sum('calculated_amount');
                        @endphp
                        <span class="text-xs text-brand-600 block mt-1 font-bold">₦{{ number_format($myCommissions, 2) }} Earned</span>
                    @else
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Revenue</span>
                        <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">₦{{ number_format($metrics['total_revenue'], 2) }}</h3>
                        <span class="text-xs text-emerald-600 block mt-1 font-semibold">{{ $metrics['closed_deals'] }} Deals Won ({{ $metrics['conversion_rate'] }}%)</span>
                    @endif
                </div>
                <span class="p-4 bg-emerald-50 text-emerald-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1M10 6h4a2 2 0 110 4h-4V6z"></path></svg>
                </span>
            </div>
        @endif
    </div>

    {{-- 4. Modular Visual Analytics & Department Desks --}}
    @if(!empty($hr_data) && (Auth::user()->hasPermission('hr.view_staff') && !Auth::user()->hasPermission('leads.view_own')))
        {{-- HR Department Distribution & Submission Desk --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-bold text-dark-900">Today's Staff Daily KPI Submissions</h3>
                        <p class="text-xs text-gray-500">Live work reports logged by team members today.</p>
                    </div>
                    <a href="{{ route('hr.submissions.review') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">Review All &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-4 py-3 font-bold text-gray-500">Staff Name</th>
                                <th class="px-4 py-3 font-bold text-gray-500">Department</th>
                                <th class="px-4 py-3 font-bold text-gray-500">Task Summary</th>
                                <th class="px-4 py-3 font-bold text-gray-500">Logged At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($hr_data['recent_submissions'] as $sub)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-3 font-bold text-dark-900">{{ $sub->user->name ?? 'Staff' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $sub->user->departmentRelation->name ?? $sub->user->department ?? 'General' }}</td>
                                <td class="px-4 py-3 text-gray-700 max-w-xs truncate">{{ $sub->summary_of_work ?: 'Logged daily metrics' }}</td>
                                <td class="px-4 py-3 text-gray-400">{{ $sub->created_at->format('h:i A') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-400">No staff submissions received yet today.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Department Headcount Distribution --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div>
                    <h3 class="text-lg font-bold text-dark-900">Workforce by Department</h3>
                    <p class="text-xs text-gray-500">Staff allocation across company units.</p>
                </div>
                <div class="space-y-3 pt-2">
                    @foreach($hr_data['dept_headcount'] as $dept)
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-dark-900">{{ $dept->department_name }}</span>
                            <span class="text-gray-500">{{ $dept->count }} Members</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $hr_data['total_staff'] > 0 ? min(100, ($dept->count / $hr_data['total_staff']) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    @elseif(!empty($finance_data) && (Auth::user()->hasPermission('finance.view_ledger') && !Auth::user()->hasPermission('leads.view_own')))
        {{-- Finance Audit & Expense Claims Desks --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Pending Proof of Payments --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-bold text-dark-900">Pending Proof of Payments (POP)</h3>
                        <p class="text-xs text-gray-500">Client bank transfer receipts needing audit.</p>
                    </div>
                </div>
                <div class="space-y-3">
                    @forelse($finance_data['pending_pops'] as $pop)
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-dark-900 text-sm">{{ $pop->sale->lead->full_name ?? 'Client' }}</h4>
                            <p class="text-xs text-gray-500">{{ $pop->sale->property->name ?? 'Estate Unit' }} &bull; Milestone #{{ $pop->milestone_number }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-extrabold text-emerald-600 text-sm">₦{{ number_format($pop->amount, 2) }}</span>
                            <span class="block text-[10px] text-amber-600 font-bold uppercase">Pending Audit</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-center text-gray-400 py-6">No unverified client payments waiting.</p>
                    @endforelse
                </div>
            </div>

            {{-- Pending Expense Approvals --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-bold text-dark-900">Pending OPEX Expense Claims</h3>
                        <p class="text-xs text-gray-500">Site diesel, marketing, and operational claims.</p>
                    </div>
                    <a href="{{ route('accounting.expenses.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">Open Expense Desk &rarr;</a>
                </div>
                <div class="space-y-3">
                    @forelse($finance_data['pending_expenses'] as $exp)
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-dark-900 text-sm">{{ $exp->title }}</h4>
                            <p class="text-xs text-gray-500">{{ $exp->category }} &bull; Logged by {{ $exp->user->name ?? 'Staff' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-extrabold text-rose-600 text-sm">₦{{ number_format($exp->amount, 2) }}</span>
                            <span class="block text-[10px] text-amber-600 font-bold uppercase">Pending Approval</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-center text-gray-400 py-6">All operational expense claims are reviewed.</p>
                    @endforelse
                </div>
            </div>
        </div>

    @else
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
    @endif

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
