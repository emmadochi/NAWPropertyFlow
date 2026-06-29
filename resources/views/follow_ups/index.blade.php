@extends('layouts.app')

@section('content')
<div class="space-y-8" x-data="{ addFollowUpOpen: false, completeFollowUpOpen: false, selectedFollowUpId: null, completionNotes: '', activeView: localStorage.getItem('followUpsView') || 'queue', selectedDate: '' }" x-init="$watch('activeView', v => localStorage.setItem('followUpsView', v))">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Follow-Ups Queue</h1>
            <p class="text-sm text-gray-500 mt-1">Schedule and execute automated follow-up touchpoints.</p>
        </div>
        <div class="flex items-center space-x-2">
            <!-- View Toggle Buttons -->
            <div class="flex items-center bg-gray-100 rounded-xl p-1">
                <button @click="activeView = 'queue'"
                    :class="activeView === 'queue' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 text-xs font-bold rounded-lg transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Queue</span>
                </button>
                <button @click="activeView = 'calendar'"
                    :class="activeView === 'calendar' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500 hover:text-gray-700'"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 text-xs font-bold rounded-lg transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Calendar</span>
                </button>
            </div>
            <button @click="addFollowUpOpen = true" class="inline-flex items-center space-x-2 px-5 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/10 hover:shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Add Follow-Up Task</span>
            </button>
        </div>
    </div>

    <!-- Category Columns Layout (3 Columns: Overdue, Today, Tomorrow) -->
    <div x-show="activeView === 'queue'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Column 1: Overdue -->
        <div class="bg-rose-50/50 rounded-3xl p-5 border border-rose-100 flex flex-col h-[600px]">
            <div class="flex items-center justify-between pb-3 border-b border-rose-200 mb-4 flex-shrink-0">
                <h3 class="font-bold text-rose-800 text-sm flex items-center space-x-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-600 animate-pulse"></span>
                    <span>Overdue Task Reminders</span>
                </h3>
                <span class="px-2 py-0.5 text-xs font-bold bg-rose-100 text-rose-700 rounded-md">{{ $overdue->count() }}</span>
            </div>

            <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                @forelse($overdue as $task)
                <div class="bg-white p-4 rounded-2xl border border-rose-150 shadow-sm flex flex-col justify-between space-y-3 hover:shadow-md transition-all">
                    <div class="space-y-1">
                        <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-wider text-rose-600">
                            <span>{{ $task->type }}</span>
                            <span class="bg-rose-50 px-1.5 py-0.2 rounded-md">LATE: {{ $task->due_date->diffForHumans() }}</span>
                        </div>
                        <h4 class="font-bold text-dark-900 text-sm">
                            <a href="{{ route('leads.show', $task->lead_id) }}" class="hover:underline">{{ $task->lead->full_name }}</a>
                        </h4>
                        <p class="text-xs text-gray-600 leading-relaxed">{{ $task->notes }}</p>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-50">
                        <span class="text-[10px] text-gray-400 font-semibold">{{ $task->due_date->format('M d, H:i A') }}</span>
                        <button @click="selectedFollowUpId = {{ $task->id }}; completeFollowUpOpen = true" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100 transition-all flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Resolve</span>
                        </button>
                    </div>
                </div>
                @empty
                <div class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-400">
                    <h5 class="text-xs font-bold text-rose-800">No overdue tasks</h5>
                    <p class="text-[10px] text-gray-500 mt-1">Excellent job! All assignments are updated.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Column 2: Due Today -->
        <div class="bg-amber-50/50 rounded-3xl p-5 border border-amber-100 flex flex-col h-[600px]">
            <div class="flex items-center justify-between pb-3 border-b border-amber-200 mb-4 flex-shrink-0">
                <h3 class="font-bold text-amber-800 text-sm flex items-center space-x-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span>Due Today</span>
                </h3>
                <span class="px-2 py-0.5 text-xs font-bold bg-amber-100 text-amber-700 rounded-md">{{ $dueToday->count() }}</span>
            </div>

            <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                @forelse($dueToday as $task)
                <div class="bg-white p-4 rounded-2xl border border-amber-150 shadow-sm flex flex-col justify-between space-y-3 hover:shadow-md transition-all">
                    <div class="space-y-1">
                        <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-wider text-amber-600">
                            <span>{{ $task->type }}</span>
                            <span>{{ $task->due_date->format('H:i A') }}</span>
                        </div>
                        <h4 class="font-bold text-dark-900 text-sm">
                            <a href="{{ route('leads.show', $task->lead_id) }}" class="hover:underline">{{ $task->lead->full_name }}</a>
                        </h4>
                        <p class="text-xs text-gray-600 leading-relaxed">{{ $task->notes }}</p>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-50">
                        <span class="text-[10px] text-gray-400 font-semibold">{{ $task->due_date->format('M d, H:i A') }}</span>
                        <button @click="selectedFollowUpId = {{ $task->id }}; completeFollowUpOpen = true" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100 transition-all flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Complete</span>
                        </button>
                    </div>
                </div>
                @empty
                <div class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-400">
                    <h5 class="text-xs font-bold text-amber-800">No tasks for today</h5>
                    <p class="text-[10px] text-gray-500 mt-1">Take this time to search or prospect new clients.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Column 3: Due Tomorrow -->
        <div class="bg-blue-50/50 rounded-3xl p-5 border border-blue-100 flex flex-col h-[600px]">
            <div class="flex items-center justify-between pb-3 border-b border-blue-200 mb-4 flex-shrink-0">
                <h3 class="font-bold text-blue-800 text-sm flex items-center space-x-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <span>Due Tomorrow</span>
                </h3>
                <span class="px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700 rounded-md">{{ $dueTomorrow->count() }}</span>
            </div>

            <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                @forelse($dueTomorrow as $task)
                <div class="bg-white p-4 rounded-2xl border border-blue-150 shadow-sm flex flex-col justify-between space-y-3 hover:shadow-md transition-all">
                    <div class="space-y-1">
                        <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-wider text-blue-600">
                            <span>{{ $task->type }}</span>
                            <span>{{ $task->due_date->format('H:i A') }}</span>
                        </div>
                        <h4 class="font-bold text-dark-900 text-sm">
                            <a href="{{ route('leads.show', $task->lead_id) }}" class="hover:underline">{{ $task->lead->full_name }}</a>
                        </h4>
                        <p class="text-xs text-gray-600 leading-relaxed">{{ $task->notes }}</p>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-50">
                        <span class="text-[10px] text-gray-400 font-semibold">{{ $task->due_date->format('M d, H:i A') }}</span>
                        <button @click="selectedFollowUpId = {{ $task->id }}; completeFollowUpOpen = true" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100 transition-all flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Resolve</span>
                        </button>
                    </div>
                </div>
                @empty
                <div class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-400">
                    <h5 class="text-xs font-bold text-blue-800">No tasks for tomorrow</h5>
                    <p class="text-[10px] text-gray-500 mt-1">Excellent! Tomorrow is clear.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- ================================================================
         CALENDAR VIEW
         ================================================================ -->
    <div x-show="activeView === 'calendar'" x-cloak id="followup-calendar-wrap">

        <!-- Calendar Card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            <!-- Calendar Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50">
                <div class="flex items-center space-x-4">
                    <button id="cal-prev"
                        class="w-8 h-8 flex items-center justify-center bg-white border border-gray-200 rounded-xl text-gray-500 hover:text-brand-600 hover:border-brand-300 shadow-sm transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <h2 id="cal-title" class="text-xl font-extrabold text-dark-900 tracking-tight"></h2>
                    <button id="cal-next"
                        class="w-8 h-8 flex items-center justify-center bg-white border border-gray-200 rounded-xl text-gray-500 hover:text-brand-600 hover:border-brand-300 shadow-sm transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <button id="cal-today"
                        class="px-3.5 py-1.5 text-xs font-bold bg-brand-500 text-white rounded-lg hover:bg-brand-600 transition-all ml-2">
                        Today
                    </button>
                </div>
                <!-- Legend -->
                <div class="hidden md:flex items-center space-x-4 text-xs font-semibold">
                    <span class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span><span class="text-gray-600">Overdue</span></span>
                    <span class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span><span class="text-gray-600">Today</span></span>
                    <span class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span><span class="text-gray-600">Upcoming</span></span>
                    <span class="flex items-center space-x-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span><span class="text-gray-600">Completed</span></span>
                </div>
            </div>

            <!-- Weekday Headers -->
            <div class="grid grid-cols-7 text-center text-[11px] font-extrabold text-gray-400 uppercase tracking-widest border-b border-gray-100 bg-gray-50/50">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                <div class="py-3 border-r border-gray-50 last:border-r-0">{{ $day }}</div>
                @endforeach
            </div>

            <!-- Day Cells Grid (rendered by JS) -->
            <div id="cal-grid" class="grid grid-cols-7" style="min-height: 520px;"></div>

        </div>

        <!-- JSON events payload for JS consumption -->
        <script id="followup-events-data" type="application/json">
            {!! json_encode($allTasks->map(function($t) {
                return [
                    'id'       => $t->id,
                    'lead'     => $t->lead ? $t->lead->full_name : 'Unknown',
                    'lead_id'  => $t->lead_id,
                    'type'     => $t->type,
                    'notes'    => $t->notes,
                    'status'   => $t->status,
                    'due_date' => $t->due_date ? \Carbon\Carbon::parse($t->due_date)->format('Y-m-d') : null,
                ];
            })) !!}
        </script>

    </div>
    <!-- END CALENDAR VIEW -->

    <!-- Add Follow-Up Modal -->
    <div x-cloak x-show="addFollowUpOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-6" @click.away="addFollowUpOpen = false">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Schedule Follow-Up Task</h3>
                <button @click="addFollowUpOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <form action="{{ route('follow-ups.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select Lead Prospect *</label>
                    <select name="lead_id" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-700 bg-white">
                        <option value="">Choose client...</option>
                        @foreach($leads as $lead)
                        <option value="{{ $lead->id }}">{{ $lead->full_name }} ({{ $lead->phone_number }})</option>
                        @endforeach
                    </select>
                </div>

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
                           x-model="selectedDate"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Task Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 outline-none text-sm text-gray-800 resize-none"
                              placeholder="Describe task goal..."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="addFollowUpOpen = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl">
                        Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Complete Follow-Up Modal -->
    <div x-cloak x-show="completeFollowUpOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-6" @click.away="completeFollowUpOpen = false">
            <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Complete Follow-Up Task</h3>
                <button @click="completeFollowUpOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <form :action="'/follow-ups/' + selectedFollowUpId" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="Completed">

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Completion Log Notes</label>
                    <textarea name="notes" rows="4" required x-model="completionNotes"
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none text-sm text-gray-800 resize-none"
                              placeholder="e.g. Client requested follow up inspection next week."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="completeFollowUpOpen = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-600/15">
                        Log as Completed
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
    const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const today = new Date();
    today.setHours(0,0,0,0);

    let currentYear  = today.getFullYear();
    let currentMonth = today.getMonth(); // 0-indexed

    // ─── Load events from embedded JSON ───────────────────────────────────────
    function loadEvents() {
        const el = document.getElementById('followup-events-data');
        if (!el) return [];
        try { return JSON.parse(el.textContent.trim()); } catch { return []; }
    }

    // ─── Render Calendar Grid ─────────────────────────────────────────────────
    function renderCalendar(year, month) {
        const grid     = document.getElementById('cal-grid');
        const titleEl  = document.getElementById('cal-title');
        if (!grid || !titleEl) return;

        titleEl.textContent = `${MONTHS[month]} ${year}`;

        const events   = loadEvents();
        const firstDay = new Date(year, month, 1).getDay(); // 0=Sun
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        grid.innerHTML = '';

        // Blank leading cells
        for (let b = 0; b < firstDay; b++) {
            const blank = document.createElement('div');
            blank.className = 'bg-gray-50/50 border-r border-b border-gray-100 min-h-[90px]';
            grid.appendChild(blank);
        }

        // Day cells
        for (let d = 1; d <= daysInMonth; d++) {
            const cellDate   = new Date(year, month, d);
            cellDate.setHours(0,0,0,0);
            const dateStr    = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const isToday    = cellDate.getTime() === today.getTime();
            const isPast     = cellDate < today;
            const dayEvents  = events.filter(e => e.due_date === dateStr);

            const cell = document.createElement('div');
            cell.className = [
                'relative border-r border-b border-gray-100 min-h-[90px] p-2 cursor-pointer transition-colors hover:bg-blue-50/40 group flex flex-col',
                isToday ? 'bg-amber-50 border-amber-200' : '',
            ].join(' ');

            // Day number badge
            const dayNum = document.createElement('span');
            dayNum.className = [
                'text-xs font-extrabold w-7 h-7 flex items-center justify-center rounded-full mb-1 flex-shrink-0',
                isToday
                    ? 'bg-brand-500 text-white'
                    : isPast
                        ? 'text-gray-400'
                        : 'text-gray-700'
            ].join(' ');
            dayNum.textContent = d;
            cell.appendChild(dayNum);

            // Event chips
            const maxVisible = 3;
            dayEvents.slice(0, maxVisible).forEach(ev => {
                const evDate = new Date(ev.due_date + 'T00:00:00');
                const isOverdue = evDate < today && ev.status === 'Pending';

                const chip = document.createElement('div');
                chip.className = [
                    'text-[10px] font-bold px-1.5 py-0.5 rounded-md truncate mb-0.5 leading-snug',
                    ev.status === 'Completed' ? 'bg-emerald-100 text-emerald-700' :
                    isToday && ev.status === 'Pending' ? 'bg-amber-100 text-amber-700' :
                    isOverdue ? 'bg-rose-100 text-rose-700' :
                    'bg-blue-100 text-blue-700'
                ].join(' ');
                chip.title = `${ev.type} — ${ev.lead}: ${ev.notes || ''}`;
                chip.textContent = `${ev.type === 'Call' ? '📞' : ev.type === 'Meeting' ? '🤝' : '📝'} ${ev.lead}`;
                cell.appendChild(chip);
            });

            // Overflow indicator
            if (dayEvents.length > maxVisible) {
                const more = document.createElement('span');
                more.className = 'text-[10px] text-gray-400 font-semibold';
                more.textContent = `+${dayEvents.length - maxVisible} more`;
                cell.appendChild(more);
            }

            // Click → open modal with prefilled date
            cell.addEventListener('click', () => {
                const timeStr = `${dateStr}T09:00`; // default to 9am
                // Set via Alpine store
                const root = document.querySelector('[x-data]');
                if (root && root._x_dataStack) {
                    const alpineData = root._x_dataStack[0];
                    if (alpineData) {
                        alpineData.selectedDate = timeStr;
                        alpineData.addFollowUpOpen = true;
                    }
                }
            });

            grid.appendChild(cell);
        }

        // Trailing blank cells to complete the last row
        const totalCells = firstDay + daysInMonth;
        const remainder  = totalCells % 7;
        if (remainder !== 0) {
            for (let t = 0; t < 7 - remainder; t++) {
                const blank = document.createElement('div');
                blank.className = 'bg-gray-50/50 border-r border-b border-gray-100 min-h-[90px]';
                grid.appendChild(blank);
            }
        }
    }

    // ─── Navigation ───────────────────────────────────────────────────────────
    function initCalendar() {
        const prevBtn  = document.getElementById('cal-prev');
        const nextBtn  = document.getElementById('cal-next');
        const todayBtn = document.getElementById('cal-today');
        if (!prevBtn) return; // calendar not in DOM yet

        prevBtn.onclick = () => {
            currentMonth--;
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            renderCalendar(currentYear, currentMonth);
        };
        nextBtn.onclick = () => {
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            renderCalendar(currentYear, currentMonth);
        };
        todayBtn.onclick = () => {
            currentYear  = today.getFullYear();
            currentMonth = today.getMonth();
            renderCalendar(currentYear, currentMonth);
        };

        renderCalendar(currentYear, currentMonth);
    }

    // Init on load and on SPA navigation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCalendar);
    } else {
        initCalendar();
    }
    document.addEventListener('spa-load-complete', () => {
        currentYear  = today.getFullYear();
        currentMonth = today.getMonth();
        initCalendar();
    });
})();
</script>
@endpush
