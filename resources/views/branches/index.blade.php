@extends('layouts.app')

@section('content')
<div class="space-y-8" x-data="{ 
    addBranchOpen: false, 
    editBranchOpen: false, 
    editBranch: { id: '', name: '', address: '', city: '', phone: '', email: '' } 
}">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0 pb-4 border-b border-gray-200">
        <div>
            <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Branch Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Configure and manage geographic branch offices for team and inventory scoping.</p>
        </div>
        <div>
            <button @click="addBranchOpen = true" class="inline-flex items-center space-x-2 px-5 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/10 hover:shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0h-3m-9-1a9 9 0 1118 0 9 9 0 01-18 0z"></path>
                </svg>
                <span>Add New Branch</span>
            </button>
        </div>
    </div>

    <!-- Branches Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($branches as $branch)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="space-y-4">
                <div class="flex items-start justify-between">
                    <div class="p-3 bg-brand-50 text-brand-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <span class="px-2.5 py-1 text-[10px] font-bold bg-dark-50 text-dark-600 border border-dark-100 rounded-full uppercase tracking-wider">
                        {{ $branch->city }}
                    </span>
                </div>
                
                <div>
                    <h3 class="font-extrabold text-dark-900 text-lg leading-tight">{{ $branch->name }}</h3>
                    <p class="text-xs text-gray-400 mt-1 flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ $branch->address }}
                    </p>
                </div>

                <!-- Stats Badges -->
                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-gray-50">
                    <div class="bg-gray-50 rounded-xl p-2 text-center">
                        <span class="block text-xs font-bold text-dark-950">{{ $branch->users()->count() }}</span>
                        <span class="text-[9px] font-medium text-gray-400 uppercase tracking-wider">Staff</span>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-2 text-center">
                        <span class="block text-xs font-bold text-dark-950">{{ $branch->leads()->count() }}</span>
                        <span class="text-[9px] font-medium text-gray-400 uppercase tracking-wider">Leads</span>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-2 text-center">
                        <span class="block text-xs font-bold text-dark-950">{{ $branch->properties()->count() }}</span>
                        <span class="text-[9px] font-medium text-gray-400 uppercase tracking-wider">Properties</span>
                    </div>
                </div>

                <div class="pt-2 text-xs space-y-1">
                    @if($branch->phone)
                    <div class="flex items-center text-gray-600">
                        <span class="font-bold mr-2 text-gray-400">Phone:</span> {{ $branch->phone }}
                    </div>
                    @endif
                    @if($branch->email)
                    <div class="flex items-center text-gray-600">
                        <span class="font-bold mr-2 text-gray-400">Email:</span> {{ $branch->email }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Card Actions -->
            <div class="flex items-center justify-end space-x-2 pt-4 mt-4 border-t border-gray-100">
                <button @click="
                    editBranch = { 
                        id: '{{ $branch->id }}', 
                        name: '{{ addslashes($branch->name) }}', 
                        address: '{{ addslashes($branch->address) }}', 
                        city: '{{ addslashes($branch->city) }}', 
                        phone: '{{ $branch->phone }}', 
                        email: '{{ $branch->email }}' 
                    }; 
                    editBranchOpen = true;
                " class="px-3.5 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold text-xs rounded-xl border border-gray-200 transition-colors">
                    Edit Details
                </button>

                <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this branch office?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs rounded-xl border border-rose-200 transition-colors">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white border border-gray-200 rounded-3xl p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto text-gray-400 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <h3 class="font-extrabold text-dark-900 text-lg">No branches configured</h3>
            <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">Geographic branches allow you to isolate and filter CRM listings for regional offices. Create your first branch above.</p>
        </div>
        @endforelse
    </div>

    <!-- Create Branch Modal -->
    <div x-cloak x-show="addBranchOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-6" @click.away="addBranchOpen = false">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Add New Branch</h3>
                <button @click="addBranchOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>

            <form action="{{ route('branches.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Branch Name *</label>
                    <input type="text" name="name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800" placeholder="e.g. Abuja Maitama">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">City *</label>
                        <input type="text" name="city" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800" placeholder="e.g. Abuja">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Phone Number</label>
                        <input type="text" name="phone" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800" placeholder="e.g. +234...">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Email Address</label>
                    <input type="email" name="email" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800" placeholder="e.g. abuja@nawproperties.com">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Street Address *</label>
                    <input type="text" name="address" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800" placeholder="e.g. 14 Gana Street, Maitama">
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="addBranchOpen = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-md">
                        Create Branch
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Branch Modal -->
    <div x-cloak x-show="editBranchOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-6" @click.away="editBranchOpen = false">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Edit Branch Details</h3>
                <button @click="editBranchOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
            </div>

            <form :action="'/branches/' + editBranch.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Branch Name *</label>
                    <input type="text" name="name" x-model="editBranch.name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">City *</label>
                        <input type="text" name="city" x-model="editBranch.city" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Phone Number</label>
                        <input type="text" name="phone" x-model="editBranch.phone" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Email Address</label>
                    <input type="email" name="email" x-model="editBranch.email" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Street Address *</label>
                    <input type="text" name="address" x-model="editBranch.address" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="editBranchOpen = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-md">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
