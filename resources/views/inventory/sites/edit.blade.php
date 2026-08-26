@extends('layouts.app')

@section('title', 'Edit Site Store - ' . $site->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.sites.index') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 dark:text-slate-400">Inventory Sites</a>
                <span class="text-xs text-gray-400">/</span>
                <span class="text-xs font-semibold text-brand-600 dark:text-brand-400">Edit Site</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Edit Site: {{ $site->name }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.sites.show', $site) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-all">
                View Site
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300 text-sm">
            <p class="font-bold mb-1">Please fix the following errors:</p>
            <ul class="list-disc pl-5 space-y-0.5 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.sites.update', $site) }}" class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-slate-800 p-6 space-y-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Linked Project -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Target Project <span class="text-rose-500">*</span></label>
                <select name="project_id" required class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ old('project_id', $site->project_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->location }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Site Name -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Site Store Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $site->name) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Site Code -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Unique Code <span class="text-rose-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $site->code) }}" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white uppercase font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Geofence Radius -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Geofence Radius (Meters) <span class="text-rose-500">*</span></label>
                <input type="number" name="geofence_radius_meters" value="{{ old('geofence_radius_meters', $site->geofence_radius_meters) }}" min="10" max="5000" required
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- GPS Coordinates -->
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">GPS Latitude</label>
                <input type="number" step="0.0000001" name="gps_lat" value="{{ old('gps_lat', $site->gps_lat) }}"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">GPS Longitude</label>
                <input type="number" step="0.0000001" name="gps_lng" value="{{ old('gps_lng', $site->gps_lng) }}"
                       class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <!-- Physical Address -->
            <div class="md:col-span-2 space-y-1">
                <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-300">Physical Site Address</label>
                <textarea name="address" rows="3"
                          class="w-full py-2.5 px-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('address', $site->address) }}</textarea>
            </div>

            <!-- Active Status -->
            <div class="md:col-span-2 flex items-center gap-3 pt-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $site->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300 dark:border-slate-700">
                <label for="is_active" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    Active (Ready to receive and issue materials)
                </label>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100 dark:border-slate-800 flex justify-between items-center">
            <button type="button" onclick="if(confirm('Are you sure you want to delete this site?')) document.getElementById('delete-site-form').submit();"
                    class="text-xs font-bold text-rose-600 hover:text-rose-700">
                Delete Site
            </button>
            <div class="flex gap-3">
                <a href="{{ route('inventory.sites.show', $site) }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 text-slate-700 rounded-xl text-sm font-bold transition-all">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-sm shadow-brand-500/30 transition-all">
                    Save Changes
                </button>
            </div>
        </div>
    </form>

    <form id="delete-site-form" method="POST" action="{{ route('inventory.sites.destroy', $site) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
