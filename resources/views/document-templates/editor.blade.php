@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-150 dark:border-slate-800">
        <div>
            <a href="{{ route('document-templates.show', $template) }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-gray-500 dark:text-slate-400 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Template Details</span>
            </a>
            <h1 class="text-2xl font-black text-dark-900 dark:text-white tracking-tight">Edit Document Content</h1>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Design rich legal clauses and map automated event trigger actions for {{ $template->name }}.</p>
        </div>
    </div>

    <!-- Edit template panel -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-150 dark:border-slate-700/60 p-6 md:p-8 shadow-sm">
        <form action="{{ route('document-templates.update', $template) }}" method="POST" class="space-y-6" id="doc-edit-form">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">Template Display Name *</label>
                    <input type="text" name="name" value="{{ old('name', $template->name) }}" required class="w-full px-3.5 py-2.5 border border-gray-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-dark-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">Trigger Pipeline Event *</label>
                    <select name="trigger_event" required class="w-full px-3.5 py-2.5 border border-gray-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-dark-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="deal_won" {{ $template->trigger_event === 'deal_won' ? 'selected' : '' }}>Deal Won (Sale logged)</option>
                        <option value="payment_received" {{ $template->trigger_event === 'payment_received' ? 'selected' : '' }}>Payment Received (Milestone paid)</option>
                        <option value="inspection_completed" {{ $template->trigger_event === 'inspection_completed' ? 'selected' : '' }}>Inspection Completed</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ $template->is_active ? 'checked' : '' }} class="rounded border-gray-300 dark:border-slate-700 text-brand-500 focus:ring-brand-500">
                <label for="is_active" class="text-xs font-bold text-gray-600 dark:text-slate-300 uppercase">Enable template auto-triggering on dispatch</label>
            </div>

            <!-- Custom WYSIWYG Editor Section -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase">Template Document Body Editor *</label>

                    <!-- Searchable Dynamic Token Insert helper -->
                    <div x-data="{
                        openTokens: false,
                        search: '',
                        categories: [
                            {
                                name: '👤 Lead & Client',
                                tokens: [
                                    { label: 'Client Full Name', tag: 'client_name', desc: 'e.g. Chief Emeka Adeleke' },
                                    { label: 'Client Phone', tag: 'client_phone', desc: '+234 803 123 4567' },
                                    { label: 'Client Email', tag: 'client_email', desc: 'client@example.com' },
                                    { label: 'Client Home Address', tag: 'client_address', desc: 'Residential contact address' },
                                    { label: 'Client NIN / ID', tag: 'client_nin', desc: 'National ID / Tax ID' },
                                    { label: 'Client Date of Birth', tag: 'client_dob', desc: 'DD/MM/YYYY' },
                                    { label: 'Client Occupation', tag: 'client_occupation', desc: 'Profession / Business' },
                                    { label: 'Client Company Name', tag: 'client_company', desc: 'Employer or Firm' },
                                    { label: 'Client Nationality', tag: 'client_nationality', desc: 'Nigerian' },
                                    { label: 'Passport / ID No.', tag: 'client_passport', desc: 'Official ID No.' }
                                ]
                            },
                            {
                                name: '🏘️ Property & Land Specs',
                                tokens: [
                                    { label: 'Property Name', tag: 'property_name', desc: 'The Orange Valley Heights' },
                                    { label: 'Property Type', tag: 'property_type', desc: 'Residential Land / Duplex' },
                                    { label: 'Location / Estate', tag: 'property_location', desc: 'Guzape District' },
                                    { label: 'Property Full Address', tag: 'property_address', desc: 'Plot 402, Cadastral Zone' },
                                    { label: 'State / City', tag: 'property_state', desc: 'Abuja (FCT) / Lagos' },
                                    { label: 'Plot Size (sqm)', tag: 'property_size', desc: '500 sqm' },
                                    { label: 'Unit / House Type', tag: 'property_unit_type', desc: '4-Bedroom Terrace' },
                                    { label: 'Plot / Block No.', tag: 'property_block', desc: 'Block B, Plot 14' },
                                    { label: 'Floor / Level', tag: 'property_floor', desc: '2nd Floor' },
                                    { label: 'Survey Plan No.', tag: 'survey_plan_no', desc: 'SURV/ABJ/2026/092' },
                                    { label: 'Title Type', tag: 'title_type', desc: 'C of O / R of O / Deed' },
                                    { label: 'Base / List Price', tag: 'property_price', desc: '₦180,000,000.00' },
                                    { label: 'Property Description', tag: 'property_description', desc: 'Architectural overview' }
                                ]
                            },
                            {
                                name: '💰 Deals, Payments & Schedule',
                                tokens: [
                                    { label: 'Final Sale / Deal Value', tag: 'deal_value', desc: '₦85,000,000.00' },
                                    { label: 'Initial Deposit / Paid', tag: 'down_payment', desc: '₦25,000,000.00' },
                                    { label: 'Outstanding Balance', tag: 'outstanding_balance', desc: '₦60,000,000.00' },
                                    { label: 'Payment Plan Duration', tag: 'payment_plan_duration', desc: '6 Months' },
                                    { label: 'Units Purchased', tag: 'units_purchased', desc: '1 unit(s)' },
                                    { label: 'Transaction Reference', tag: 'transaction_ref', desc: 'REF-TX-89201' },
                                    { label: 'Commission Amount', tag: 'commission_amount', desc: '₦4,250,000.00' },
                                    { label: 'Milestones Payment Table', tag: 'milestone_payments', desc: 'HTML Table Breakdown' }
                                ]
                            },
                            {
                                name: '🏢 Company & Developer Info',
                                tokens: [
                                    { label: 'Company Name', tag: 'company_name', desc: 'NAW PropertyFlow Real Estate' },
                                    { label: 'Company Address', tag: 'company_address', desc: 'Maitama, Abuja' },
                                    { label: 'Company Phone', tag: 'company_phone', desc: '+234 800 000 0000' },
                                    { label: 'Company Email', tag: 'company_email', desc: 'info@propertyflow.com' },
                                    { label: 'Corporate RC Number', tag: 'company_rc_number', desc: 'RC 1892044' }
                                ]
                            },
                            {
                                name: '📅 Dates & Deadlines',
                                tokens: [
                                    { label: 'Current Date', tag: 'current_date', desc: 'Today (e.g. August 25, 2026)' },
                                    { label: 'Date of Sale', tag: 'date_of_sale', desc: 'Deal Closing Date' },
                                    { label: 'Inspection Date', tag: 'inspection_date', desc: 'Site Tour Date' },
                                    { label: 'Key Handover Date', tag: 'key_handover_date', desc: 'Keys Delivery Target' },
                                    { label: 'Contract Date', tag: 'contract_date', desc: 'Execution Date' },
                                    { label: 'Expected Completion Date', tag: 'completion_date', desc: 'Project Delivery Date' }
                                ]
                            },
                            {
                                name: '🧑‍💼 Staff & Legal Attestation',
                                tokens: [
                                    { label: 'Assigned Agent Name', tag: 'agent_name', desc: 'Sales Executive' },
                                    { label: 'Agent Phone', tag: 'agent_phone', desc: 'Officer Contact' },
                                    { label: 'Agent Email', tag: 'agent_email', desc: 'Officer Email' },
                                    { label: 'Agent Branch', tag: 'agent_branch', desc: 'Abuja Central' },
                                    { label: 'Document Reference No.', tag: 'document_ref', desc: 'DOC-90218' },
                                    { label: 'Witness 1 Name', tag: 'witness_1_name', desc: 'First Legal Witness' },
                                    { label: 'Witness 2 Name', tag: 'witness_2_name', desc: 'Second Legal Witness' },
                                    { label: 'Solicitor / Notary Name', tag: 'solicitor_name', desc: 'Legal Counsel' },
                                    { label: 'Solicitor Law Firm', tag: 'solicitor_firm', desc: 'Chambers / Firm' },
                                    { label: 'Signatory Capacity', tag: 'signatory_capacity', desc: 'Vendor / Purchaser' }
                                ]
                            }
                        ],
                        insert(tag) {
                            insertTokenToEditor('doc-editor', '&#123;&#123;' + tag + '&#125;&#125;');
                            this.openTokens = false;
                        },
                        filterTokens(tokens) {
                            if (!this.search.trim()) return tokens;
                            const q = this.search.toLowerCase();
                            return tokens.filter(t => t.label.toLowerCase().includes(q) || t.tag.toLowerCase().includes(q) || t.desc.toLowerCase().includes(q));
                        }
                    }" class="relative">
                        <button type="button" @click="openTokens = !openTokens" class="px-3.5 py-1.5 bg-brand-50 hover:bg-brand-100 dark:bg-brand-950 dark:hover:bg-brand-900 border border-brand-200 dark:border-brand-800 rounded-xl text-xs font-black text-brand-700 dark:text-brand-300 flex items-center space-x-1.5 shadow-sm transition-all">
                            <span>✨ Insert Dynamic Field Token</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-cloak x-show="openTokens" @click.away="openTokens = false"
                             class="absolute right-0 mt-1.5 w-80 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-2xl z-50 overflow-hidden text-left text-xs">

                            <!-- Search Input -->
                            <div class="p-2.5 bg-gray-50 dark:bg-slate-900/80 border-b border-gray-200 dark:border-slate-700">
                                <input type="text" x-model="search" placeholder="Search 35+ fields (e.g. nin, plot, price)..."
                                       class="w-full px-3 py-1.5 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-xs text-dark-900 dark:text-white focus:outline-none focus:border-brand-500 placeholder:text-gray-400">
                            </div>

                            <!-- Tokens List -->
                            <div class="max-h-[380px] overflow-y-auto divide-y divide-gray-100 dark:divide-slate-700/60">
                                <template x-for="cat in categories" :key="cat.name">
                                    <div x-show="filterTokens(cat.tokens).length > 0">
                                        <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 dark:text-brand-400 uppercase tracking-widest bg-brand-50/80 dark:bg-brand-950/60 sticky top-0" x-text="cat.name"></div>
                                        <template x-for="tok in filterTokens(cat.tokens)" :key="tok.tag">
                                            <button type="button" @click="insert(tok.tag)"
                                                    class="w-full text-left px-3 py-2 hover:bg-brand-50 dark:hover:bg-slate-700 flex items-center justify-between group transition-colors">
                                                <div>
                                                    <p class="font-bold text-dark-900 dark:text-white text-xs group-hover:text-brand-600 dark:group-hover:text-brand-400" x-text="tok.label"></p>
                                                    <p class="text-[10px] text-gray-400 dark:text-slate-400" x-text="tok.desc"></p>
                                                </div>
                                                <code class="text-[9px] px-1.5 py-0.5 bg-gray-100 dark:bg-slate-900 text-gray-600 dark:text-slate-300 rounded font-mono" x-text="'{{' + tok.tag + '}}'"></code>
                                            </button>
                                        </template>
                                    </div>
                                </template>
                            </div>
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
                        <!-- Multi-Page Break & Clear -->
                        <button type="button" onclick="insertPageBreak('doc-editor')" title="Insert Multi-Page Break (Splits PDF into Next Page)" class="wysiwyg-btn text-[10px] font-black text-amber-600 hover:text-amber-700 bg-amber-50/60 hover:bg-amber-100/80 dark:bg-amber-950/40 dark:hover:bg-amber-900/60 dark:text-amber-300 px-2 rounded-lg border border-amber-300/40 flex items-center space-x-1 transition-all">
                            <span>📄 Page Break</span>
                        </button>

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
                    >{!! $template->latestVersion ? $template->latestVersion->content : '' !!}</div>

                    <!-- Source mode textarea -->
                    <textarea id="doc-editor-source"
                              class="hidden w-full min-h-[420px] p-6 text-xs font-mono text-emerald-400 bg-slate-950 focus:outline-none resize-none border-t border-slate-800"
                              oninput="wysiwyg_sync_from_source('doc-editor', 'doc-content-input', this.value)"
                    >{{ $template->latestVersion ? $template->latestVersion->content : '' }}</textarea>
                </div>

                <!-- Hidden textarea submitted with form -->
                <textarea name="content" id="doc-content-input" class="hidden">{{ $template->latestVersion ? $template->latestVersion->content : '' }}</textarea>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-150 dark:border-slate-700">
                <a href="{{ route('document-templates.show', $template) }}" class="text-xs font-bold text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white">
                    Cancel &amp; Rollback
                </a>
                <button type="submit" class="px-7 py-3 bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-brand-500/20 transition-all">
                    Save New Version
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

function insertPageBreak(editorId) {
    const editor = document.getElementById(editorId);
    editor.focus();
    const pageBreakHtml = '<div class="page-break" style="page-break-after: always; break-after: page; border-top: 2px dashed #FEA500; margin: 28px 0; text-align: center; color: #FEA500; font-size: 11px; font-weight: bold; padding: 6px 0; background: rgba(254, 165, 0, 0.05);">✂️ --- PAGE BREAK (Next Page Starts Here) ---</div><p><br></p>';
    document.execCommand('insertHTML', false, pageBreakHtml);
    wysiwyg_sync(editorId, 'doc-content-input');
}

// Sync before form submission
document.getElementById('doc-edit-form').addEventListener('submit', function() {
    wysiwyg_sync('doc-editor', 'doc-content-input');
});
</script>
@endpush
@endsection
