@extends('layouts.app')

@section('content')
<div class="space-y-8" 
     x-data="{ 
        editLeadOpen: false, 
        scheduleFollowUpOpen: false, 
        bookInspectionOpen: false, 
        recordSaleOpen: false,
        activeTab: 'timeline' 
     }">

    <!-- Top Navigation Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        <div class="flex items-center space-x-4">
            <a href="{{ route('leads.index') }}" class="p-2 bg-white text-gray-500 hover:text-gray-700 border border-gray-200 rounded-xl transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-extrabold text-dark-900 leading-none">{{ $lead->full_name }}</h1>
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
                </div>
                <p class="text-xs text-gray-500 mt-1">Lead Ref #L{{ $lead->id }} • Captured {{ $lead->created_at->format('d M Y') }}</p>
            </div>
        </div>

        <div class="flex space-x-2">
            @if($lead->status !== 'Closed Won')
            <button @click="recordSaleOpen = true" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-600/10 transition-all flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1"></path>
                </svg>
                <span>Record Sale</span>
            </button>
            @endif
            <button @click="editLeadOpen = true" class="px-4 py-2.5 bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 rounded-xl transition-all shadow-sm text-sm font-bold">
                Edit details
            </button>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Lead Details Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm space-y-6">
                <div class="flex items-center space-x-4 pb-4 border-b border-gray-100">
                    <div class="w-14 h-14 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center font-extrabold text-xl">
                        {{ substr($lead->full_name, 0, 2) }}
                    </div>
                    <div>
                        <h3 class="font-bold text-dark-900 text-lg leading-tight">{{ $lead->full_name }}</h3>
                        <span class="text-xs text-gray-500 font-medium">Interest: {{ $lead->propertyInterest ? $lead->propertyInterest->name : 'General inquiry' }}</span>
                    </div>
                </div>

                <!-- Contact Details List -->
                <div class="space-y-4 text-sm">
                    <div>
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Phone Number</span>
                        <a href="tel:{{ $lead->phone_number }}" class="text-dark-800 font-semibold hover:text-brand-600">{{ $lead->phone_number }}</a>
                    </div>
                    @if($lead->whatsapp_number)
                    <div>
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">WhatsApp Number</span>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->whatsapp_number) }}" target="_blank" class="text-emerald-600 font-semibold hover:underline flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.458L0 24zm6.59-4.846c1.6.95 3.197 1.45 4.817 1.453 5.461 0 9.898-4.432 9.9-9.893.002-2.646-1.01-5.132-2.85-6.974S14.653 1.082 12.01 1.08c-5.468 0-9.91 4.436-9.912 9.898-.001 1.83.486 3.62 1.411 5.2l-.994 3.628 3.722-.972zm11.233-7.502c-.3-.15-1.77-.875-2.045-.975s-.475-.15-.675.15-.775.975-.95 1.175-.35.225-.65.075c-.3-.15-1.265-.467-2.41-1.485-.89-.79-1.49-1.77-1.665-2.07s-.018-.462.13-.61c.135-.133.3-.35.45-.525.15-.175.2-.3.3-.5s.05-.375-.025-.525-.675-1.625-.925-2.225c-.244-.589-.491-.51-.675-.52-.175-.01-.375-.01-.575-.01s-.525.075-.8.375c-.275.3-1.05 1.025-1.05 2.5s1.075 2.9 1.225 3.1c.15.2 2.11 3.225 5.11 4.525.714.31 1.27.495 1.7.635.717.227 1.37.195 1.885.118.574-.085 1.77-.725 2.02-1.39s.25-1.235.175-1.39-.275-.25-.575-.4z"/>
                            </svg>
                            <span>Chat on WhatsApp</span>
                        </a>
                    </div>
                    @endif
                    <div>
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Email Address</span>
                        <a href="mailto:{{ $lead->email }}" class="text-dark-800 font-semibold hover:text-brand-600">{{ $lead->email ?? 'N/A' }}</a>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Budget Range</span>
                        <span class="text-dark-900 font-bold">{{ $lead->budget_range ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Preferred Location</span>
                        <span class="text-dark-900 font-semibold">{{ $lead->preferred_location ?? 'Any' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Lead Acquisition Channel</span>
                        <span class="text-dark-900 font-semibold">{{ $lead->lead_source }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Assigned Sales Officer</span>
                        <span class="text-dark-900 font-semibold">{{ $lead->assignedOfficer ? $lead->assignedOfficer->name : 'Unassigned' }}</span>
                    </div>
                </div>

                @if($lead->notes)
                <div class="pt-4 border-t border-gray-100">
                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Description Notes</span>
                    <p class="text-xs text-gray-600 leading-relaxed bg-gray-50 rounded-xl p-3 border border-gray-100">{{ $lead->notes }}</p>
                </div>
                @endif

                <!-- Delete Action (Admins only) -->
                @if(in_array(Auth::user()->role, ['super_admin', 'company_admin']))
                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lead? This action is permanent.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-100 px-3.5 py-2 rounded-xl transition-all">
                            Delete Prospect
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        <!-- Right: Tabbed Activities details -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col h-[650px]">
                
                <!-- Tab Controls -->
                <div class="flex bg-gray-50 border-b border-gray-100">
                    <button @click="activeTab = 'timeline'" :class="{'bg-white text-brand-600 font-bold border-r border-gray-100': activeTab === 'timeline', 'text-gray-500 hover:text-dark-900': activeTab !== 'timeline'}" class="flex-1 py-4 text-xs font-semibold uppercase tracking-wider text-center focus:outline-none transition-all">
                        Activity Timeline
                    </button>
                    <button @click="activeTab = 'followups'" :class="{'bg-white text-brand-600 font-bold border-x border-gray-100': activeTab === 'followups', 'text-gray-500 hover:text-dark-900': activeTab !== 'followups'}" class="flex-1 py-4 text-xs font-semibold uppercase tracking-wider text-center focus:outline-none transition-all">
                        Follow-Ups ({{ $lead->followUps->where('status', 'Pending')->count() }})
                    </button>
                    <button @click="activeTab = 'inspections'" :class="{'bg-white text-brand-600 font-bold border-x border-gray-100': activeTab === 'inspections', 'text-gray-500 hover:text-dark-900': activeTab !== 'inspections'}" class="flex-1 py-4 text-xs font-semibold uppercase tracking-wider text-center focus:outline-none transition-all">
                        Inspections ({{ $lead->inspections->count() }})
                    </button>
                    <button @click="activeTab = 'documents'" :class="{'bg-white text-brand-600 font-bold border-x border-gray-100': activeTab === 'documents', 'text-gray-500 hover:text-dark-900': activeTab !== 'documents'}" class="flex-1 py-4 text-xs font-semibold uppercase tracking-wider text-center focus:outline-none transition-all">
                        KYC & Files ({{ $lead->documents->count() }})
                    </button>
                    <button @click="activeTab = 'payments'" :class="{'bg-white text-brand-600 font-bold border-l border-gray-100': activeTab === 'payments', 'text-gray-500 hover:text-dark-900': activeTab !== 'payments'}" class="flex-1 py-4 text-xs font-semibold uppercase tracking-wider text-center focus:outline-none transition-all">
                        Payments ({{ $lead->sales->count() }})
                    </button>
                </div>

                <!-- Tab Panels -->
                <div class="flex-1 p-6 overflow-y-auto">
                    
                    <!-- Timeline Tab -->
                    <div x-show="activeTab === 'timeline'" class="space-y-6">
                        <!-- Quick Note Form -->
                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 mb-6">
                            <h4 class="text-xs font-extrabold text-gray-500 uppercase tracking-wider mb-2">Log Activity / Quick Note</h4>
                            <form id="quick-note-form" class="space-y-2">
                                <textarea name="note" rows="2" required placeholder="Type a note (e.g., 'Called client, they requested pricing for 3-bedroom unit next week')..." 
                                    class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none resize-none text-gray-800 placeholder-gray-400"></textarea>
                                <div class="flex justify-end">
                                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md shadow-brand-500/10 transition-all flex items-center space-x-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>Add Note</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Timeline List -->
                        <div id="timeline-container" class="space-y-6 relative before:absolute before:inset-y-0 before:left-[13px] before:w-0.5 before:bg-gray-100">
                            @forelse($timeline as $item)
                            <div class="relative pl-8 pb-4 group last:pb-0">
                                <!-- Icon Badge -->
                                <span class="absolute left-0 top-0.5 w-7 h-7 rounded-full border-2 border-white shadow-sm flex items-center justify-center text-xs text-white {{ $item['color'] }}">
                                    {{ $item['icon'] }}
                                </span>
                                
                                <div class="flex flex-col md:flex-row md:items-center justify-between text-xs text-gray-500 gap-1">
                                    <span class="font-extrabold text-gray-400 uppercase tracking-wider">{{ $item['activity_type'] }}</span>
                                    <span>
                                        @if($item['created_at'] instanceof \Carbon\Carbon)
                                            {{ $item['created_at']->format('M d, Y h:i A') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($item['created_at'])->format('M d, Y h:i A') }}
                                        @endif
                                        • by <strong class="text-gray-600">{{ $item['user'] }}</strong>
                                    </span>
                                </div>
                                <p class="text-sm text-dark-800 font-medium mt-1 leading-relaxed">{{ $item['description'] }}</p>
                            </div>
                            @empty
                            <div class="h-full flex flex-col items-center justify-center text-center p-12" id="timeline-empty">
                                <span class="text-gray-300 mb-3 block">No activities recorded yet.</span>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Followups Tab -->
                    <div x-show="activeTab === 'followups'" class="space-y-6">
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                            <h4 class="font-bold text-dark-900 text-sm">Actionable logs</h4>
                            <button @click="scheduleFollowUpOpen = true" class="text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 border border-brand-100 px-3 py-1.5 rounded-xl transition-all">
                                + Schedule Follow-Up
                            </button>
                        </div>

                        <div class="space-y-4">
                            @forelse($lead->followUps as $followUp)
                            <div class="p-4 bg-gray-50 border border-gray-100 rounded-2xl flex justify-between items-start">
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide rounded-md
                                            {{ $followUp->type === 'Call' ? 'bg-orange-100 text-orange-700' : '' }}
                                            {{ $followUp->type === 'Meeting' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $followUp->type === 'Note' ? 'bg-gray-200 text-gray-700' : '' }}
                                        ">
                                            {{ $followUp->type }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $followUp->due_date->format('M d, Y h:i A') }}</span>
                                        <span class="text-xs font-semibold {{ $followUp->status === 'Completed' ? 'text-emerald-600' : 'text-amber-600' }}">{{ $followUp->status }}</span>
                                    </div>
                                    <p class="text-xs text-gray-700 font-medium leading-relaxed">{{ $followUp->notes }}</p>
                                </div>
                                @if($followUp->status === 'Pending')
                                <form action="{{ route('follow-ups.update', $followUp->id) }}" method="POST" class="flex-shrink-0">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="Completed">
                                    <button type="submit" class="p-1.5 bg-white text-emerald-600 hover:bg-emerald-50 border border-gray-200 hover:border-emerald-200 rounded-lg shadow-sm" title="Mark Completed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                            @empty
                            <p class="text-xs text-gray-500 text-center py-8">No follow-ups recorded yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Inspections Tab -->
                    <div x-show="activeTab === 'inspections'" class="space-y-6">
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                            <h4 class="font-bold text-dark-900 text-sm">Site Tours and Visitions</h4>
                            <button @click="bookInspectionOpen = true" class="text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 border border-brand-100 px-3 py-1.5 rounded-xl transition-all">
                                + Book Inspection
                            </button>
                        </div>

                        <div class="space-y-4">
                            @forelse($lead->inspections as $ins)
                            <div class="p-4 bg-gray-50 border border-gray-100 rounded-2xl flex items-start justify-between">
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs text-blue-600 font-bold">{{ $ins->inspection_date->format('M d, Y h:i A') }}</span>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-100 text-blue-700">{{ $ins->status }}</span>
                                    </div>
                                    <h5 class="text-xs font-bold text-dark-800">{{ $ins->property->name }}</h5>
                                    <p class="text-xs text-gray-600 leading-relaxed">{{ $ins->notes }}</p>
                                </div>
                                
                                @if($ins->status === 'Scheduled')
                                <div class="flex space-x-1 flex-shrink-0" x-data="{ openOptions: false }">
                                    <form action="{{ route('inspections.update', $ins->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="Completed">
                                        <button type="submit" class="px-2.5 py-1 bg-white hover:bg-emerald-50 text-emerald-600 border border-gray-200 hover:border-emerald-200 text-[10px] font-bold rounded-lg shadow-sm transition-all">
                                            Complete
                                        </button>
                                    </form>
                                    <form action="{{ route('inspections.update', $ins->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="Cancelled">
                                        <button type="submit" class="px-2.5 py-1 bg-white hover:bg-rose-50 text-rose-600 border border-gray-200 hover:border-rose-200 text-[10px] font-bold rounded-lg shadow-sm transition-all">
                                            Cancel
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                            @empty
                            <p class="text-xs text-gray-500 text-center py-8">No inspections logged yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Documents Tab -->
                    <div x-show="activeTab === 'documents'" class="space-y-6">
                        
                        <!-- Upload Form -->
                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <h5 class="text-xs font-bold text-dark-900 uppercase tracking-wider mb-3">Upload Lead Document</h5>
                            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                                @csrf
                                <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                                
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Document Display Name</label>
                                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs bg-white focus:outline-none" placeholder="e.g. KYC Passport">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Category</label>
                                    <select name="category" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs bg-white focus:outline-none">
                                        <option value="KYC">KYC Document</option>
                                        <option value="Contract">Contract / Offer</option>
                                        <option value="Agreement">Deed of Agreement</option>
                                        <option value="Payment Receipt">Payment Receipt</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">Attach File</label>
                                    <input type="file" name="document_file" required class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100 cursor-pointer">
                                </div>

                                <div class="md:col-span-3 flex justify-end pt-1">
                                    <button type="submit" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg shadow-sm transition-all">Upload File</button>
                                </div>
                            </form>
                        </div>

                        <!-- Documents List -->
                        <div class="space-y-3">
                            <h4 class="font-bold text-dark-900 text-sm pb-2 border-b border-gray-100">Files Uploaded</h4>
                            @forelse($lead->documents as $doc)
                            <div class="p-3 bg-white border border-gray-150 rounded-xl flex items-center justify-between hover:border-gray-300 transition-all text-xs">
                                <div class="flex items-center space-x-3 overflow-hidden">
                                    <span class="p-2 bg-gray-50 text-gray-500 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </span>
                                    <div class="overflow-hidden">
                                        <h5 class="font-bold text-dark-900 truncate">{{ $doc->name }}</h5>
                                        <span class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold">{{ $doc->category }} • by {{ $doc->uploader->name }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('documents.download', $doc->id) }}" class="p-1.5 bg-gray-50 text-brand-600 hover:bg-brand-50 border border-gray-200 rounded-lg" title="Download">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </a>
                                    @if(Auth::user()->role !== 'sales_executive')
                                    <form action="{{ route('documents.destroy', $doc->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-gray-50 text-rose-600 hover:bg-rose-50 border border-gray-200 rounded-lg" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <p class="text-xs text-gray-500 text-center py-8">No documents uploaded.</p>
                            @endforelse
                        </div>

                    </div>

                    <!-- Payments Tab -->
                    <div x-show="activeTab === 'payments'" class="space-y-6">
                        <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                            <h4 class="font-bold text-dark-900 text-sm">Property Purchase & Payments</h4>
                        </div>

                        <div class="space-y-4">
                            @forelse($lead->sales as $sale)
                            <div class="p-5 bg-gray-50 border border-gray-100 rounded-2xl space-y-4">
                                <div class="flex flex-col md:flex-row md:items-center justify-between">
                                    <div>
                                        <h5 class="text-sm font-bold text-dark-800">{{ $sale->property->name }}</h5>
                                        <span class="text-xs text-gray-400 mt-1 block">Purchase Date: {{ $sale->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <div class="text-right mt-2 md:mt-0">
                                        <span class="text-xs text-gray-400 font-bold block">DEAL VALUE</span>
                                        <span class="text-sm font-bold text-brand-500">₦{{ number_format($sale->deal_value, 2) }}</span>
                                    </div>
                                </div>

                                @if($sale->paymentPlan)
                                <div class="bg-white p-4 rounded-xl border border-gray-150 grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Total Paid</span>
                                        <span class="text-sm font-bold text-emerald-600">₦{{ number_format($sale->paymentPlan->amount_paid, 2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Outstanding Balance</span>
                                        <span class="text-sm font-bold text-brand-500">₦{{ number_format($sale->paymentPlan->balance, 2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Plan Type & Status</span>
                                        <span class="text-xs font-semibold capitalize bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md">
                                            {{ $sale->paymentPlan->plan_type }} ({{ $sale->paymentPlan->status }})
                                        </span>
                                    </div>
                                </div>
                                <div class="flex justify-end pt-2">
                                    <a href="{{ route('payments.show-plan', $sale->paymentPlan->id) }}" class="inline-flex items-center px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-colors">
                                        Manage Milestones & Payments
                                    </a>
                                </div>
                                @else
                                <div class="bg-amber-50 p-4 rounded-xl border border-amber-100 text-xs text-amber-800 flex flex-col md:flex-row md:items-center justify-between">
                                    <span>No payment plan has been set up for this sale. Configure the plan now.</span>
                                    <a href="{{ route('payments.create-plan', $sale->id) }}" class="mt-2 md:mt-0 inline-flex items-center px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg transition-colors">
                                        Setup Payment Plan
                                    </a>
                                </div>
                                @endif
                            </div>
                            @empty
                            <p class="text-xs text-gray-500 text-center py-8">No property sales recorded for this lead yet.</p>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

    <!-- Edit Lead details Modal -->
    <div x-cloak x-show="editLeadOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl p-6 md:p-8 space-y-6 my-8" @click.away="editLeadOpen = false">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-xl font-bold text-dark-900">Edit Lead Details</h3>
                <button @click="editLeadOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('leads.update', $lead->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Full Name *</label>
                        <input type="text" name="full_name" value="{{ $lead->full_name }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Email Address</label>
                        <input type="email" name="email" value="{{ $lead->email }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Phone Number *</label>
                        <input type="text" name="phone_number" value="{{ $lead->phone_number }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" value="{{ $lead->whatsapp_number }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Budget Range *</label>
                        <select name="budget_range" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                            <option value="₦10M - ₦30M" {{ $lead->budget_range === '₦10M - ₦30M' ? 'selected' : '' }}>₦10M - ₦30M</option>
                            <option value="₦30M - ₦60M" {{ $lead->budget_range === '₦30M - ₦60M' ? 'selected' : '' }}>₦30M - ₦60M</option>
                            <option value="₦60M - ₦100M" {{ $lead->budget_range === '₦60M - ₦100M' ? 'selected' : '' }}>₦60M - ₦100M</option>
                            <option value="₦100M+" {{ $lead->budget_range === '₦100M+' ? 'selected' : '' }}>₦100M+</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Preferred Location</label>
                        <input type="text" name="preferred_location" value="{{ $lead->preferred_location }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Property Interest</label>
                        <select name="property_interest_id"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                            <option value="">General inquiry</option>
                            @foreach($properties as $property)
                            <option value="{{ $property->id }}" {{ $lead->property_interest_id == $property->id ? 'selected' : '' }}>{{ $property->name }} - ₦{{ number_format($property->price, 0) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Lead Source *</label>
                        <select name="lead_source" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                            <option value="Website" {{ $lead->lead_source === 'Website' ? 'selected' : '' }}>Website</option>
                            <option value="Referral" {{ $lead->lead_source === 'Referral' ? 'selected' : '' }}>Referral</option>
                            <option value="Social Media" {{ $lead->lead_source === 'Social Media' ? 'selected' : '' }}>Social Media</option>
                            <option value="WhatsApp" {{ $lead->lead_source === 'WhatsApp' ? 'selected' : '' }}>WhatsApp</option>
                            <option value="Cold Call" {{ $lead->lead_source === 'Cold Call' ? 'selected' : '' }}>Cold Call</option>
                        </select>
                    </div>

                    @if(Auth::user()->role !== 'sales_executive')
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Assign Sales Officer</label>
                        <select name="assigned_to"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                            <option value="">Unassigned</option>
                            @foreach($officers as $officer)
                            <option value="{{ $officer->id }}" {{ $lead->assigned_to == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <input type="hidden" name="assigned_to" value="{{ $lead->assigned_to }}">
                    @endif

                    @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin())
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Assign Branch Office</label>
                        <select name="branch_id"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white cursor-pointer">
                            <option value="">Unassigned (Corporate Head Office)</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $lead->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }} ({{ $branch->city }})</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Pipeline Status Stage *</label>
                        <select name="status" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                            <option value="New" {{ $lead->status === 'New' ? 'selected' : '' }}>New</option>
                            <option value="Contacted" {{ $lead->status === 'Contacted' ? 'selected' : '' }}>Contacted</option>
                            <option value="Follow Up" {{ $lead->status === 'Follow Up' ? 'selected' : '' }}>Follow Up</option>
                            <option value="Inspection Scheduled" {{ $lead->status === 'Inspection Scheduled' ? 'selected' : '' }}>Inspection Scheduled</option>
                            <option value="Negotiation" {{ $lead->status === 'Negotiation' ? 'selected' : '' }}>Negotiation</option>
                            <option value="Payment Processing" {{ $lead->status === 'Payment Processing' ? 'selected' : '' }}>Payment Processing</option>
                            <option value="Closed Won" {{ $lead->status === 'Closed Won' ? 'selected' : '' }}>Closed Won</option>
                            <option value="Closed Lost" {{ $lead->status === 'Closed Lost' ? 'selected' : '' }}>Closed Lost</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Lead Discussion Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800 resize-none"
                              placeholder="Notes...">{{ $lead->notes }}</textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="editLeadOpen = false" class="px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/10">
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedule Follow-Up Modal -->
    <div x-cloak x-show="scheduleFollowUpOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-6" @click.away="scheduleFollowUpOpen = false">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Schedule Follow-Up Task</h3>
                <button @click="scheduleFollowUpOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('follow-ups.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Follow-Up Type *</label>
                    <select name="type" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                        <option value="Call">Phone Call</option>
                        <option value="Meeting">Direct Meeting</option>
                        <option value="Note">Notes/Reminders</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Due Date & Time *</label>
                    <input type="datetime-local" name="due_date" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Task Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800 resize-none"
                              placeholder="Describe follow-up goal: e.g. Present discount options."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="scheduleFollowUpOpen = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl">
                        Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Book Inspection Modal -->
    <div x-cloak x-show="bookInspectionOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-6" @click.away="bookInspectionOpen = false">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Book Property Inspection</h3>
                <button @click="bookInspectionOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('inspections.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select Property *</label>
                    <select name="property_id" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                        @foreach($properties as $property)
                        <option value="{{ $property->id }}" {{ $lead->property_interest_id == $property->id ? 'selected' : '' }}>{{ $property->name }} ({{ $property->location }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Inspection Date & Time *</label>
                    <input type="datetime-local" name="inspection_date" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                </div>

                @if(Auth::user()->role !== 'sales_executive')
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Assign Sales Officer</label>
                    <select name="assigned_to"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                        <option value="">Default ({{ $lead->assignedOfficer ? $lead->assignedOfficer->name : 'Unassigned' }})</option>
                        @foreach($officers as $officer)
                        <option value="{{ $officer->id }}">{{ $officer->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Inspection Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800 resize-none"
                              placeholder="Meeting location, logistics instructions..."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="bookInspectionOpen = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl">
                        Schedule Tour
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Record Sale Modal -->
    <div x-cloak x-show="recordSaleOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-6" @click.away="recordSaleOpen = false">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Record Deal Closed (Sale)</h3>
                <button @click="recordSaleOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('sales.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                
                <div x-data="{ selectedPropertyId: '{{ $lead->property_interest_id ?? '' }}' }" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Purchased Property *</label>
                        <select name="property_id" required x-model="selectedPropertyId"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                            <option value="">-- Choose Property --</option>
                            @foreach($properties as $property)
                            <option value="{{ $property->id }}">{{ $property->name }} - ₦{{ number_format($property->price, 0) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Specific Property Unit (Optional)</label>
                        <select name="property_unit_id"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                            <option value="">-- General Allocation (No specific unit) --</option>
                            @foreach($properties as $property)
                                @foreach($property->units as $unit)
                                <option x-show="selectedPropertyId == '{{ $property->id }}'" value="{{ $unit->id }}">
                                    Unit #{{ $unit->unit_number }} ({{ $unit->unit_type ?: 'Standard' }}) - ₦{{ number_format($unit->price, 2) }}
                                </option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Final Deal Value (₦) *</label>
                    <input type="number" name="deal_value" step="0.01" value="{{ $lead->propertyInterest ? $lead->propertyInterest->price : '' }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Units Purchased *</label>
                    <input type="number" name="units_purchased" min="1" value="1" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Payment Receipt File (PDF/Image)</label>
                    <input type="file" name="payment_receipt" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-600 hover:file:bg-emerald-100 cursor-pointer">
                </div>

                @if(Auth::user()->role !== 'sales_executive')
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Sales Officer Credit</label>
                    <select name="sales_officer_id"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                        <option value="">Default ({{ $lead->assignedOfficer ? $lead->assignedOfficer->name : 'Unassigned' }})</option>
                        @foreach($officers as $officer)
                        <option value="{{ $officer->id }}">{{ $officer->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="recordSaleOpen = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-600/15">
                        Close Won Deal
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
    function initTimelineNotes() {
        const form = document.getElementById('quick-note-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const textarea = form.querySelector('textarea[name="note"]');
            const submitBtn = form.querySelector('button[type="submit"]');
            const noteText = textarea.value.trim();
            if (!noteText) return;

            // Disable UI
            textarea.disabled = true;
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50');

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const res = await fetch('{{ route('leads.notes.store', $lead->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ note: noteText })
                });

                if (!res.ok) throw new Error('Failed to post note');

                const data = await res.json();
                if (data.success) {
                    textarea.value = '';
                    
                    // Hide empty placeholder
                    const emptyPlaceholder = document.getElementById('timeline-empty');
                    if (emptyPlaceholder) emptyPlaceholder.remove();

                    // Prepend to timeline list
                    const container = document.getElementById('timeline-container');
                    if (container) {
                        const card = document.createElement('div');
                        card.className = 'relative pl-8 pb-4 group last:pb-0 opacity-0 translate-y-2 transition-all duration-300';
                        card.innerHTML = `
                            <span class="absolute left-0 top-0.5 w-7 h-7 rounded-full border-2 border-white shadow-sm flex items-center justify-center text-xs text-white ${data.activity.color}">
                                ${data.activity.icon}
                            </span>
                            <div class="flex flex-col md:flex-row md:items-center justify-between text-xs text-gray-500 gap-1">
                                <span class="font-extrabold text-gray-400 uppercase tracking-wider">${data.activity.activity_type}</span>
                                <span>${data.activity.created_at} • by <strong class="text-gray-600">${data.activity.user}</strong></span>
                            </div>
                            <p class="text-sm text-dark-800 font-medium mt-1 leading-relaxed">${escapeHtml(data.activity.description)}</p>
                        `;
                        container.prepend(card);

                        // Trigger transition animation
                        requestAnimationFrame(() => {
                            requestAnimationFrame(() => {
                                card.classList.remove('opacity-0', 'translate-y-2');
                            });
                        });
                    }
                }
            } catch (err) {
                alert('❌ Failed to save note. Please try again.');
            } finally {
                // Re-enable UI
                textarea.disabled = false;
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50');
            }
        });

        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTimelineNotes);
    } else {
        initTimelineNotes();
    }
    document.addEventListener('spa-load-complete', initTimelineNotes);
})();
</script>
@endpush

