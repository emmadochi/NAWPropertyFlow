@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">🎯 Departmental Goals &amp; Targets</h1>
            <p class="text-xs text-gray-500 mt-1">Configure and manage monthly KPI milestones across all business units.</p>
        </div>
        <a href="{{ route('hr.leaderboard') }}" class="text-xs font-semibold text-gray-500 hover:text-brand-600 flex items-center gap-1">
            ← Staff Leaderboard
        </a>
    </div>

    {{-- Month/Year Filter --}}
    <form method="GET" action="{{ route('hr.department-targets.index') }}" class="flex items-center gap-2 bg-white p-3 rounded-2xl border border-gray-150 shadow-sm w-fit">
        <select name="month" class="bg-gray-50 border border-gray-250 rounded-xl px-3 py-1.5 text-xs focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none font-bold text-gray-600">
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
            @endfor
        </select>
        <select name="year" class="bg-gray-50 border border-gray-250 rounded-xl px-3 py-1.5 text-xs focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none font-bold text-gray-600">
            @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        <button type="submit" class="bg-brand-500 text-white px-4 py-1.5 rounded-xl text-xs font-bold hover:bg-brand-600 shadow-md shadow-brand-500/10 transition-all">Filter</button>
    </form>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- List of Current Targets (Left 2 cols) --}}
        <div class="lg:col-span-2 space-y-6">
            @foreach($departments as $dept)
            @php $deptTargets = $targets->get($dept->id) ?? collect(); @endphp
            <div class="bg-white rounded-3xl border border-gray-150 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="text-lg">
                            {{ $dept->icon ?? '🏢' }}
                        </span>
                        <h3 class="font-bold text-dark-900 text-sm">{{ $dept->name }} Department Goals</h3>
                    </div>
                    <span class="text-[10px] font-bold text-brand-600 bg-brand-50 border border-brand-100 px-2.5 py-1 rounded-full uppercase tracking-wider">
                        {{ \Carbon\Carbon::create()->month($month)->format('M') }} {{ $year }}
                    </span>
                </div>

                @if($deptTargets->isEmpty())
                    <div class="p-8 text-center text-xs text-gray-400 font-semibold">No targets configured for this month. Set targets on the right panel.</div>
                @else
                    <div class="divide-y divide-gray-100 text-xs">
                        @foreach($deptTargets as $t)
                        <div class="p-5 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Metric Key</span>
                                <p class="text-sm font-extrabold text-dark-900 capitalize">{{ str_replace('_', ' ', $t->metric) }}</p>
                            </div>
                            <div class="flex space-x-6 items-center">
                                <div class="text-right">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Target Value</span>
                                    <p class="text-sm font-extrabold text-brand-600">
                                        @if(str_contains($t->metric, 'revenue') || str_contains($t->metric, 'value'))
                                            ₦{{ number_format($t->target_value, 2) }}
                                        @else
                                            {{ number_format($t->target_value, 0) }}
                                        @endif
                                    </p>
                                </div>
                                @if($t->actual_value !== null)
                                <div class="text-right border-l pl-6 border-gray-100">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actual Achieved</span>
                                    <p class="text-sm font-extrabold text-emerald-600">
                                        @if(str_contains($t->metric, 'revenue') || str_contains($t->metric, 'value'))
                                            ₦{{ number_format($t->actual_value, 2) }}
                                        @else
                                            {{ number_format($t->actual_value, 0) }}
                                        @endif
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Set Targets Form (Right col) --}}
        <div>
            <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm sticky top-6 space-y-5" x-data="targetSelector()">
                <div>
                    <h2 class="font-bold text-dark-900 text-sm">Configure Department Target</h2>
                    <p class="text-[11px] text-gray-400 mt-1">Select a department and target metric to establish monthly milestones.</p>
                </div>

                <form method="POST" action="{{ route('hr.department-targets.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="target_month" value="{{ $month }}">
                    <input type="hidden" name="target_year" value="{{ $year }}">

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Department *</label>
                        <select name="department_id" x-model="selectedDeptId" @change="updateMetrics()" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white text-gray-700 font-semibold">
                            <template x-for="dept in departments">
                                <option :value="dept.id" x-text="dept.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">KPI Target Metric *</label>
                        <select name="metric" x-model="selectedMetric" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white text-gray-700 font-semibold">
                            <template x-for="m in metrics">
                                <option :value="m.key" x-text="m.label"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2" x-text="getValueLabel()"></label>
                        <input type="number" step="any" name="target_value" required min="0" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white font-semibold text-gray-800" placeholder="e.g. 50">
                    </div>

                    <div x-show="isManualMetric()">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Actual Value Achieved (Optional)</label>
                        <input type="number" step="any" name="actual_value" min="0" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white font-semibold text-gray-800" placeholder="e.g. 45">
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md shadow-brand-500/10 transition-all pt-3">
                        Establish Target Milestone
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function targetSelector() {
    return {
        selectedDeptId: '',
        selectedMetric: '',
        metrics: [],
        departments: @json($departments),

        init() {
            if (this.departments.length > 0) {
                this.selectedDeptId = this.departments[0].id;
                this.updateMetrics();
            }
        },

        updateMetrics() {
            const dept = this.departments.find(d => d.id == this.selectedDeptId);
            if (dept) {
                this.metrics = dept.metrics || [];
                if (this.metrics.length > 0) {
                    this.selectedMetric = this.metrics[0].key;
                } else {
                    this.selectedMetric = '';
                }
            }
        },

        getValueLabel() {
            const m = this.metrics.find(m => m.key === this.selectedMetric);
            if (m && m.unit === 'currency') {
                return 'Target Amount (₦) *';
            }
            return 'Target Count *';
        },

        isManualMetric() {
            const m = this.metrics.find(m => m.key === this.selectedMetric);
            return m && m.type === 'manual';
        }
    }
}
</script>
@endpush
@endsection
