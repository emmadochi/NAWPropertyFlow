@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-150">
        <div>
            <a href="{{ route('generated-documents.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Generated list</span>
            </a>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">{{ $document->title }}</h1>
            <p class="text-xs text-gray-550 mt-1">
                Compiled on {{ $document->created_at->format('M d, Y h:i A') }} • Target Client: <strong class="text-gray-700">{{ $document->lead->full_name }}</strong>
            </p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('generated-documents.download', $document) }}" class="inline-flex items-center space-x-1.5 px-4 py-2.5 bg-white border border-gray-250 text-gray-700 font-bold text-xs rounded-xl shadow-sm hover:bg-gray-50 transition-all">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Download PDF</span>
            </a>
            @if($document->lead->email)
            <form action="{{ route('generated-documents.email', $document) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center space-x-1.5 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span>Email Attachment</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Letterhead Preview card -->
    <div class="bg-white rounded-3xl border border-gray-150 p-8 md:p-12 shadow-sm relative overflow-hidden">
        
        <!-- Header Print branding representation -->
        <div class="border-b border-gray-100 pb-4 mb-6 text-center space-y-1">
            <h2 class="text-xl font-bold text-brand-600 tracking-tight">NAW PROPERTIES</h2>
            <p class="text-[10px] text-gray-400">Plot 12, Admiralty Way, Lekki Phase 1, Lagos, Nigeria | info@nawproperties.com</p>
        </div>

        <div class="prose max-w-none text-xs text-gray-800 leading-relaxed font-sans text-justify">
            {!! $document->content !!}
        </div>

        <!-- Corporate stamp preview placeholder -->
        <div class="mt-12 pt-8 border-t border-gray-100 flex justify-between text-xs text-gray-400">
            <div>
                <span class="block">Draft ID: #GD{{ $document->id }}</span>
                <span>Audit signature: SYSTEM_DISPATCHED</span>
            </div>
            <div class="text-right">
                <span class="block">&copy; NAW Properties Ltd</span>
            </div>
        </div>

    </div>

</div>
@endsection
