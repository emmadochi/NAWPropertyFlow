@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="campaignCreator()">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-gray-200 dark:border-slate-800 gap-4">
        <div>
            <a href="{{ route('campaigns.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-semibold text-gray-500 hover:text-brand-500 transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Back to Campaigns</span>
            </a>
            <h1 class="text-2xl font-extrabold text-dark-900 dark:text-white tracking-tight">Create Real Estate Campaign / Newsletter</h1>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Design luxury property newsletters or quick broadcast alerts with instant preview.</p>
        </div>

        <div class="flex items-center space-x-3">
            <button type="button" @click="showPreviewModal = true" class="px-4 py-2.5 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 text-dark-900 dark:text-white font-bold text-xs rounded-xl transition-colors flex items-center space-x-1.5 border border-gray-200 dark:border-slate-700">
                <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                <span>Live Preview</span>
            </button>
            <button type="button" @click="showTestModal = true" class="px-4 py-2.5 bg-orange-50 hover:bg-orange-100 text-brand-600 font-bold text-xs rounded-xl transition-colors flex items-center space-x-1.5 border border-brand-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <span>Send Test to Inbox</span>
            </button>
        </div>
    </div>

    <!-- Quick Real Estate Templates Gallery -->
    <div class="bg-gradient-to-r from-orange-50 to-amber-50 dark:from-slate-800 dark:to-slate-850 rounded-2xl p-5 border border-orange-100 dark:border-slate-700 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-xs font-black text-dark-900 dark:text-white uppercase tracking-wider flex items-center space-x-1.5">
                    <span class="text-brand-500">✨</span>
                    <span>1-Click Real Estate Luxury Templates</span>
                </h3>
                <p class="text-[11px] text-gray-500 dark:text-slate-400">Click any template below to load a pre-designed luxury layout into the editor.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <button type="button" @click="loadTemplate('launch')" 
                    class="p-3.5 rounded-2xl border transition-all text-left group relative"
                    :class="activeTemplate === 'launch' ? 'bg-orange-50/90 dark:bg-slate-800 border-brand-500 shadow-md ring-2 ring-brand-500/20' : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 hover:border-brand-400 hover:shadow-sm'">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-xl bg-orange-100 text-brand-600 flex items-center justify-center font-bold text-base group-hover:scale-110 transition-transform">🚀</div>
                    <span x-show="activeTemplate === 'launch'" class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-brand-500 text-white">Active</span>
                </div>
                <h4 class="text-xs font-bold text-dark-900 dark:text-white">New Estate Launch</h4>
                <p class="text-[10px] text-gray-400 mt-0.5 leading-tight">Hero banner, price perk, plot sizes &amp; inspection CTA.</p>
            </button>

            <button type="button" @click="loadTemplate('digest')" 
                    class="p-3.5 rounded-2xl border transition-all text-left group relative"
                    :class="activeTemplate === 'digest' ? 'bg-blue-50/90 dark:bg-slate-800 border-blue-500 shadow-md ring-2 ring-blue-500/20' : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 hover:border-blue-400 hover:shadow-sm'">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-base group-hover:scale-110 transition-transform">📰</div>
                    <span x-show="activeTemplate === 'digest'" class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-blue-600 text-white">Active</span>
                </div>
                <h4 class="text-xs font-bold text-dark-900 dark:text-white">Monthly Investor Digest</h4>
                <p class="text-[10px] text-gray-400 mt-0.5 leading-tight">Market commentary, featured property cards &amp; updates.</p>
            </button>

            <button type="button" @click="loadTemplate('promo')" 
                    class="p-3.5 rounded-2xl border transition-all text-left group relative"
                    :class="activeTemplate === 'promo' ? 'bg-emerald-50/90 dark:bg-slate-800 border-emerald-500 shadow-md ring-2 ring-emerald-500/20' : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 hover:border-emerald-400 hover:shadow-sm'">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-base group-hover:scale-110 transition-transform">🏷️</div>
                    <span x-show="activeTemplate === 'promo'" class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-600 text-white">Active</span>
                </div>
                <h4 class="text-xs font-bold text-dark-900 dark:text-white">Flash Discount Promo</h4>
                <p class="text-[10px] text-gray-400 mt-0.5 leading-tight">15% discount badge, installment spread &amp; WhatsApp link.</p>
            </button>

            <button type="button" @click="loadTemplate('progress')" 
                    class="p-3.5 rounded-2xl border transition-all text-left group relative"
                    :class="activeTemplate === 'progress' ? 'bg-purple-50/90 dark:bg-slate-800 border-purple-500 shadow-md ring-2 ring-purple-500/20' : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 hover:border-purple-400 hover:shadow-sm'">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-base group-hover:scale-110 transition-transform">🏗️</div>
                    <span x-show="activeTemplate === 'progress'" class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-purple-600 text-white">Active</span>
                </div>
                <h4 class="text-xs font-bold text-dark-900 dark:text-white">Construction Progress</h4>
                <p class="text-[10px] text-gray-400 mt-0.5 leading-tight">Site milestone photos, road grading &amp; allocation news.</p>
            </button>
        </div>
    </div>

    <form action="{{ route('campaigns.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6" id="campaign-create-form">
        @csrf

        <!-- Main Form Left Panel -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-200 dark:border-slate-700 p-6 md:p-8 shadow-sm space-y-6">

                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">Campaign Name *</label>
                        <input type="text" name="name" x-model="campaignName" required class="w-full px-3 py-2.5 border border-gray-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-dark-900 dark:text-white focus:border-brand-500 focus:outline-none" placeholder="e.g. Q3 Investor Newsletter - Luxury Land &amp; Duplex Offers">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">Channel Type *</label>
                            <select name="type" x-model="type" required class="w-full px-3 py-2.5 border border-gray-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-dark-900 dark:text-white focus:border-brand-500 focus:outline-none">
                                <option value="email">Email Campaign / Newsletter</option>
                                <option value="sms">SMS Broadcast</option>
                                <option value="whatsapp">WhatsApp Alert</option>
                            </select>
                        </div>

                        <div x-show="type === 'email'">
                            <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">Subject Line *</label>
                            <input type="text" name="subject" x-model="subject" :required="type === 'email'" class="w-full px-3 py-2.5 border border-gray-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-dark-900 dark:text-white focus:border-brand-500 focus:outline-none" placeholder="e.g. 🌟 Exclusive: New Luxury Estate Launch &amp; 15% Launch Offer">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="type === 'email'">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">From Name</label>
                            <input type="text" name="from_name" value="{{ \App\Models\CompanySetting::getCached()?->company_name ?? 'RICAF Nigeria Limited' }}" class="w-full px-3 py-2.5 border border-gray-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-dark-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">From Email</label>
                            <input type="email" name="from_email" value="{{ \App\Models\CompanySetting::getCached()?->email ?? 'info@ricafltd.com' }}" class="w-full px-3 py-2.5 border border-gray-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-dark-900 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Custom Visual Editor for Email -->
                <div class="space-y-2" x-show="type === 'email'">
                    <div class="flex items-center justify-between">
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase">Newsletter Body Content *</label>

                        <!-- Quick Token Insert Helper -->
                        <div x-data="{ openTokens: false }" class="relative">
                            <button type="button" @click="openTokens = !openTokens" class="px-3 py-1.5 bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 border border-gray-200 dark:border-slate-700 rounded-lg text-[10px] font-bold text-gray-600 dark:text-slate-300 flex items-center space-x-1">
                                <span>Insert Personalization Tag</span>
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-cloak x-show="openTokens" @click.away="openTokens = false"
                                 class="absolute right-0 mt-1 w-64 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-2xl z-50 divide-y divide-gray-100 dark:divide-slate-700 py-1 text-left text-[11px] max-h-[400px] overflow-y-auto">
                                <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 uppercase tracking-widest bg-brand-50 dark:bg-slate-900">👤 Lead / Client</div>
                                <a href="#" @click.prevent="insertCampaignToken('@{{name}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-300 hover:bg-brand-50 hover:text-brand-700 font-semibold">Client Name</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{email}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-300 hover:bg-brand-50 hover:text-brand-700 font-semibold">Client Email</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{phone}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-300 hover:bg-brand-50 hover:text-brand-700 font-semibold">Client Phone</a>
                                
                                <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 uppercase tracking-widest bg-brand-50 dark:bg-slate-900">🏘️ Property &amp; Offer</div>
                                <a href="#" @click.prevent="insertCampaignToken('@{{property_name}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-300 hover:bg-brand-50 hover:text-brand-700 font-semibold">Property / Estate Name</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{property_price}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-300 hover:bg-brand-50 hover:text-brand-700 font-semibold">Property Price (₦)</a>

                                <div class="px-3 py-1.5 text-[9px] font-black text-brand-600 uppercase tracking-widest bg-brand-50 dark:bg-slate-900">🏢 Company &amp; Contact</div>
                                <a href="#" @click.prevent="insertCampaignToken('@{{company_name}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-300 hover:bg-brand-50 hover:text-brand-700 font-semibold">Company Name</a>
                                <a href="#" @click.prevent="insertCampaignToken('@{{company_phone}}'); openTokens=false" class="block px-3 py-1.5 text-gray-700 dark:text-slate-300 hover:bg-brand-50 hover:text-brand-700 font-semibold">Company Phone</a>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Editor Wrapper -->
                    <div id="campaign-editor-wrap" class="border border-gray-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm focus-within:ring-2 focus-within:ring-brand-500/20 focus-within:border-brand-400 transition-all bg-white dark:bg-slate-900">
                        <!-- Toolbar -->
                        <div class="flex flex-wrap items-center gap-1 px-3 py-2 bg-gray-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700">
                            <button type="button" onclick="campaign_exec('bold')" title="Bold" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"/></svg></button>
                            <button type="button" onclick="campaign_exec('italic')" title="Italic" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"/></svg></button>
                            <button type="button" onclick="campaign_exec('underline')" title="Underline" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17c3.31 0 6-2.69 6-6V3h-2.5v8c0 1.93-1.57 3.5-3.5 3.5S8.5 12.93 8.5 11V3H6v8c0 3.31 2.69 6 6 6zm-7 2v2h14v-2H5z"/></svg></button>
                            
                            <div class="w-px h-4 bg-gray-200 dark:bg-slate-700 mx-1"></div>
                            
                            <button type="button" onclick="campaign_exec('formatBlock','H2')" title="Heading" class="wysiwyg-btn text-xs font-black">H2</button>
                            <button type="button" onclick="campaign_exec('formatBlock','H3')" title="Subheading" class="wysiwyg-btn text-xs font-black">H3</button>
                            <button type="button" onclick="campaign_exec('formatBlock','P')" title="Paragraph" class="wysiwyg-btn text-xs font-semibold">¶</button>

                            <div class="w-px h-4 bg-gray-200 dark:bg-slate-700 mx-1"></div>

                            <button type="button" onclick="campaign_exec('insertUnorderedList')" title="Bullet List" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M4 10.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0-6c-.83 0-1.5.67-1.5 1.5S3.17 7.5 4 7.5 5.5 6.83 5.5 6 4.83 4.5 4 4.5zm0 12c-.83 0-1.5.68-1.5 1.5s.68 1.5 1.5 1.5 1.5-.68 1.5-1.5-.67-1.5-1.5-1.5zM7 19h14v-2H7v2zm0-6h14v-2H7v2zm0-8v2h14V5H7z"/></svg></button>
                            <button type="button" onclick="campaign_insert_link()" title="Insert Link / Button" class="wysiwyg-btn"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg></button>
                            <button type="button" onclick="campaign_toggle_source(this)" title="Toggle HTML Source" class="wysiwyg-btn ml-auto text-[10px] font-black text-purple-600 hover:text-purple-800 hover:bg-purple-50 px-2">&lt;/&gt; HTML</button>
                        </div>

                        <!-- Editable area -->
                        <div id="campaign-editor"
                             contenteditable="true"
                             class="min-h-[360px] p-6 text-sm text-gray-800 dark:text-slate-200 leading-relaxed focus:outline-none"
                             oninput="campaign_sync()"
                             onblur="campaign_sync()"
                        ></div>

                        <!-- Source mode textarea -->
                        <textarea id="campaign-editor-source"
                                  class="hidden w-full min-h-[360px] p-4 text-xs font-mono bg-gray-900 text-green-400 focus:outline-none resize-none border-t border-gray-200"
                                  oninput="campaign_sync_from_source(this.value)"
                        ></textarea>
                    </div>

                    <!-- Hidden input for form submission -->
                    <textarea name="body" id="campaign-body-input" class="hidden"></textarea>
                </div>

                <!-- SMS/WhatsApp plain text area -->
                <div x-show="type !== 'email'">
                    <textarea name="body_plain" x-model="plainBody" class="w-full h-44 px-4 py-3 border border-gray-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-dark-900 dark:text-white" placeholder="Type plain text broadcast message... Use @{{name}} for recipient name."></textarea>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-slate-700">
                    <a href="{{ route('campaigns.index') }}" class="text-xs font-bold text-gray-500 hover:text-gray-700">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold text-xs rounded-xl shadow-md shadow-brand-500/20 transition-all">
                        Save as Draft &amp; Calculate Segment
                    </button>
                </div>

            </div>
        </div>

        <!-- Audience Filter Panel Right -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-200 dark:border-slate-700 p-6 shadow-sm space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-dark-900 dark:text-white mb-1">Target Audience Segment</h3>
                    <p class="text-[11px] text-gray-400">Filter which buyers or leads will receive this newsletter.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">Lead Stage</label>
                        <select name="audience_status" x-model="audienceStatus" @change="updatePreview()" class="w-full px-3 py-2.5 border border-gray-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-dark-900 dark:text-white focus:border-brand-500 focus:outline-none">
                            <option value="">All Leads &amp; Contacts</option>
                            <option value="new">New Inquiries</option>
                            <option value="contacted">Contacted Leads</option>
                            <option value="qualified">Qualified High-Intent</option>
                            <option value="proposal_sent">Proposal / Offer Sent</option>
                            <option value="negotiating">In Negotiation</option>
                            <option value="won">Closed Buyers (Existing Clients)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">Lead Source</label>
                        <select name="audience_source" x-model="audienceSource" @change="updatePreview()" class="w-full px-3 py-2.5 border border-gray-200 dark:border-slate-700 rounded-xl text-xs bg-white dark:bg-slate-900 text-dark-900 dark:text-white focus:border-brand-500 focus:outline-none">
                            <option value="">All Sources</option>
                            <option value="Website">Website Inquiries</option>
                            <option value="Instagram">Instagram Ads</option>
                            <option value="Facebook">Facebook Campaigns</option>
                            <option value="Referral">Client Referrals</option>
                            <option value="Direct Call">Direct Calls</option>
                        </select>
                    </div>
                </div>

                <div class="bg-orange-50 dark:bg-slate-900 rounded-2xl p-4 border border-orange-100 dark:border-slate-700 space-y-2">
                    <span class="text-[10px] font-bold text-brand-600 uppercase block tracking-wider">Estimated Audience</span>
                    <div class="flex items-center space-x-2">
                        <span class="text-3xl font-black text-dark-900 dark:text-white" x-text="previewCount">Calculating...</span>
                        <span class="text-xs text-gray-400">recipients matched</span>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal 1: Live Interactive Preview Modal (Mobile & Desktop Toggle) -->
    <div x-show="showPreviewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-dark-900/60 backdrop-blur-sm" @click="showPreviewModal = false"></div>
        <div class="relative bg-white dark:bg-slate-900 rounded-3xl max-w-3xl w-full p-6 shadow-2xl border border-gray-200 dark:border-slate-700 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-slate-800">
                <div class="flex items-center space-x-3">
                    <span class="text-lg font-extrabold text-dark-900 dark:text-white">Live Email Preview</span>
                    <div class="flex items-center bg-gray-100 dark:bg-slate-800 p-1 rounded-xl">
                        <button type="button" @click="previewMode = 'desktop'" :class="previewMode === 'desktop' ? 'bg-white dark:bg-slate-700 text-brand-500 shadow-sm' : 'text-gray-400'" class="px-3 py-1 rounded-lg text-xs font-bold transition-all">🖥️ Desktop</button>
                        <button type="button" @click="previewMode = 'mobile'" :class="previewMode === 'mobile' ? 'bg-white dark:bg-slate-700 text-brand-500 shadow-sm' : 'text-gray-400'" class="px-3 py-1 rounded-lg text-xs font-bold transition-all">📱 Mobile</button>
                    </div>
                </div>
                <button @click="showPreviewModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            
            <div class="flex-1 overflow-y-auto py-6 flex justify-center bg-gray-100 dark:bg-slate-950 rounded-2xl my-3">
                <div :class="previewMode === 'mobile' ? 'w-[375px] shadow-2xl border-4 border-gray-800 rounded-3xl bg-white' : 'w-full max-w-2xl bg-white shadow-md rounded-2xl'" class="overflow-hidden p-6 text-gray-800" x-html="renderPreviewContent()">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" @click="showPreviewModal = false" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-dark-900 text-xs font-bold rounded-xl">Close Preview</button>
            </div>
        </div>
    </div>

    <!-- Modal 2: Send Test Email Modal -->
    <div x-show="showTestModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-dark-900/60 backdrop-blur-sm" @click="showTestModal = false"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-gray-200 dark:border-slate-700">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-orange-50 text-brand-500 flex items-center justify-center font-bold">✉️</div>
                <div>
                    <h3 class="text-base font-extrabold text-dark-900 dark:text-white">Send Live Test Email</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Deliver this newsletter preview directly to your inbox.</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-slate-300 uppercase mb-1.5">Recipient Email *</label>
                    <input type="email" x-model="testRecipient" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-sm font-semibold focus:border-brand-500 focus:outline-none">
                </div>

                <div x-show="testStatusMessage" class="p-3 rounded-xl text-xs font-bold" :class="testStatusType === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'" x-text="testStatusMessage"></div>

                <div class="flex items-center justify-end space-x-3 pt-3">
                    <button type="button" @click="showTestModal = false" class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-slate-400 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="button" @click="dispatchTestEmail()" :disabled="sendingTest" class="bg-brand-500 hover:bg-brand-600 text-white font-bold px-5 py-2.5 rounded-xl text-xs shadow-md shadow-brand-500/20 disabled:opacity-50">
                        <span x-show="!sendingTest">Send Test Now</span>
                        <span x-show="sendingTest">Sending...</span>
                    </button>
                </div>
            </div>
  </div>

