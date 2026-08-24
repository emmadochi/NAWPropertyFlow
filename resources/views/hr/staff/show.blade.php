@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Profile Header --}}
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700/60 p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
            <div class="w-20 h-20 rounded-2xl bg-brand-500 text-white flex items-center justify-center text-2xl font-black flex-shrink-0 shadow-md shadow-brand-500/20">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="flex-1">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-black text-dark-900 dark:text-white">{{ $user->name }}</h1>
                        <p class="text-sm text-gray-500 dark:text-slate-400">{{ $user->email }}</p>
                        <div class="flex items-center gap-2 mt-2 flex-wrap">
                            <span class="px-2.5 py-0.5 bg-brand-50 text-brand-700 dark:bg-brand-950 dark:text-brand-300 border border-brand-200 dark:border-brand-800 rounded-full text-xs font-bold uppercase tracking-wider">{{ str_replace('_', ' ', $user->role) }}</span>
                            @if($user->branch)
                            <span class="px-2.5 py-0.5 bg-gray-100 text-gray-700 dark:bg-slate-700 dark:text-slate-300 rounded-full text-xs font-semibold">{{ $user->branch->name }}</span>
                            @endif
                            <span class="px-2.5 py-0.5 {{ ($user->status ?? 'active') === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' }} rounded-full text-xs font-bold">
                                {{ ($user->status ?? 'active') === 'active' ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('settings.index') }}" class="inline-flex items-center space-x-1 text-xs font-bold text-gray-500 hover:text-brand-600 bg-gray-50 hover:bg-gray-100 dark:bg-slate-700 dark:hover:bg-slate-600 px-3 py-1.5 rounded-xl transition-all">
                        <span>← Back to Team</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- This Month Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100 dark:border-slate-700">
            <div class="text-center p-3 bg-gray-50/50 dark:bg-slate-900/40 rounded-2xl">
                <p class="text-3xl font-black text-dark-900 dark:text-white">{{ $stats['leads'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1 font-semibold">Leads Assigned</p>
            </div>
            <div class="text-center p-3 bg-gray-50/50 dark:bg-slate-900/40 rounded-2xl">
                <p class="text-3xl font-black text-brand-600 dark:text-brand-400">{{ $stats['sales'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1 font-semibold">Deals Closed</p>
            </div>
            <div class="text-center p-3 bg-gray-50/50 dark:bg-slate-900/40 rounded-2xl">
                <p class="text-lg font-black text-emerald-600 dark:text-emerald-400">₦{{ number_format($stats['revenue'] ?? 0, 2) }}</p>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1 font-semibold">Sales Revenue</p>
            </div>
            <div class="text-center p-3 bg-gray-50/50 dark:bg-slate-900/40 rounded-2xl">
                <p class="text-3xl font-black {{ ($stats['conversionRate'] ?? 0) >= 50 ? 'text-emerald-600' : (($stats['conversionRate'] ?? 0) >= 25 ? 'text-amber-600' : 'text-rose-600') }}">{{ $stats['conversionRate'] ?? 0 }}%</p>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1 font-semibold">Conversion Rate</p>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div x-data="{ tab: 'disciplinary' }" class="space-y-4">
        <div class="flex gap-2 border-b border-gray-200 dark:border-slate-700 overflow-x-auto pb-px">
            @foreach([
                'disciplinary' => '⚠️ Disciplinary & Queries',
                'reviews' => '📊 Performance Reviews',
                'salary' => '💳 Salary & Payslips',
                'certifications' => '🎓 Certifications',
                'onboarding' => '📋 Onboarding Tasks'
            ] as $t => $label)
            <button @click="tab = '{{ $t }}'" :class="tab === '{{ $t }}' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200'"
                class="px-4 py-2.5 text-xs uppercase tracking-wider border-b-2 transition-all whitespace-nowrap">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- 1. Disciplinary Tab --}}
        <div x-show="tab === 'disciplinary'" class="space-y-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700/60 overflow-hidden shadow-sm">
                <div class="p-5 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h2 class="font-black text-dark-900 dark:text-white">Disciplinary Records &amp; Query Letters</h2>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Formal queries, warnings, suspensions, and salary fine sanctions.</p>
                    </div>
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr']))
                    <button x-data @click="$dispatch('open-disc-modal')" class="text-xs bg-rose-500 text-white px-3.5 py-2 rounded-xl font-bold hover:bg-rose-600 shadow-md shadow-rose-500/20 transition-all">+ Add Record</button>
                    @endif
                </div>
                @if($disciplinary->isEmpty())
                    <div class="p-12 text-center text-gray-400 dark:text-slate-500 text-sm font-medium">No disciplinary records or queries found for this staff member.</div>
                @else
                <div class="divide-y divide-gray-50 dark:divide-slate-700/50">
                    @foreach($disciplinary as $record)
                    <div class="p-5 hover:bg-gray-50/40 dark:hover:bg-slate-700/20 transition-colors">
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-1.5 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2.5 py-0.5 text-xs font-black rounded-full uppercase tracking-wider
                                        {{ $record->incident_type === 'termination' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' :
                                           ($record->incident_type === 'suspension' ? 'bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300' :
                                           ($record->incident_type === 'query' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300')) }}">
                                        {{ \App\Models\DisciplinaryRecord::TYPES[$record->incident_type] ?? $record->incident_type }}
                                    </span>
                                    <span class="text-xs text-gray-400 font-semibold">Incident Date: {{ $record->incident_date ? $record->incident_date->format('d M Y') : 'N/A' }}</span>
                                </div>
                                <p class="text-sm font-semibold text-dark-900 dark:text-white">{{ $record->description }}</p>
                                <div class="bg-gray-50 dark:bg-slate-900/60 p-3 rounded-xl border border-gray-100 dark:border-slate-700 text-xs">
                                    <p class="font-bold text-dark-900 dark:text-white"><strong class="text-brand-600 dark:text-brand-400">Action Taken:</strong> {{ $record->action_taken }}</p>
                                    @if($record->resolution_notes)
                                    <p class="text-gray-600 dark:text-slate-300 mt-1"><strong class="text-emerald-600">Resolution:</strong> {{ $record->resolution_notes }}</p>
                                    @endif
                                </div>
                                <p class="text-[11px] text-gray-400 font-medium">Issued by {{ $record->issuedBy?->name ?? 'HR Management' }} @if($record->resolved_at) · Resolved {{ $record->resolved_at->format('d M Y') }}@endif</p>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full flex-shrink-0
                                {{ $record->status === 'resolved' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' }}">
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
            <div x-data="{ open: false }" x-on:open-disc-modal.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/50 backdrop-blur-sm" @click.self="open = false">
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-md p-6 border border-gray-200 dark:border-slate-700">
                    <h3 class="font-black text-dark-900 dark:text-white text-lg mb-1">Issue Disciplinary Record / Query</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-4">Log a formal query, warning, or penalty for {{ $user->name }}.</p>
                    <form method="POST" action="{{ route('hr.staff.disciplinary.store', $user) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Sanction Type *</label>
                                <select name="incident_type" required class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                                    @foreach(\App\Models\DisciplinaryRecord::TYPES as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Incident Date *</label>
                                <input type="date" name="incident_date" required value="{{ today()->format('Y-m-d') }}" class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Incident Description *</label>
                            <textarea name="description" rows="3" required placeholder="Detailed summary of the infraction or complaint..." class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs focus:border-brand-500 focus:outline-none resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Sanction / Action Taken *</label>
                            <textarea name="action_taken" rows="2" required placeholder="e.g. ₦25,000 Disciplinary Salary Deduction & Written Warning" class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs focus:border-brand-500 focus:outline-none resize-none"></textarea>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="flex-1 bg-rose-500 text-white py-2.5 rounded-xl text-xs font-bold hover:bg-rose-600 transition-colors">Save Record</button>
                            <button type="button" @click="open = false" class="flex-1 border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-slate-300 py-2.5 rounded-xl text-xs font-medium hover:bg-gray-50 transition-colors">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

        {{-- 2. Performance Reviews Tab --}}
        <div x-show="tab === 'reviews'" class="space-y-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700/60 overflow-hidden shadow-sm">
                <div class="p-5 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h2 class="font-black text-dark-900 dark:text-white">Performance Appraisals</h2>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Quarterly and annual review scorecards.</p>
                    </div>
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr', 'sales_manager']))
                    <button x-data @click="$dispatch('open-review-modal')" class="text-xs bg-brand-500 text-white px-3.5 py-2 rounded-xl font-bold hover:bg-brand-600 shadow-md shadow-brand-500/20 transition-all">+ Add Review</button>
                    @endif
                </div>
                @if($reviews->isEmpty())
                    <div class="p-12 text-center text-gray-400 dark:text-slate-500 text-sm">No appraisals recorded yet.</div>
                @else
                <div class="divide-y divide-gray-50 dark:divide-slate-700/50">
                    @foreach($reviews as $review)
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h4 class="font-bold text-dark-900 dark:text-white">{{ $review->review_period }}</h4>
                                <p class="text-xs text-gray-400">Appraised by {{ $review->reviewer?->name ?? 'HR Lead' }}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($review->score)
                                <span class="px-3 py-1 bg-brand-50 text-brand-600 dark:bg-brand-950 dark:text-brand-400 font-black rounded-xl text-xs">
                                    Score: {{ $review->score }}/100
                                </span>
                                @endif
                                @if($review->rating)
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 font-bold rounded-xl text-xs uppercase">
                                    {{ str_replace('_', ' ', $review->rating) }}
                                </span>
                                @endif
                            </div>
                        </div>
                        @if($review->strengths)
                        <p class="text-xs text-emerald-800 bg-emerald-50 dark:bg-emerald-950/50 dark:text-emerald-300 rounded-xl p-3 mb-2 font-medium"><strong>Strengths:</strong> {{ $review->strengths }}</p>
                        @endif
                        @if($review->areas_for_improvement)
                        <p class="text-xs text-amber-800 bg-amber-50 dark:bg-amber-950/50 dark:text-amber-300 rounded-xl p-3 font-medium"><strong>Areas for Improvement:</strong> {{ $review->areas_for_improvement }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Add Review Modal --}}
            @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr', 'sales_manager']))
            <div x-data="{ open: false }" x-on:open-review-modal.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/50 backdrop-blur-sm" @click.self="open = false">
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-lg p-6 border border-gray-200 dark:border-slate-700 max-h-screen overflow-y-auto">
                    <h3 class="font-black text-dark-900 dark:text-white text-lg mb-4">Add Performance Appraisal</h3>
                    <form method="POST" action="{{ route('hr.staff.reviews.store', $user) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-3 gap-3">
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Review Period *</label>
                                <input type="text" name="review_period" required placeholder="e.g. Q3-2026 or Annual-2026" class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Score (0-100)</label>
                                <input type="number" name="score" min="0" max="100" placeholder="85" class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Rating</label>
                            <select name="rating" class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                                <option value="">Select rating...</option>
                                @foreach(\App\Models\PerformanceReview::RATINGS as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Strengths</label>
                            <textarea name="strengths" rows="2" class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs focus:border-brand-500 focus:outline-none resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Areas for Improvement</label>
                            <textarea name="areas_for_improvement" rows="2" class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs focus:border-brand-500 focus:outline-none resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Manager Comments</label>
                            <textarea name="manager_comments" rows="2" class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs focus:border-brand-500 focus:outline-none resize-none"></textarea>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="flex-1 bg-brand-500 text-white py-2.5 rounded-xl text-xs font-bold hover:bg-brand-600 transition-colors">Save Appraisal</button>
                            <button type="button" @click="open = false" class="flex-1 border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-slate-300 py-2.5 rounded-xl text-xs font-medium hover:bg-gray-50 transition-colors">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

        {{-- 3. Salary & Payslips Tab --}}
        <div x-show="tab === 'salary'" class="space-y-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700/60 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="font-black text-dark-900 dark:text-white">Compensation Structure</h2>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Base salary, allowances, and bank details.</p>
                    </div>
                </div>
                @if($salaryStructure)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 bg-gray-50 dark:bg-slate-900/60 rounded-2xl border border-gray-100 dark:border-slate-700">
                        <span class="text-xs font-bold text-gray-400 uppercase">Base Salary</span>
                        <p class="text-xl font-black text-dark-900 dark:text-white mt-1">₦{{ number_format($salaryStructure->base_salary, 2) }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-slate-900/60 rounded-2xl border border-gray-100 dark:border-slate-700">
                        <span class="text-xs font-bold text-gray-400 uppercase">Allowances (Housing + Transport)</span>
                        <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">₦{{ number_format($salaryStructure->housing_allowance + $salaryStructure->transport_allowance + $salaryStructure->other_allowances, 2) }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-slate-900/60 rounded-2xl border border-gray-100 dark:border-slate-700">
                        <span class="text-xs font-bold text-gray-400 uppercase">Bank Account</span>
                        <p class="text-sm font-bold text-dark-900 dark:text-white mt-1">{{ $salaryStructure->bank_name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ $salaryStructure->account_number ?? 'N/A' }}</p>
                    </div>
                </div>
                @else
                <p class="text-xs text-gray-400 italic">No salary structure configured yet for this staff member.</p>
                @endif
            </div>

            {{-- Payslips List --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700/60 overflow-hidden shadow-sm">
                <div class="p-5 border-b border-gray-100 dark:border-slate-700">
                    <h3 class="font-black text-dark-900 dark:text-white">Generated Payslips</h3>
                </div>
                @if($payslips->isEmpty())
                    <div class="p-12 text-center text-gray-400 text-sm">No payslips generated yet.</div>
                @else
                <div class="divide-y divide-gray-50 dark:divide-slate-700/50">
                    @foreach($payslips as $slip)
                    <div class="p-4 flex items-center justify-between gap-4">
                        <div>
                            <p class="font-bold text-dark-900 dark:text-white text-sm">{{ $slip->payrollBatch->title }}</p>
                            <p class="text-xs text-gray-500">Gross: ₦{{ number_format($slip->gross_pay, 2) }} | Deductions: -₦{{ number_format($slip->total_deductions, 2) }}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="font-black text-brand-600 dark:text-brand-400 text-sm">Net: ₦{{ number_format($slip->net_pay, 2) }}</span>
                            <a href="{{ route('payroll.payslip.download', $slip->id) }}" target="_blank" class="px-3 py-1.5 rounded-xl bg-brand-50 hover:bg-brand-100 dark:bg-brand-950 text-brand-600 dark:text-brand-400 text-xs font-bold transition-all">
                                View PDF
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- 4. Certifications Tab --}}
        <div x-show="tab === 'certifications'" class="space-y-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700/60 overflow-hidden shadow-sm">
                <div class="p-5 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h2 class="font-black text-dark-900 dark:text-white">Certifications &amp; Licenses</h2>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Professional credentials, REDAN licenses, and training.</p>
                    </div>
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr', 'sales_manager']))
                    <button x-data @click="$dispatch('open-cert-modal')" class="text-xs bg-brand-500 text-white px-3.5 py-2 rounded-xl font-bold hover:bg-brand-600 shadow-md shadow-brand-500/20 transition-all">+ Add</button>
                    @endif
                </div>
                @if($certifications->isEmpty())
                    <div class="p-12 text-center text-gray-400 text-sm">No certifications recorded.</div>
                @else
                <div class="divide-y divide-gray-50 dark:divide-slate-700/50">
                    @foreach($certifications as $cert)
                    <div class="p-4 flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <p class="font-bold text-dark-900 dark:text-white text-sm">{{ $cert->title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $cert->issuing_body }} @if($cert->certificate_number) · #{{ $cert->certificate_number }}@endif</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            @if($cert->expiry_date)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase {{ $cert->isExpired() ? 'bg-rose-100 text-rose-700' : ($cert->isExpiringSoon() ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                    {{ $cert->isExpired() ? 'Expired' : ($cert->isExpiringSoon() ? 'Expiring Soon' : 'Valid') }}
                                </span>
                                <p class="text-[11px] text-gray-400 mt-1">Expires {{ $cert->expiry_date->format('d M Y') }}</p>
                            @else
                                <span class="px-2.5 py-1 bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-300 rounded-full text-[10px] font-black uppercase">No Expiry</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Add Certification Modal --}}
            @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr', 'sales_manager']))
            <div x-data="{ open: false }" x-on:open-cert-modal.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/50 backdrop-blur-sm" @click.self="open = false">
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-md p-6 border border-gray-200 dark:border-slate-700">
                    <h3 class="font-black text-dark-900 dark:text-white text-lg mb-4">Add Certification</h3>
                    <form method="POST" action="{{ route('hr.staff.certifications.store', $user) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Title *</label>
                            <input type="text" name="title" required placeholder="e.g. Certified Real Estate Sales Professional" class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Issuing Body</label>
                                <input type="text" name="issuing_body" placeholder="e.g. REDAN" class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Cert. Number</label>
                                <input type="text" name="certificate_number" class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Issued Date</label>
                                <input type="date" name="issued_date" class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Expiry Date</label>
                                <input type="date" name="expiry_date" class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="flex-1 bg-brand-500 text-white py-2.5 rounded-xl text-xs font-bold hover:bg-brand-600 transition-colors">Save</button>
                            <button type="button" @click="open = false" class="flex-1 border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-slate-300 py-2.5 rounded-xl text-xs font-medium hover:bg-gray-50 transition-colors">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

        {{-- 5. Onboarding Tab --}}
        <div x-show="tab === 'onboarding'" class="space-y-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700/60 overflow-hidden shadow-sm">
                <div class="p-5 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h2 class="font-black text-dark-900 dark:text-white">Onboarding Checklist Progress</h2>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Probation &amp; compliance checklist.</p>
                    </div>
                    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr']))
                    <button x-data @click="$dispatch('open-onboarding-modal')" class="text-xs bg-brand-500 text-white px-3.5 py-2 rounded-xl font-bold hover:bg-brand-600 shadow-md shadow-brand-500/20 transition-all">+ Add Task</button>
                    @endif
                </div>

                {{-- Progress Bar --}}
                <div class="px-5 py-4 bg-gray-50/50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="w-full bg-gray-200 dark:bg-slate-700 rounded-full h-3">
                            <div class="bg-brand-500 h-3 rounded-full transition-all duration-300" style="width: {{ $user->onboardingPercentage() }}%"></div>
                        </div>
                    </div>
                    <span class="text-xs font-black text-brand-600 dark:text-brand-400 flex-shrink-0">{{ $user->onboardingPercentage() }}% Complete</span>
                </div>

                @if($onboardingTasks->isEmpty())
                    <div class="p-12 text-center text-gray-400 text-sm">No onboarding tasks set up for this staff member.</div>
                @else
                <div class="divide-y divide-gray-50 dark:divide-slate-700/50">
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
                                <p class="font-bold text-sm {{ $task->is_completed ? 'text-gray-400 line-through' : 'text-dark-900 dark:text-white' }}">{{ $task->task_name }}</p>
                                <p class="text-[11px] text-gray-400">
                                    Assigned by: {{ $task->assignedBy?->name ?? 'HR Management' }} 
                                    @if($task->due_date) · Due: {{ $task->due_date->format('d M Y') }}@endif
                                    @if($task->is_completed && $task->completed_at) · Completed: {{ $task->completed_at->format('d M Y H:i') }}@endif
                                </p>
                            </div>
                        </div>
                        @if(in_array(Auth::user()->role, ['super_admin', 'company_admin', 'hr']))
                        <form action="{{ route('hr.staff.onboarding.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Remove this onboarding task?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 p-1.5 rounded-lg transition-colors">
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
            <div x-data="{ open: false }" x-on:open-onboarding-modal.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/50 backdrop-blur-sm" @click.self="open = false">
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-md p-6 border border-gray-200 dark:border-slate-700">
                    <h3 class="font-black text-dark-900 dark:text-white text-lg mb-4">Add Onboarding Task</h3>
                    <form method="POST" action="{{ route('hr.staff.onboarding.store', $user) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Task Name *</label>
                            <input type="text" name="task_name" required placeholder="e.g. Set up bank account for payroll" class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 dark:text-slate-300 mb-1">Due Date</label>
                            <input type="date" name="due_date" class="w-full border border-gray-200 dark:border-slate-700 dark:bg-slate-900 rounded-xl px-3 py-2.5 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="flex-1 bg-brand-500 text-white py-2.5 rounded-xl text-xs font-bold hover:bg-brand-600 transition-colors">Add Task</button>
                            <button type="button" @click="open = false" class="flex-1 border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-slate-300 py-2.5 rounded-xl text-xs font-medium hover:bg-gray-50 transition-colors">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
