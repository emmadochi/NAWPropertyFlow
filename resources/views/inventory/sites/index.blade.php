@extends('layouts.app')

@section('title', 'Construction Sites & Warehouses')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Dashboard</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Inventory Sites</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Construction Sites &amp; Warehouses</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manage project stock points, GPS coordinates, and delivery geofences.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.sites.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Add Site / Warehouse</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300 text-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-4">
        <form method="GET" action="{{ route('inventory.sites.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search site name, code or address..."
                       class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <div class="sm:col-span-4">
                <select name="project_id" class="w-full py-2 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">All Linked Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition-all">Filter</button>
                @if(request()->hasAny(['search', 'project_id']))
                    <a href="{{ route('inventory.sites.index') }}" class="py-2 px-3 bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-300 rounded-xl text-sm font-semibold">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Sites Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">
                <thead class="bg-gray-50 dark:bg-slate-800/60 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-gray-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Site Name &amp; Code</th>
                        <th class="py-3.5 px-4">Linked Project</th>
                        <th class="py-3.5 px-4">GPS Geofence</th>
                        <th class="py-3.5 px-4 text-center">Tracked SKUs</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($sites as $site)
                        <tr class="hover:bg-gray-50/75 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="p-1.5 rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400">
                                        🏗️
                                    </span>
                                    <div>
                                        <a href="{{ route('inventory.sites.show', $site) }}" class="hover:text-brand-600 hover:underline">
                                            {{ $site->name }}
                                        </a>
                                        <span class="block text-xs font-mono text-gray-400">{{ $site->code }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $site->project?->name ?? 'N/A' }}</span>
                                <span class="block text-xs text-gray-400">{{ $site->project?->location ?? '' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($site->gps_lat && $site->gps_lng)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 dark:text-slate-300">
                                        📍 {{ number_format($site->gps_lat, 4) }}, {{ number_format($site->gps_lng, 4) }}
                                    </span>
                                    <span class="block text-[11px] text-gray-400">Radius: {{ $site->geofence_radius_meters }}m</span>
                                @else
                                    <span class="text-xs text-amber-500 font-medium">⚠️ No GPS configured</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                    {{ $site->stock_count }} items
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($site->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 dark:bg-slate-800 dark:text-gray-400">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-2">
                                <a href="{{ route('inventory.sites.show', $site) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                    View
                                </a>
                                <a href="{{ route('inventory.sites.edit', $site) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-700 dark:bg-brand-950 dark:text-brand-300">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center">
                                    <span class="text-4xl mb-2">🏗️</span>
                                    <p class="font-bold text-slate-600 dark:text-slate-300">No construction sites found.</p>
                                    <p class="text-xs text-gray-400 mt-1">Get started by creating your first site storage facility.</p>
                                    <a href="{{ route('inventory.sites.create') }}" class="mt-4 px-4 py-2 bg-brand-600 text-white rounded-xl text-xs font-bold">Add New Site</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sites->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-slate-800">
                {{ $sites->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
