@extends('layouts.app')

@section('content')
<div class="space-y-8 print:space-y-4">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0 pb-4 border-b border-gray-200 print:border-b-0 print:pb-0">
        <div>
            <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Advanced Reports & Export Center</h1>
            <p class="text-sm text-gray-500 mt-1 print:hidden">Perform multi-branch comparisons, analyze conversions, and extract transaction logs.</p>
        </div>
        <div class="flex flex-wrap gap-2 print:hidden">
            <button onclick="window.print()" 
                class="inline-flex items-center space-x-2 px-4 py-2.5 bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 rounded-xl transition-all shadow-sm text-xs font-bold">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Print Report / Save PDF</span>
            </button>
            <a href="{{ route('reports.export.leads') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 rounded-xl transition-all shadow-sm text-xs font-bold">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Export Raw Leads (CSV)</span>
            </a>
            <a href="{{ route('reports.export.sales') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition-all shadow-md text-xs">
                <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Export Raw Sales (CSV)</span>
            </a>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white p-6 rounded-3xl border border-gray-150 shadow-sm print:hidden">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
            </div>
            @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin())
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Filter Branch</label>
                <select name="branch_id"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                    <option value="all">All Branches</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <input type="hidden" name="branch_id" value="{{ $branchId }}">
            <div></div>
            @endif
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl transition-all shadow-md">
                    Apply Filters
                </button>
                <a href="{{ route('reports.index') }}" class="py-3 px-4 bg-gray-150 hover:bg-gray-200 text-gray-700 font-bold text-sm rounded-xl text-center transition-all">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Active Filters Alert for Print -->
    <div class="hidden print:block bg-gray-50 border border-gray-200 p-4 rounded-xl text-xs space-y-1">
        <p class="font-bold text-gray-800">Filtered Report Summary</p>
        <p class="text-gray-600">Reporting Period: <span class="font-semibold">{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}</span> to <span class="font-semibold">{{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</span></p>
        <p class="text-gray-600">Branch Scope: <span class="font-semibold">{{ $branchId && $branchId !== 'all' ? $branches->firstWhere('id', $branchId)->name ?? 'All Branches' : 'All Branches' }}</span></p>
    </div>

    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm print:border-gray-300">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Gross Transaction Revenue</span>
            <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">₦{{ number_format($metrics['total_revenue'] ?? 0, 2) }}</h3>
            <p class="text-xs text-gray-500 mt-1">Generated from {{ $metrics['closed_deals'] ?? 0 }} closed won deals.</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm print:border-gray-300">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Overall Conversion Rate</span>
            <h3 class="text-2xl font-extrabold text-brand-600 mt-1">{{ $metrics['conversion_rate'] ?? 0 }}%</h3>
            <p class="text-xs text-gray-500 mt-1">Ratio of Won deals to Total captured leads.</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm print:border-gray-300">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Hostings</span>
            <h3 class="text-2xl font-extrabold text-blue-600 mt-1">{{ $metrics['scheduled_inspections'] ?? 0 }} Tours</h3>
            <p class="text-xs text-gray-500 mt-1">Currently active scheduled site inspections.</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 print:hidden">
        
        <!-- Chart 1: Leads Trend -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <h3 class="text-xs font-bold text-dark-900 uppercase tracking-wider mb-4">Leads Capture Trend</h3>
            <div class="relative h-60">
                <canvas id="leadsTrendChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Lead Sources -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <h3 class="text-xs font-bold text-dark-900 uppercase tracking-wider mb-4">Lead Sources Distribution</h3>
            <div class="relative h-60">
                <canvas id="sourcesChart"></canvas>
            </div>
        </div>

        <!-- Chart 3: Revenue Trend -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <h3 class="text-xs font-bold text-dark-900 uppercase tracking-wider mb-4">Revenue Closed Trend</h3>
            <div class="relative h-60">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Tables Grid -->
    <div class="space-y-8 print:space-y-6">

        @if(Auth::user()->isSuperAdmin() || Auth::user()->isCompanyAdmin())
        <!-- Branch-Level Performance Comparison -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col print:border-gray-300">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center print:bg-white print:border-b-2">
                <div>
                    <h3 class="font-extrabold text-dark-900 text-sm">Branch Performance Comparison</h3>
                    <p class="text-[10px] text-gray-500 print:hidden">Metrics compared across company branch offices.</p>
                </div>
                <a href="{{ route('reports.export.branch-comparison', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                   class="inline-flex items-center space-x-1.5 px-3 py-1.5 text-xs font-bold text-brand-600 bg-brand-50 hover:bg-brand-100 border border-brand-100 rounded-xl transition-all shadow-sm print:hidden">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Export Excel (CSV)</span>
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-100/50 border-b border-gray-200 print:bg-white print:border-b-2">
                            <th class="px-6 py-3 font-bold text-gray-500">Branch Name</th>
                            <th class="px-6 py-3 font-bold text-gray-500 text-center">Total Leads</th>
                            <th class="px-6 py-3 font-bold text-gray-500 text-center">Closed Deals</th>
                            <th class="px-6 py-3 font-bold text-gray-500 text-center">Conversion %</th>
                            <th class="px-6 py-3 font-bold text-gray-500 text-right">Gross Revenue (₦)</th>
                            <th class="px-6 py-3 font-bold text-gray-500 text-right">Top Sales Representative</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($branchComparison as $branch)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4 font-bold text-dark-900">{{ $branch->name }}</td>
                            <td class="px-6 py-4 text-center text-gray-800">{{ $branch->total_leads }}</td>
                            <td class="px-6 py-4 text-center text-gray-800">{{ $branch->closed_deals }}</td>
                            <td class="px-6 py-4 text-center font-bold text-brand-600">{{ $branch->conversion_rate }}%</td>
                            <td class="px-6 py-4 text-right font-extrabold text-emerald-600">₦{{ number_format($branch->gross_revenue, 2) }}</td>
                            <td class="px-6 py-4 text-right text-gray-500 text-[11px]">{{ $branch->top_agent }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-400">No branch data available for this range.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Table 1: Leads by Source -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col print:border-gray-300">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center print:bg-white print:border-b-2">
                    <div>
                        <h3 class="font-extrabold text-dark-900 text-sm">Leads Performance by Source</h3>
                        <p class="text-[10px] text-gray-500 print:hidden">Conversion breakdown by incoming channel.</p>
                    </div>
                    <a href="{{ route('reports.export.leads-by-source', ['start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $branchId]) }}" 
                       class="inline-flex items-center space-x-1.5 px-3 py-1.5 text-xs font-bold text-brand-600 bg-brand-50 hover:bg-brand-100 border border-brand-100 rounded-xl transition-all shadow-sm print:hidden">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>Export CSV</span>
                    </a>
                </div>
                
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-100/50 border-b border-gray-200 print:bg-white print:border-b-2">
                                <th class="px-6 py-3 font-bold text-gray-500">Source</th>
                                <th class="px-6 py-3 font-bold text-gray-500 text-center">Total Leads</th>
                                <th class="px-6 py-3 font-bold text-gray-500 text-center">Active</th>
                                <th class="px-6 py-3 font-bold text-gray-500 text-center">Won</th>
                                <th class="px-6 py-3 font-bold text-gray-500 text-right">Conversion Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($leadsBySource as $row)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-4 font-bold text-dark-900">{{ $row->lead_source }}</td>
                                <td class="px-6 py-4 text-center text-gray-800">{{ $row->total_leads }}</td>
                                <td class="px-6 py-4 text-center text-amber-600">{{ $row->active_leads }}</td>
                                <td class="px-6 py-4 text-center text-emerald-600 font-semibold">{{ $row->won_leads }}</td>
                                <td class="px-6 py-4 text-right font-extrabold text-brand-600">{{ $row->conversion_rate }}%</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-400">No data found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Table 2: Sales by Agent -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col print:border-gray-300">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center print:bg-white print:border-b-2">
                    <div>
                        <h3 class="font-extrabold text-dark-900 text-sm">Agent Revenue Performance</h3>
                        <p class="text-[10px] text-gray-500 print:hidden">Revenue and average deal size generated by agents.</p>
                    </div>
                    <a href="{{ route('reports.export.sales-by-agent', ['start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $branchId]) }}" 
                       class="inline-flex items-center space-x-1.5 px-3 py-1.5 text-xs font-bold text-brand-600 bg-brand-50 hover:bg-brand-100 border border-brand-100 rounded-xl transition-all shadow-sm print:hidden">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>Export CSV</span>
                    </a>
                </div>
                
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-100/50 border-b border-gray-200 print:bg-white print:border-b-2">
                                <th class="px-6 py-3 font-bold text-gray-500">Agent Name</th>
                                <th class="px-6 py-3 font-bold text-gray-500 text-center">Closed Deals</th>
                                <th class="px-6 py-3 font-bold text-gray-500 text-right">Avg Value</th>
                                <th class="px-6 py-3 font-bold text-gray-500 text-right">Gross Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($salesByAgent as $row)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-dark-900">{{ $row->name }}</div>
                                    <span class="text-[10px] text-gray-400 capitalize">{{ str_replace('_', ' ', $row->role) }}</span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-gray-800">{{ $row->deals_closed }}</td>
                                <td class="px-6 py-4 text-right text-gray-700">₦{{ number_format($row->avg_deal_value, 2) }}</td>
                                <td class="px-6 py-4 text-right font-extrabold text-emerald-600">₦{{ number_format($row->gross_revenue ?? 0, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-400">No sales record found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Table 3: Follow-up Compliance Rate -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col print:border-gray-300">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center print:bg-white print:border-b-2">
                    <div>
                        <h3 class="font-extrabold text-dark-900 text-sm">Follow-up Task Compliance</h3>
                        <p class="text-[10px] text-gray-500 print:hidden">Tasks completed vs. overdue tasks per representative.</p>
                    </div>
                    <a href="{{ route('reports.export.followup-compliance', ['start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $branchId]) }}" 
                       class="inline-flex items-center space-x-1.5 px-3 py-1.5 text-xs font-bold text-brand-600 bg-brand-50 hover:bg-brand-100 border border-brand-100 rounded-xl transition-all shadow-sm print:hidden">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>Export CSV</span>
                    </a>
                </div>
                
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-100/50 border-b border-gray-200 print:bg-white print:border-b-2">
                                <th class="px-6 py-3 font-bold text-gray-500">Agent</th>
                                <th class="px-6 py-3 font-bold text-gray-500 text-center">Total Tasks</th>
                                <th class="px-6 py-3 font-bold text-gray-500 text-center">Completed</th>
                                <th class="px-6 py-3 font-bold text-gray-500 text-center">Overdue</th>
                                <th class="px-6 py-3 font-bold text-gray-500 text-right">Compliance Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($followUpCompliance as $row)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-4 font-bold text-dark-900">{{ $row->agent_name }}</td>
                                <td class="px-6 py-4 text-center text-gray-800">{{ $row->total }}</td>
                                <td class="px-6 py-4 text-center text-emerald-600">{{ $row->completed }}</td>
                                <td class="px-6 py-4 text-center text-rose-600">{{ $row->overdue }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold
                                        {{ $row->compliance_rate >= 80 ? 'bg-emerald-100 text-emerald-800' : ($row->compliance_rate >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $row->compliance_rate }}%
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-400">No task activity logged.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Property Interest Statistics -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col print:border-gray-300">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center print:bg-white print:border-b-2">
                    <div>
                        <h3 class="font-extrabold text-dark-900 text-sm">Property Performance Summary</h3>
                        <p class="text-[10px] text-gray-500 print:hidden">Interest levels and transaction values by building block.</p>
                    </div>
                </div>
                
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-100/50 border-b border-gray-200 print:bg-white print:border-b-2">
                                <th class="px-6 py-3 font-bold text-gray-500">Property Details</th>
                                <th class="px-6 py-3 font-bold text-gray-500 text-center">Units Stock</th>
                                <th class="px-6 py-3 font-bold text-gray-500 text-right">Revenue Closed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($properties_report as $prop)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-dark-900">{{ $prop->name }}</div>
                                    <span class="text-[10px] text-gray-400">{{ $prop->location }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($prop->available_units > 0)
                                    <span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">{{ $prop->available_units }} units left</span>
                                    @else
                                    <span class="font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">Sold Out</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-extrabold text-emerald-600 text-right">
                                    ₦{{ number_format($prop->total_revenue ?? 0, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Stylesheet rules for PDF printing output -->
<style>
@media print {
    body {
        background: white !important;
        color: black !important;
        font-size: 10px !important;
    }
    .shadow-sm, .shadow-md, .shadow-2xl {
        box-shadow: none !important;
    }
    .rounded-2xl, .rounded-3xl {
        border-radius: 0 !important;
    }
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    function initReportsCharts() {
        // --- 1. Leads Trend Chart ---
        const trendCtx = document.getElementById('leadsTrendChart');
        if (trendCtx) {
            const leadsLabels = {!! json_encode($leads_by_month->pluck('month_name')) !!};
            const leadsCounts = {!! json_encode($leads_by_month->pluck('count')) !!};

            new Chart(trendCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: leadsLabels,
                    datasets: [{
                        label: 'Captured Leads',
                        data: leadsCounts,
                        borderColor: '#FEA500',
                        backgroundColor: 'rgba(254, 165, 0, 0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [5, 5] } }
                    }
                }
            });
        }

        // --- 2. Lead Sources distribution ---
        const sourcesCtx = document.getElementById('sourcesChart');
        if (sourcesCtx) {
            const sourceLabels = {!! json_encode($source_performance->pluck('lead_source')) !!};
            const sourceCounts = {!! json_encode($source_performance->pluck('count')) !!};

            new Chart(sourcesCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: sourceLabels,
                    datasets: [{
                        data: sourceCounts,
                        backgroundColor: ['#FEA500', '#3B82F6', '#10B981', '#EC4899', '#8B5CF6', '#F59E0B'],
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } }
                    }
                }
            });
        }

        // --- 3. Revenue Trend Chart ---
        const revCtx = document.getElementById('revenueTrendChart');
        if (revCtx) {
            const revLabels = {!! json_encode($sales_by_month->pluck('month_name')) !!};
            const revAmounts = {!! json_encode($sales_by_month->pluck('total')) !!};

            new Chart(revCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: revLabels,
                    datasets: [{
                        label: 'Gross Sales (₦)',
                        data: revAmounts,
                        backgroundColor: '#10B981',
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [5, 5] } }
                    }
                }
            });
        }
    }

    // Run on boot and after SPA swap
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initReportsCharts);
    } else {
        initReportsCharts();
    }
    document.addEventListener('spa-load-complete', initReportsCharts);
})();
</script>
@endpush
