@extends('layouts.app')

@section('content')
<div class="space-y-8" x-data="{ manualGenOpen: false }">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Generated Document Instances</h1>
            <p class="text-sm text-gray-500 mt-1">Audit auto-compiled deeds, invoices, and contracts sent to clients.</p>
        </div>
        @if(in_array(Auth::user()->role, ['super_admin', 'company_admin']))
        <div>
            <button @click="manualGenOpen = true" class="inline-flex items-center space-x-2 px-5 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/10 hover:shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span>Compile Document Manually</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Generated Documents list -->
    <div class="bg-white rounded-3xl border border-gray-150 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-550/5 border-b border-gray-150 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        <th class="p-6">Document Title</th>
                        <th class="p-6">Client / Prospect</th>
                        <th class="p-6">Template Used</th>
                        <th class="p-6">Date Generated</th>
                        <th class="p-6">Officer Credit</th>
                        <th class="p-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium text-dark-800">
                    @forelse($documents as $doc)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="p-6">
                            <span class="block font-bold text-dark-900 text-sm hover:text-brand-500 transition-colors">
                                <a href="{{ route('generated-documents.show', $doc) }}">{{ $doc->title }}</a>
                            </span>
                        </td>
                        <td class="p-6 font-bold text-gray-700">
                            <a href="{{ route('leads.show', $doc->lead_id) }}" class="underline hover:text-brand-500">{{ $doc->lead->full_name }}</a>
                        </td>
                        <td class="p-6">
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-lg bg-gray-100 text-gray-700">
                                {{ $doc->template ? $doc->template->name : 'Custom Template' }}
                            </span>
                        </td>
                        <td class="p-6 text-gray-550">
                            {{ $doc->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td class="p-6 text-gray-550">
                            {{ $doc->generator ? $doc->generator->name : 'System' }}
                        </td>
                        <td class="p-6 text-right flex justify-end space-x-1.5">
                            <a href="{{ route('generated-documents.show', $doc) }}" class="px-2.5 py-1.5 bg-gray-50 hover:bg-brand-50 border border-gray-250 hover:border-brand-200 text-gray-700 hover:text-brand-600 rounded-lg text-[10px] font-bold transition-all">
                                Preview
                            </a>
                            <a href="{{ route('generated-documents.download', $doc) }}" class="p-1.5 bg-gray-50 hover:bg-gray-100 text-brand-600 border border-gray-250 rounded-lg" title="Download PDF">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </a>
                            @if($doc->lead->email)
                            <form action="{{ route('generated-documents.email', $doc) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 bg-gray-50 hover:bg-emerald-50 text-emerald-600 hover:text-emerald-700 border border-gray-250 rounded-lg" title="Mail Attachment to Client">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-gray-500">
                            <span class="p-4 bg-gray-50 text-gray-400 rounded-full inline-block mb-3">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </span>
                            <h4 class="text-sm font-bold text-dark-900">No generated documents recorded</h4>
                            <p class="text-xs text-gray-400 mt-1">Automated events like Deal Won or Payment Received will compile document drafts here.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($documents->hasPages())
        <div class="bg-gray-50 border-t border-gray-150 p-4">
            {{ $documents->links() }}
        </div>
        @endif
    </div>

    @if(in_array(Auth::user()->role, ['super_admin', 'company_admin']))
    <!-- Manual Generate Document Modal Wizard (Admin Only) -->
    <div x-cloak x-show="manualGenOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-900/60 transition-opacity">
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl p-6 md:p-8 space-y-4" @click.away="manualGenOpen = false">
            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                <h3 class="text-lg font-bold text-dark-900">Compile Document Wizard</h3>
                <button @click="manualGenOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <form action="{{ route('generated-documents.generate') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Target Lead / Prospect *</label>
                    <select name="lead_id" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white">
                        <option value="">-- Choose Lead --</option>
                        @foreach(\App\Models\Lead::orderBy('full_name')->get() as $l)
                        <option value="{{ $l->id }}">{{ $l->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Base Document Template *</label>
                    <select name="document_template_id" required class="w-full px-3 py-2 border rounded-lg text-xs bg-white text-gray-750">
                        <option value="">-- Choose Template Layout --</option>
                        @foreach(\App\Models\DocumentTemplate::where('is_active', true)->orderBy('name')->get() as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} (Trigger: {{ $t->trigger_event }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="manualGenOpen = false" class="text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-lg shadow-sm">Generate Document</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
