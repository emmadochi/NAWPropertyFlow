@extends('layouts.app')

@section('content')
<div class="space-y-8" x-data="{ activeMilestone: null }">
    {{-- Welcome Banner --}}
    <div class="bg-gradient-to-r from-brand-500 via-brand-600 to-dark-900 rounded-3xl p-8 text-white relative overflow-hidden shadow-xl shadow-brand-500/10">
        <div class="absolute right-0 top-0 opacity-10 w-96 transform translate-x-12 -translate-y-12">
            <svg viewBox="0 0 100 100" fill="currentColor" class="w-full h-full">
                <path d="M50 0 L100 25 L100 75 L50 100 L0 75 L0 25 Z" />
            </svg>
        </div>
        <div class="relative z-10 space-y-2">
            <span class="text-xs font-bold uppercase tracking-widest bg-white/20 px-3 py-1 rounded-full">Buyer Portal</span>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Welcome, {{ Auth::user()->name }}</h1>
            <p class="text-sm text-white/80 max-w-lg">Track your property investments, payment schedules, construction milestones, and download contract documentation in real-time.</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm flex items-center space-x-4">
            <div class="p-4 bg-emerald-50 rounded-2xl text-emerald-600 text-2xl">💰</div>
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Amount Paid</span>
                <span class="text-2xl font-black text-dark-900">₦{{ number_format($totalInvested, 2) }}</span>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm flex items-center space-x-4">
            <div class="p-4 bg-rose-50 rounded-2xl text-rose-600 text-2xl">⏳</div>
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Outstanding Balance</span>
                <span class="text-2xl font-black text-dark-900">₦{{ number_format($totalBalance, 2) }}</span>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm flex items-center space-x-4">
            <div class="p-4 bg-brand-50 rounded-2xl text-brand-500 text-2xl">🏢</div>
            <div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Units Purchased</span>
                <span class="text-2xl font-black text-dark-900">{{ $unitsCount }}</span>
            </div>
        </div>
    </div>

    @if($sales->isEmpty())
        <div class="bg-white border border-gray-150 rounded-3xl p-12 text-center shadow-sm">
            <span class="text-4xl">🏢</span>
            <h3 class="font-extrabold text-dark-900 text-lg mt-4">No Properties Found</h3>
            <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">There are no property sales recorded under your email address. Please contact support or your relationship manager.</p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Column (Left 2 cols) --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Properties and Construction Milestones --}}
                <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm space-y-4">
                    <h3 class="font-extrabold text-dark-900 text-sm">My Properties &amp; Construction Progress</h3>
                    
                    <div class="divide-y divide-gray-100">
                        @foreach($sales as $sale)
                        <div class="py-4 first:pt-0 last:pb-0 space-y-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-black text-dark-900 text-base">{{ $sale->property->name }}</h4>
                                    <p class="text-[11px] text-gray-400 font-semibold mt-0.5">
                                        Unit: <span class="text-brand-600 font-bold">{{ $sale->propertyUnit ? $sale->propertyUnit->unit_number . ' (' . $sale->propertyUnit->type . ')' : 'General Allocations' }}</span>
                                    </p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-brand-50 text-brand-700 border border-brand-100">
                                    {{ $sale->status }}
                                </span>
                            </div>

                            {{-- Project Milestones progress --}}
                            @if($sale->property && $sale->property->project)
                                @php
                                    $completedMilestones = $sale->property->project->milestones->where('status', 'completed')->count();
                                    $totalMilestones = $sale->property->project->milestones->count();
                                    $progressPercent = $totalMilestones > 0 ? round(($completedMilestones / $totalMilestones) * 100) : 0;
                                @endphp
                                <div class="space-y-1.5">
                                    <div class="flex justify-between items-center text-xs font-bold text-gray-500">
                                        <span>Construction Progress</span>
                                        <span class="text-brand-600">{{ $progressPercent }}%</span>
                                    </div>
                                    <div class="w-full h-2.5 bg-gray-100 rounded-full overflow-hidden relative">
                                        <div class="h-full bg-brand-500 rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                                    </div>
                                    <div class="flex justify-between items-center text-[10px] text-gray-400 font-medium">
                                        <span>Project: {{ $sale->property->project->name }}</span>
                                        <span>{{ $completedMilestones }} / {{ $totalMilestones }} Milestones Met</span>
                                    </div>
                                </div>
                            @else
                                <div class="text-[11px] text-gray-400 italic">No construction milestones tracked for this development.</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Payment Milestones Timeline --}}
                <div class="bg-white rounded-3xl border border-gray-150 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-extrabold text-dark-900 text-sm">Payment Installment Schedule</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-100/70 border-b border-gray-200">
                                    <th class="px-6 py-3.5 font-bold text-gray-500">Milestone Label</th>
                                    <th class="px-6 py-3.5 font-bold text-gray-500">Amount Due</th>
                                    <th class="px-6 py-3.5 font-bold text-gray-500">Due Date</th>
                                    <th class="px-6 py-3.5 font-bold text-gray-500">Status</th>
                                    <th class="px-6 py-3.5 font-bold text-gray-500 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($sales as $sale)
                                    @if($sale->paymentPlan && $sale->paymentPlan->milestones)
                                        @foreach($sale->paymentPlan->milestones as $ms)
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-dark-900">{{ $ms->label }}</div>
                                                <span class="text-[10px] text-gray-400 font-semibold">{{ $sale->property->name }}</span>
                                            </td>
                                            <td class="px-6 py-4 font-extrabold text-gray-700">
                                                ₦{{ number_format($ms->amount_due, 2) }}
                                            </td>
                                            <td class="px-6 py-4 font-semibold text-gray-500">
                                                {{ $ms->due_date }}
                                                                             <td class="px-6 py-4">
                                                @if($ms->status === 'paid')
                                                    @if($ms->verified_at)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                            ✓ Paid &amp; Verified
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                            ⏳ Under Audit
                                                        </span>
                                                    @endif
                                                @elseif($ms->proof_of_payment)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                        📄 Reviewing POP
                                                    </span>
                                                @elseif($ms->status === 'partial')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                        Partially Paid
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold bg-gray-50 text-gray-600 border border-gray-200">
                                                        Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right space-x-1.5">
                                                @if($ms->status === 'paid' && $ms->verified_at)
                                                    <a href="{{ route('buyer.payments.receipt', $ms) }}" target="_blank" class="text-[10px] font-extrabold text-emerald-700 hover:text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-xl transition-all inline-flex items-center space-x-1">
                                                        <span>Official Receipt</span>
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                    </a>
                                                @elseif($ms->proof_of_payment)
                                                    <button type="button" @click="activeMilestone = { id: {{ $ms->id }}, label: '{{ $ms->label }}', amount_due: {{ $ms->amount_due }}, amount_paid: {{ $ms->amount_paid }} }" class="text-[10px] font-bold text-blue-600 hover:text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-1.5 rounded-xl transition-all">
                                                        Update POP
                                                    </button>
                                                @else
                                                    <button type="button" @click="activeMilestone = { id: {{ $ms->id }}, label: '{{ $ms->label }}', amount_due: {{ $ms->amount_due }}, amount_paid: {{ $ms->amount_paid }} }" class="text-[10px] font-bold text-white bg-brand-500 hover:bg-brand-600 px-3 py-1.5 rounded-xl transition-all shadow-sm">
                                                        Upload Proof
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>           </div>

            {{-- Sidebar Column (Right 1 col) --}}
            <div class="space-y-6">
                {{-- Document Center --}}
                <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm space-y-4">
                    <h3 class="font-extrabold text-dark-900 text-sm">Document Library</h3>
                    <p class="text-[10px] text-gray-400">Download signed contracts, allocation letters, and receipts.</p>
                    
                    <div class="space-y-2">
                        @if($documents->isEmpty() && $generatedDocuments->isEmpty())
                            <div class="text-[11px] text-gray-400 text-center py-4 italic">No documents available yet.</div>
                        @else
                            {{-- Automated Generated Docs --}}
                            @foreach($generatedDocuments as $genDoc)
                            <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-100 rounded-2xl">
                                <div class="flex items-center space-x-2.5 overflow-hidden">
                                    <span class="text-xl">📄</span>
                                    <div class="truncate">
                                        <h5 class="text-xs font-bold text-dark-900 truncate">{{ $genDoc->template->title ?? 'Contract Document' }}</h5>
                                        <span class="text-[9px] text-gray-400 uppercase font-semibold">Generated PDF</span>
                                    </div>
                                </div>
                                <a href="{{ route('buyer.generated-documents.download', $genDoc) }}" class="p-1.5 hover:bg-white rounded-lg border border-transparent hover:border-gray-250 transition-all text-brand-500">
                                    ⬇️
                                </a>
                            </div>
                            @endforeach

                            {{-- Manual Uploaded Docs --}}
                            @foreach($documents as $doc)
                            <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-100 rounded-2xl">
                                <div class="flex items-center space-x-2.5 overflow-hidden">
                                    <span class="text-xl">📁</span>
                                    <div class="truncate">
                                        <h5 class="text-xs font-bold text-dark-900 truncate">{{ $doc->name }}</h5>
                                        <span class="text-[9px] text-gray-400 uppercase font-semibold">{{ $doc->category }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('buyer.documents.download', $doc) }}" class="p-1.5 hover:bg-white rounded-lg border border-transparent hover:border-gray-250 transition-all text-brand-500">
                                    ⬇️
                                </a>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                {{-- Assigned Sales Officer Card --}}
                @foreach($sales->unique('sales_officer_id') as $sale)
                    @if($sale->salesOfficer)
                    <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm space-y-4">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Assigned Relationship Manager</span>
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-base shadow-md shadow-brand-500/10">
                                {{ substr($sale->salesOfficer->name, 0, 2) }}
                            </div>
                            <div>
                                <h4 class="font-extrabold text-dark-900 text-sm leading-tight">{{ $sale->salesOfficer->name }}</h4>
                                <span class="text-[10px] text-gray-400 font-semibold">{{ $sale->salesOfficer->departmentRelation->name ?? $sale->salesOfficer->department ?? 'Sales' }} Unit</span>
                            </div>
                        </div>
                        <div class="space-y-1.5 text-xs text-gray-600 pt-2 border-t border-gray-100">
                            @if($sale->salesOfficer->phone_number)
                                <div class="flex items-center space-x-2">
                                    <span>📞</span>
                                    <span class="font-medium">{{ $sale->salesOfficer->phone_number }}</span>
                                </div>
                            @endif
                            <div class="flex items-center space-x-2">
                                <span>✉️</span>
                                <span class="font-medium truncate">{{ $sale->salesOfficer->email }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- Proof of Payment Upload Modal (Alpine.js controlled) --}}
    <div x-cloak x-show="activeMilestone !== null" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-dark-900/60 backdrop-blur-sm transition-opacity" x-transition>
        <div class="bg-white rounded-3xl border border-gray-150 shadow-2xl w-full max-w-md overflow-hidden p-6 md:p-8 space-y-6" @click.away="activeMilestone = null">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-extrabold text-dark-900">Upload Proof of Payment</h3>
                    <p class="text-xs text-brand-600 font-bold mt-0.5" x-text="activeMilestone ? activeMilestone.label : ''"></p>
                </div>
                <button @click="activeMilestone = null" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-xl hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form :action="'/buyer/payments/' + (activeMilestone ? activeMilestone.id : '') + '/submit-pop'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Amount Paid (₦) *</label>
                    <input type="number" step="0.01" name="amount_paid" :value="activeMilestone ? activeMilestone.amount_due : ''" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-900 font-bold bg-gray-50/50">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Bank Reference / Teller # *</label>
                    <input type="text" name="bank_reference" required placeholder="e.g. GTB/TRX-948201 or Cash Deposit"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-900">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Payment Date</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-900">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Attach Receipt / Transfer Screenshot *</label>
                    <input type="file" name="proof_file" required accept=".pdf,.png,.jpg,.jpeg"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs text-gray-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    <p class="text-[10px] text-gray-400 mt-1">Upload JPEG, PNG, or PDF file (Max 10MB).</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Additional Note (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Transfer made from Access Bank account..." class="w-full px-4 py-2 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-xs text-gray-900"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-3 border-t border-gray-100">
                    <button type="button" @click="activeMilestone = null" class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-lg shadow-brand-500/10">
                        Submit Proof of Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
