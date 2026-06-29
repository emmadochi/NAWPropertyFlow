@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-150">
        <div>
            <a href="{{ route('document-templates.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Templates</span>
            </a>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">{{ $template->name }}</h1>
            <p class="text-xs text-gray-500 mt-1">
                Trigger: <span class="uppercase tracking-wider font-bold text-gray-650 bg-gray-100 px-2 py-0.5 rounded-md text-[10px]">{{ str_replace('_', ' ', $template->trigger_event) }}</span>
            </p>
        </div>
        @if(Auth::user()->role !== 'sales_executive')
        <div>
            <a href="{{ route('document-templates.edit', $template) }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span>Edit Document Content</span>
            </a>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Current Template HTML Preview -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-150 p-6 md:p-8 shadow-sm space-y-4">
            <h3 class="font-extrabold text-dark-900 text-base pb-3 border-b border-gray-100">Latest Layout Preview</h3>
            
            <div class="prose max-w-none text-xs text-gray-700 bg-slate-50 p-6 rounded-2xl border border-slate-100 min-h-[300px]">
                @if($template->latestVersion)
                {!! $template->latestVersion->content !!}
                @else
                <p class="text-gray-400 italic">No content version configured yet.</p>
                @endif
            </div>
        </div>

        <!-- Right: Versions Revision History Timeline -->
        <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm space-y-4">
            <h3 class="font-extrabold text-dark-900 text-base pb-3 border-b border-gray-100">Revision History</h3>
            
            <div class="relative pl-4 border-l border-gray-150 space-y-4">
                @foreach($template->versions as $version)
                <div class="relative">
                    <span class="absolute -left-[21px] top-1.5 w-2.5 h-2.5 rounded-full border-2 border-white bg-brand-500 shadow-sm"></span>
                    <div class="text-xs space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-dark-900">Version {{ $version->version_number }}</span>
                            <span class="text-[9px] text-gray-400 font-semibold">{{ $version->created_at->format('M d, Y') }}</span>
                        </div>
                        <p class="text-[10px] text-gray-550 leading-relaxed">
                            Created by {{ $version->creator ? $version->creator->name : 'System' }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@endsection
