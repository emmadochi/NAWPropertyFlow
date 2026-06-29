@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="campaignCreator()">

    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-gray-150">
        <div>
            <a href="{{ route('campaigns.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Campaigns</span>
            </a>
            <h1 class="text-2xl font-extrabold text-dark-900 tracking-tight">Create Marketing Campaign</h1>
            <p class="text-xs text-gray-500 mt-1">Design a targeted broadcast campaign for your database segment.</p>
        </div>
    </div>

    <form action="{{ route('campaigns.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6" id="campaign-create-form">
        @csrf

        <!-- Main Form Left Panel -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl border border-gray-150 p-6 md:p-8 shadow-sm space-y-6">

                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Campaign Name *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white" placeholder="e.g. June Special Promo - Ikoyi Estates">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Channel Type *</label>
                            <select name="type" x-model="type" required class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white text-gray-700">
                                <option value="email">Email Campaign</option>
                                <option value="sms">SMS Campaign</option>
                                <option value="whatsapp">WhatsApp Alert</option>
                            </select>
                        </div>

                        <div x-show="type === 'email'">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Subject Line *</label>
                            <input type="text" name="subject" :required="type === 'email'" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white" placeholder="e.g. Exclusive Deals on Nigeria Luxury Properties">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="type === 'email'">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">From Name</label>
                            <input type="text" name="from_name" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white" placeholder="e.g. NAW Sales Team">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">From Email</label>
                            <input type="email" name="from_email" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white" placeholder="e.g. sales@nawproperties.com">
                        </div>
                    </div>
                </div>

                <!-- Custom WYSIWYG Editor for Email -->
                <div class="space-y-2" x-show="type === 'email'">
                    <div class="flex items-center justify-between">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase">Message Content *</label>

                        <!-- Quick Token Insert helper -->
                        <div x-data="{ openTokens: false }" class="relative">
                            <button type="button" @click="openTokens = !openTokens" class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 border border-gray-250 rounded-lg text-[10px] font-bold text-gray-600 flex items-center space-x-1">
                                <span>Insert Token</span>
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-cloak x-show="openTokens" @click.away="openTokens = false"
                                 class="absolute right-0 mt-1 w-64 bg-white border border-gray-200 rounded-xl shadow-2xl z-50 divide-y divide-gray-100 py-1 text-left text-[11px] max-h-[400px] overflow-y-auto">

                                {{-- Lead / Client --}}
                                <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 uppercase tracking-widest bg-brand-50 sticky top-0">👤 Lead / Client</div>
                                <a href="#" @click.prevent="insertCampaignToken('@{{name}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Client Name</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{email}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold" x-show="type === 'email'">Client Email</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{phone}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Client Phone</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{address}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Client Address</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{lead_source}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Lead Source</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{lead_status}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Lead Status</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{assigned_agent}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Assigned Agent</a>

                                {{-- Property --}}
                                <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 uppercase tracking-widest bg-brand-50 sticky top-0">🏘️ Property</div>
                                <a href="#" @click.prevent="insertCampaignToken('@{{property_name}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Property Name</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{property_type}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Property Type</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{property_location}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Estate / Location</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{property_city}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">City</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{property_price}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Property Price</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{property_size}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Plot Size (sqm)</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{property_unit_type}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Unit Type</a>

                                {{-- Tracking --}}
                                <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 uppercase tracking-widest bg-brand-50 sticky top-0" x-show="type === 'email'">📊 Tracking</div>
                                <a href="#" @click.prevent="insertCampaignToken('@{{click_tracking_url}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold" x-show="type === 'email'">Click Tracking Link</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{unsubscribe_url}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold" x-show="type === 'email'">Unsubscribe URL</a>

                                {{-- Company --}}
                                <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 uppercase tracking-widest bg-brand-50 sticky top-0">🏢 Company</div>
                                <a href="#" @click.prevent="insertCampaignToken('@{{company_name}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Company Name</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{company_phone}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Company Phone</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{company_email}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Company Email</a>

                                {{-- Dates --}}
                                <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 uppercase tracking-widest bg-brand-50 sticky top-0">📅 Dates</div>
                                <a href="#" @click.prevent="insertCampaignToken('@{{current_date}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Today's Date</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{promo_expiry_date}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Promo Expiry Date</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{open_day_date}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 hover:bg-brand-50 hover:text-brand-700 font-semibold">Open Day / Event Date</a>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Editor Wrapper -->
                    <div id="campaign-editor-wrap" class="border border-gray-200 rounded-xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-brand-500/20 focus-within:border-brand-400 transition-all">
                        <!-- Toolbar -->
                        <div class="flex flex-wrap items-center gap-0.5 px-2 py-1.5 bg-gray-50 border-b border-gray-200">
                            <button type="button" onclick="campaign_exec('bold')" title="Bold" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"/></svg></button>
                            <button type="button" onclick="campaign_exec('italic')" title="Italic" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"/></svg></button>
                            <button type="button" onclick="campaign_exec('underline')" title="Underline" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17c3.31 0 6-2.69 6-6V3h-2.5v8c0 1.93-1.57 3.5-3.5 3.5S8.5 12.93 8.5 11V3H6v8c0 3.31 2.69 6 6 6zm-7 2v2h14v-2H5z"/></svg></button>
                            <button type="button" onclick="campaign_exec('strikeThrough')" title="Strikethrough" class="wysiwyg-btn text-xs font-bold">S̶</button>

                            <div class="w-px h-4 bg-gray-200 mx-1"></div>

                            <button type="button" onclick="campaign_exec('formatBlock','H1')" title="Heading 1" class="wysiwyg-btn text-[10px] font-black">H1</button>
                            <button type="button" onclick="campaign_exec('formatBlock','H2')" title="Heading 2" class="wysiwyg-btn text-[10px] font-black">H2</button>
                            <button type="button" onclick="campaign_exec('formatBlock','P')" title="Paragraph" class="wysiwyg-btn text-[10px] font-semibold">¶</button>

                            <div class="w-px h-4 bg-gray-200 mx-1"></div>

                            <button type="button" onclick="campaign_exec('insertUnorderedList')" title="Bullet List" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M4 10.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0-6c-.83 0-1.5.67-1.5 1.5S3.17 7.5 4 7.5 5.5 6.83 5.5 6 4.83 4.5 4 4.5zm0 12c-.83 0-1.5.68-1.5 1.5s.68 1.5 1.5 1.5 1.5-.68 1.5-1.5-.67-1.5-1.5-1.5zM7 19h14v-2H7v2zm0-6h14v-2H7v2zm0-8v2h14V5H7z"/></svg></button>
                            <button type="button" onclick="campaign_exec('insertOrderedList')" title="Numbered List" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M2 17h2v.5H3v1h1v.5H2v1h3v-4H2v1zm1-9h1V4H2v1h1v3zm-1 3h1.8L2 13.1v.9h3v-1H3.2L5 10.9V10H2v1zm5-6v2h14V5H7zm0 14h14v-2H7v2zm0-6h14v-2H7v2z"/></svg></button>

                            <div class="w-px h-4 bg-gray-200 mx-1"></div>

                            <button type="button" onclick="campaign_exec('justifyLeft')" title="Align Left" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M15 15H3v2h12v-2zm0-8H3v2h12V7zM3 13h18v-2H3v2zm0 8h18v-2H3v2zM3 3v2h18V3H3z"/></svg></button>
                            <button type="button" onclick="campaign_exec('justifyCenter')" title="Align Center" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M7 15v2h10v-2H7zm-4 6h18v-2H3v2zm0-8h18v-2H3v2zm4-6v2h10V7H7zM3 3v2h18V3H3z"/></svg></button>
                            <button type="button" onclick="campaign_exec('justifyRight')" title="Align Right" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 21h18v-2H3v2zm6-4h12v-2H9v2zm-6-4h18v-2H3v2zm6-4h12V7H9v2zM3 3v2h18V3H3z"/></svg></button>

                            <div class="w-px h-4 bg-gray-200 mx-1"></div>

                            <button type="button" onclick="campaign_insert_link()" title="Insert Link" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg></button>
                            <button type="button" onclick="campaign_exec('removeFormat')" title="Clear Formatting" class="wysiwyg-btn text-red-400 hover:text-red-600"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M20 8.69V4h-4.69L12 .69 8.69 4H4v4.69L.69 12 4 15.31V20h4.69L12 23.31 15.31 20H20v-4.69L23.31 12 20 8.69zm-2 5.79V18h-3.52L12 20.48 9.52 18H6v-3.52L3.52 12 6 9.52V6h3.52L12 3.52 14.48 6H18v3.52L20.48 12 18 14.48z"/></svg></button>
                            <button type="button" onclick="campaign_toggle_source(this)" title="Toggle HTML Source" class="wysiwyg-btn ml-auto text-[9px] font-black text-purple-600 hover:text-purple-800 hover:bg-purple-50 px-2">&lt;/&gt;</button>
                        </div>

                        <!-- Editable area -->
                        <div id="campaign-editor"
                             contenteditable="true"
                             class="min-h-[300px] p-4 text-sm text-gray-800 leading-relaxed focus:outline-none"
                             style="font-family: 'Plus Jakarta Sans', sans-serif;"
                             oninput="campaign_sync()"
                             onblur="campaign_sync()"
                        ></div>

                        <!-- Source mode textarea -->
                        <textarea id="campaign-editor-source"
                                  class="hidden w-full min-h-[300px] p-4 text-xs font-mono bg-gray-900 text-green-400 focus:outline-none resize-none border-t border-gray-200"
                                  oninput="campaign_sync_from_source(this.value)"
                        ></textarea>
                    </div>

                    <!-- Hidden input for form submission -->
                    <textarea name="body" id="campaign-body-input" class="hidden"></textarea>
                </div>

                <!-- SMS/WhatsApp plain text area -->
                <div x-show="type !== 'email'">
                    <textarea name="body_plain" x-model="plainBody" class="w-full h-40 px-3 py-2 border rounded-lg text-xs" placeholder="Type plain text message... Use @{{name}} for lead name."></textarea>
                    <p class="text-[10px] text-gray-400 mt-1">SMS messages are limited in formatting. Standard costs apply.</p>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-150">
                    <a href="{{ route('campaigns.index') }}" class="text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                        Save as Draft &amp; Build Segment
                    </button>
                </div>

            </div>
        </div>

        <!-- Audience Filter Panel Right -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl border border-gray-150 p-6 shadow-sm space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-dark-900 mb-1">Audience Targeting</h3>
                    <p class="text-[11px] text-gray-400">Configure segment rules to filter who receives this campaign.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Lead Status</label>
                        <select name="audience_status" x-model="audienceStatus" @change="updatePreview()" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white text-gray-700">
                            <option value="">Any Status</option>
                            <option value="new">New</option>
                            <option value="contacted">Contacted</option>
                            <option value="qualified">Qualified</option>
                            <option value="proposal_sent">Proposal Sent</option>
                            <option value="negotiating">Negotiating</option>
                            <option value="won">Won (Sale)</option>
                            <option value="lost">Lost</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">Lead Source</label>
                        <select name="audience_source" x-model="audienceSource" @change="updatePreview()" class="w-full px-3 py-2.5 border rounded-lg text-xs bg-white text-gray-700">
                            <option value="">Any Source</option>
                            <option value="Website">Website</option>
                            <option value="Instagram">Instagram</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Referral">Referral</option>
                            <option value="Direct Call">Direct Call</option>
                            <option value="PropertyPro">PropertyPro</option>
                        </select>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 space-y-2">
                    <span class="text-[10px] font-bold text-gray-400 uppercase block tracking-wider">Estimated Audience</span>
                    <div class="flex items-center space-x-2">
                        <span class="text-3xl font-black text-dark-900" x-text="previewCount">Calculating...</span>
                        <span class="text-xs text-gray-400">Leads matched</span>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>

