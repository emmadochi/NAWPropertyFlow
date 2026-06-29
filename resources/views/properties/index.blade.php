@extends('layouts.app')

@section('content')
<div class="space-y-8" x-data="{ addPropertyOpen: false }">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Properties Portfolios</h1>
            <p class="text-sm text-gray-500 mt-1">Manage estates, layouts, and units availability metrics.</p>
        </div>
        @if(Auth::user()->role !== 'sales_executive')
        <div>
            <button @click="addPropertyOpen = true" class="inline-flex items-center space-x-2 px-5 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/10 hover:shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Add Property Portfolio</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Filter / Quick Actions Bar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-150 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form action="{{ route('properties.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <select name="type" class="px-3 py-2 border rounded-xl text-xs bg-white text-gray-700">
                <option value="">-- All Types --</option>
                <option value="Duplex" {{ request('type') === 'Duplex' ? 'selected' : '' }}>Duplex</option>
                <option value="Terrace" {{ request('type') === 'Terrace' ? 'selected' : '' }}>Terrace</option>
                <option value="Flat" {{ request('type') === 'Flat' ? 'selected' : '' }}>Flat</option>
                <option value="Land" {{ request('type') === 'Land' ? 'selected' : '' }}>Land</option>
            </select>
            <input type="text" name="location" value="{{ request('location') }}" placeholder="Filter by location..." class="px-3 py-2 border rounded-xl text-xs bg-white text-gray-700">
            <button type="submit" class="px-4 py-2 bg-brand-50 hover:bg-brand-100 text-brand-600 font-bold text-xs rounded-xl border border-brand-100 transition-all">
                Apply Filters
            </button>
            @if(request()->anyFilled(['type', 'location']))
            <a href="{{ route('properties.index') }}" class="text-xs text-gray-500 hover:text-gray-700 font-medium">Clear Filters</a>
            @endif
        </form>
    </div>

    <!-- Properties Gallery Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($properties as $property)
        <div class="bg-white rounded-3xl border border-gray-150 shadow-sm overflow-hidden flex flex-col hover:border-gray-300 hover:shadow-md transition-all duration-300">
            <!-- Property Cover Image -->
            <div class="h-48 bg-gray-100 relative overflow-hidden flex items-center justify-center">
                @if(!empty($property->images) && isset($property->images[0]))
                <img src="{{ asset('storage/' . $property->images[0]) }}" alt="{{ $property->name }}" class="w-full h-full object-cover">
                @else
                <!-- Elegant SVG Placeholder -->
                <span class="p-4 bg-brand-50 text-brand-500 rounded-full">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </span>
                @endif
                
                <span class="absolute top-4 left-4 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-white/95 text-dark-800 rounded-lg shadow-sm border border-gray-100">
                    {{ $property->property_type }}
                </span>

                @if($property->is_off_plan)
                <span class="absolute top-4 right-4 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-amber-500 text-white rounded-lg shadow-sm">
                    Off-Plan
                </span>
                @endif
            </div>

            <!-- Property Body details -->
            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                <div class="space-y-1">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-extrabold text-dark-900 text-lg leading-tight">{{ $property->name }}</h3>
                        <span class="text-sm font-bold text-emerald-600 flex-shrink-0">₦{{ number_format($property->price, 2) }}</span>
                    </div>
                    <div class="text-xs text-gray-500 flex items-center space-x-1">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>{{ $property->location }}</span>
                    </div>

                    @if($property->project)
                    <div class="text-xs text-brand-600 font-semibold pt-1">
                        Project: <a href="{{ route('projects.show', $property->project) }}" class="underline hover:text-brand-700">{{ $property->project->name }}</a>
                    </div>
                    @endif

                    @if($property->landmark)
                    <div class="text-[11px] text-gray-550 italic">
                        Near: {{ $property->landmark }}
                    </div>
                    @endif
                </div>

                <p class="text-xs text-gray-600 line-clamp-3 leading-relaxed">{{ $property->description }}</p>

                <!-- Amenities tags -->
                @if(!empty($property->amenities))
                <div class="flex flex-wrap gap-1 pt-1">
                    @foreach($property->amenities as $amenity)
                    <span class="px-2 py-0.5 bg-slate-50 text-[10px] text-gray-500 font-semibold rounded-md border border-gray-150">
                        {{ $amenity }}
                    </span>
                    @endforeach
                </div>
                @endif

                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Available Stock</span>
                        @if($property->available_units > 0)
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">{{ $property->available_units }} Units left</span>
                        @else
                        <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">Sold Out</span>
                        @endif
                    </div>

                    <div class="flex items-center space-x-1.5" x-data="{ editPropOpen: false }">
                        <a href="{{ route('properties.units.index', $property) }}" class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 border border-brand-200 text-brand-700 rounded-lg text-xs font-bold transition-all">
                            Units Pipeline
                        </a>

                        @if(Auth::user()->role !== 'sales_executive')
                        <button @click="editPropOpen = true" class="p-2 bg-gray-50 text-gray-600 hover:bg-gray-100 border border-gray-200 rounded-lg" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        
                        <!-- Inline Edit Property Modal -->
                        <div x-cloak x-show="editPropOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 text-left">
                            <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-4 max-h-[90vh] overflow-y-auto" @click.away="editPropOpen = false">
                                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                    <h4 class="font-bold text-dark-900">Edit Portfolio Details</h4>
                                    <button @click="editPropOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                                </div>
                                <form action="{{ route('properties.update', $property->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Property Name *</label>
                                        <input type="text" name="name" value="{{ $property->name }}" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Estate Name</label>
                                        <input type="text" name="estate_name" value="{{ $property->estate_name }}" class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Location *</label>
                                        <input type="text" name="location" value="{{ $property->location }}" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase">Off-Plan Project</label>
                                            <select name="project_id" class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                                                <option value="">-- None --</option>
                                                @foreach($projects as $proj)
                                                <option value="{{ $proj->id }}" {{ $property->project_id == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mt-1 flex items-center space-x-2">
                                                <input type="checkbox" name="is_off_plan" value="1" {{ $property->is_off_plan ? 'checked' : '' }} class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                                <span>Off-Plan Toggle</span>
                                            </label>
                                        </div>
                                    </div>

                                    @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin())
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Assign to Branch Office</label>
                                        <select name="branch_id" class="w-full px-3 py-2 border rounded-lg text-xs bg-white cursor-pointer text-gray-700">
                                            <option value="">Unassigned (Corporate Head Office)</option>
                                            @foreach($branches as $br)
                                                <option value="{{ $br->id }}" {{ $property->branch_id == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase">Type</label>
                                            <select name="property_type" class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                                                <option value="Duplex" {{ $property->property_type === 'Duplex' ? 'selected' : '' }}>Duplex</option>
                                                <option value="Terrace" {{ $property->property_type === 'Terrace' ? 'selected' : '' }}>Terrace</option>
                                                <option value="Flat" {{ $property->property_type === 'Flat' ? 'selected' : '' }}>Flat</option>
                                                <option value="Land" {{ $property->property_type === 'Land' ? 'selected' : '' }}>Land</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase">Available Units</label>
                                            <input type="number" name="available_units" value="{{ $property->available_units }}" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase">Latitude</label>
                                            <input type="number" name="latitude" step="0.00000001" value="{{ $property->latitude }}" class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase">Longitude</label>
                                            <input type="number" name="longitude" step="0.00000001" value="{{ $property->longitude }}" class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase">Nearest Landmark</label>
                                            <input type="text" name="landmark" value="{{ $property->landmark }}" class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase">Completion Status</label>
                                            <input type="text" name="completion_status" value="{{ $property->completion_status }}" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. Completed, Q4 2026">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Price (₦) *</label>
                                        <input type="number" name="price" step="0.01" value="{{ $property->price }}" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase font-semibold">Amenities (comma-separated)</label>
                                        <input type="text" name="amenities[]" value="{{ !empty($property->amenities) ? implode(', ', $property->amenities) : '' }}" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="Pool, Gym, Security, Water Treatment">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Add Cover Images</label>
                                        <input type="file" name="images[]" multiple class="w-full text-xs text-gray-500 cursor-pointer">
                                    </div>
                                    <div class="flex justify-end space-x-2 pt-2">
                                        <button type="button" @click="editPropOpen = false" class="text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                                        <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg shadow-sm">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-12 text-gray-500">
            <span class="p-4 bg-gray-50 text-gray-400 rounded-full inline-block mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
            </span>
            <h4 class="text-sm font-bold text-dark-900">No properties recorded</h4>
            <p class="text-xs text-gray-400 mt-1">Please add properties portfolios to show layout statistics.</p>
        </div>
        @endforelse
    </div>

    @if($properties->hasPages())
    <div class="bg-gray-50 border-t border-gray-150 p-4 rounded-2xl">
        {{ $properties->links() }}
    </div>
    @endif

    <!-- Add Property Modal -->
    <div x-cloak x-show="addPropertyOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-4 max-h-[90vh] overflow-y-auto" @click.away="addPropertyOpen = false">
            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Add Property Portfolio</h3>
                <button @click="addPropertyOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <form action="{{ route('properties.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Property Name *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. Orange Valley Villa">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Estate Name</label>
                    <input type="text" name="estate_name" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. Orange Valley Estate">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Location *</label>
                    <input type="text" name="location" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. Lekki, Lagos">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Off-Plan Project</label>
                        <select name="project_id" class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                            <option value="">-- None --</option>
                            @foreach($projects as $proj)
                            <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mt-1 flex items-center space-x-2">
                            <input type="checkbox" name="is_off_plan" value="1" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                            <span>Off-Plan Toggle</span>
                        </label>
                    </div>
                </div>

                @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin())
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Assign to Branch Office</label>
                    <select name="branch_id" class="w-full px-3 py-2 border rounded-lg text-xs bg-white cursor-pointer text-gray-700">
                        <option value="">Unassigned (Corporate Head Office)</option>
                        @foreach($branches as $br)
                            <option value="{{ $br->id }}">{{ $br->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Property Type *</label>
                        <select name="property_type" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                            <option value="Duplex">Duplex</option>
                            <option value="Terrace">Terrace</option>
                            <option value="Flat">Flat</option>
                            <option value="Land">Land</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Available Units *</label>
                        <input type="number" name="available_units" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="5">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Latitude</label>
                        <input type="number" name="latitude" step="0.00000001" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="6.4281">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Longitude</label>
                        <input type="number" name="longitude" step="0.00000001" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="3.4219">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Nearest Landmark</label>
                        <input type="text" name="landmark" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. Shoprite Mall">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Completion Status</label>
                        <input type="text" name="completion_status" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. Under Construction">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Price (₦) *</label>
                    <input type="number" name="price" step="0.01" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. 75000000">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase font-semibold">Amenities (comma-separated)</label>
                    <input type="text" name="amenities[]" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="Pool, Gym, Security, Water Treatment">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Property Images</label>
                    <input type="file" name="images[]" multiple class="w-full text-xs text-gray-500 cursor-pointer">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-lg text-xs bg-white resize-none" placeholder="Description details..."></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="addPropertyOpen = false" class="text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg shadow-sm">Save Portfolio</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
