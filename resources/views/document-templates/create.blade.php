@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-150 dark:border-slate-800">
        <div>
            <a href="{{ route('document-templates.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-gray-500 dark:text-slate-400 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Templates</span>
            </a>
            <h1 class="text-2xl font-black text-dark-900 dark:text-white tracking-tight">Create Document Template</h1>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Design automated Deed of Assignment, Sales Receipts, and Contract templates with dynamic token merging.</p>
        </div>
    </div>

    <!-- Preset Starter Templates Picker -->
    <div class="bg-gradient-to-r from-brand-500/10 via-brand-500/5 to-transparent border border-brand-500/20 rounded-3xl p-5">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h3 class="text-xs font-black text-brand-700 dark:text-brand-300 uppercase tracking-wider">⚡ 1-Click Preset Layouts</h3>
                <p class="text-xs text-gray-600 dark:text-slate-300 mt-0.5">Load a professionally formatted Nigerian real estate legal deed or receipt instantly:</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" onclick="loadPresetTemplate('deed')" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-brand-300 dark:border-brand-700 hover:bg-brand-50 text-xs font-bold text-brand-700 dark:text-brand-300 rounded-xl shadow-sm transition-all">
                    📜 Deed of Assignment
                </button>
                <button type="button" onclick="loadPresetTemplate('receipt')" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-brand-300 dark:border-brand-700 hover:bg-brand-50 text-xs font-bold text-brand-700 dark:text-brand-300 rounded-xl shadow-sm transition-all">
                    🧾 Official Sales Receipt
                </button>
                <button type="button" onclick="loadPresetTemplate('offer')" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-brand-300 dark:border-brand-700 hover:bg-brand-50 text-xs font-bold text-brand-700 dark:text-brand-300 rounded-xl shadow-sm transition-all">
                    📑 Contract of Sale / Offer
                </button>
            </div>
        </div>
    </div>

    <!-- Create template panel -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-150 dark:border-slate-700/60 p-6 md:p-8 shadow-sm">
        <form action="{{ route('document-templates.store') }}" method="POST" class="space-y-6" id="doc-create-form">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">Template Display Name *</label>
                    <input type="text" name="name" id="template-name-input" required class="w-full px-3.5 py-2.5 border border-gray-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-dark-900 dark:text-white focus:outline-none focus:border-brand-500" placeholder="e.g. Deed of Custom Assignment &amp; Title Conveyance">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">Trigger Pipeline Event *</label>
                    <select name="trigger_event" id="template-trigger-input" required class="w-full px-3.5 py-2.5 border border-gray-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-dark-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="deal_won">Deal Won (Sale logged)</option>
                        <option value="payment_received">Payment Received (Milestone paid)</option>
                        <option value="inspection_completed">Inspection Completed</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded border-gray-300 dark:border-slate-700 text-brand-500 focus:ring-brand-500">
                <label for="is_active" class="text-xs font-bold text-gray-600 dark:text-slate-300 uppercase">Enable template auto-triggering on dispatch</label>
            </div>

            <!-- Custom WYSIWYG Editor Section -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase">Template Document Body Editor *</label>

                    <!-- Quick Token Insert helper -->
                    <div x-data="{ openTokens: false }" class="relative">
                        <button type="button" @click="openTokens = !openTokens" class="px-3 py-1.5 bg-gray-50 dark:bg-slate-700 hover:bg-gray-100 dark:hover:bg-slate-600 border border-gray-200 dark:border-slate-600 rounded-xl text-[10px] font-bold text-gray-700 dark:text-slate-200 flex items-center space-x-1 transition-all">
                            <span>Insert Dynamic Field Token</span>
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-cloak x-show="openTokens" @click.away="openTokens = false"
                             class="absolute right-0 mt-1 w-72 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-2xl z-50 divide-y divide-gray-100 dark:divide-slate-700 py-1 text-left text-[11px] max-h-[420px] overflow-y-auto">

                            {{-- Lead / Client --}}
                            <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 dark:text-brand-400 uppercase tracking-widest bg-brand-50 dark:bg-brand-950/60 sticky top-0">👤 Lead / Client</div>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;client_name&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Client Full Name</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;client_phone&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Client Phone</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;client_email&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Client Email</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;client_address&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Client Home Address</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;client_nin&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Client NIN / ID</a>

                            {{-- Property --}}
                            <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 dark:text-brand-400 uppercase tracking-widest bg-brand-50 dark:bg-brand-950/60 sticky top-0">🏘️ Property</div>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;property_name&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Property Name</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;property_type&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Property Type</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;property_location&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Location / Estate</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;property_state&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">State / City</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;property_size&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Plot Size (sqm)</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;property_block&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Plot / Block No.</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;title_type&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Title Type (C of O / R of O)</a>

                            {{-- Financials --}}
                            <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 dark:text-brand-400 uppercase tracking-widest bg-brand-50 dark:bg-brand-950/60 sticky top-0">💰 Financials</div>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;deal_value&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Deal / Sale Value (₦)</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;down_payment&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Down Payment (₦)</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;outstanding_balance&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Outstanding Balance (₦)</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;transaction_ref&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Transaction Reference</a>

                            {{-- Company & Dates --}}
                            <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 dark:text-brand-400 uppercase tracking-widest bg-brand-50 dark:bg-brand-950/60 sticky top-0">🏢 Company &amp; Dates</div>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;company_name&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Company Name</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;company_address&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Company Address</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;current_date&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Current Date</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-editor', '&#123;&#123;agent_name&#125;&#125;'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-200 hover:bg-brand-50 dark:hover:bg-slate-700 hover:text-brand-700 font-semibold">Assigned Agent Name</a>
                        </div>
                    </div>
                </div>

                <!-- Custom Editor Wrapper -->
                <div id="doc-editor-wrap" class="border border-gray-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-brand-500/20 focus-within:border-brand-400 transition-all bg-white dark:bg-slate-900">
                    <!-- Toolbar -->
                    <div class="flex flex-wrap items-center gap-1 px-3 py-2 bg-gray-50 dark:bg-slate-800/80 border-b border-gray-200 dark:border-slate-700" id="doc-editor-toolbar">
                        <!-- Text style -->
                        <button type="button" onclick="wysiwyg_exec('doc-editor','bold')" title="Bold" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"/></svg></button>
                        <button type="button" onclick="wysiwyg_exec('doc-editor','italic')" title="Italic" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"/></svg></button>
                        <button type="button" onclick="wysiwyg_exec('doc-editor','underline')" title="Underline" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17c3.31 0 6-2.69 6-6V3h-2.5v8c0 1.93-1.57 3.5-3.5 3.5S8.5 12.93 8.5 11V3H6v8c0 3.31 2.69 6 6 6zm-7 2v2h14v-2H5z"/></svg></button>

                        <div class="w-px h-4 bg-gray-200 dark:bg-slate-700 mx-1"></div>

                        <!-- Headings -->
                        <button type="button" onclick="wysiwyg_exec('doc-editor','formatBlock','H1')" title="Heading 1" class="wysiwyg-btn text-[11px] font-black">H1</button>
                        <button type="button" onclick="wysiwyg_exec('doc-editor','formatBlock','H2')" title="Heading 2" class="wysiwyg-btn text-[11px] font-black">H2</button>
                        <button type="button" onclick="wysiwyg_exec('doc-editor','formatBlock','H3')" title="Heading 3" class="wysiwyg-btn text-[11px] font-black">H3</button>
                        <button type="button" onclick="wysiwyg_exec('doc-editor','formatBlock','P')" title="Paragraph" class="wysiwyg-btn text-[11px] font-semibold">¶</button>

                        <div class="w-px h-4 bg-gray-200 dark:bg-slate-700 mx-1"></div>

                        <!-- Lists -->
                        <button type="button" onclick="wysiwyg_exec('doc-editor','insertUnorderedList')" title="Bullet List" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M4 10.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0-6c-.83 0-1.5.67-1.5 1.5S3.17 7.5 4 7.5 5.5 6.83 5.5 6 4.83 4.5 4 4.5zm0 12c-.83 0-1.5.68-1.5 1.5s.68 1.5 1.5 1.5 1.5-.68 1.5-1.5-.67-1.5-1.5-1.5zM7 19h14v-2H7v2zm0-6h14v-2H7v2zm0-8v2h14V5H7z"/></svg></button>
                        <button type="button" onclick="wysiwyg_exec('doc-editor','insertOrderedList')" title="Numbered List" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M2 17h2v.5H3v1h1v.5H2v1h3v-4H2v1zm1-9h1V4H2v1h1v3zm-1 3h1.8L2 13.1v.9h3v-1H3.2L5 10.9V10H2v1zm5-6v2h14V5H7zm0 14h14v-2H7v2zm0-6h14v-2H7v2z"/></svg></button>

                        <div class="w-px h-4 bg-gray-200 dark:bg-slate-700 mx-1"></div>

                        <!-- Alignment -->
                        <button type="button" onclick="wysiwyg_exec('doc-editor','justifyLeft')" title="Align Left" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M15 15H3v2h12v-2zm0-8H3v2h12V7zM3 13h18v-2H3v2zm0 8h18v-2H3v2zM3 3v2h18V3H3z"/></svg></button>
                        <button type="button" onclick="wysiwyg_exec('doc-editor','justifyCenter')" title="Align Center" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M7 15v2h10v-2H7zm-4 6h18v-2H3v2zm0-8h18v-2H3v2zm4-6v2h10V7H7zM3 3v2h18V3H3z"/></svg></button>
                        <button type="button" onclick="wysiwyg_exec('doc-editor','justifyRight')" title="Align Right" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 21h18v-2H3v2zm6-4h12v-2H9v2zm-6-4h18v-2H3v2zm6-4h12V7H9v2zM3 3v2h18V3H3z"/></svg></button>

                        <!-- HTML Source Toggle -->
                        <button type="button" onclick="wysiwyg_toggle_source('doc-editor', 'doc-editor-source', this)" title="Toggle HTML Source" class="wysiwyg-btn ml-auto text-[10px] font-black text-brand-600 hover:text-brand-800 hover:bg-brand-50 px-2.5">&lt;/&gt; HTML</button>
                    </div>

                    <!-- Editable area -->
                    <div id="doc-editor"
                         contenteditable="true"
                         class="min-h-[420px] p-6 text-sm text-dark-900 dark:text-slate-100 leading-relaxed focus:outline-none bg-white dark:bg-slate-900"
                         style="font-family: inherit;"
                         oninput="wysiwyg_sync('doc-editor', 'doc-content-input')"
                         onblur="wysiwyg_sync('doc-editor', 'doc-content-input')"
                    ></div>

                    <!-- Source mode textarea -->
                    <textarea id="doc-editor-source"
                              class="hidden w-full min-h-[420px] p-6 text-xs font-mono text-emerald-400 bg-slate-950 focus:outline-none resize-none border-t border-slate-800"
                              oninput="wysiwyg_sync_from_source('doc-editor', 'doc-content-input', this.value)"
                    ></textarea>
                </div>

                <!-- Hidden textarea submitted with form -->
                <textarea name="content" id="doc-content-input" class="hidden"></textarea>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-150 dark:border-slate-700">
                <a href="{{ route('document-templates.index') }}" class="text-xs font-bold text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white">
                    Cancel &amp; Rollback
                </a>
                <button type="submit" class="px-7 py-3 bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-brand-500/20 transition-all">
                    Save and Compile Template
                </button>
            </div>
        </form>
    </div>