<style>
.wysiwyg-btn {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 4px 6px; border-radius: 8px; color: #4b5563;
    transition: background 0.15s, color 0.15s; min-width: 28px; height: 28px;
}
.wysiwyg-btn:hover { background: #f3f4f6; color: #ea580c; }
#campaign-editor h1 { font-size: 1.5em; font-weight: 800; margin: .5em 0; }
#campaign-editor h2 { font-size: 1.25em; font-weight: 700; margin: .4em 0; }
#campaign-editor p  { margin: .4em 0; }
#campaign-editor ul { list-style: disc; padding-left: 1.5em; }
#campaign-editor ol { list-style: decimal; padding-left: 1.5em; }
</style>

<script>
function campaign_exec(cmd, value) {
    const editor = document.getElementById('campaign-editor');
    if (editor) editor.focus();
    document.execCommand(cmd, false, value || null);
    campaign_sync();
}
function campaign_sync() {
    const editor = document.getElementById('campaign-editor');
    const input = document.getElementById('campaign-body-input');
    if (editor && input) {
        input.value = editor.innerHTML;
    }
}
function campaign_sync_from_source(html) {
    const editor = document.getElementById('campaign-editor');
    const input = document.getElementById('campaign-body-input');
    if (editor) editor.innerHTML = html;
    if (input) input.value = html;
}
function campaign_toggle_source(btn) {
    const editor = document.getElementById('campaign-editor');
    const source = document.getElementById('campaign-editor-source');
    if (!editor || !source) return;
    const isSource = !source.classList.contains('hidden');
    if (isSource) {
        editor.innerHTML = source.value;
        source.classList.add('hidden');
        editor.classList.remove('hidden');
        if (btn) btn.classList.remove('bg-purple-100');
    } else {
        source.value = editor.innerHTML;
        editor.classList.add('hidden');
        source.classList.remove('hidden');
        if (btn) btn.classList.add('bg-purple-100');
    }
}
function campaign_insert_link() {
    const url = prompt('Enter Destination URL:', 'https://');
    if (url) campaign_exec('createLink', url);
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('campaign-create-form');
    if (form) {
        form.addEventListener('submit', function() {
            campaign_sync();
        });
    }
});

