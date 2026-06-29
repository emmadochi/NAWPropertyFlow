@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-dark-900">Submit Leave Request</h1>
        <p class="text-sm text-gray-500 mt-0.5">Fill in your leave details below</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <form method="POST" action="{{ route('hr.leave.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Leave Type</label>
                <select name="leave_type" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                    <option value="">Select leave type...</option>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" {{ old('leave_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date', today()->format('Y-m-d')) }}" required min="{{ today()->format('Y-m-d') }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">End Date</label>
                    <input type="date" name="end_date" value="{{ old('end_date', today()->format('Y-m-d')) }}" required min="{{ today()->format('Y-m-d') }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Reason <span class="font-normal text-gray-400">(optional)</span></label>
                <textarea name="reason" rows="4" placeholder="Please provide a brief reason for your leave..." class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none resize-none">{{ old('reason') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-brand-500 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-600 transition-colors">Submit Request</button>
                <a href="{{ route('hr.leave.index') }}" class="text-gray-500 hover:text-gray-700 text-sm font-medium">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
