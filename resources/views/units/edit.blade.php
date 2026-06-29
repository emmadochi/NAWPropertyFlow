@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-150">
        <div>
            <a href="{{ route('properties.units.index', $property) }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Units List</span>
            </a>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">Edit Unit Block details</h1>
            <p class="text-xs text-gray-500 mt-1">Modify inventory parameters, floor structure, and pricing model.</p>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-3xl border border-gray-150 p-6 md:p-8 shadow-sm">
        <form action="{{ route('properties.units.update', [$property, $unit]) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Unit Number *</label>
                    <input type="text" name="unit_number" value="{{ old('unit_number', $unit->unit_number) }}" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Unit Layout Type</label>
                    <input type="text" name="unit_type" value="{{ old('unit_type', $unit->unit_type) }}" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Price (₦) *</label>
                    <input type="number" name="price" value="{{ old('price', $unit->price) }}" step="0.01" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Annual Service Charge</label>
                    <input type="number" name="service_charge" value="{{ old('service_charge', $unit->service_charge) }}" step="0.01" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Floor Number</label>
                    <input type="number" name="floor_number" value="{{ old('floor_number', $unit->floor_number) }}" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Size (SQM)</label>
                    <input type="number" name="size_sqm" value="{{ old('size_sqm', $unit->size_sqm) }}" step="0.01" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase">Unit Status *</label>
                <select name="status" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                    <option value="available" {{ $unit->status === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="reserved" {{ $unit->status === 'reserved' ? 'selected' : '' }}>Reserved</option>
                    <option value="sold" {{ $unit->status === 'sold' ? 'selected' : '' }}>Sold</option>
                    <option value="unavailable" {{ $unit->status === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase">Description / Features</label>
                <textarea name="description" rows="4" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white resize-none">{{ old('description', $unit->description) }}</textarea>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-150">
                <button type="button" onclick="history.back()" class="text-xs font-bold text-gray-500 hover:text-gray-700">
                    Cancel & Go Back
                </button>
                <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
