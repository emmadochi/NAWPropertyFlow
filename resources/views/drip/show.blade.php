@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="dripSequenceManager()">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between pb-4 border-b border-gray-150">
        <div>
            <a href="{{ route('drip-sequences.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Drip Sequences</span>
            </a>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">{{ $sequence->name }}</h1>
            <p class="text-xs text-gray-500 mt-1">
                Triggered by: <span class="font-bold text-brand-500">{{ \App\Models\DripSequence::TRIGGERS[$sequence->trigger_event] ?? $sequence->trigger_event }}</span>
            </p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <form action="{{ route('drip-sequences.toggle', $sequence) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="px-4 py-2 bg-white hover:bg-gray-50 border border-gray-200 text-dark-800 rounded-xl text-xs font-bold transition-all">
                    {{ $sequence->is_active ? 'Deactivate Sequence' : 'Activate Sequence' }}
                </button>
            </form>
            <button @click="showAddStepModal = true" class="inline-flex items-center space-x-2 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Add Step</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Timeline of Steps (Left / Center) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl border border-gray-150 p-6 md:p-8 shadow-sm">
                <h3 class="text-sm font-bold text-dark-900 mb-6">Workflow Automation Steps</h3>

                @if($steps->isEmpty())
                <div class="border-2 border-dashed border-gray-200 rounded-2xl p-10 text-center text-gray-400 text-xs">
                    No steps added to this drip sequence yet. Click "Add Step" to configure the first message.
                </div>
                @else
                <div class="relative pl-6 border-l border-gray-150 ml-3 space-y-8">
                    @foreach($steps as $index => $step)
                    <div class="relative">
                        <!-- Step circle indicator -->
                        <span class="absolute -left-[35px] top-0 w-6 h-6 rounded-full bg-brand-50 text-brand-500 border border-brand-100 flex items-center justify-center text-[10px] font-black">
                            {{ $index + 1 }}
                        </span>

                        <div class="bg-gray-50/50 hover:bg-gray-50 border border-gray-150 rounded-2xl p-5 transition-all">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-gray-100 text-gray-700 uppercase">
                                        {{ $step->type }}
                                    </span>
                                    <span class="text-[10px] font-bold text-gray-400">
                                        Wait: {{ $step->delayLabel() }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button @click="editStep({{ $step }})" class="p-1 text-gray-400 hover:text-brand-500 transition-colors" title="Edit Step">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <form action="{{ route('drip-sequences.steps.destroy', [$sequence, $step]) }}" method="POST" onsubmit="return confirm('Remove this step?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-gray-400 hover:text-rose-500 transition-colors" title="Delete Step">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            @if($step->type === 'email')
                            <div class="text-xs font-semibold text-dark-900 mb-1">Subject: {{ $step->subject }}</div>
                            @endif
                            <div class="text-[11px] text-gray-500 bg-white border border-gray-100 rounded-xl p-3 max-h-24 overflow-y-auto">
                                {!! nl2br(e($step->body)) !!}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- Enrolled Leads & Analytics (Right Panel) -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-dark-900">Enrolled Contacts</h3>
                <p class="text-[11px] text-gray-400">Track active leads navigating this drip flow sequence.</p>

                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($sequence->enrollments as $enr)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100 text-xs">
                        <div>
                            <div class="font-semibold text-dark-900">{{ $enr->lead?->full_name }}</div>
                            <div class="text-[9px] text-gray-400">Next send: {{ $enr->next_send_at ? $enr->next_send_at->format('M d, H:i') : 'N/A' }}</div>
                        </div>
                        <div>
                            @if($enr->status === 'active')
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800 uppercase">Active</span>
                            @elseif($enr->status === 'completed')
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-blue-100 text-blue-800 uppercase">Completed</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-gray-100 text-gray-700 uppercase">{{ $enr->status }}</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-xs text-gray-400">No leads currently enrolled.</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- ADD STEP MODAL -->
    <div x-cloak x-show="showAddStepModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/40">
        <div class="bg-white rounded-3xl border border-gray-250 shadow-2xl w-full max-w-xl p-6 md:p-8 space-y-6" @click.away="showAddStepModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-150">
                <h3 class="text-sm font-bold text-dark-950">Add Step to Sequence</h3>
                <button type="button" @click="showAddStepModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('drip-sequences.steps.store', $sequence) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Message Channel</label>
                        <select name="type" x-model="modalType" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white text-gray-700">
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                            <option value="whatsapp">WhatsApp</option>
                        </select>
                    </div>

                    <div x-show="modalType === 'email'">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Email Subject</label>
                        <input type="text" name="subject" :required="modalType === 'email'" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white" placeholder="Subject Line">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Delay Days</label>
                        <input type="number" name="delay_days" value="0" min="0" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Delay Hours</label>
                        <input type="number" name="delay_hours" value="0" min="0" max="23" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Message Content</label>
                    <textarea name="body" required class="w-full h-32 px-3 py-2 border rounded-lg text-xs bg-white" placeholder="Enter message text... Use @{{name}} to insert lead full name."></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-gray-150">
                    <button type="button" @click="showAddStepModal = false" class="px-4 py-2 border rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md">Add Step</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT STEP MODAL -->
    <div x-cloak x-show="showEditStepModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/40">
        <div class="bg-white rounded-3xl border border-gray-250 shadow-2xl w-full max-w-xl p-6 md:p-8 space-y-6" @click.away="showEditStepModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-150">
                <h3 class="text-sm font-bold text-dark-955">Edit Step</h3>
                <button type="button" @click="showEditStepModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form :action="editActionUrl" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Message Channel</label>
                        <select name="type" x-model="editingStep.type" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white text-gray-700">
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                            <option value="whatsapp">WhatsApp</option>
                        </select>
                    </div>

                    <div x-show="editingStep.type === 'email'">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Email Subject</label>
                        <input type="text" name="subject" x-model="editingStep.subject" :required="editingStep.type === 'email'" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Delay Days</label>
                        <input type="number" name="delay_days" x-model="editingStep.delay_days" min="0" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Delay Hours</label>
                        <input type="number" name="delay_hours" x-model="editingStep.delay_hours" min="0" max="23" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Message Content</label>
                    <textarea name="body" x-model="editingStep.body" required class="w-full h-32 px-3 py-2 border rounded-lg text-xs bg-white"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-gray-150">
                    <button type="button" @click="showEditStepModal = false" class="px-4 py-2 border rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function dripSequenceManager() {
        return {
            showAddStepModal: false,
            showEditStepModal: false,
            modalType: 'email',
            editingStep: {
                id: null,
                type: 'email',
                subject: '',
                body: '',
                delay_days: 0,
                delay_hours: 0
            },
            editActionUrl: '',

            editStep(step) {
                this.editingStep = { ...step };
                this.editActionUrl = `/drip-sequences/{{ $sequence->id }}/steps/` + step.id;
                this.showEditStepModal = true;
            }
        }
    }
</script>
@endsection