@push('styles')
<style>
.wysiwyg-btn {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 4px 6px; border-radius: 6px; color: #4b5563;
    transition: background 0.15s, color 0.15s; min-width: 26px; height: 26px;
}
.wysiwyg-btn:hover { background: #e5e7eb; color: #111827; }
#campaign-editor h1 { font-size: 1.5em; font-weight: 800; margin: .5em 0; }
#campaign-editor h2 { font-size: 1.25em; font-weight: 700; margin: .4em 0; }
#campaign-editor p  { margin: .4em 0; }
#campaign-editor ul { list-style: disc; padding-left: 1.5em; }
#campaign-editor ol { list-style: decimal; padding-left: 1.5em; }
#campaign-editor a  { color: #6366f1; text-decoration: underline; }
</style>
@endpush

@push('scripts')
<script>
/* ---- Campaign WYSIWYG helpers ---- */
function campaign_exec(cmd, value) {
    document.getElementById('campaign-editor').focus();
    document.execCommand(cmd, false, value || null);
    campaign_sync();
}
function campaign_sync() {
    document.getElementById('campaign-body-input').value = document.getElementById('campaign-editor').innerHTML;
}
function campaign_sync_from_source(html) {
    document.getElementById('campaign-editor').innerHTML = html;
    document.getElementById('campaign-body-input').value = html;
}
function campaign_toggle_source(btn) {
    const editor = document.getElementById('campaign-editor');
    const source = document.getElementById('campaign-editor-source');
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
function campaign_insert_link() {
    const url = prompt('Enter URL:', 'https://');
    if (url) campaign_exec('createLink', url);
}

// Sync before form submission
document.getElementById('campaign-create-form').addEventListener('submit', function() {
    campaign_sync();
});

function campaignCreator() {
    return {
        type: 'email',
        audienceStatus: '',
        audienceSource: '',
        previewCount: '...',
        plainBody: '',

        init() {
            this.$watch('type', () => this.updatePreview());
            this.updatePreview();
        },

        insertCampaignToken(token) {
            if (this.type === 'email') {
                const editor = document.getElementById('campaign-editor');
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
                campaign_sync();
            } else {
                this.plainBody += token;
            }
        },

        updatePreview() {
            this.previewCount = '...';
            fetch('{{ route("campaigns.preview-audience") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    type: this.type,
                    audience_status: this.audienceStatus,
                    audience_source: this.audienceSource
                })
            })
            .then(res => res.json())
            .then(data => { this.previewCount = data.count; })
            .catch(() => { this.previewCount = 'Error'; });
        }
    }
}
</script>
@endpush
@endsection
