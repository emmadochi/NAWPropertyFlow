@extends('layouts.app')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <h1 class="text-3xl font-extrabold text-dark-900 tracking-tight">Smart Document Templates</h1>
            <p class="text-sm text-gray-500 mt-1">Design letterheads, contracts, and transmittal terms triggered by pipeline events.</p>
        </div>
        @if(Auth::user()->role !== 'sales_executive')
        <div>
            <a href="{{ route('document-templates.create') }}" class="inline-flex items-center space-x-2 px-5 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-brand-500/10 hover:shadow-brand-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Create Template</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Templates Grid Table -->
    <div class="bg-white rounded-3xl border border-gray-150 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-550/5 border-b border-gray-150 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        <th class="p-6">Template Name</th>
                        <th class="p-6">Trigger Event</th>
                        <th class="p-6">Latest Version</th>
                        <th class="p-6">Status</th>
                        <th class="p-6">Created By</th>
                        <th class="p-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium text-dark-800">
                    @forelse($templates as $template)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="p-6">
                            <span class="block font-bold text-dark-900 text-sm hover:text-brand-500 transition-colors">
                                <a href="{{ route('document-templates.show', $template) }}">{{ $template->name }}</a>
                            </span>
                        </td>
                        <td class="p-6">
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-lg bg-slate-100 text-slate-700">
                                {{ str_replace('_', ' ', $template->trigger_event) }}
                            </span>
                        </td>
                        <td class="p-6">
                            <span class="text-gray-550 font-bold">V{{ $template->latestVersion ? $template->latestVersion->version_number : '1' }}</span>
                        </td>
                        <td class="p-6">
                            @if($template->is_active)
                            <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100">Active</span>
                            @else
                            <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded-md bg-gray-100 text-gray-600">Inactive</span>
                            @endif
                        </td>
                        <td class="p-6">
                            <span class="text-gray-550">{{ $template->creator ? $template->creator->name : 'System' }}</span>
                        </td>
                        <td class="p-6 text-right flex justify-end space-x-1.5">
                            <a href="{{ route('document-templates.show', $template) }}" class="px-2.5 py-1.5 bg-gray-50 hover:bg-brand-50 border border-gray-250 hover:border-brand-200 text-gray-700 hover:text-brand-600 rounded-lg text-[10px] font-bold transition-all">
                                History
                            </a>
                            @if(Auth::user()->role !== 'sales_executive')
                            <a href="{{ route('document-templates.edit', $template) }}" class="p-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-250 rounded-lg" title="Edit Content">
                                <svg class="w-3.5 h-3.5 text-gray-550" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <form action="{{ route('document-templates.destroy', $template) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this template?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 bg-gray-50 hover:bg-rose-50 text-gray-400 hover:text-rose-600 border border-gray-250 hover:border-rose-200 rounded-lg" title="Delete">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
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
                            <h4 class="text-sm font-bold text-dark-900">No templates found</h4>
                            <p class="text-xs text-gray-400 mt-1">Create document templates to begin auto-generating receipts and deeds.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($templates->hasPages())
        <div class="bg-gray-50 border-t border-gray-150 p-4">
            {{ $templates->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