</div>

@push('styles')
<style>
.wysiwyg-btn {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 5px 8px; border-radius: 8px; color: #64748b;
    transition: background 0.15s, color 0.15s; min-width: 28px; height: 28px;
}
.wysiwyg-btn:hover { background: #f1f5f9; color: #0f172a; }
.dark .wysiwyg-btn:hover { background: #334155; color: #f8fafc; }
#doc-editor h1 { font-size: 1.8em; font-weight: 800; margin: .6em 0 .3em; color: #0f172a; }
.dark #doc-editor h1 { color: #f8fafc; }
#doc-editor h2 { font-size: 1.4em; font-weight: 700; margin: .5em 0 .2em; color: #0f172a; }
.dark #doc-editor h2 { color: #f8fafc; }
#doc-editor h3 { font-size: 1.2em; font-weight: 700; margin: .4em 0 .2em; color: #0f172a; }
.dark #doc-editor h3 { color: #f8fafc; }
#doc-editor p  { margin: .4em 0; line-height: 1.6; }
#doc-editor ul { list-style: disc; padding-left: 1.5em; margin: .5em 0; }
#doc-editor ol { list-style: decimal; padding-left: 1.5em; margin: .5em 0; }
#doc-editor table { width: 100%; border-collapse: collapse; margin: 1em 0; font-size: 0.9em; }
#doc-editor th, #doc-editor td { border: 1px solid #cbd5e1; padding: 8px 12px; }
#doc-editor th { background-color: #f8fafc; font-weight: bold; }
.dark #doc-editor th { background-color: #1e293b; border-color: #334155; }
.dark #doc-editor td { border-color: #334155; }
</style>
@endpush

@push('scripts')
<script>
const PRESET_TEMPLATES = {
    deed: {
        name: 'Deed of Assignment & Conveyance of Title',
        trigger: 'deal_won',
        html: `<h2>DEED OF ASSIGNMENT</h2>
<p><strong>THIS DEED OF ASSIGNMENT</strong> is made this <strong>{{current_date}}</strong></p>
<p><strong>BETWEEN:</strong></p>
<p><strong>{{company_name}}</strong>, having its corporate head office at {{company_address}} (hereinafter referred to as the <em>"ASSIGNOR"</em> which expression shall where the context so admits include its successors-in-title and assigns) of the ONE PART;</p>
<p><strong>AND:</strong></p>
<p><strong>{{client_name}}</strong>, residing at {{client_address}} (NIN: {{client_nin}}) (hereinafter referred to as the <em>"ASSIGNEE"</em> which expression shall include their legal representatives and assigns) of the OTHER PART.</p>
<hr/>
<h3>1. RECITALS</h3>
<p>The Assignor is the absolute owner of that piece of land known as <strong>Plot {{property_block}}</strong> measuring approximately <strong>{{property_size}} sqm</strong>, situated at <strong>{{property_location}}, {{property_state}}</strong>, with Title: <strong>{{title_type}}</strong>.</p>
<p>The Assignor has agreed to assign all its rights and unexpired term to the Assignee for the total consideration sum of <strong>₦{{deal_value}}</strong>.</p>
<hr/>
<h3>2. WITNESSETH</h3>
<p>In consideration of the sum of <strong>₦{{deal_value}}</strong> paid by the Assignee to the Assignor (receipt of which the Assignor hereby acknowledges), the Assignor conveys and assigns unto the Assignee the property free from all encumbrances.</p>
<br/>
<table style="width: 100%; border: none;">
  <tr>
    <td style="border:none; width: 50%;">
      <p>___________________________<br/><strong>SIGNED for the ASSIGNOR</strong><br/>Managing Director, {{company_name}}</p>
    </td>
    <td style="border:none; width: 50%;">
      <p>___________________________<br/><strong>SIGNED by the ASSIGNEE</strong><br/>{{client_name}}</p>
    </td>
  </tr>
</table>`
    },
    receipt: {
        name: 'Official Sales & Milestone Payment Receipt',
        trigger: 'payment_received',
        html: `<div style="text-align: center; border-bottom: 2px solid #FEA500; padding-bottom: 15px; margin-bottom: 20px;">
  <h1 style="margin: 0; color: #0f172a;">{{company_name}}</h1>
  <p style="margin: 4px 0; font-size: 13px; color: #64748b;">{{company_address}}</p>
  <h3 style="margin: 10px 0 0; color: #FEA500; text-transform: uppercase;">OFFICIAL PAYMENT RECEIPT</h3>
</div>
<p><strong>Receipt Date:</strong> {{current_date}} | <strong>Ref No:</strong> {{transaction_ref}}</p>
<table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
  <tr style="background: #f8fafc;">
    <th style="padding: 10px; text-align: left;">Customer Name:</th>
    <td style="padding: 10px;">{{client_name}} ({{client_phone}})</td>
  </tr>
  <tr>
    <th style="padding: 10px; text-align: left;">Property Scheme:</th>
    <td style="padding: 10px;">{{property_name}} (Plot {{property_block}})</td>
  </tr>
  <tr style="background: #f8fafc;">
    <th style="padding: 10px; text-align: left;">Location:</th>
    <td style="padding: 10px;">{{property_location}}, {{property_state}}</td>
  </tr>
  <tr>
    <th style="padding: 10px; text-align: left;">Total Property Value:</th>
    <td style="padding: 10px; font-weight: bold;">₦{{deal_value}}</td>
  </tr>
  <tr style="background: #ecfdf5;">
    <th style="padding: 10px; text-align: left; color: #065f46;">Amount Paid:</th>
    <td style="padding: 10px; font-size: 16px; font-weight: bold; color: #059669;">₦{{down_payment}}</td>
  </tr>
  <tr>
    <th style="padding: 10px; text-align: left;">Outstanding Balance:</th>
    <td style="padding: 10px; font-weight: bold; color: #dc2626;">₦{{outstanding_balance}}</td>
  </tr>
</table>
<br/>
<p><em>Issued by Officer: {{agent_name}}</em></p>`
    },
    offer: {
        name: 'Letter of Allocation & Offer of Sale',
        trigger: 'deal_won',
        html: `<h2>LETTER OF ALLOCATION &amp; PROVISIONAL OFFER</h2>
<p><strong>Date:</strong> {{current_date}}</p>
<p><strong>To:</strong><br/>{{client_name}}<br/>{{client_phone}} | {{client_email}}<br/>{{client_address}}</p>
<p>Dear {{client_name}},</p>
<p>We are pleased to convey the provisional offer and allocation of real estate unit in <strong>{{property_name}}</strong> located at <strong>{{property_location}}, {{property_state}}</strong> under the following agreed conditions:</p>
<ul>
  <li><strong>Plot / Unit:</strong> Block / Plot {{property_block}} (Size: {{property_size}} sqm)</li>
  <li><strong>Total Agreed Purchase Price:</strong> ₦{{deal_value}}</li>
  <li><strong>Deposit Paid:</strong> ₦{{down_payment}}</li>
  <li><strong>Title:</strong> {{title_type}}</li>
</ul>
<p>Please review and execute within fourteen (14) business days to formalize deed execution.</p>
<br/>
<p>Yours faithfully,<br/><strong>For: {{company_name}}</strong><br/>{{agent_name}} (Sales Executive)</p>`
    }
};

function loadPresetTemplate(type) {
    const preset = PRESET_TEMPLATES[type];
    if (!preset) return;
    document.getElementById('template-name-input').value = preset.name;
    document.getElementById('template-trigger-input').value = preset.trigger;
    document.getElementById('doc-editor').innerHTML = preset.html;
    wysiwyg_sync('doc-editor', 'doc-content-input');
}

/* ---- Shared WYSIWYG helpers (doc-editor) ---- */
function wysiwyg_exec(editorId, cmd, value) {
    document.getElementById(editorId).focus();
    document.execCommand(cmd, false, value || null);
    wysiwyg_sync(editorId, 'doc-content-input');
}

function wysiwyg_sync(editorId, inputId) {
    const content = document.getElementById(editorId).innerHTML;
    document.getElementById(inputId).value = content;
}

function wysiwyg_sync_from_source(editorId, inputId, html) {
    document.getElementById(editorId).innerHTML = html;
    document.getElementById(inputId).value = html;
}

function wysiwyg_toggle_source(editorId, sourceId, btn) {
    const editor = document.getElementById(editorId);
    const source = document.getElementById(sourceId);
    const isSource = !source.classList.contains('hidden');
    if (isSource) {
        editor.innerHTML = source.value;
        source.classList.add('hidden');
        editor.classList.remove('hidden');
        btn.classList.remove('bg-brand-100');
    } else {
        source.value = editor.innerHTML;
        editor.classList.add('hidden');
        source.classList.remove('hidden');
        btn.classList.add('bg-brand-100');
    }
}

function insertTokenToEditor(editorId, token) {
    const editor = document.getElementById(editorId);
    editor.focus();
    const sel = window.getSelection();
    if (sel && sel.rangeCount) {
        const range = sel.getRangeAt(0);
        range.deleteContents();
        const node = document.createTextNode(token);
        range.insertNode(node);
        range.setStartAfter(node);
        range.setEndAfter(node);
        sel.removeAllRanges();
        sel.addRange(range);
    } else {
        editor.innerHTML += token;
    }
    wysiwyg_sync(editorId, 'doc-content-input');
}

// Default load deed preset if blank
document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('doc-editor').innerHTML.trim()) {
        loadPresetTemplate('deed');
    }
});

// Sync before form submission
document.getElementById('doc-create-form').addEventListener('submit', function() {
    wysiwyg_sync('doc-editor', 'doc-content-input');
});
</script>
@endpush
@endsection
