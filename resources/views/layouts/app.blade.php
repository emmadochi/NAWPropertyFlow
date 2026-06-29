<!DOCTYPE html>
<html lang="en" x-data="{ theme: localStorage.getItem('theme') || 'light' }" x-bind:class="theme === 'dark' ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NAW PropertyFlow CRM') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fffcf5',
                            100: '#fff5e0',
                            200: '#ffe6b3',
                            300: '#ffd080',
                            400: '#ffb54d',
                            500: '#FEA500', // Core orange accent
                            600: '#e09200',
                            700: '#b87700',
                            800: '#8f5c00',
                            900: '#664200'
                        },
                        dark: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-slate-900 text-dark-800 dark:text-slate-200 antialiased h-screen flex flex-col md:flex-row overflow-hidden transition-colors duration-200" x-data="{ mobileSidebarOpen: false }">

    <!-- Mobile Header -->
    <div class="md:hidden bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-800 px-4 py-3 flex items-center justify-between w-full z-20 transition-colors duration-200">
        <div class="flex items-center space-x-2">
            <span class="p-2 bg-brand-100 rounded-lg text-brand-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </span>
            <span class="font-extrabold text-xl tracking-tight text-dark-900 dark:text-white">NAW <span class="text-brand-500">PropertyFlow</span></span>
        </div>
        <button @click="mobileSidebarOpen = true" class="text-dark-600 dark:text-slate-300 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    @php
        $__companySetting = \App\Models\CompanySetting::first();
        $__tierLabels = ['starter' => 'Starter', 'professional' => 'Professional', 'enterprise' => 'Enterprise'];
        $__tierColors = [
            'starter'      => 'bg-gray-100 text-gray-600',
            'professional' => 'bg-blue-100 text-blue-700',
            'enterprise'   => 'bg-amber-100 text-amber-700',
        ];
        $__currentTier = $__companySetting?->package_tier ?? 'starter';
        $__tierLabel   = $__tierLabels[$__currentTier] ?? ucfirst($__currentTier);
        $__tierClass   = $__tierColors[$__currentTier] ?? 'bg-gray-100 text-gray-600';
    @endphp

    <!-- Sidebar Layout -->
    <aside class="fixed inset-y-0 left-0 z-30 w-64 bg-white dark:bg-slate-900 border-r border-gray-200 dark:border-slate-800 flex flex-col justify-between transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out md:static"
           :class="{'translate-x-0': mobileSidebarOpen, '-translate-x-full': !mobileSidebarOpen}">
        
        <div class="flex flex-col h-full">
            <!-- Sidebar Header / Logo -->
            <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 min-w-0">
                        @if($__companySetting?->logo_path)
                            <img src="{{ asset('storage/' . $__companySetting->logo_path) }}"
                                 alt="Company Logo"
                                 class="w-9 h-9 rounded-lg object-contain bg-gray-50 border border-gray-100 flex-shrink-0">
                        @else
                            <span class="w-9 h-9 flex-shrink-0 flex items-center justify-center bg-brand-100 rounded-lg text-brand-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </span>
                        @endif
                        <div class="min-w-0">
                            <p class="font-extrabold text-sm text-dark-900 dark:text-white leading-tight truncate">
                                {{ $__companySetting?->company_name ?? config('app.name') }}
                            </p>
                            <span class="inline-flex items-center mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $__tierClass }}">
                                {{ $__tierLabel }} Plan
                            </span>
                        </div>
                    </div>
                <button @click="mobileSidebarOpen = false" class="md:hidden text-gray-500 dark:text-slate-400 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            {{-- Cache company settings ONCE per sidebar render to avoid N+1 queries --}}
            @php $__cs = \App\Models\CompanySetting::first(); @endphp
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                @if(Auth::user()->role === 'customer')
                    <a href="{{ route('buyer.dashboard') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('buyer.dashboard') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>Buyer Dashboard</span>
                    </a>
                @else
                    {{-- 1. MAIN OVERVIEW --}}
                    <div class="mt-4 mb-1 text-xs font-bold text-gray-400 uppercase tracking-wider px-4">Overview</div>
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('dashboard') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr', 'sales_manager']) && $__cs?->hasFeature('leaderboard'))
                    <a href="{{ route('hr.leaderboard') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('hr.leaderboard') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>Sales Leaderboard</span>
                    </a>
                    @endif

                    {{-- 2. SALES & CLIENTS --}}
                    <div class="mt-4 mb-1 text-xs font-bold text-gray-400 uppercase tracking-wider px-4">Sales & Clients</div>
                    <a href="{{ route('leads.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('leads.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>Leads Pipeline</span>
                    </a>
                    <a href="{{ route('follow-ups.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('follow-ups.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Follow-Ups</span>
                    </a>
                    <a href="{{ route('inspections.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('inspections.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Inspections</span>
                    </a>

                    {{-- 3. INVENTORY --}}
                    <div class="mt-4 mb-1 text-xs font-bold text-gray-400 uppercase tracking-wider px-4">Inventory</div>
                    <a href="{{ route('properties.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('properties.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>Properties</span>
                    </a>
                    <a href="{{ route('projects.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('projects.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span>Projects</span>
                    </a>

                    {{-- 4. MARKETING (Professional+) --}}
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'sales_manager', 'media_manager']) && $__cs?->hasFeature('marketing'))
                    <div class="mt-4 mb-1 text-xs font-bold text-gray-400 uppercase tracking-wider px-4">Marketing</div>
                    <a href="{{ route('campaigns.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('campaigns.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>Campaigns</span>
                    </a>
                    <a href="{{ route('drip-sequences.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('drip-sequences.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                        <span>Drip Sequences</span>
                    </a>
                    @endif

                    {{-- 5. DOCUMENTS (Professional+) --}}
                    @if($__cs?->hasFeature('file_manager') || $__cs?->hasFeature('docs'))
                    <div class="mt-4 mb-1 text-xs font-bold text-gray-400 uppercase tracking-wider px-4">Documents</div>
                    @if($__cs?->hasFeature('file_manager'))
                    <a href="{{ route('file-storage.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('file-storage.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                        <span>File Manager</span>
                    </a>
                    @endif
                    @if($__cs?->hasFeature('docs'))
                    <a href="{{ route('generated-documents.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('generated-documents.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Generated Docs</span>
                    </a>
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin']))
                    <a href="{{ route('document-templates.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('document-templates.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Doc Templates</span>
                    </a>
                    @endif
                    @endif
                    @endif

                    {{-- 6. ANALYTICS (Enterprise) --}}
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr', 'sales_manager', 'media_manager', 'project_manager']) && $__cs?->hasFeature('advanced_reports'))
                    <div class="mt-4 mb-1 text-xs font-bold text-gray-400 uppercase tracking-wider px-4">Analytics</div>
                    <a href="{{ route('reports.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('reports.index') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span>Analytics Reports</span>
                    </a>
                    <a href="{{ route('reports.departments.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('reports.departments.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                        </svg>
                        <span>Department Reports</span>
                    </a>
                    @endif

                    {{-- 7. HR & PERFORMANCE (Enterprise) --}}
                    @if($__cs?->hasFeature('hr'))
                    <div class="mt-4 mb-1 text-xs font-bold text-gray-400 uppercase tracking-wider px-4">HR & Performance</div>
                    <a href="{{ route('hr.submissions.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('hr.submissions.index') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span>My KPI Logs</span>
                    </a>
                    <a href="{{ route('hr.leave.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('hr.leave.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>Leave Requests</span>
                    </a>
                    @if(Auth::check() && (Auth::user()->hasRole(['super_admin', 'company_admin', 'hr']) || Auth::user()->managedDepartments()->where('is_active', true)->exists()))
                    <a href="{{ route('hr.submissions.review') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('hr.submissions.review') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Submissions Review</span>
                    </a>
                    <a href="{{ route('hr.department-targets.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('hr.department-targets.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span>Department Targets</span>
                    </a>
                    @endif
                    @endif

                    {{-- 8. ADMINISTRATION --}}
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr']))
                    <div class="mt-4 mb-1 text-xs font-bold text-gray-400 uppercase tracking-wider px-4">Administration</div>

                    {{-- Team Settings: all tiers (company_admin / hr only) --}}
                    <a href="{{ route('settings.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('settings.index') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>Team Settings</span>
                    </a>

                    {{-- Department Setup: Professional+ --}}
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin']) && $__cs?->hasFeature('department_setup'))
                    <a href="{{ route('departments.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('departments.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span>Department Setup</span>
                    </a>
                    @endif

                    {{-- Branch Settings: Professional+ (multi_branch) --}}
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin']) && $__cs?->hasFeature('multi_branch'))
                    <a href="{{ route('branches.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('branches.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span>Branch Settings</span>
                    </a>
                    @endif

                    {{-- Activity Logs: Enterprise only --}}
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin']) && $__cs?->hasFeature('activity_logs'))
                    <a href="{{ route('settings.activity-logs.index') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('settings.activity-logs.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Activity Logs</span>
                    </a>
                    @endif

                    {{-- Company Settings: all tiers (company_admin only) --}}
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin']))
                    <a href="{{ route('settings.company.edit') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('settings.company.*') ? 'bg-brand-50 text-brand-600 border border-brand-100 dark:bg-slate-800 dark:text-brand-400 dark:border-brand-500/30' : 'text-gray-600 hover:bg-gray-50 hover:text-dark-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white border border-transparent dark:border-transparent' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span>Company Settings</span>
                    </a>
                    @endif

                    @endif {{-- end administration role gate --}}
                @endif {{-- end non-customer gate --}}
            </nav>

            <!-- User Bio Panel & Logout -->
            <div class="p-4 border-t border-gray-100 dark:border-slate-800">
                <div class="bg-gray-50 dark:bg-slate-800/50 rounded-xl p-3 flex items-center justify-between border border-gray-100 dark:border-slate-700/50">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <div class="w-10 h-10 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold flex-shrink-0 text-sm shadow-sm">
                            {{ substr(Auth::user()->name, 0, 2) }}
                        </div>
                        <div class="truncate pr-2">
                            <h4 class="text-sm font-semibold text-dark-900 dark:text-white truncate leading-none mb-1">{{ Auth::user()->name }}</h4>
                            <span class="text-xs text-gray-500 dark:text-slate-400 uppercase font-semibold tracking-wider">{{ str_replace('_', ' ', Auth::user()->role) }}</span>
                        </div>
                    </div>
                    
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-400 dark:text-slate-400 hover:text-rose-500 transition-colors p-1.5 rounded-lg hover:bg-white dark:hover:bg-slate-700" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Overlay backdrops on mobile -->
    <div x-cloak x-show="mobileSidebarOpen" @click="mobileSidebarOpen = false" class="fixed inset-0 bg-dark-900/40 z-20 md:hidden transition-opacity"></div>

    <!-- Main View Workspace Area -->
    <main class="flex-1 flex flex-col overflow-hidden relative">
        
        <!-- Flash Message Banners -->
        <div class="absolute top-4 right-4 z-50 flex flex-col space-y-2 pointer-events-none" x-data="{ showSuccess: true, showError: true }" x-init="setTimeout(() => { showSuccess = false; showError = false; }, 5000)">
            @if(session('success'))
            <div x-show="showSuccess" class="bg-emerald-600 text-white px-5 py-3 rounded-xl shadow-xl flex items-center space-x-3 pointer-events-auto border border-emerald-500 transition-opacity" x-transition>
                <svg class="w-5 h-5 text-emerald-100 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div x-show="showError" class="bg-rose-600 text-white px-5 py-3 rounded-xl shadow-xl flex items-center space-x-3 pointer-events-auto border border-rose-500 transition-opacity" x-transition>
                <svg class="w-5 h-5 text-rose-100 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div x-show="showError" class="bg-rose-600 text-white px-5 py-3 rounded-xl shadow-xl pointer-events-auto border border-rose-500 transition-opacity" x-transition>
                <div class="flex items-center space-x-3 mb-1">
                    <svg class="w-5 h-5 text-rose-100 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-semibold">Validation Errors:</span>
                </div>
                <ul class="list-disc list-inside text-xs text-rose-100 space-y-0.5 ml-8">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <!-- Active Branch Header Bar -->
        @if(Auth::check())
            @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin())
                @php
                    $allBranchesForSelector = \App\Models\Branch::orderBy('name', 'asc')->get();
                    $currentSelectedBranchId = session('selected_branch_id', 'all');
                @endphp
                <div class="bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-800 px-6 py-4 flex items-center justify-between flex-shrink-0 transition-colors duration-200">
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Active Scope:</span>
                        <form action="" method="GET" class="inline" id="branch-scope-form">
                            <select name="switch_branch_id" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 text-gray-800 rounded-xl px-3 py-1.5 text-xs font-bold focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none cursor-pointer">
                                <option value="all" {{ $currentSelectedBranchId === 'all' ? 'selected' : '' }}>All Branches (Global)</option>
                                @foreach($allBranchesForSelector as $br)
                                    <option value="{{ $br->id }}" {{ $currentSelectedBranchId == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="flex items-center space-x-4">
                        @if($currentSelectedBranchId !== 'all')
                            @php $activeBr = $allBranchesForSelector->firstWhere('id', $currentSelectedBranchId); @endphp
                            @if($activeBr)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-100 text-brand-800">
                                    {{ $activeBr->city }}
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                Corporate Head Office
                            </span>
                        @endif

                        <!-- Global Search Shortcut Button -->
                        <button @click="$dispatch('open-global-search')" class="hidden sm:inline-flex items-center space-x-2 text-left bg-gray-50 border border-gray-200 hover:border-gray-300 text-gray-400 hover:text-gray-600 rounded-xl px-3 py-1.5 text-xs transition-colors cursor-pointer focus:outline-none">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <span class="font-medium">Search...</span>
                            <kbd class="bg-white border border-gray-200 text-[10px] font-semibold text-gray-400 rounded px-1.5 py-0.5 ml-2 font-sans">Ctrl K</kbd>
                        </button>
                        <button @click="$dispatch('open-global-search')" class="sm:hidden p-1 text-gray-400 hover:text-dark-700 transition-colors focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>

                        <!-- Dark Mode Toggle -->
                        <button @click="theme = (theme === 'dark' ? 'light' : 'dark'); localStorage.setItem('theme', theme)" 
                                class="p-1.5 text-gray-400 dark:text-slate-400 hover:text-brand-500 dark:hover:text-brand-400 transition-colors rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 focus:outline-none" 
                                title="Toggle Theme">
                            <!-- Moon icon for light mode -->
                            <svg x-show="theme !== 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                            <!-- Sun icon for dark mode -->
                            <svg x-cloak x-show="theme === 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </button>

                        <!-- Automated Notifications Bell Dropdown -->
                        <div class="relative" x-data="headerNotifications()" x-init="fetchNotifications()" @open-global-search.window="open = false" @spa-load-complete.window="fetchNotifications()">
                            <button @click="open = !open" class="relative p-1.5 text-gray-400 hover:text-dark-700 transition-colors focus:outline-none cursor-pointer rounded-lg hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <span x-show="unreadCount > 0" class="absolute top-1 right-1 block h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white" x-cloak></span>
                            </button>
                            
                            <!-- Dropdown Panel -->
                            <div x-show="open" 
                                 @click.away="open = false" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 bg-white border border-gray-100 rounded-2xl shadow-xl z-40 py-2 overflow-hidden"
                                 x-cloak>
                                
                                <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                                    <span class="text-xs font-bold text-dark-900">Notifications</span>
                                    <span x-show="unreadCount > 0" class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-50 text-rose-600" x-text="unreadCount + ' due'"></span>
                                </div>

                                <div class="max-h-64 overflow-y-auto divide-y divide-gray-50">
                                    <template x-for="alert in alerts" :key="alert.id">
                                        <a :href="alert.url" @click="open = false" class="flex items-start p-3 hover:bg-gray-50 transition-colors group">
                                            <span class="p-2 rounded-xl mr-3 flex-shrink-0" 
                                                  :class="{
                                                      'bg-orange-50 text-orange-500': alert.type === 'followup',
                                                      'bg-blue-50 text-blue-500': alert.type === 'inspection',
                                                      'bg-emerald-50 text-emerald-500': alert.type === 'lead'
                                                  }">
                                                <template x-if="alert.type === 'followup'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </template>
                                                <template x-if="alert.type === 'inspection'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    </svg>
                                                </template>
                                                <template x-if="alert.type === 'lead'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                                    </svg>
                                                </template>
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <h5 class="text-xs font-bold text-dark-900 group-hover:text-brand-600 transition-colors" x-text="alert.title"></h5>
                                                <p class="text-[11px] text-gray-500 mt-0.5 leading-relaxed line-clamp-2" x-text="alert.description"></p>
                                                <span class="text-[9px] text-gray-400 block mt-1" x-text="alert.time"></span>
                                            </div>
                                        </a>
                                    </template>
                                    
                                    <template x-if="alerts.length === 0">
                                        <div class="px-4 py-8 text-center text-xs text-gray-400">
                                            All caught up! No pending alerts.
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif(Auth::user()->branch)
                <div class="bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-800 px-6 py-4 flex items-center justify-between flex-shrink-0 transition-colors duration-200">
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Branch:</span>
                        <span class="text-sm font-bold text-dark-900">{{ Auth::user()->branch->name }}</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-100 text-brand-800">
                            {{ Auth::user()->branch->city }}
                        </span>
                        
                        <!-- Global Search Shortcut Button -->
                        <button @click="$dispatch('open-global-search')" class="hidden sm:inline-flex items-center space-x-2 text-left bg-gray-50 border border-gray-200 hover:border-gray-300 text-gray-400 hover:text-gray-600 rounded-xl px-3 py-1.5 text-xs transition-colors cursor-pointer focus:outline-none">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <span class="font-medium">Search...</span>
                            <kbd class="bg-white border border-gray-200 text-[10px] font-semibold text-gray-400 rounded px-1.5 py-0.5 ml-2 font-sans">Ctrl K</kbd>
                        </button>
                        <button @click="$dispatch('open-global-search')" class="sm:hidden p-1 text-gray-400 hover:text-dark-700 transition-colors focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>

                        <!-- Dark Mode Toggle -->
                        <button @click="theme = (theme === 'dark' ? 'light' : 'dark'); localStorage.setItem('theme', theme)" 
                                class="p-1.5 text-gray-400 dark:text-slate-400 hover:text-brand-500 dark:hover:text-brand-400 transition-colors rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 focus:outline-none" 
                                title="Toggle Theme">
                            <!-- Moon icon for light mode -->
                            <svg x-show="theme !== 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                            </svg>
                            <!-- Sun icon for dark mode -->
                            <svg x-cloak x-show="theme === 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </button>

                        <!-- Automated Notifications Bell Dropdown -->
                        <div class="relative" x-data="headerNotifications()" x-init="fetchNotifications()" @open-global-search.window="open = false" @spa-load-complete.window="fetchNotifications()">
                            <button @click="open = !open" class="relative p-1.5 text-gray-400 hover:text-dark-700 transition-colors focus:outline-none cursor-pointer rounded-lg hover:bg-gray-50">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <span x-show="unreadCount > 0" class="absolute top-1 right-1 block h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white" x-cloak></span>
                            </button>
                            
                            <!-- Dropdown Panel -->
                            <div x-show="open" 
                                 @click.away="open = false" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 bg-white border border-gray-100 rounded-2xl shadow-xl z-40 py-2 overflow-hidden"
                                 x-cloak>
                                
                                <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                                    <span class="text-xs font-bold text-dark-900">Notifications</span>
                                    <span x-show="unreadCount > 0" class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-50 text-rose-600" x-text="unreadCount + ' due'"></span>
                                </div>

                                <div class="max-h-64 overflow-y-auto divide-y divide-gray-50">
                                    <template x-for="alert in alerts" :key="alert.id">
                                        <a :href="alert.url" @click="open = false" class="flex items-start p-3 hover:bg-gray-50 transition-colors group">
                                            <span class="p-2 rounded-xl mr-3 flex-shrink-0" 
                                                  :class="{
                                                      'bg-orange-50 text-orange-500': alert.type === 'followup',
                                                      'bg-blue-50 text-blue-500': alert.type === 'inspection',
                                                      'bg-emerald-50 text-emerald-500': alert.type === 'lead'
                                                  }">
                                                <template x-if="alert.type === 'followup'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </template>
                                                <template x-if="alert.type === 'inspection'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    </svg>
                                                </template>
                                                <template x-if="alert.type === 'lead'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                                    </svg>
                                                </template>
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <h5 class="text-xs font-bold text-dark-900 group-hover:text-brand-600 transition-colors" x-text="alert.title"></h5>
                                                <p class="text-[11px] text-gray-500 mt-0.5 leading-relaxed line-clamp-2" x-text="alert.description"></p>
                                                <span class="text-[9px] text-gray-400 block mt-1" x-text="alert.time"></span>
                                            </div>
                                        </a>
                                    </template>
                                    
                                    <template x-if="alerts.length === 0">
                                        <div class="px-4 py-8 text-center text-xs text-gray-400">
                                            All caught up! No pending alerts.
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Inner Content Window Container -->
        <div class="flex-1 overflow-y-auto px-6 py-8 md:px-8">
            @yield('content')
        </div>

    </main>

    <!-- SPA Router Script -->
    <script id="spa-main-script">
        document.addEventListener('DOMContentLoaded', () => {
            // SPA Loader Progress Bar
            const createProgressBar = () => {
                let bar = document.getElementById('spa-progress-bar');
                if (!bar) {
                    bar = document.createElement('div');
                    bar.id = 'spa-progress-bar';
                    bar.style.position = 'fixed';
                    bar.style.top = '0';
                    bar.style.left = '0';
                    bar.style.height = '3px';
                    bar.style.backgroundColor = '#FEA500'; // brand core orange accent
                    bar.style.zIndex = '9999';
                    bar.style.transition = 'width 0.2s ease, opacity 0.4s ease';
                    bar.style.width = '0';
                    document.body.appendChild(bar);
                }
                return bar;
            };

            const showProgress = () => {
                const bar = createProgressBar();
                bar.style.opacity = '1';
                bar.style.width = '30%';
                setTimeout(() => { bar.style.width = '70%'; }, 200);
            };

            const hideProgress = () => {
                const bar = createProgressBar();
                bar.style.width = '100%';
                setTimeout(() => {
                    bar.style.opacity = '0';
                    setTimeout(() => { bar.style.width = '0'; }, 400);
                }, 100);
            };

            // Execute script elements in target container sequentially to preserve dependency loading order
            async function executeScripts(container, doc) {
                // Execute inline or nested script elements
                const scripts = Array.from(container.querySelectorAll('script'));
                for (const script of scripts) {
                    await new Promise((resolve) => {
                        const newScript = document.createElement('script');
                        Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        if (script.src) {
                            newScript.onload = () => resolve();
                            newScript.onerror = () => resolve();
                            document.head.appendChild(newScript);
                        } else {
                            newScript.appendChild(document.createTextNode(script.innerHTML));
                            document.body.appendChild(newScript);
                            newScript.parentNode.removeChild(newScript);
                            resolve();
                        }
                    });
                }

                // Also execute scripts that were pushed to bottom of body in fetched HTML
                const bodyScripts = Array.from(doc.body.querySelectorAll('script'));
                for (const script of bodyScripts) {
                    if (!script.closest('main') && !script.closest('aside') && script.id !== 'spa-main-script') {
                        await new Promise((resolve) => {
                            const newScript = document.createElement('script');
                            Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                            if (script.src) {
                                newScript.onload = () => resolve();
                                newScript.onerror = () => resolve();
                                document.head.appendChild(newScript);
                            } else {
                                newScript.appendChild(document.createTextNode(script.innerHTML));
                                document.body.appendChild(newScript);
                                newScript.parentNode.removeChild(newScript);
                                resolve();
                            }
                        });
                    }
                }
            }

            // Load page via AJAX
            async function loadPage(url, pushState = true) {
                showProgress();
                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        window.location.href = url;
                        return;
                    }

                    // Handle file downloads/previews
                    const contentType = response.headers.get('content-type') || '';
                    const disposition = response.headers.get('content-disposition') || '';
                    if (!contentType.includes('text/html') || disposition.includes('attachment')) {
                        hideProgress();
                        // Trigger standard download behavior
                        const blob = await response.blob();
                        const downloadUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = downloadUrl;
                        let filename = 'download';
                        const filenameMatch = disposition.match(/filename="?([^"]+)"?/);
                        if (filenameMatch && filenameMatch[1]) {
                            filename = filenameMatch[1];
                        }
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(downloadUrl);
                        return;
                    }

                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Swap main content
                    const currentMain = document.querySelector('main');
                    const newMain = doc.querySelector('main');
                    if (currentMain && newMain) {
                        currentMain.innerHTML = newMain.innerHTML;
                    }

                    // Swap sidebar nav links to update active state
                    const currentNav = document.querySelector('nav');
                    const newNav = doc.querySelector('nav');
                    if (currentNav && newNav) {
                        currentNav.innerHTML = newNav.innerHTML;
                    }

                    // Update page Title
                    document.title = doc.title;

                    // Execute script nodes
                    if (newMain) {
                        await executeScripts(newMain, doc);
                    }

                    // Re-init Alpine JS
                    if (window.Alpine) {
                        if (typeof window.Alpine.initTree === 'function') {
                            window.Alpine.initTree(currentMain);
                        } else {
                            document.dispatchEvent(new Event('DOMContentLoaded'));
                        }
                    }

                    // Dispatch custom load complete event to reload headers/alerts
                    window.dispatchEvent(new CustomEvent('spa-load-complete'));

                    if (pushState) {
                        window.history.pushState({ url }, '', url);
                    }

                    // Scroll back to top
                    const contentWindow = document.querySelector('.overflow-y-auto');
                    if (contentWindow) contentWindow.scrollTop = 0;

                } catch (e) {
                    console.error('SPA Load Error:', e);
                    window.location.href = url;
                } finally {
                    hideProgress();
                }
            }

            // Intercept internal link clicks
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link) return;

                if (link.getAttribute('target') === '_blank') return;
                if (link.hasAttribute('data-no-spa')) return;
                if (link.getAttribute('download') !== null) return;

                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

                try {
                    const urlObj = new URL(href, window.location.origin);
                    if (urlObj.origin !== window.location.origin) return;

                    // Skip logout, file preview/download endpoints
                    if (href.includes('/logout') || href.includes('/download') || href.includes('/preview')) return;

                    e.preventDefault();
                    loadPage(urlObj.href);
                } catch (err) {
                    // Ignore URL parse failures and perform default browser action
                }
            });

            // Hook programmatic Form submissions (e.g. branch selectors or executive assignment onchange triggers)
            const originalSubmit = HTMLFormElement.prototype.submit;
            HTMLFormElement.prototype.submit = function() {
                const event = new Event('submit', { bubbles: true, cancelable: true });
                this.dispatchEvent(event);
                if (!event.defaultPrevented) {
                    originalSubmit.call(this);
                }
            };

            // Intercept form submissions
            document.addEventListener('submit', async (e) => {
                const form = e.target;
                if (form.hasAttribute('data-no-spa')) return;
                if (form.getAttribute('action') && form.getAttribute('action').includes('/logout')) return;

                e.preventDefault();
                showProgress();

                const method = (form.getAttribute('method') || 'GET').toUpperCase();
                let action = form.getAttribute('action') || window.location.href;

                try {
                    const formData = new FormData(form);
                    let options = {
                        method,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    };

                    if (method === 'GET') {
                        const params = new URLSearchParams(formData).toString();
                        action = action.includes('?') ? `${action}&${params}` : `${action}?${params}`;
                    } else {
                        options.body = formData;
                    }

                    const response = await fetch(action, options);
                    if (response.redirected) {
                        await loadPage(response.url);
                    } else {
                        // In case of inline validation page returns
                        const html = await response.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        const currentMain = document.querySelector('main');
                        const newMain = doc.querySelector('main');
                        if (currentMain && newMain) {
                            currentMain.innerHTML = newMain.innerHTML;
                        }

                        const currentNav = document.querySelector('nav');
                        const newNav = doc.querySelector('nav');
                        if (currentNav && newNav) {
                            currentNav.innerHTML = newNav.innerHTML;
                        }

                        document.title = doc.title;

                        if (newMain) {
                            await executeScripts(newMain, doc);
                        }

                        if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                            window.Alpine.initTree(currentMain);
                        }
                    }
                } catch (err) {
                    console.error('SPA Submit Error:', err);
                    originalSubmit.call(form); // Perform standard hard submit
                } finally {
                    hideProgress();
                }
            });

            // Intercept dynamic switches/selectors with onchange submits
            document.addEventListener('change', (e) => {
                if (e.target.name === 'switch_branch_id') {
                    const form = e.target.form;
                    if (form) {
                        e.preventDefault();
                        const val = e.target.value;
                        const url = new URL(window.location.href);
                        url.searchParams.set('switch_branch_id', val);
                        loadPage(url.href);
                    }
                }
            });

            // Handle browser back/forward popstate
            window.addEventListener('popstate', (e) => {
                if (e.state && e.state.url) {
                    loadPage(e.state.url, false);
                } else {
                    loadPage(window.location.href, false);
                }
            });
        });
    </script>

    <!-- Global Command Palette Modal -->
    <div x-cloak 
         x-data="globalSearchPalette()" 
         @open-global-search.window="openModal()" 
         @keydown.window.prevent.cmd.k="openModal()" 
         @keydown.window.prevent.ctrl.k="openModal()"
         x-show="open" 
         class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20 flex items-start justify-center"
         role="dialog" 
         aria-modal="true">
         
        <!-- Backdrop -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-dark-900/40 backdrop-blur-sm transition-opacity" 
             @click="closeModal()"></div>

        <!-- Modal Box -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="mx-auto max-w-xl w-full transform divide-y divide-gray-100 overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 transition-all mt-10 z-10 border border-gray-100"
             @click.away="closeModal()">
            
            <!-- Search Input Box -->
            <div class="relative flex items-center px-4 py-3.5">
                <svg class="pointer-events-none h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" 
                       x-model="query" 
                       @input.debounce.300ms="performSearch()"
                       class="h-11 w-full border-0 bg-transparent text-dark-900 placeholder:text-gray-400 focus:ring-0 focus:outline-none text-sm" 
                       placeholder="Search leads, properties, projects or files..." 
                       x-ref="searchInput"
                       @keydown.escape="closeModal()">
                <button @click="closeModal()" class="text-xs font-semibold text-gray-400 hover:text-dark-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 px-2 py-1 rounded-lg transition-colors">ESC</button>
            </div>

            <!-- Loading Spinner -->
            <div x-show="isLoading" class="p-6 text-center" x-cloak>
                <svg class="animate-spin h-6 w-6 text-brand-500 mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <!-- Initial Empty State -->
            <div x-show="query === ''" class="px-6 py-14 text-center text-sm sm:px-14">
                <svg class="mx-auto h-8 w-8 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <p class="font-semibold text-dark-800">Quick Command Palette</p>
                <p class="text-gray-500 mt-1 text-xs">Type at least 2 characters to search across Leads, Properties, Projects and Documents.</p>
            </div>

            <!-- No Results State -->
            <div x-show="query !== '' && !isLoading && isEmpty()" class="px-6 py-14 text-center text-sm sm:px-14" x-cloak>
                <svg class="mx-auto h-8 w-8 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="font-semibold text-dark-800">No results found</p>
                <p class="text-gray-500 mt-1 text-xs">We couldn't find anything matching "<span x-text="query"></span>". Please try a different query.</p>
            </div>

            <!-- Search Results -->
            <div x-show="query !== '' && !isLoading && !isEmpty()" class="max-h-96 overflow-y-auto p-4 space-y-4" x-cloak>
                
                <!-- Leads Category -->
                <template x-if="results.leads.length > 0">
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-2">Leads</h4>
                        <div class="space-y-1">
                            <template x-for="lead in results.leads" :key="'l-' + lead.id">
                                <a :href="lead.url" @click="closeModal()" class="flex items-center justify-between px-3 py-2 hover:bg-brand-50 hover:text-brand-900 rounded-xl transition-all group">
                                    <div class="flex items-center space-x-3 overflow-hidden">
                                        <span class="p-1.5 bg-brand-100 text-brand-600 rounded-lg group-hover:bg-white group-hover:text-brand-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </span>
                                        <span class="text-sm font-semibold text-dark-800 group-hover:text-brand-900 truncate" x-text="lead.title"></span>
                                    </div>
                                    <span class="text-xs text-gray-500 truncate max-w-[180px]" x-text="lead.subtitle"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Properties Category -->
                <template x-if="results.properties.length > 0">
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-2">Properties</h4>
                        <div class="space-y-1">
                            <template x-for="prop in results.properties" :key="'pr-' + prop.id">
                                <a :href="prop.url" @click="closeModal()" class="flex items-center justify-between px-3 py-2 hover:bg-emerald-50 hover:text-emerald-900 rounded-xl transition-all group">
                                    <div class="flex items-center space-x-3 overflow-hidden">
                                        <span class="p-1.5 bg-emerald-100 text-emerald-600 rounded-lg group-hover:bg-white group-hover:text-emerald-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                            </svg>
                                        </span>
                                        <span class="text-sm font-semibold text-dark-800 group-hover:text-emerald-900 truncate" x-text="prop.title"></span>
                                    </div>
                                    <span class="text-xs text-gray-500 truncate max-w-[180px]" x-text="prop.subtitle"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Projects Category -->
                <template x-if="results.projects.length > 0">
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-2">Projects</h4>
                        <div class="space-y-1">
                            <template x-for="proj in results.projects" :key="'p-' + proj.id">
                                <a :href="proj.url" @click="closeModal()" class="flex items-center justify-between px-3 py-2 hover:bg-blue-50 hover:text-blue-900 rounded-xl transition-all group">
                                    <div class="flex items-center space-x-3 overflow-hidden">
                                        <span class="p-1.5 bg-blue-100 text-blue-600 rounded-lg group-hover:bg-white group-hover:text-blue-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </span>
                                        <span class="text-sm font-semibold text-dark-800 group-hover:text-blue-900 truncate" x-text="proj.title"></span>
                                    </div>
                                    <span class="text-xs text-gray-500 truncate max-w-[180px]" x-text="proj.subtitle"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Files Category -->
                <template x-if="results.files.length > 0">
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-2">Files</h4>
                        <div class="space-y-1">
                            <template x-for="file in results.files" :key="'f-' + file.id">
                                <a :href="file.url" @click="closeModal()" class="flex items-center justify-between px-3 py-2 hover:bg-purple-50 hover:text-purple-900 rounded-xl transition-all group">
                                    <div class="flex items-center space-x-3 overflow-hidden">
                                        <span class="p-1.5 bg-purple-100 text-purple-600 rounded-lg group-hover:bg-white group-hover:text-purple-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </span>
                                        <span class="text-sm font-semibold text-dark-800 group-hover:text-purple-900 truncate" x-text="file.title"></span>
                                    </div>
                                    <span class="text-xs text-gray-500 truncate max-w-[180px]" x-text="file.subtitle"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>

            </div>

        </div>
    </div>

    <!-- Alpine globalSearchPalette definitions -->
    <script>
        function globalSearchPalette() {
            return {
                open: false,
                query: '',
                isLoading: false,
                results: {
                    leads: [],
                    properties: [],
                    projects: [],
                    files: []
                },

                openModal() {
                    this.query = '';
                    this.results = { leads: [], properties: [], projects: [], files: [] };
                    this.open = true;
                    // Focus input field
                    this.$nextTick(() => {
                        this.$refs.searchInput.focus();
                    });
                },

                closeModal() {
                    this.open = false;
                },

                isEmpty() {
                    return this.results.leads.length === 0 &&
                           this.results.properties.length === 0 &&
                           this.results.projects.length === 0 &&
                           this.results.files.length === 0;
                },

                async performSearch() {
                    const q = this.query.trim();
                    if (q.length < 2) {
                        this.results = { leads: [], properties: [], projects: [], files: [] };
                        return;
                    }

                    this.isLoading = true;
                    try {
                        const response = await fetch(`/api/global-search?query=${encodeURIComponent(q)}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();
                        this.results = data;
                    } catch (e) {
                        console.error('Palette search error:', e);
                    } finally {
                        this.isLoading = false;
                    }
                }
            }
        }

        function headerNotifications() {
            return {
                open: false,
                unreadCount: 0,
                alerts: [],

                async fetchNotifications() {
                    try {
                        const response = await fetch('/api/notifications', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();
                        this.unreadCount = data.unread_count;
                        this.alerts = data.alerts;
                    } catch (e) {
                        console.error('Failed to fetch notifications:', e);
                    }
                }
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
