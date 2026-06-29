@extends('layouts.app')

@section('content')
<div class="space-y-8" x-data="{ 
    addUnitOpen: false, 
    bulkCreateOpen: false, 
    reserveOpen: false, 
    bookSaleOpen: false,
    activeUnitId: null, 
    activeUnitNumber: '',
    activeUnitPrice: 0,
    activeReservedLeadId: '',
    planType: 'outright',
    numberOfInstallments: 3,
    downpaymentPercent: 20,
    dealValue: 0,
    milestones: [],
    
    initMilestones() {
        this.dealValue = this.activeUnitPrice;
        this.calculateMilestones();
    },
    
    calculateMilestones() {
        this.milestones = [];
        const dealVal = parseFloat(this.dealValue) || 0;
        
        if (this.planType === 'outright') {
            const today = new Date();
            today.setDate(today.getDate() + 7);
            const dueStr = today.toISOString().split('T')[0];
            
            this.milestones.push({
                label: 'Outright Payment',
                amount_due: dealVal.toFixed(2),
                due_date: dueStr
            });
        } else if (this.planType === 'installment') {
            const downPct = parseFloat(this.downpaymentPercent) || 0;
            const downAmt = (dealVal * downPct) / 100;
            
            // Downpayment Milestone
            const today = new Date();
            const downDueStr = today.toISOString().split('T')[0];
            this.milestones.push({
                label: 'Downpayment (' + downPct + '%)',
                amount_due: downAmt.toFixed(2),
                due_date: downDueStr
            });
            
            // Installment Milestones
            const instCount = parseInt(this.numberOfInstallments) || 1;
            const remainingAmt = dealVal - downAmt;
            const instAmt = remainingAmt / instCount;
            
            for (let i = 1; i <= instCount; i++) {
                const dueDate = new Date();
                dueDate.setMonth(dueDate.getMonth() + i);
                const dueStr = dueDate.toISOString().split('T')[0];
                
                this.milestones.push({
                    label: 'Installment #' + i,
                    amount_due: instAmt.toFixed(2),
                    due_date: dueStr
                });
            }
        } else if (this.planType === 'mortgage') {
            const downAmt = dealVal * 0.30;
            const today = new Date();
            const downDueStr = today.toISOString().split('T')[0];
            
            this.milestones.push({
                label: 'Equity Contribution (30%)',
                amount_due: downAmt.toFixed(2),
                due_date: downDueStr
            });
            
            const bankAmt = dealVal * 0.70;
            const bankDueDate = new Date();
            bankDueDate.setDate(bankDueDate.getDate() + 60);
            const bankDueStr = bankDueDate.toISOString().split('T')[0];
            
            this.milestones.push({
                label: 'Mortgage Bank Financing (70%)',
                amount_due: bankAmt.toFixed(2),
                due_date: bankDueStr
            });
        }
    }
}" x-init="$watch('planType', () => calculateMilestones()); $watch('numberOfInstallments', () => calculateMilestones()); $watch('downpaymentPercent', () => calculateMilestones()); $watch('dealValue', () => calculateMilestones()); $watch('activeUnitPrice', () => initMilestones())">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <a href="{{ route('properties.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Property Portfolio</span>
            </a>
            <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">{{ $property->name }} — Units Pipeline</h1>
            <p class="text-sm text-gray-500 mt-1">Manage individual unit sizes, sales reservations, and bulk generate unit blocks.</p>
        </div>
        @if(Auth::user()->role !== 'sales_executive')
        <div class="flex space-x-2">
            <button @click="bulkCreateOpen = true" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-white border border-gray-250 text-gray-700 font-bold text-xs rounded-xl shadow-sm hover:bg-gray-50 transition-all">
                <svg class="w-4 h-4 text-gray-550" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span>Bulk Generate Units</span>
            </button>
            <button @click="addUnitOpen = true" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Add Single Unit</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-gray-150 shadow-sm">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Inventory</span>
            <div class="text-2xl font-extrabold text-dark-900 mt-1">{{ $stats['total'] }} Units</div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-gray-150 shadow-sm">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Available Status</span>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['available'] }} Units</div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-gray-150 shadow-sm">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Reserved (Hold)</span>
            <div class="text-2xl font-extrabold text-amber-500 mt-1">{{ $stats['reserved'] }} Units</div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-gray-150 shadow-sm">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sold (Closed)</span>
            <div class="text-2xl font-extrabold text-rose-500 mt-1">{{ $stats['sold'] }} Units</div>
        </div>
    </div>

    <!-- Units Inventory Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($units as $unit)
        <div class="bg-white rounded-3xl border border-gray-150 shadow-sm overflow-hidden flex flex-col justify-between hover:border-gray-300 hover:shadow-md transition-all duration-300">
            <!-- Header detail -->
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-400">#{{ $unit->unit_number }}</span>
                    <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded-md bg-{{ $unit->status_badge }}-50 text-{{ $unit->status_badge }}-700 border border-{{ $unit->status_badge }}-100">
                        {{ $unit->status }}
                    </span>
                </div>

                <div>
                    <h3 class="font-extrabold text-dark-900 text-base">{{ $unit->unit_type ?: 'Standard Layout' }}</h3>
                    <span class="text-xs font-semibold text-gray-500">
                        {{ $unit->size_sqm ? number_format($unit->size_sqm) . ' SQM' : '' }}
                        {{ $unit->floor_number ? '• Floor ' . $unit->floor_number : '' }}
                    </span>
                </div>

                <div class="text-sm font-extrabold text-brand-600">
                    ₦{{ number_format($unit->price, 2) }}
                </div>

                @if($unit->status === 'reserved' && $unit->reservedByLead)
                <div class="bg-amber-50/50 p-2.5 rounded-xl border border-amber-100/50 text-[10px] text-amber-800 space-y-1">
                    <div class="font-bold flex justify-between">
                        <span>Reserved by:</span>
                        <span>{{ $unit->reservedByLead->name }}</span>
                    </div>
                    @if($unit->reservation_expires_at)
                    <div>Expires: <strong class="text-amber-900">{{ $unit->reservation_expires_at->format('M d, Y') }}</strong></div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Action buttons footer -->
            <div class="px-5 pb-5 pt-3 border-t border-gray-50 flex items-center justify-between">
                <div class="flex space-x-1">
                    @if($unit->status === 'available')
                    <button @click="reserveOpen = true; activeUnitId = {{ $unit->id }}; activeUnitNumber = '{{ $unit->unit_number }}'" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 border border-amber-250 text-amber-700 rounded-lg text-[10px] font-bold transition-all">
                        Reserve Hold
                    </button>
                    <button @click="activeUnitId = {{ $unit->id }}; activeUnitNumber = '{{ $unit->unit_number }}'; activeUnitPrice = {{ $unit->price }}; bookSaleOpen = true;" class="px-2.5 py-1 bg-brand-500 hover:bg-brand-650 text-white rounded-lg text-[10px] font-bold transition-all ml-1">
                        Book Sale
                    </button>
                    @elseif($unit->status === 'reserved')
                    <form action="{{ route('properties.units.release', [$property, $unit]) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 border border-emerald-250 text-emerald-700 rounded-lg text-[10px] font-bold transition-all">
                            Release Hold
                        </button>
                    </form>
                    <button @click="activeUnitId = {{ $unit->id }}; activeUnitNumber = '{{ $unit->unit_number }}'; activeUnitPrice = {{ $unit->price }}; activeReservedLeadId = '{{ $unit->reserved_by_lead_id }}'; bookSaleOpen = true;" class="px-2.5 py-1 bg-brand-500 hover:bg-brand-650 text-white rounded-lg text-[10px] font-bold transition-all ml-1">
                        Book Sale
                    </button>
                    @endif
                </div>

                <div class="flex space-x-1">
                    @if(Auth::user()->role !== 'sales_executive')
                    <a href="{{ route('properties.units.edit', [$property, $unit]) }}" class="p-1.5 bg-gray-50 hover:bg-gray-100 text-gray-500 border border-gray-250 rounded-lg" title="Edit">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                    <form action="{{ route('properties.units.destroy', [$property, $unit]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this unit?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 bg-gray-50 hover:bg-rose-50 text-gray-400 hover:text-rose-600 border border-gray-250 hover:border-rose-200 rounded-lg" title="Delete">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-4 text-center py-12 text-gray-500">
            <span class="p-4 bg-gray-50 text-gray-400 rounded-full inline-block mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </span>
            <h4 class="text-sm font-bold text-dark-900">No units logged</h4>
            <p class="text-xs text-gray-400 mt-1">Generate a block of units or add a single unit to populate the inventory portfolio.</p>
        </div>
        @endforelse
    </div>

    @if($units->hasPages())
    <div class="bg-gray-50 border-t border-gray-150 p-4 rounded-2xl">
        {{ $units->links() }}
    </div>
    @endif

    <!-- Single Unit Create Modal -->
    <div x-cloak x-show="addUnitOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-4" @click.away="addUnitOpen = false">
            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Add Property Unit</h3>
                <button @click="addUnitOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <form action="{{ route('properties.units.store', $property) }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Unit Number *</label>
                        <input type="text" name="unit_number" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. Block A Unit 10">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Unit Layout Type</label>
                        <input type="text" name="unit_type" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. 3 Bedroom Terrace">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Price (₦) *</label>
                        <input type="number" name="price" step="0.01" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. 85000000">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Service Charge (₦/Yr)</label>
                        <input type="number" name="service_charge" step="0.01" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. 1000000">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Floor Number</label>
                        <input type="number" name="floor_number" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="1">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Size (SQM)</label>
                        <input type="number" name="size_sqm" step="0.01" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="145">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Unit Status *</label>
                    <select name="status" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                        <option value="available">Available</option>
                        <option value="reserved">Reserved</option>
                        <option value="sold">Sold</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Description / Features</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-lg text-xs bg-white resize-none" placeholder="Provide features like ensuite, boys quarters, etc."></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="addUnitOpen = false" class="text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg shadow-sm">Save Unit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Create Units Wizard Modal -->
    <div x-cloak x-show="bulkCreateOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-4" @click.away="bulkCreateOpen = false">
            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Bulk Generate Unit Block</h3>
                <button @click="bulkCreateOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <form action="{{ route('properties.units.bulk-create', $property) }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-3 gap-2">
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Unit Prefix *</label>
                        <input type="text" name="prefix" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. Apt -, Block A-">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Start No. *</label>
                        <input type="number" name="start_number" required min="1" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="1">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Units Count *</label>
                        <input type="number" name="count" required min="1" max="200" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. 20">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Unit Type Layout</label>
                        <input type="text" name="unit_type" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="e.g. 3 Bedroom flat">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Unit Price (₦) *</label>
                        <input type="number" name="price" required min="0" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="₦">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Annual Service Charge</label>
                        <input type="number" name="service_charge" min="0" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="₦">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Average Size (SQM)</label>
                    <input type="number" name="size_sqm" class="w-full px-3 py-2 border rounded-lg text-xs bg-white" placeholder="120">
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="bulkCreateOpen = false" class="text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg shadow-sm">Bulk Generate</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reserve Modal hold template -->
    <div x-cloak x-show="reserveOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-4" @click.away="reserveOpen = false">
            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Reserve Unit Block: <span x-text="activeUnitNumber"></span></h3>
                <button @click="reserveOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <form :action="'{{ route('properties.units.reserve', [$property, ':unit']) }}'.replace(':unit', activeUnitId)" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Interested Lead / Client *</label>
                    <select name="lead_id" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                        <option value="">-- Choose Lead Profile --</option>
                        @foreach($leads as $lead)
                        <option value="{{ $lead->id }}">{{ $lead->full_name }} ({{ $lead->email ?: 'No Email' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Hold Duration (Days) *</label>
                    <input type="number" name="hold_days" value="7" required min="1" max="90" class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Reservation Notes</label>
                    <textarea name="reservation_notes" rows="3" class="w-full px-3 py-2 border rounded-lg text-xs bg-white resize-none" placeholder="Add specific terms, expected deposit date, etc."></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="reserveOpen = false" class="text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg shadow-sm">Reserve Hold</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Convert Reserved / Book Sale Modal with Milestone Generator -->
    <div x-cloak x-show="bookSaleOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl p-6 md:p-8 space-y-4 max-h-[90vh] overflow-y-auto" @click.away="bookSaleOpen = false">
            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Book Sale for Unit: <span x-text="activeUnitNumber"></span></h3>
                <button @click="bookSaleOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <form :action="'{{ route('properties.units.convert-sale', [$property, ':unit']) }}'.replace(':unit', activeUnitId)" method="POST" class="space-y-4">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Purchasing Client *</label>
                        <select name="lead_id" required x-model="activeReservedLeadId" class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                            <option value="">-- Select Client --</option>
                            @foreach($leads as $lead)
                            <option value="{{ $lead->id }}">{{ $lead->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Final Deal Value (₦) *</label>
                        <input type="number" name="deal_value" required x-model="dealValue" class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Payment Plan Type *</label>
                        <select name="plan_type" x-model="planType" class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                            <option value="outright">Outright</option>
                            <option value="installment">Installments</option>
                            <option value="mortgage">Mortgage</option>
                        </select>
                    </div>

                    <div x-show="planType === 'installment'">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Downpayment % *</label>
                        <input type="number" x-model="downpaymentPercent" min="5" max="95" class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                    </div>

                    <div x-show="planType === 'installment'">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Installments No. *</label>
                        <input type="number" x-model="numberOfInstallments" min="1" max="60" class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                    </div>
                </div>

                <!-- Generated Milestones Preview -->
                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 space-y-2">
                    <h4 class="text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">Milestone Schedule Preview</h4>
                    
                    <div class="space-y-1.5 max-h-48 overflow-y-auto">
                        <template x-for="(ms, index) in milestones" :key="index">
                            <div class="flex items-center justify-between text-xs bg-white p-2 rounded-xl border border-gray-150 shadow-sm">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-800" x-text="ms.label"></span>
                                    <span class="text-[10px] text-gray-400" x-text="'Due: ' + ms.due_date"></span>
                                </div>
                                <span class="font-extrabold text-emerald-600" x-text="'₦' + parseFloat(ms.amount_due).toLocaleString()"></span>
                                
                                <!-- Hidden form inputs to submit milestones structure -->
                                <input type="hidden" :name="'milestones['+index+'][label]'" :value="ms.label">
                                <input type="hidden" :name="'milestones['+index+'][amount_due]'" :value="ms.amount_due">
                                <input type="hidden" :name="'milestones['+index+'][due_date]'" :value="ms.due_date">
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Special Booking Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 border rounded-lg text-xs bg-white resize-none" placeholder="Milestone terms, discounts, etc."></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="bookSaleOpen = false" class="text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-brand-500 hover:bg-brand-650 text-white text-xs font-bold rounded-lg shadow-sm">Confirm Sale Booking</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
