@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-150">
        <div>
            <a href="{{ route('document-templates.show', $template) }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Template Details</span>
            </a>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">Edit Document Content</h1>
            <p class="text-xs text-gray-500 mt-1">Design rich layout content and map automated event trigger actions.</p>
        </div>
    </div>

    <!-- Edit template panel -->
    <div class="bg-white rounded-3xl border border-gray-150 p-6 md:p-8 shadow-sm">
        <form action="{{ route('document-templates.update', $template) }}" method="POST" class="space-y-6" id="doc-edit-form">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Template Display Name *</label>
                    <input type="text" name="name" value="{{ old('name', $template->name) }}" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Trigger Pipeline Event *</label>
                    <select name="trigger_event" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white text-gray-700">
                        <option value="deal_won" {{ $template->trigger_event === 'deal_won' ? 'selected' : '' }}>Deal Won (Sale logged)</option>
                        <option value="payment_received" {{ $template->trigger_event === 'payment_received' ? 'selected' : '' }}>Payment Received (Milestone paid)</option>
                        <option value="inspection_completed" {{ $template->trigger_event === 'inspection_completed' ? 'selected' : '' }}>Inspection Completed</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ $template->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                <label for="is_active" class="text-xs font-bold text-gray-600 uppercase">Enable template auto-triggering on dispatch</label>
            </div>

            <!-- Custom WYSIWYG Editor Section -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase">Template Document Body Editor *</label>

                    <!-- Quick Token Insert helper -->
                    <div x-data="{ openTokens: false }" class="relative">
                        <button type="button" @click="openTokens = !openTokens" class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-250 rounded-lg text-[10px] font-bold text-gray-600 flex items-center space-x-1">
                            <span>Insert Variable Token</span>
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-cloak x-show="openTokens" @click.away="openTokens = false"
                             class="absolute right-0 mt-1 w-72 bg-white border border-gray-200 rounded-xl shadow-2xl z-50 divide-y divide-gray-100 py-1 text-left text-[11px] max-h-[480px] overflow-y-auto">

                            {{-- Lead / Client --}}
                            <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 uppercase tracking-widest bg-brand-50 sticky top-0">👤 Lead / Client</div>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{client_name}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Client Full Name</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{client_phone}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Client Phone</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{client_email}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Client Email</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{client_address}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Client Home Address</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{client_nin}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Client NIN</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{client_dob}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Client Date of Birth</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{client_occupation}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Client Occupation</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{client_company}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Client Company Name</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{client_nationality}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Client Nationality</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{client_passport}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Passport / ID Number</a>

                            {{-- Property --}}
                            <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 uppercase tracking-widest bg-brand-50 sticky top-0">🏘️ Property</div>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{property_name}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Property Name</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{property_type}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Property Type</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{property_address}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Property Full Address</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{property_location}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Estate / Development</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{property_city}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">City / LGA</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{property_state}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">State</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{property_size}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Plot Size (sqm)</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{property_unit_type}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Unit Type (e.g. 3-Bed)</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{property_floor}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Floor / Level</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{property_block}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Block / Plot Number</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{survey_plan_no}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Survey Plan Number</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{title_type}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Title Type (C of O / R of O)</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{property_price}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Base / List Price</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{property_description}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Property Description</a>

                            {{-- Deal / Finance --}}
                            <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 uppercase tracking-widest bg-brand-50 sticky top-0">💰 Deal / Finance</div>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{deal_value}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Final Sale Value</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{down_payment}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Initial Deposit / Down Payment</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{outstanding_balance}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Outstanding Balance</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{payment_plan_duration}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Payment Plan Duration</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{units_purchased}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Units Count</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{transaction_ref}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Transaction Reference</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{commission_amount}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Commission Amount</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{milestone_payments}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Milestones Payment Table</a>

                            {{-- Agent / Staff --}}
                            <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 uppercase tracking-widest bg-brand-50 sticky top-0">🧑‍💼 Agent / Staff</div>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{agent_name}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Agent Full Name</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{agent_phone}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Agent Phone</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{agent_email}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Agent Email</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{agent_branch}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Agent Branch</a>

                            {{-- Company --}}
                            <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 uppercase tracking-widest bg-brand-50 sticky top-0">🏢 Company</div>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{company_name}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Company Name</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{company_address}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Company Address</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{company_phone}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Company Phone</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{company_email}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Company Email</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{company_rc_number}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">RC Number</a>

                            {{-- Dates & Schedule --}}
                            <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 uppercase tracking-widest bg-brand-50 sticky top-0">📅 Dates &amp; Schedule</div>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{current_date}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Today's Date</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{date_of_sale}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Date of Sale</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{inspection_date}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Inspection Date</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{key_handover_date}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Key Handover Date</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{contract_date}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Contract Execution Date</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{completion_date}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Expected Completion Date</a>

                            {{-- Legal --}}
                            <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 uppercase tracking-widest bg-brand-50 sticky top-0">⚖️ Legal</div>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{document_ref}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Document Reference No.</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{witness_1_name}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Witness 1 Name</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{witness_2_name}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Witness 2 Name</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{solicitor_name}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Solicitor / Notary Name</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{solicitor_firm}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Solicitor Firm</a>
                            <a href="#" @click.prevent="insertTokenToEditor('doc-edit-editor', '@{{signatory_capacity}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Signatory Capacity</a>
                        </div>
                    </div>
                </div>

                <!-- Custom Editor Wrapper -->
                <div id="doc-edit-editor-wrap" class="border border-gray-200 rounded-xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-brand-500/20 focus-within:border-brand-400 transition-all">
                    <!-- Toolbar -->
                    <div class="flex flex-wrap items-center gap-0.5 px-2 py-1.5 bg-gray-50 border-b border-gray-200">
                        <button type="button" onclick="wysiwyg_exec('doc-edit-editor','bold')" title="Bold" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"/></svg></button>
                        <button type="button" onclick="wysiwyg_exec('doc-edit-editor','italic')" title="Italic" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"/></svg></button>
                        <button type="button" onclick="wysiwyg_exec('doc-edit-editor','underline')" title="Underline" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17c3.31 0 6-2.69 6-6V3h-2.5v8c0 1.93-1.57 3.5-3.5 3.5S8.5 12.93 8.5 11V3H6v8c0 3.31 2.69 6 6 6zm-7 2v2h14v-2H5z"/></svg></button>
                        <button type="button" onclick="wysiwyg_exec('doc-edit-editor','strikeThrough')" title="Strikethrough" class="wysiwyg-btn text-xs font-bold">S̶</button>

                        <div class="w-px h-4 bg-gray-200 mx-1"></div>

                        <button type="button" onclick="wysiwyg_exec('doc-edit-editor','formatBlock','H1')" title="Heading 1" class="wysiwyg-btn text-[10px] font-black">H1</button>
                        <button type="button" onclick="wysiwyg_exec('doc-edit-editor','formatBlock','H2')" title="Heading 2" class="wysiwyg-btn text-[10px] font-black">H2</button>
                        <button type="button" onclick="wysiwyg_exec('doc-edit-editor','formatBlock','H3')" title="Heading 3" class="wysiwyg-btn text-[10px] font-black">H3</button>
                        <button type="button" onclick="wysiwyg_exec('doc-edit-editor','formatBlock','P')" title="Paragraph" class="wysiwyg-btn text-[10px] font-semibold">¶</button>

                        <div class="w-px h-4 bg-gray-200 mx-1"></div>

                        <button type="button" onclick="wysiwyg_exec('doc-edit-editor','insertUnorderedList')" title="Bullet List" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M4 10.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0-6c-.83 0-1.5.67-1.5 1.5S3.17 7.5 4 7.5 5.5 6.83 5.5 6 4.83 4.5 4 4.5zm0 12c-.83 0-1.5.68-1.5 1.5s.68 1.5 1.5 1.5 1.5-.68 1.5-1.5-.67-1.5-1.5-1.5zM7 19h14v-2H7v2zm0-6h14v-2H7v2zm0-8v2h14V5H7z"/></svg></button>
                        <button type="button" onclick="wysiwyg_exec('doc-edit-editor','insertOrderedList')" title="Numbered List" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M2 17h2v.5H3v1h1v.5H2v1h3v-4H2v1zm1-9h1V4H2v1h1v3zm-1 3h1.8L2 13.1v.9h3v-1H3.2L5 10.9V10H2v1zm5-6v2h14V5H7zm0 14h14v-2H7v2zm0-6h14v-2H7v2z"/></svg></button>

                        <div class="w-px h-4 bg-gray-200 mx-1"></div>

                        <button type="button" onclick="wysiwyg_exec('doc-edit-editor','justifyLeft')" title="Align Left" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M15 15H3v2h12v-2zm0-8H3v2h12V7zM3 13h18v-2H3v2zm0 8h18v-2H3v2zM3 3v2h18V3H3z"/></svg></button>
                        <button type="button" onclick="wysiwyg_exec('doc-edit-editor','justifyCenter')" title="Align Center" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M7 15v2h10v-2H7zm-4 6h18v-2H3v2zm0-8h18v-2H3v2zm4-6v2h10V7H7zM3 3v2h18V3H3z"/></svg></button>
                        <button type="button" onclick="wysiwyg_exec('doc-edit-editor','justifyRight')" title="Align Right" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 21h18v-2H3v2zm6-4h12v-2H9v2zm-6-4h18v-2H3v2zm6-4h12V7H9v2zM3 3v2h18V3H3z"/></svg></button>

                        <div class="w-px h-4 bg-gray-200 mx-1"></div>

                        <button type="button" onclick="wysiwyg_insert_link('doc-edit-editor')" title="Insert Link" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg></button>
                        <button type="button" onclick="wysiwyg_exec('doc-edit-editor','removeFormat')" title="Clear Formatting" class="wysiwyg-btn text-red-400 hover:text-red-600"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M20 8.69V4h-4.69L12 .69 8.69 4H4v4.69L.69 12 4 15.31V20h4.69L12 23.31 15.31 20H20v-4.69L23.31 12 20 8.69zm-2 5.79V18h-3.52L12 20.48 9.52 18H6v-3.52L3.52 12 6 9.52V6h3.52L12 3.52 14.48 6H18v3.52L20.48 12 18 14.48z"/></svg></button>
                        <button type="button" onclick="wysiwyg_toggle_source('doc-edit-editor', 'doc-edit-source', this)" title="Toggle HTML Source" class="wysiwyg-btn ml-auto text-[9px] font-black text-purple-600 hover:text-purple-800 hover:bg-purple-50 px-2">&lt;/&gt;</button>
                    </div>

                    <!-- Editable area (pre-loaded with existing content) -->
                    <div id="doc-edit-editor"
                         contenteditable="true"
                         class="min-h-[400px] p-4 text-sm text-gray-800 leading-relaxed focus:outline-none"
                         style="font-family: 'Plus Jakarta Sans', sans-serif;"
                         oninput="wysiwyg_sync('doc-edit-editor', 'doc-edit-content-input')"
                         onblur="wysiwyg_sync('doc-edit-editor', 'doc-edit-content-input')"
                    >{!! old('content', $template->latestVersion ? $template->latestVersion->content : '') !!}</div>

                    <!-- Source mode textarea -->
                    <textarea id="doc-edit-source"
                              class="hidden w-full min-h-[400px] p-4 text-xs font-mono bg-gray-900 text-green-400 focus:outline-none resize-none border-t border-gray-200"
                              oninput="wysiwyg_sync_from_source('doc-edit-editor', 'doc-edit-content-input', this.value)"
                    ></textarea>
                </div>

                <!-- Hidden textarea submitted with form -->
                <textarea name="content" id="doc-edit-content-input" class="hidden">{{ old('content', $template->latestVersion ? $template->latestVersion->content : '') }}</textarea>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-150">
                <button type="button" onclick="history.back()" class="text-xs font-bold text-gray-500 hover:text-gray-700">
                    Cancel &amp; Rollback
                </button>
                <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
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
    padding: 4px 6px; border-radius: 6px; color: #4b5563;
    transition: background 0.15s, color 0.15s; min-width: 26px; height: 26px;
}
.wysiwyg-btn:hover { background: #e5e7eb; color: #111827; }
#doc-edit-editor h1 { font-size: 1.6em; font-weight: 800; margin: .5em 0; }
#doc-edit-editor h2 { font-size: 1.3em; font-weight: 700; margin: .5em 0; }
#doc-edit-editor h3 { font-size: 1.1em; font-weight: 700; margin: .4em 0; }
#doc-edit-editor p  { margin: .4em 0; }
#doc-edit-editor ul { list-style: disc; padding-left: 1.5em; }
#doc-edit-editor ol { list-style: decimal; padding-left: 1.5em; }
#doc-edit-editor a  { color: #6366f1; text-decoration: underline; }
</style>
@endpush

@push('scripts')
<script>
/* ---- Shared WYSIWYG helpers (doc-edit-editor) ---- */
function wysiwyg_exec(editorId, cmd, value) {
    document.getElementById(editorId).focus();
    document.execCommand(cmd, false, value || null);
    wysiwyg_sync(editorId, 'doc-edit-content-input');
}

function wysiwyg_sync(editorId, inputId) {
    document.getElementById(inputId).value = document.getElementById(editorId).innerHTML;
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
        btn.classList.remove('bg-purple-100');
    } else {
        source.value = editor.innerHTML;
        editor.classList.add('hidden');
        source.classList.remove('hidden');
        btn.classList.add('bg-purple-100');
    }
}

function wysiwyg_insert_link(editorId) {
    const url = prompt('Enter URL:', 'https://');
    if (url) wysiwyg_exec(editorId, 'createLink', url);
}

function insertTokenToEditor(editorId, token) {
    const editor = document.getElementById(editorId);
    editor.focus();
    const sel = window.getSelection();
    if (sel.rangeCount) {
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
    wysiwyg_sync(editorId, 'doc-edit-content-input');
}

// Sync before form submission
document.getElementById('doc-edit-form').addEventListener('submit', function() {
    wysiwyg_sync('doc-edit-editor', 'doc-edit-content-input');
});
</script>
@endpush
@endsection
