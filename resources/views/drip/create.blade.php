@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-150">
        <div>
            <a href="{{ route('drip-sequences.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Drip Sequences</span>
            </a>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">Create Drip Sequence</h1>
            <p class="text-xs text-gray-500 mt-1">Initialize a new campaign that triggers on real-time CRM events.</p>
        </div>
    </div>

    <!-- Create form -->
    <div class="bg-white rounded-3xl border border-gray-150 p-6 md:p-8 shadow-sm">
        <form action="{{ route('drip-sequences.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Sequence Name *</label>
                <input type="text" name="name" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white" placeholder="e.g. Off-Plan Buyer Welcome Drip">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Description</label>
                <textarea name="description" class="w-full h-20 px-3 py-2 border rounded-lg text-xs bg-white" placeholder="Outline the purpose of this sequence..."></textarea>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">CRM Trigger Action Event *</label>
                <select name="trigger_event" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white text-gray-700">
                    @foreach($triggers as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-150">
                <a href="{{ route('drip-sequences.index') }}" class="text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                    Create & Continue
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
