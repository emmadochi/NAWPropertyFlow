@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Profile Header --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
            <div class="w-20 h-20 rounded-2xl bg-brand-500 text-white flex items-center justify-center text-2xl font-extrabold flex-shrink-0">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="flex-1">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-dark-900">{{ $user->name }}</h1>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                        <div class="flex items-center gap-2 mt-2 flex-wrap">
                            <span class="px-2.5 py-0.5 bg-brand-100 text-brand-800 rounded-full text-xs font-semibold uppercase">{{ str_replace('_', ' ', $user->role) }}</span>
                            @if($user->branch)
                            <span class="px-2.5 py-0.5 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">{{ $user->branch->name }}</span>
                            @endif
                            <span class="px-2.5 py-0.5 {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }} rounded-full text-xs font-semibold">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('hr.leaderboard') }}" class="text-sm text-gray-400 hover:text-brand-600 flex-shrink-0">← Back</a>
                </div>
            </div>
        </div>

        {{-- This Month Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100">
            <div class="text-center">
                <p class="text-3xl font-extrabold text-dark-900">{{ $stats['leads'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Leads This Month</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-extrabold text-brand-600">{{ $stats['sales'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Sales Closed</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-extrabold text-emerald-600">₦{{ number_format($stats['revenue']) }}</p>
                <p class="text-xs text-gray-500 mt-1">Revenue Generated</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-extrabold {{ $stats['conversionRate'] >= 50 ? 'text-emerald-600' : ($stats['conversionRate'] >= 25 ? 'text-amber-600' : 'text-rose-600') }}">{{ $stats['conversionRate'] }}%</p>
                <p class="text-xs text-gray-500 mt-1">Conversion Rate</p>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div x-data="{ tab: 'onboarding' }" class="space-y-4">
        <div class="flex gap-2 border-b border-gray-200 overflow-x-auto pb-px">
            @foreach(['onboarding' => '📋 Onboarding Checklist', 'certifications' => '🎓 Certifications', 'reviews' => '📊 Reviews', 'disciplinary' => '⚠️ Disciplinary'] as $t => $label)
            <button @click="tab = '{{ $t }}'" :class="tab === '{{ $t }}' ? 'border-brand-500 text-brand-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-4 py-2.5 text-sm border-b-2 transition-colors whitespace-nowrap">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- Certifications Tab --}}
        <div x-show="tab === 'certifications'" class="space-y-4">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-dark-900">Certifications & Training</h2>
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr', 'sales_manager']))
                    <button x-data @click="$dispatch('open-cert-modal')" class="text-xs bg-brand-500 text-white px-3 py-1.5 rounded-xl font-semibold hover:bg-brand-600 transition-colors">+ Add</button>
                    @endif
                </div>
                @if($certifications->isEmpty())
                    <div class="p-8 text-center text-gray-400 text-sm">No certifications recorded.</div>
                @else
                <div class="divide-y divide-gray-50">
                    @foreach($certifications as $cert)
                    <div class="p-4 flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <p class="font-semibold text-dark-900">{{ $cert->title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $cert->issuing_body }} @if($cert->certificate_number) · #{{ $cert->certificate_number }}@endif</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            @if($cert->expiry_date)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $cert->isExpired() ? 'bg-rose-100 text-rose-700' : ($cert->isExpiringSoon() ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                    {{ $cert->isExpired() ? 'Expired' : ($cert->isExpiringSoon() ? 'Expiring Soon' : 'Valid') }}
                                </span>
                                <p class="text-xs text-gray-400 mt-1">Expires {{ $cert->expiry_date->format('d M Y') }}</p>
                            @else
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">No Expiry</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Add Certification Modal --}}
            @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr', 'sales_manager']))
            <div x-data="{ open: false }" x-on:open-cert-modal.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/50" @click.self="open = false">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                    <h3 class="font-bold text-dark-900 text-lg mb-4">Add Certification</h3>
                    <form method="POST" action="{{ route('hr.staff.certifications.store', $user) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Title *</label>
                            <input type="text" name="title" required placeholder="e.g. Real Estate Practitioner License" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Issuing Body</label>
                                <input type="text" name="issuing_body" placeholder="e.g. NIESV" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Cert. Number</label>
                                <input type="text" name="certificate_number" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Issued Date</label>
                                <input type="date" name="issued_date" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Expiry Date</label>
                                <input type="date" name="expiry_date" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="flex-1 bg-brand-500 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-600 transition-colors">Save</button>
                            <button type="button" @click="open = false" class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

        {{-- Performance Reviews Tab --}}
        <div x-show="tab === 'reviews'" class="space-y-4">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-dark-900">Performance Reviews</h2>
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr', 'sales_manager']))
                    <button x-data @click="$dispatch('open-review-modal')" class="text-xs bg-brand-500 text-white px-3 py-1.5 rounded-xl font-semibold hover:bg-brand-600 transition-colors">+ Add Review</button>
                    @endif
                </div>
                @if($reviews->isEmpty())
                    <div class="p-8 text-center text-gray-400 text-sm">No reviews recorded.</div>
                @else
                <div class="divide-y divide-gray-50">
                    @foreach($reviews as $review)
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4 mb-3">
                            <div>
                                <p class="font-semibold text-dark-900">{{ $review->review_period }}</p>
                                <p class="text-xs text-gray-500">Reviewed by {{ $review->reviewer->name }} · {{ $review->created_at->format('d M Y') }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                @if($review->score)
                                <p class="text-2xl font-extrabold {{ $review->score >= 80 ? 'text-emerald-600' : ($review->score >= 60 ? 'text-amber-600' : 'text-rose-600') }}">{{ $review->score }}<span class="text-sm font-normal text-gray-400">/100</span></p>
                                @endif
                                @if($review->rating)
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                    {{ $review->rating === 'excellent' ? 'bg-emerald-100 text-emerald-700' :
                                       ($review->rating === 'good' ? 'bg-blue-100 text-blue-700' :
                                       ($review->rating === 'average' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700')) }}">
                                    {{ ucfirst($review->rating) }}
                                </span>
                                @endif
                            </div>
                        </div>
                        @if($review->strengths)
                        <p class="text-xs text-gray-600 bg-emerald-50 rounded-xl px-3 py-2 mb-2"><strong class="text-emerald-700">Strengths:</strong> {{ $review->strengths }}</p>
                        @endif
                        @if($review->areas_for_improvement)
                        <p class="text-xs text-gray-600 bg-amber-50 rounded-xl px-3 py-2"><strong class="text-amber-700">Areas for improvement:</strong> {{ $review->areas_for_improvement }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Add Review Modal --}}
            @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr', 'sales_manager']))
            <div x-data="{ open: false }" x-on:open-review-modal.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/50" @click.self="open = false">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 max-h-screen overflow-y-auto">
                    <h3 class="font-bold text-dark-900 text-lg mb-4">Add Performance Review</h3>
                    <form method="POST" action="{{ route('hr.staff.reviews.store', $user) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-3 gap-3">
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Review Period *</label>
                                <input type="text" name="review_period" required placeholder="e.g. Q2-2025 or Annual-2025" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Score (0-100)</label>
                                <input type="number" name="score" min="0" max="100" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Rating</label>
                            <select name="rating" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                <option value="">Select rating...</option>
                                @foreach(\App\Models\PerformanceReview::RATINGS as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Strengths</label>
                            <textarea name="strengths" rows="2" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Areas for Improvement</label>
                            <textarea name="areas_for_improvement" rows="2" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Manager Comments</label>
                            <textarea name="manager_comments" rows="2" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none resize-none"></textarea>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="flex-1 bg-brand-500 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-600 transition-colors">Save Review</button>
                            <button type="button" @click="open = false" class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

        {{-- Disciplinary Tab --}}
        <div x-show="tab === 'disciplinary'" class="space-y-4">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-dark-900">Disciplinary Records</h2>
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr']))
                    <button x-data @click="$dispatch('open-disc-modal')" class="text-xs bg-rose-500 text-white px-3 py-1.5 rounded-xl font-semibold hover:bg-rose-600 transition-colors">+ Add Record</button>
                    @endif
                </div>
                @if($disciplinary->isEmpty())
                    <div class="p-8 text-center text-gray-400 text-sm">No disciplinary records.</div>
                @else
                <div class="divide-y divide-gray-50">
                    @foreach($disciplinary as $record)
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full
                                        {{ $record->incident_type === 'termination' ? 'bg-rose-100 text-rose-700' :
                                           ($record->incident_type === 'suspension' ? 'bg-orange-100 text-orange-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ \App\Models\DisciplinaryRecord::TYPES[$record->incident_type] ?? $record->incident_type }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $record->incident_date->format('d M Y') }}</span>
                                </div>
                                <p class="text-sm text-gray-700">{{ $record->description }}</p>
                                <p class="text-xs text-gray-500 mt-1">Action: {{ $record->action_taken }}</p>
                                <p class="text-xs text-gray-400 mt-1">Issued by {{ $record->issuedBy->name }}</p>
                            </div>
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full flex-shrink-0
                                {{ $record->status === 'resolved' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ ucfirst($record->status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Add Disciplinary Modal --}}
            @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr']))
            <div x-data="{ open: false }" x-on:open-disc-modal.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/50" @click.self="open = false">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                    <h3 class="font-bold text-dark-900 text-lg mb-4">Add Disciplinary Record</h3>
                    <form method="POST" action="{{ route('hr.staff.disciplinary.store', $user) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Type *</label>
                                <select name="incident_type" required class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                    @foreach(\App\Models\DisciplinaryRecord::TYPES as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Incident Date *</label>
                                <input type="date" name="incident_date" required value="{{ today()->format('Y-m-d') }}" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Description *</label>
                            <textarea name="description" rows="3" required class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Action Taken *</label>
                            <textarea name="action_taken" rows="2" required class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none resize-none"></textarea>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="flex-1 bg-rose-500 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-rose-600 transition-colors">Save Record</button>
                            <button type="button" @click="open = false" class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

        {{-- Onboarding Tab --}}
        <div x-show="tab === 'onboarding'" class="space-y-4">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-semibold text-dark-900">Onboarding Checklist Progress</h2>
                        <p class="text-xs text-gray-500 mt-1">Checklist progress for the new hire.</p>
                    </div>
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr']))
                    <button x-data @click="$dispatch('open-onboarding-modal')" class="text-xs bg-brand-500 text-white px-3 py-1.5 rounded-xl font-semibold hover:bg-brand-600 transition-colors">+ Add Task</button>
                    @endif
                </div>

                {{-- Progress Bar --}}
                <div class="px-5 py-4 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-brand-500 h-3 rounded-full transition-all duration-300" style="width: {{ $user->onboardingPercentage() }}%"></div>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-brand-600 flex-shrink-0">{{ $user->onboardingPercentage() }}% Complete</span>
                </div>

                @if($onboardingTasks->isEmpty())
                    <div class="p-8 text-center text-gray-400 text-sm">No onboarding tasks set up for this staff member.</div>
                @else
                <div class="divide-y divide-gray-50">
                    @foreach($onboardingTasks as $task)
                    <div class="p-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <form action="{{ route('hr.staff.onboarding.toggle', $task->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="flex-shrink-0 w-6 h-6 rounded-lg border-2 {{ $task->is_completed ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-gray-300 hover:border-brand-500' }} flex items-center justify-center transition-all">
                                    @if($task->is_completed)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    @endif
                                </button>
                            </form>
                            <div>
                                <p class="font-semibold {{ $task->is_completed ? 'text-gray-400 line-through' : 'text-dark-900' }}">{{ $task->task_name }}</p>
                                <p class="text-[10px] text-gray-400">
                                    Assigned by: {{ $task->assignedBy->name ?? 'N/A' }} 
                                    @if($task->due_date) · Due: {{ $task->due_date->format('d M Y') }}@endif
                                    @if($task->is_completed && $task->completed_at) · Completed: {{ $task->completed_at->format('d M Y H:i') }}@endif
                                </p>
                            </div>
                        </div>
                        @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr']))
                        <form action="{{ route('hr.staff.onboarding.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Remove this onboarding task?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100/50 p-1.5 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Add Onboarding Task Modal --}}
            @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr']))
            <div x-data="{ open: false }" x-on:open-onboarding-modal.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/50" @click.self="open = false">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                    <h3 class="font-bold text-dark-900 text-lg mb-4">Add Onboarding Task</h3>
                    <form method="POST" action="{{ route('hr.staff.onboarding.store', $user) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Task Name *</label>
                            <input type="text" name="task_name" required placeholder="e.g. Set up direct deposit" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Due Date</label>
                            <input type="date" name="due_date" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="flex-1 bg-brand-500 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-600 transition-colors">Add Task</button>
                            <button type="button" @click="open = false" class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