window.campaignCreator = function() {
    return {
        type: 'email',
        campaignName: '',
        subject: '',
        activeTemplate: 'launch',
        audienceStatus: '',
        audienceSource: '',
        previewCount: '...',
        plainBody: '',
        showPreviewModal: false,
        showTestModal: false,
        previewMode: 'desktop',
        testRecipient: '{{ Auth::user()->email }}',
        sendingTest: false,
        testStatusMessage: '',
        testStatusType: 'success',

        init() {
            this.$watch('type', () => this.updatePreview());
            this.updatePreview();
            this.$nextTick(() => {
                this.loadTemplate('launch');
            });
        },

        loadTemplate(key) {
            this.activeTemplate = key;
            this.type = 'email';
            const company = '{{ \App\Models\CompanySetting::getCached()?->company_name ?? "RICAF Nigeria Limited" }}';
            const phone = '{{ \App\Models\CompanySetting::getCached()?->phone ?? "+234 800 RICAF CRM" }}';

            let tpl = '';
            if (key === 'launch') {
                this.campaignName = '🚀 New Estate Groundbreaking Launch - Special Investor Allotment';
                this.subject = '🌟 Announcement: RICAF Signature Gardens Launch & 15% Early Investor Discount';
                tpl = `
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #1e293b; line-height: 1.6;">
    <div style="background: linear-gradient(135deg, #F37021 0%, #ea580c 100%); padding: 30px 20px; text-align: center; border-radius: 16px 16px 0 0;">
        <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">${company}</h1>
        <p style="color: #ffedd5; margin: 6px 0 0 0; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Exclusive New Estate Launch</p>
    </div>
    <div style="padding: 30px 25px; background: #ffffff; border: 1px solid #f1f5f9; border-top: none;">
        <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-top: 0;">Dear @{{name}},</h2>
        <p style="font-size: 14px; color: #475569;">We are excited to announce our newest residential &amp; commercial development — <strong>RICAF Signature Gardens</strong>, strategically situated in the fastest-growing investment corridor.</p>
        
        <div style="background: #fff7ed; border-left: 4px solid #F37021; padding: 15px; border-radius: 0 12px 12px 0; margin: 20px 0;">
            <p style="margin: 0; font-size: 14px; font-weight: bold; color: #9a3412;">🎉 Early Bird Special: 15% Launch Discount</p>
            <p style="margin: 5px 0 0 0; font-size: 13px; color: #c2410c;">Initial Deposit: <strong>20% only</strong> with flexible 12-month installment spread.</p>
        </div>

        <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 10px;">Why Invest in this Estate?</h3>
        <ul style="padding-left: 20px; font-size: 13px; color: #475569; margin: 0 0 25px 0;">
            <li style="margin-bottom: 8px;">100% Dry Land with Verified Certificate of Occupancy (C of O).</li>
            <li style="margin-bottom: 8px;">Paved Access Roads, 24/7 Solar Street Lighting &amp; Perimeter Security.</li>
            <li style="margin-bottom: 8px;">Projected 40% capital appreciation over the next 18 months.</li>
        </ul>

        <div style="text-align: center; margin: 30px 0 20px 0;">
            <a href="tel:${phone}" style="background: #F37021; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; font-weight: bold; font-size: 14px; display: inline-block; box-shadow: 0 4px 12px rgba(243, 112, 33, 0.3);">📅 Book Free Site Inspection</a>
        </div>
    </div>
    <div style="background: #f8fafc; padding: 20px; text-align: center; font-size: 11px; color: #94a3b8; border-radius: 0 0 16px 16px; border: 1px solid #f1f5f9; border-top: none;">
        <p style="margin: 0;">${company} • Luxury Real Estate &amp; Developments</p>
        <p style="margin: 4px 0 0 0;">Need assistance? Call ${phone} or reply directly to this email.</p>
    </div>
</div>`;
            } else if (key === 'digest') {
                this.campaignName = '📰 Monthly Real Estate Market Digest & Property Deals';
                this.subject = '📈 Nigeria Property Investor Digest: Market Insights & Available Allocations';
                tpl = `
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #1e293b; line-height: 1.6;">
    <div style="background: linear-gradient(135deg, #F37021 0%, #ea580c 100%); padding: 30px 20px; text-align: center; border-radius: 16px 16px 0 0;">
        <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">${company}</h1>
        <p style="color: #ffedd5; margin: 6px 0 0 0; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Monthly Real Estate Intelligence &amp; Opportunities</p>
    </div>
    <div style="padding: 30px 25px; background: #ffffff; border: 1px solid #f1f5f9; border-top: none;">
        <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 0;">Market Insight for @{{name}}</h2>
        <p style="font-size: 13px; color: #475569;">With infrastructural developments accelerating in key hubs, land banking and prime off-plan residential units continue to outpace inflation.</p>
        
        <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-top: 25px;">Featured Available Listings</h3>
        
        <div style="border: 1px solid #fed7aa; background: #fffaf5; border-radius: 12px; padding: 15px; margin: 15px 0;">
            <span style="background: #ffedd5; color: #c2410c; font-size: 11px; font-weight: bold; padding: 3px 8px; border-radius: 6px;">Hot Listing</span>
            <h4 style="margin: 8px 0 4px 0; font-size: 15px; color: #0f172a;">Prime 500sqm Residential Plot</h4>
            <p style="margin: 0; font-size: 13px; color: #64748b;">Title: Governor's Consent • Flexible 6-month payment plan.</p>
        </div>

        <div style="text-align: center; margin: 25px 0 10px 0;">
            <a href="tel:${phone}" style="background: #F37021; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; font-weight: bold; font-size: 13px; display: inline-block; box-shadow: 0 4px 12px rgba(243, 112, 33, 0.3);">Speak to an Investment Advisor</a>
        </div>
    </div>
    <div style="background: #f8fafc; padding: 15px; text-align: center; font-size: 11px; color: #94a3b8; border-radius: 0 0 16px 16px; border: 1px solid #f1f5f9; border-top: none;">
        <p style="margin: 0;">${company} • Real Estate Portfolio Management</p>
        <p style="margin: 4px 0 0 0;">Need assistance? Call ${phone} or reply directly to this email.</p>
    </div>
</div>`;
            } else if (key === 'promo') {
                this.campaignName = '🏷️ Flash Promo - Limited Plots at 15% Price Slash';
                this.subject = '🔥 72-Hour Flash Sale: Own Prime Land with Zero Development Fees';
                tpl = `
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #1e293b; line-height: 1.6;">
    <div style="background: linear-gradient(135deg, #F37021 0%, #ea580c 100%); padding: 30px 20px; text-align: center; border-radius: 16px 16px 0 0;">
        <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">${company}</h1>
        <p style="color: #ffedd5; margin: 6px 0 0 0; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">⚡ 72-Hour Flash Sale &amp; Exclusive Price Reductions</p>
    </div>
    <div style="padding: 30px 25px; background: #ffffff; border: 1px solid #f1f5f9; border-top: none;">
        <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 0;">Hello @{{name}},</h2>
        <p style="font-size: 13px; color: #475569;">For the next 72 hours, ${company} is offering an unprecedented discount on our flagship estates with <strong>instant deed allocation upon 30% downpayment</strong>.</p>
        
        <div style="background: #fff7ed; border: 1px dashed #F37021; border-radius: 12px; padding: 15px; text-align: center; margin: 20px 0;">
            <p style="margin: 0; font-size: 12px; color: #9a3412; text-transform: uppercase; font-weight: bold;">Promo Code</p>
            <p style="margin: 5px 0; font-size: 22px; font-weight: 900; color: #ea580c; letter-spacing: 2px;">RICAF-PROMO15</p>
            <p style="margin: 0; font-size: 11px; color: #c2410c;">Mention this promo code when speaking to our sales manager.</p>
        </div>

        <div style="text-align: center; margin: 25px 0 10px 0;">
            <a href="https://wa.me/${phone.replace(/[^0-9]/g, '')}?text=Hello,%20I%20am%20interested%20in%20the%20RICAF%20Flash%20Promo" style="background: #22c55e; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; font-weight: bold; font-size: 13px; display: inline-block; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);">💬 Claim on WhatsApp</a>
        </div>
    </div>
    <div style="background: #f8fafc; padding: 15px; text-align: center; font-size: 11px; color: #94a3b8; border-radius: 0 0 16px 16px; border: 1px solid #f1f5f9; border-top: none;">
        <p style="margin: 0;">${company} • Luxury Real Estate &amp; Developments</p>
        <p style="margin: 4px 0 0 0;">Need assistance? Call ${phone} or reply directly to this email.</p>
    </div>
</div>`;
            } else if (key === 'progress') {
                this.campaignName = '🏗️ Site Construction & Milestone Progress Report';
                this.subject = '📸 Construction Progress Update: Road Grading & Infrastructure on Schedule';
                tpl = `
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #1e293b; line-height: 1.6;">
    <div style="background: linear-gradient(135deg, #F37021 0%, #ea580c 100%); padding: 30px 20px; text-align: center; border-radius: 16px 16px 0 0;">
        <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">${company}</h1>
        <p style="color: #ffedd5; margin: 6px 0 0 0; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Development Milestone Update</p>
    </div>
    <div style="padding: 30px 25px; background: #ffffff; border: 1px solid #f1f5f9; border-top: none;">
        <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 0;">Dear Valued Buyer (@{{name}}),</h2>
        <p style="font-size: 13px; color: #475569;">We are delighted to share the latest on-site milestone report for our ongoing projects. Quality control and delivery timelines remain our highest priority.</p>
        
        <div style="background: #fff7ed; border-radius: 12px; padding: 15px; margin: 20px 0; border: 1px solid #fed7aa;">
            <p style="margin: 0 0 5px 0; font-size: 13px; font-weight: bold; color: #0f172a;">Site Progress: <strong>75% Completed</strong></p>
            <div style="width: 100%; background: #fed7aa; height: 10px; border-radius: 5px; overflow: hidden;">
                <div style="background: #F37021; width: 75%; height: 100%;"></div>
            </div>
            <p style="margin: 10px 0 0 0; font-size: 12px; color: #9a3412;">Perimeter fencing and drainage systems finalized. Electrification underway.</p>
        </div>

        <p style="font-size: 13px; color: #475569;">You can also access your real-time payment schedule and property documents anytime on your Client Portal.</p>
        
        <div style="text-align: center; margin: 25px 0 10px 0;">
            <a href="tel:${phone}" style="background: #F37021; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; font-weight: bold; font-size: 13px; display: inline-block; box-shadow: 0 4px 12px rgba(243, 112, 33, 0.3);">Contact Project Engineer</a>
        </div>
    </div>
    <div style="background: #f8fafc; padding: 15px; text-align: center; font-size: 11px; color: #94a3b8; border-radius: 0 0 16px 16px; border: 1px solid #f1f5f9; border-top: none;">
        <p style="margin: 0;">${company} • Construction &amp; Development Department</p>
        <p style="margin: 4px 0 0 0;">Need assistance? Call ${phone} or reply directly to this email.</p>
    </div>
</div>`;
            }

            this.$nextTick(() => {
                const editor = document.getElementById('campaign-editor');
                const source = document.getElementById('campaign-editor-source');
                const bodyInput = document.getElementById('campaign-body-input');

                if (editor) editor.innerHTML = tpl;
                if (source) source.value = tpl;
                if (bodyInput) bodyInput.value = tpl;
                
                campaign_sync();

                const editorWrap = document.getElementById('campaign-editor-wrap');
                if (editorWrap && window.innerWidth < 768) {
                    editorWrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        },

        insertCampaignToken(token) {
            if (this.type === 'email') {
                const editor = document.getElementById('campaign-editor');
                if (editor) {
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
                }
                campaign_sync();
            } else {
                this.plainBody += token;
            }
        },

        renderPreviewContent() {
            const bodyInput = document.getElementById('campaign-body-input');
            const editor = document.getElementById('campaign-editor');
            const html = (bodyInput ? bodyInput.value : '') || (editor ? editor.innerHTML : '');
            return html
                .replace(/@{{name}}/g, 'Alhaji Ibrahim Musa')
                .replace(/@{{email}}/g, 'ibrahim@example.com')
                .replace(/@{{phone}}/g, '+234 803 123 4567')
                .replace(/@{{property_name}}/g, 'RICAF Signature Court')
                .replace(/@{{property_price}}/g, '₦185,000,000');
        },

        async dispatchTestEmail() {
            this.sendingTest = true;
            this.testStatusMessage = '';
            campaign_sync();

            try {
                const bodyInput = document.getElementById('campaign-body-input');
                const res = await fetch('{{ route("campaigns.send-test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        test_email: this.testRecipient,
                        subject: this.subject || 'RICAF Newsletter Preview',
                        body: bodyInput ? bodyInput.value : ''
                    })
                });
                const data = await res.json();
                this.testStatusType = data.success ? 'success' : 'error';
                this.testStatusMessage = data.message;
            } catch (err) {
                this.testStatusType = 'error';
                this.testStatusMessage = 'Error connecting to mail dispatcher: ' + err.message;
            } finally {
                this.sendingTest = false;
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
            .catch(() => { this.previewCount = '0'; });
        }
    };
};
</script>
@endsection
