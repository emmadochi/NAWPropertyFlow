<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'NAW PropertyFlow CRM') }} — The Complete Operating System for Nigerian Real Estate Developers & Brokerages</title>
    
    <!-- Primary Meta Tags -->
    <meta name="title" content="NAW PropertyFlow CRM — The Complete Operating System for Nigerian Real Estate Developers & Brokerages">
    <meta name="description" content="Automate plot allocations, milestone installment tracking, multi-tier realtor commissions, diaspora investor portals, legal document generation, and payroll. Built specifically for Nigerian Real Estate Developers.">
    <meta name="keywords" content="Real Estate CRM Nigeria, Abuja Real Estate Software, Plot Allocation Management, Realtor Commission Tracker, Diaspora Property Portal, Nigerian Payroll for Developers, Real Estate Affiliate Program Nigeria">

    <!-- Open Graph / Facebook / LinkedIn -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.nawpropertyflow.com.ng">
    <meta property="og:title" content="NAW PropertyFlow CRM — The Operating Engine for Nigerian Real Estate">
    <meta property="og:description" content="Eliminate spreadsheet leakages, track milestone installments, automate realtor commissions, and build 100% diaspora investor trust.">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://www.nawpropertyflow.com.ng">
    <meta property="twitter:title" content="NAW PropertyFlow CRM — Nigerian Real Estate ERP">
    <meta property="twitter:description" content="Automate plot allocations, milestone installments, realtor commissions, and documents for Nigerian Developers.">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏢</text></svg>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['"Space Grotesk"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fffcf5',
                            100: '#fff5e0',
                            200: '#ffe6b3',
                            300: '#ffd080',
                            400: '#ffb54d',
                            500: '#D4AF37', // Luxury Real Estate Gold
                            600: '#B89325',
                            700: '#94751A',
                            800: '#70570F',
                            900: '#4D3B0A'
                        },
                        navy: {
                            800: '#0B2545',
                            900: '#06152B',
                            950: '#030B17',
                        },
                        dark: {
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    },
                    animation: {
                        'blob': 'blob 8s infinite',
                        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js Component -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pageApp', () => ({
                mobileMenuOpen: false,
                scrolled: false,
                demoModalOpen: false,
                demoPackage: 'Abuja Enterprise Edition',
                activeTab: 'all',
                clientName: '',
                clientCompany: '',
                clientPhone: '',
                clientEmail: '',

                openDemoModal(pkg = 'Abuja Enterprise Edition') {
                    this.demoPackage = pkg;
                    this.demoModalOpen = true;
                    this.mobileMenuOpen = false;
                },

                submitDemoForm() {
                    const isAffiliate = this.demoPackage.includes('Partner') || this.demoPackage.includes('Affiliate');
                    const msg = [
                        isAffiliate ? '🌟 New Affiliate / Channel Partner Registration' : '🏢 New Executive Demo Request — NAW PropertyFlow',
                        '',
                        '👤 Full Name: ' + this.clientName,
                        '🏢 Company / Agency / Firm: ' + this.clientCompany,
                        '📞 Direct Phone: ' + this.clientPhone,
                        '✉️ Email: ' + this.clientEmail,
                        '🎯 Selected Program / Plan: ' + this.demoPackage
                    ].join('\n');

                    const url = 'https://wa.me/2348101358139?text=' + encodeURIComponent(msg);
                    window.open(url, '_blank');

                    this.demoModalOpen = false;
                    this.clientName = '';
                    this.clientCompany = '';
                    this.clientPhone = '';
                    this.clientEmail = '';
                }
            }));
        });
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        h1, h2, h3, h4, .font-display { font-family: 'Space Grotesk', sans-serif; }
        .glass-panel {
            background: rgba(11, 37, 69, 0.45);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        .text-gradient-gold {
            background: linear-gradient(135deg, #FFFFFF 0%, #D4AF37 50%, #F59E0B 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="bg-navy-950 text-slate-200 antialiased selection:bg-brand-500 selection:text-navy-950" x-data="pageApp()" @scroll.window="scrolled = (window.pageYOffset > 20)">

    <!-- Background Atmospheric Glows -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] left-[-10%] w-[550px] h-[550px] bg-brand-500/15 rounded-full mix-blend-screen filter blur-[120px] animate-blob"></div>
        <div class="absolute top-[25%] right-[-10%] w-[500px] h-[500px] bg-blue-600/15 rounded-full mix-blend-screen filter blur-[120px] animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-[-15%] left-[25%] w-[600px] h-[600px] bg-emerald-600/10 rounded-full mix-blend-screen filter blur-[140px] animate-blob animation-delay-4000"></div>
    </div>

    <!-- Navigation Header -->
    <nav class="fixed w-full z-50 transition-all duration-300" :class="{'glass-panel py-3 shadow-2xl shadow-black/60': scrolled, 'bg-transparent py-5': !scrolled}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 via-brand-500 to-amber-300 flex items-center justify-center text-navy-950 font-bold shadow-lg shadow-brand-500/25 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6 text-navy-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="font-extrabold text-xl sm:text-2xl tracking-tight text-white font-display">NAW <span class="text-brand-500">PropertyFlow</span></span>
                        <span class="hidden sm:inline-block text-[9px] uppercase tracking-widest bg-brand-500/20 text-brand-400 font-bold px-2 py-0.5 rounded-full ml-2 border border-brand-500/30">Abuja Edition</span>
                    </div>
                </a>

                <!-- Desktop Menu Links -->
                <div class="hidden lg:flex items-center space-x-6">
                    <a href="#features" class="text-xs font-semibold uppercase tracking-wider text-slate-300 hover:text-brand-400 transition-colors">Core Modules</a>
                    <a href="#solutions" class="text-xs font-semibold uppercase tracking-wider text-slate-300 hover:text-brand-400 transition-colors">Why It Matters</a>
                    <a href="#affiliates" class="text-xs font-bold uppercase tracking-wider text-amber-300 hover:text-brand-400 transition-colors flex items-center gap-1">
                        <span>Partner & Earn (15%)</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    </a>
                    <a href="#prospectus" class="text-xs font-semibold uppercase tracking-wider text-slate-300 hover:text-brand-400 transition-colors">PDF Downloads</a>
                    <a href="#pricing" class="text-xs font-semibold uppercase tracking-wider text-slate-300 hover:text-brand-400 transition-colors">Pilot Plans</a>
                    <a href="#contact" class="text-xs font-semibold uppercase tracking-wider text-slate-300 hover:text-brand-400 transition-colors">Contact</a>
                </div>

                <!-- Action / Contact Buttons -->
                <div class="hidden md:flex items-center space-x-3">
                    <a href="https://demo.nawpropertyflow.com.ng" class="px-4 py-2 rounded-full border border-brand-500/40 bg-brand-500/10 hover:bg-brand-500/20 text-brand-400 font-bold text-xs flex items-center gap-1.5 transition-all">
                        <span>Launch Live Demo</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                    @auth('system_admin')
                        <a href="{{ route('system.dashboard') }}" class="text-xs font-bold text-navy-950 bg-white hover:bg-slate-200 px-4 py-2 rounded-full transition-all">System Dashboard</a>
                    @else
                        <a href="{{ route('system.login') }}" class="text-xs font-bold text-slate-300 hover:text-white px-3 py-2">System Login</a>
                        <a href="#" @click.prevent="openDemoModal('General Walkthrough')" class="px-5 py-2.5 rounded-full bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-amber-400 text-navy-950 font-extrabold text-xs tracking-wide uppercase shadow-lg shadow-brand-500/25 transition-all transform hover:-translate-y-0.5">
                            Book VIP Walkthrough
                        </a>
                    @endauth
                </div>

                <!-- Mobile menu toggle button -->
                <div class="lg:hidden flex items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-slate-300 hover:text-white focus:outline-none p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div x-show="mobileMenuOpen" x-transition x-cloak class="lg:hidden glass-panel border-t border-white/10 absolute w-full left-0 top-full shadow-2xl">
            <div class="px-5 pt-3 pb-6 space-y-2 flex flex-col">
                <a href="#features" @click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-200 hover:bg-white/5">Core Modules</a>
                <a href="#solutions" @click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-200 hover:bg-white/5">Why It Matters</a>
                <a href="#affiliates" @click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-bold text-amber-300 hover:bg-white/5">Partner & Earn 15%</a>
                <a href="#prospectus" @click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-200 hover:bg-white/5">PDF Documentation</a>
                <a href="#pricing" @click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-200 hover:bg-white/5">Pilot Packages</a>
                <a href="#contact" @click="mobileMenuOpen = false" class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-200 hover:bg-white/5">Contact Info</a>
                <div class="pt-4 mt-2 border-t border-white/10 flex flex-col space-y-3">
                    <a href="tel:+2348101358139" class="text-center py-2.5 text-sm font-bold text-brand-400">Call: +234 810 135 8139</a>
                    @auth('system_admin')
                        <a href="{{ route('system.dashboard') }}" class="block text-center px-5 py-3 rounded-xl bg-white text-navy-950 font-bold text-sm">System Dashboard</a>
                    @else
                        <a href="{{ route('system.login') }}" class="block text-center px-5 py-3 rounded-xl bg-white/5 text-white font-bold text-sm">System Login</a>
                        <a href="#" @click.prevent="openDemoModal('Mobile Demo Request')" class="block text-center px-5 py-3 rounded-xl bg-brand-500 text-navy-950 font-extrabold text-sm shadow-lg shadow-brand-500/25 uppercase">Book a Free Demo</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="relative z-10 pt-28 pb-16 sm:pt-36 sm:pb-24 overflow-hidden">
        
        <!-- =========================================================================
             HERO SECTION
        ========================================================================== -->
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center animate-fade-in-up">
            <div class="inline-flex items-center space-x-2 px-4 py-1.5 rounded-full border border-brand-500/40 bg-brand-500/10 text-brand-400 text-xs font-bold tracking-wide uppercase mb-8">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
                </span>
                <span>The Operating System for Abuja & Nigerian Real Estate</span>
            </div>
            
            <h1 class="mx-auto max-w-5xl font-extrabold text-4xl sm:text-6xl lg:text-7xl tracking-tight text-white mb-6 font-display leading-[1.1]">
                Automate Plot Allocations, Enforce Milestone Collections & <span class="text-gradient-gold">Eliminate Commission Disputes.</span>
            </h1>
            
            <p class="mx-auto max-w-3xl text-base sm:text-xl text-slate-300 mb-10 leading-relaxed">
                Replaces messy spreadsheets with an enterprise ERP tailored for Nigerian developers: smart plot inventory, automated 3–24 month installment tracking, 1-click legal documents, diaspora investor portals, and native Naira payroll.
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mb-12">
                <a href="https://demo.nawpropertyflow.com.ng" class="w-full sm:w-auto px-8 py-4 rounded-full bg-gradient-to-r from-brand-600 via-brand-500 to-amber-400 hover:from-brand-500 hover:to-amber-300 text-navy-950 font-extrabold text-base shadow-xl shadow-brand-500/30 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2 uppercase tracking-wide">
                    <span>🚀 Launch Interactive CRM Demo</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
                <a href="#" @click.prevent="openDemoModal('Executive Demo')" class="w-full sm:w-auto px-8 py-4 rounded-full glass-panel hover:bg-white/10 text-white font-bold text-base transition-all flex items-center justify-center gap-2 border border-white/15">
                    <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Schedule Executive Meeting</span>
                </a>
                <a href="{{ asset('docs/NAW_PropertyFlow_Abuja_Prospectus.pdf') }}" target="_blank" class="w-full sm:w-auto px-6 py-4 rounded-full glass-panel hover:bg-white/10 text-slate-300 hover:text-white font-semibold text-sm transition-all flex items-center justify-center gap-2 border border-white/10">
                    <svg class="w-4 h-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>PDF Prospectus</span>
                </a>
            </div>

            <!-- Key Metric Counters Banner -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-5xl mx-auto mb-16">
                <div class="glass-card p-5 rounded-2xl border border-white/5 text-center">
                    <div class="text-2xl sm:text-3xl font-extrabold text-brand-400 font-display">100%</div>
                    <div class="text-xs text-slate-400 font-medium mt-1">Double-Allocation Prevention</div>
                </div>
                <div class="glass-card p-5 rounded-2xl border border-white/5 text-center">
                    <div class="text-2xl sm:text-3xl font-extrabold text-emerald-400 font-display">0%</div>
                    <div class="text-xs text-slate-400 font-medium mt-1">Realtor Split Calculation Errors</div>
                </div>
                <div class="glass-card p-5 rounded-2xl border border-white/5 text-center">
                    <div class="text-2xl sm:text-3xl font-extrabold text-blue-400 font-display">35%+</div>
                    <div class="text-xs text-slate-400 font-medium mt-1">Faster Milestone Debt Recovery</div>
                </div>
                <div class="glass-card p-5 rounded-2xl border border-white/5 text-center">
                    <div class="text-2xl sm:text-3xl font-extrabold text-amber-300 font-display">3 Sec</div>
                    <div class="text-xs text-slate-400 font-medium mt-1">1-Click Legal Document Factory</div>
                </div>
            </div>

            <!-- High-Impact Dashboard Representation -->
            <div class="relative mx-auto max-w-5xl rounded-2xl shadow-2xl shadow-black/90 ring-1 ring-white/10 glass-panel p-2.5">
                <div class="bg-navy-950 rounded-xl overflow-hidden flex flex-col h-[400px] sm:h-[580px] relative border border-white/10">
                    <!-- Browser Chrome Header -->
                    <div class="bg-navy-900 border-b border-white/10 px-4 py-3 flex items-center justify-between">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-rose-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        </div>
                        <div class="bg-navy-950 rounded-md px-4 py-1 text-[11px] text-slate-400 font-mono flex items-center gap-1.5 border border-white/5">
                            <svg class="w-3 h-3 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                            portal.nawpropertyflow.com.ng/dashboard
                        </div>
                        <span class="text-[10px] text-emerald-400 font-bold bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">Active Session</span>
                    </div>

                    <!-- Inner Mockup Dashboard Screen -->
                    <div class="flex flex-1 overflow-hidden bg-[#071120] text-sm text-slate-300">
                        <!-- Sidebar -->
                        <div class="w-52 hidden sm:flex flex-col gap-1.5 p-4 border-r border-white/5 bg-navy-950/60 text-xs">
                            <div class="font-bold text-white mb-3 flex items-center gap-2 text-sm font-display"><span class="w-2 h-2 rounded-full bg-brand-500"></span> NAW ERP Core</div>
                            <div class="py-2 px-3 bg-brand-500/20 text-brand-400 rounded-lg font-bold flex items-center gap-2 border border-brand-500/30">📊 Executive Dashboard</div>
                            <div class="py-2 px-3 hover:bg-white/5 rounded-lg flex items-center gap-2">📍 Plots & Inventory</div>
                            <div class="py-2 px-3 hover:bg-white/5 rounded-lg flex items-center gap-2">💳 Milestone Billing</div>
                            <div class="py-2 px-3 hover:bg-white/5 rounded-lg flex items-center gap-2">🤝 Realtor Network</div>
                            <div class="py-2 px-3 hover:bg-white/5 rounded-lg flex items-center gap-2">📄 Document Factory</div>
                            <div class="py-2 px-3 hover:bg-white/5 rounded-lg flex items-center gap-2">🌍 Diaspora Portal</div>
                            <div class="py-2 px-3 hover:bg-white/5 rounded-lg flex items-center gap-2">💼 HR & Payroll</div>
                            <div class="mt-auto pt-3 border-t border-white/5 text-slate-500 text-[10px]">Abuja Regional Instance</div>
                        </div>
                        
                        <!-- Main Content View -->
                        <div class="flex-1 flex flex-col p-4 sm:p-6 gap-5 overflow-hidden text-left">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-lg font-bold text-white font-display">Active Estates Overview — Abuja (FCT)</h3>
                                    <p class="text-[11px] text-slate-400">Guzape Hills Estate • Katampe Extension • Airport Road Land Scheme</p>
                                </div>
                                <div class="px-3.5 py-1.5 bg-brand-500 hover:bg-brand-600 text-navy-950 font-extrabold rounded-lg text-xs flex items-center gap-1.5 shadow-md">
                                    + Generate Statement (SOA)
                                </div>
                            </div>
                            
                            <!-- KPI Cards -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="glass-panel rounded-xl p-3.5 border border-white/10">
                                    <div class="text-slate-400 text-[11px] font-semibold">Total Collections (YTD)</div>
                                    <div class="text-xl font-extrabold text-white font-display">₦842,500,000</div>
                                    <div class="text-[10px] text-emerald-400 mt-1">↑ +28.4% on-time milestone payments</div>
                                </div>
                                <div class="glass-panel rounded-xl p-3.5 border border-white/10">
                                    <div class="text-slate-400 text-[11px] font-semibold">Plot Allocation Status</div>
                                    <div class="text-xl font-extrabold text-brand-400 font-display">184 / 220 Sold</div>
                                    <div class="text-[10px] text-amber-300 mt-1">36 Available • 0 Double Allocations</div>
                                </div>
                                <div class="glass-panel rounded-xl p-3.5 border border-white/10">
                                    <div class="text-slate-400 text-[11px] font-semibold">Realtor Overrides Pending</div>
                                    <div class="text-xl font-extrabold text-blue-400 font-display">₦18.4M (Vouchers Ready)</div>
                                    <div class="text-[10px] text-blue-300 mt-1">42 Active Agents Verified</div>
                                </div>
                            </div>
                            
                            <!-- Transactions table -->
                            <div class="flex-1 glass-panel rounded-xl border border-white/5 flex flex-col overflow-hidden text-xs">
                                <div class="p-3 bg-white/5 flex justify-between items-center font-bold text-white">
                                    <span>Recent Milestone & Allocation Records</span>
                                    <span class="text-brand-400 text-[11px]">Real-Time Sync</span>
                                </div>
                                <div class="divide-y divide-white/5 overflow-y-auto">
                                    <div class="p-3 flex justify-between items-center hover:bg-white/[0.02]">
                                        <div>
                                            <div class="font-bold text-white">Engr. Tunde Adeleke (Diaspora — UK)</div>
                                            <div class="text-slate-400 text-[10px]">Guzape Hills • Plot 412 (500sqm) • Milestone 3/6 Paid</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-mono font-bold text-emerald-400">₦15,000,000</div>
                                            <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 rounded text-[9px] font-bold">POP Verified</span>
                                        </div>
                                    </div>
                                    <div class="p-3 flex justify-between items-center hover:bg-white/[0.02]">
                                        <div>
                                            <div class="font-bold text-white">Hajiya Fatima Aliyu (Abuja)</div>
                                            <div class="text-slate-400 text-[10px]">Katampe Extension • Unit 4B Penthouse • Deed of Assignment Issued</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-mono font-bold text-emerald-400">₦85,000,000</div>
                                            <span class="px-2 py-0.5 bg-brand-500/20 text-brand-300 rounded text-[9px] font-bold">100% Outright</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             WHY IT MATTERS / THE SPREADSHEET PROBLEM
        ========================================================================== -->
        <div id="solutions" class="py-24 relative z-10 border-t border-white/5 bg-navy-950/80">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-brand-500 font-bold tracking-widest uppercase text-xs mb-2">The Operational Bottleneck</h2>
                    <p class="text-3xl sm:text-5xl font-extrabold text-white font-display mb-4">Why Excel Spreadsheets Fail Abuja Real Estate Developers</p>
                    <p class="text-slate-400 text-sm sm:text-base">Operating a multi-million or billion-naira real estate development company on spreadsheets creates hidden cash leakages, commission fights, and missed installment collections.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                    <!-- The Spreadsheet Disaster -->
                    <div class="glass-panel p-8 rounded-2xl border-t-2 border-red-500/60 bg-red-950/10">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center text-red-400 mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white font-display">The Spreadsheet Risk</h3>
                        </div>
                        <ul class="space-y-4 text-slate-300 text-xs sm:text-sm">
                            <li class="flex items-start gap-3">
                                <span class="text-red-400 font-bold mt-0.5">✗</span>
                                <span><strong>Plot Double-Allocations:</strong> Multiple sales reps booking the same plot across disconnected Excel files.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-red-400 font-bold mt-0.5">✗</span>
                                <span><strong>Missed Milestone Installments:</strong> Buyers on 6–24 month payment plans forget due dates; cash flow stalls.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-red-400 font-bold mt-0.5">✗</span>
                                <span><strong>Realtor Commission Fights:</strong> Disputed splits and manual overrides cause top brokers to abandon your estate.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-red-400 font-bold mt-0.5">✗</span>
                                <span><strong>Diaspora Skepticism:</strong> Overseas investors fear lack of transparent, real-time payment statements.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- The NAW PropertyFlow Advantage -->
                    <div class="glass-panel p-8 rounded-2xl border-t-2 border-brand-500 bg-brand-900/10 shadow-xl shadow-brand-500/10">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-xl bg-brand-500/20 flex items-center justify-center text-brand-400 mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-white font-display">The NAW PropertyFlow ERP</h3>
                        </div>
                        <ul class="space-y-4 text-slate-300 text-xs sm:text-sm">
                            <li class="flex items-start gap-3">
                                <span class="text-brand-400 font-bold mt-0.5">✓</span>
                                <span><strong>Automated Plot Locking:</strong> 100% zero double-allocation risk with real-time status and prototype tracking.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-brand-400 font-bold mt-0.5">✓</span>
                                <span><strong>Automated Milestone Billing:</strong> Scheduled SMS/Email due alerts and 1-click Statement of Account (SOA).</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-brand-400 font-bold mt-0.5">✓</span>
                                <span><strong>Zero-Dispute Realtor Ledger:</strong> Multi-tier broker overrides and instant automated Commission Vouchers.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-brand-400 font-bold mt-0.5">✓</span>
                                <span><strong>Diaspora Investor Dashboard:</strong> 24/7 client web access for verified receipts and construction milestone photos.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             ALL 12 CORE MODULES SHOWCASE
        ========================================================================== -->
        <div id="features" class="py-24 sm:py-32 relative z-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full border border-brand-500/30 bg-brand-500/10 text-brand-400 text-xs font-bold uppercase tracking-wider mb-3">
                        Complete 12-Module Suite
                    </div>
                    <h2 class="text-3xl sm:text-5xl font-extrabold text-white font-display mb-4">Every Feature Built for Real Estate Dominance</h2>
                    <p class="text-slate-400 text-sm sm:text-base">Everything your Managing Director, Sales Executives, Legal Counsel, Accountants, and HR team need in one unified platform.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- Module 1 -->
                    <div class="glass-panel p-6 sm:p-7 rounded-2xl border border-white/5 hover:border-brand-500/40 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-brand-500/15 flex items-center justify-center text-brand-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div class="text-[10px] font-bold text-brand-400 uppercase tracking-widest mb-1">Module 01</div>
                        <h3 class="text-lg font-bold text-white font-display mb-2">Plot & Layout Allocation</h3>
                        <p class="text-slate-400 text-xs leading-relaxed mb-4">Manage multi-estate plot sizing (250sqm, 500sqm, 1,000sqm), prototype units, layout phases, live plot locking, and zero double-allocation protection.</p>
                        <div class="text-[11px] font-semibold text-slate-300 flex items-center gap-1.5"><span class="text-emerald-400">✓</span> Real-Time Layout Availability</div>
                    </div>

                    <!-- Module 2 -->
                    <div class="glass-panel p-6 sm:p-7 rounded-2xl border border-white/5 hover:border-emerald-500/40 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/15 flex items-center justify-center text-emerald-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-1">Module 02</div>
                        <h3 class="text-lg font-bold text-white font-display mb-2">Installment & Milestone Billing</h3>
                        <p class="text-slate-400 text-xs leading-relaxed mb-4">Automate Outright, 3-Month, 6-Month, 12-Month, and 24-Month payment plans. Generates automated SMS due alerts and 1-click Statements of Account (SOA).</p>
                        <div class="text-[11px] font-semibold text-slate-300 flex items-center gap-1.5"><span class="text-emerald-400">✓</span> 1-Click PDF Payment Receipts</div>
                    </div>

                    <!-- Module 3 -->
                    <div class="glass-panel p-6 sm:p-7 rounded-2xl border border-white/5 hover:border-blue-500/40 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-blue-500/15 flex items-center justify-center text-blue-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1">Module 03</div>
                        <h3 class="text-lg font-bold text-white font-display mb-2">Realtor & Commission Hub</h3>
                        <p class="text-slate-400 text-xs leading-relaxed mb-4">Multi-tier agency structures, broker team hierarchies, automated deal split calculations upon verified deposit, and instant Commission Vouchers.</p>
                        <div class="text-[11px] font-semibold text-slate-300 flex items-center gap-1.5"><span class="text-emerald-400">✓</span> Zero Commission Disputes</div>
                    </div>

                    <!-- Module 4 -->
                    <div class="glass-panel p-6 sm:p-7 rounded-2xl border border-white/5 hover:border-purple-500/40 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-purple-500/15 flex items-center justify-center text-purple-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="text-[10px] font-bold text-purple-400 uppercase tracking-widest mb-1">Module 04</div>
                        <h3 class="text-lg font-bold text-white font-display mb-2">Lead & Inspection Logistics</h3>
                        <p class="text-slate-400 text-xs leading-relaxed mb-4">Visual Kanban pipeline, CSV lead importer, site inspection booking logistics, driver/vehicle assignments, prospect SMS directions, and daily follow-up queues.</p>
                        <div class="text-[11px] font-semibold text-slate-300 flex items-center gap-1.5"><span class="text-emerald-400">✓</span> Automated Follow-up Calendar</div>
                    </div>

                    <!-- Module 5 -->
                    <div class="glass-panel p-6 sm:p-7 rounded-2xl border border-white/5 hover:border-rose-500/40 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-rose-500/15 flex items-center justify-center text-rose-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        </div>
                        <div class="text-[10px] font-bold text-rose-400 uppercase tracking-widest mb-1">Module 05</div>
                        <h3 class="text-lg font-bold text-white font-display mb-2">Drip Nurture & Broadcasts</h3>
                        <p class="text-slate-400 text-xs leading-relaxed mb-4">Multi-step timed SMS/Email drip sequences (Day 1, 4, 10, 21) that nurture cold prospects on autopilot, plus mass promotional broadcast campaigns.</p>
                        <div class="text-[11px] font-semibold text-slate-300 flex items-center gap-1.5"><span class="text-emerald-400">✓</span> 30–90 Day Sales Closing Autopilot</div>
                    </div>

                    <!-- Module 6 -->
                    <div class="glass-panel p-6 sm:p-7 rounded-2xl border border-white/5 hover:border-brand-500/40 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-brand-500/15 flex items-center justify-center text-brand-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="text-[10px] font-bold text-brand-400 uppercase tracking-widest mb-1">Module 06</div>
                        <h3 class="text-lg font-bold text-white font-display mb-2">Legal Document Factory</h3>
                        <p class="text-slate-400 text-xs leading-relaxed mb-4">Generate Offer Letters, Contracts of Sale, Deeds of Assignment, and Provisional Allocation Letters in 3 seconds with dynamic tags, QR codes, and watermarks.</p>
                        <div class="text-[11px] font-semibold text-slate-300 flex items-center gap-1.5"><span class="text-emerald-400">✓</span> Error-Free 1-Click Legal PDFs</div>
                    </div>

                    <!-- Module 7 -->
                    <div class="glass-panel p-6 sm:p-7 rounded-2xl border border-white/5 hover:border-blue-500/40 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-blue-500/15 flex items-center justify-center text-blue-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1">Module 07</div>
                        <h3 class="text-lg font-bold text-white font-display mb-2">Diaspora Investor Portal</h3>
                        <p class="text-slate-400 text-xs leading-relaxed mb-4">Dedicated self-service web portal where UK/US/Canada buyers log in to view verified payment histories, upload bank POP receipts, and view live site progress.</p>
                        <div class="text-[11px] font-semibold text-slate-300 flex items-center gap-1.5"><span class="text-emerald-400">✓</span> 100% Diaspora Trust & Transparency</div>
                    </div>

                    <!-- Module 8 -->
                    <div class="glass-panel p-6 sm:p-7 rounded-2xl border border-white/5 hover:border-amber-500/40 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-amber-500/15 flex items-center justify-center text-amber-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        </div>
                        <div class="text-[10px] font-bold text-amber-400 uppercase tracking-widest mb-1">Module 08</div>
                        <h3 class="text-lg font-bold text-white font-display mb-2">Civil Milestones & Cloud Drive</h3>
                        <p class="text-slate-400 text-xs leading-relaxed mb-4">Track engineering completion % (fencing, drainage, grading) with virtual 3D tour links. Built-in alternative to Google Drive for storing C of O and AGIS plans.</p>
                        <div class="text-[11px] font-semibold text-slate-300 flex items-center gap-1.5"><span class="text-emerald-400">✓</span> Native Cloud Document Drive</div>
                    </div>

                    <!-- Module 9 -->
                    <div class="glass-panel p-6 sm:p-7 rounded-2xl border border-white/5 hover:border-emerald-500/40 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/15 flex items-center justify-center text-emerald-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-1">Module 09</div>
                        <h3 class="text-lg font-bold text-white font-display mb-2">Expense Accounting & P&L</h3>
                        <p class="text-slate-400 text-xs leading-relaxed mb-4">Log site development costs (Infrastructure, Survey, AGIS regularization, Marketing) with vendor receipts. Real-time net cash flow and project profitability metrics.</p>
                        <div class="text-[11px] font-semibold text-slate-300 flex items-center gap-1.5"><span class="text-emerald-400">✓</span> Project Net Margin Clarity</div>
                    </div>

                    <!-- Module 10 -->
                    <div class="glass-panel p-6 sm:p-7 rounded-2xl border border-white/5 hover:border-purple-500/40 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-purple-500/15 flex items-center justify-center text-purple-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div class="text-[10px] font-bold text-purple-400 uppercase tracking-widest mb-1">Module 10</div>
                        <h3 class="text-lg font-bold text-white font-display mb-2">HR, Staff Leaves & Payroll</h3>
                        <p class="text-slate-400 text-xs leading-relaxed mb-4">Salary structures (Basic, Housing, Transport, Allowances), monthly payroll batches, Bank Bulk CSV export (Zenith, GTB, Access), and leave approvals.</p>
                        <div class="text-[11px] font-semibold text-slate-300 flex items-center gap-1.5"><span class="text-emerald-400">✓</span> 1-Click PDF Payslip Generator</div>
                    </div>

                    <!-- Module 11 -->
                    <div class="glass-panel p-6 sm:p-7 rounded-2xl border border-white/5 hover:border-blue-500/40 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-blue-500/15 flex items-center justify-center text-blue-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1">Module 11</div>
                        <h3 class="text-lg font-bold text-white font-display mb-2">Department Targets & KPIs</h3>
                        <p class="text-slate-400 text-xs leading-relaxed mb-4">Set monthly revenue quotas and activity targets. Staff submit daily metric submissions against KPIs, producing automated executive variance reports.</p>
                        <div class="text-[11px] font-semibold text-slate-300 flex items-center gap-1.5"><span class="text-emerald-400">✓</span> Daily Staff Sales Accountability</div>
                    </div>

                    <!-- Module 12 -->
                    <div class="glass-panel p-6 sm:p-7 rounded-2xl border border-white/5 hover:border-brand-500/40 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-brand-500/15 flex items-center justify-center text-brand-400 mb-5 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <div class="text-[10px] font-bold text-brand-400 uppercase tracking-widest mb-1">Module 12</div>
                        <h3 class="text-lg font-bold text-white font-display mb-2">Multi-Branch & Role Security</h3>
                        <p class="text-slate-400 text-xs leading-relaxed mb-4">Multi-office branching (Abuja, Lagos, Port Harcourt), granular permission matrix (MD, Accounts, Legal, Sales), immutable audit logs, and global Ctrl+K search.</p>
                        <div class="text-[11px] font-semibold text-slate-300 flex items-center gap-1.5"><span class="text-emerald-400">✓</span> Enterprise-Grade Data Governance</div>
                    </div>

                </div>
            </div>
        </div>

        <!-- =========================================================================
             AFFILIATE & PARTNER PROGRAM SECTION (NEW HIGH-CONVERTING MODULE)
        ========================================================================== -->
        <div id="affiliates" class="py-24 relative z-10 border-t border-white/10 bg-gradient-to-b from-navy-950 via-navy-900 to-navy-950">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <div class="inline-flex items-center space-x-2 px-3.5 py-1 rounded-full border border-amber-400/40 bg-amber-400/10 text-amber-300 text-xs font-bold uppercase tracking-wider mb-3">
                        💰 Partner & Earn 15% Monthly
                    </div>
                    <h2 class="text-3xl sm:text-5xl font-extrabold text-white font-display mb-4">Turn Your Real Estate Network into Recurring Income</h2>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                        Are you a Realtor, Real Estate Lawyer, IT Consultant, or Business Development Lead in Abuja? Recommend NAW PropertyFlow to estate developers and earn <strong>15% monthly recurring commission</strong> for a full year.
                    </p>
                </div>

                <!-- Affiliate Earning Models Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl mx-auto mb-14">
                    
                    <!-- 15% Recurring -->
                    <div class="glass-panel p-8 rounded-3xl border-2 border-brand-500/50 bg-gradient-to-b from-brand-900/30 to-navy-950 flex flex-col justify-between">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-widest text-brand-400 mb-2">Model A (Recommended)</div>
                            <h3 class="text-xl font-bold text-white font-display mb-2">15% Monthly Recurring</h3>
                            <p class="text-slate-300 text-xs leading-relaxed mb-6">Earn 15% of the client's subscription every month for 12 full months. Pure passive income.</p>
                            <div class="bg-navy-950/80 p-4 rounded-xl border border-white/5 space-y-2 text-xs">
                                <div class="flex justify-between text-slate-400"><span>1 Developer (Growth Plan):</span> <strong class="text-brand-300">₦29,250 / mo</strong></div>
                                <div class="flex justify-between text-slate-400"><span>5 Developers Referred:</span> <strong class="text-emerald-400 font-bold">₦146,250 / mo</strong></div>
                                <div class="flex justify-between text-slate-400"><span>10 Developers Referred:</span> <strong class="text-emerald-400 font-bold">₦292,500 / mo</strong></div>
                            </div>
                        </div>
                        <div class="text-[11px] text-emerald-400 font-bold mt-6">✓ Guaranteed 48-Hour Bank Transfer</div>
                    </div>

                    <!-- 20% Instant Annual Cash -->
                    <div class="glass-panel p-8 rounded-3xl border border-white/10 flex flex-col justify-between">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-widest text-blue-400 mb-2">Model B (Instant Cash)</div>
                            <h3 class="text-xl font-bold text-white font-display mb-2">20% Upfront Annual Bounty</h3>
                            <p class="text-slate-300 text-xs leading-relaxed mb-6">When your client pays upfront for an annual contract, take home an instant 20% lump sum.</p>
                            <div class="bg-navy-950/80 p-4 rounded-xl border border-white/5 space-y-2 text-xs">
                                <div class="flex justify-between text-slate-400"><span>Starter Annual (₦900k):</span> <strong class="text-white">₦180,000 Payout</strong></div>
                                <div class="flex justify-between text-slate-400"><span>Growth Annual (₦2.34M):</span> <strong class="text-emerald-400 font-bold">₦468,000 Payout</strong></div>
                                <div class="flex justify-between text-slate-400"><span>Enterprise Annual (₦5M):</span> <strong class="text-emerald-400 font-bold">₦1,000,000 Payout</strong></div>
                            </div>
                        </div>
                        <div class="text-[11px] text-blue-300 font-bold mt-6">✓ High-Ticket Lump-Sum Bounty</div>
                    </div>

                    <!-- 3-Step Hand-off -->
                    <div class="glass-panel p-8 rounded-3xl border border-white/10 flex flex-col justify-between">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-widest text-purple-400 mb-2">How It Works</div>
                            <h3 class="text-xl font-bold text-white font-display mb-2">Zero Technical Hassle</h3>
                            <p class="text-slate-300 text-xs leading-relaxed mb-4">You do not do technical demos. You simply introduce our team on WhatsApp.</p>
                            <ol class="space-y-3 text-xs text-slate-300 list-decimal list-inside">
                                <li><strong>Share PDF Prospectus:</strong> Send via WhatsApp or Email.</li>
                                <li><strong>Connect on 3-Way Chat:</strong> Introduce the developer MD.</li>
                                <li><strong>We Demo & Close:</strong> We migrate their data & pay you within 48 hours.</li>
                            </ol>
                        </div>
                        <a href="{{ asset('docs/NAW_PropertyFlow_Affiliate_Guide.pdf') }}" target="_blank" class="mt-6 py-2.5 px-4 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs text-center border border-white/15 transition-all flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Download Partner Guide (PDF)</span>
                        </a>
                    </div>

                </div>

                <!-- Affiliate CTA Box -->
                <div class="max-w-4xl mx-auto glass-card p-8 rounded-3xl border border-brand-500/40 bg-gradient-to-r from-navy-950 via-navy-900 to-navy-950 text-center">
                    <h3 class="text-2xl font-bold text-white font-display mb-2">Ready to Start Earning with NAW PropertyFlow?</h3>
                    <p class="text-slate-300 text-xs sm:text-sm max-w-2xl mx-auto mb-6">Register as an authorized channel partner today. Get immediate access to your affiliate code, high-resolution pitch decks, and dedicated partner manager support.</p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="#" @click.prevent="openDemoModal('Affiliate / Channel Partner Registration')" class="px-8 py-3.5 rounded-full bg-gradient-to-r from-brand-600 to-brand-500 hover:from-brand-500 hover:to-amber-300 text-navy-950 font-extrabold text-xs uppercase tracking-wider shadow-lg shadow-brand-500/25 transition-all">
                            Register as an Affiliate Partner
                        </a>
                        <a href="{{ asset('docs/NAW_PropertyFlow_Affiliate_Guide.pdf') }}" target="_blank" class="px-6 py-3.5 rounded-full bg-white/10 hover:bg-white/20 text-white font-bold text-xs uppercase tracking-wider border border-white/15 transition-all flex items-center justify-center gap-2">
                            <span>Read Complete Partner Blueprint (PDF)</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             EXECUTIVE PDF DOCUMENTATION CENTER
        ========================================================================== -->
        <div id="prospectus" class="py-20 relative z-10 border-y border-white/5 bg-navy-900/60">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-5xl mx-auto glass-panel p-8 sm:p-12 rounded-3xl border border-brand-500/30 bg-gradient-to-r from-navy-950 via-navy-900 to-navy-950 shadow-2xl">
                    <div class="flex flex-col lg:flex-row justify-between items-center gap-8">
                        <div class="lg:max-w-xl">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/20 text-brand-400 text-xs font-bold uppercase tracking-wider mb-4 border border-brand-500/30">
                                📑 Official Documentation Pack
                            </div>
                            <h2 class="text-2xl sm:text-4xl font-extrabold text-white font-display mb-3">Download Executive Briefing & Product Profile</h2>
                            <p class="text-slate-300 text-xs sm:text-sm leading-relaxed mb-6">
                                Access our official 7-page commercial prospectus, 8-page master product specification guide, and partner onboarding blueprint.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ asset('docs/NAW_PropertyFlow_Abuja_Prospectus.pdf') }}" target="_blank" class="px-5 py-3 rounded-full bg-brand-500 hover:bg-brand-600 text-navy-950 font-extrabold text-xs uppercase tracking-wider flex items-center gap-2 shadow-lg shadow-brand-500/20 transition-all">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Executive Prospectus (PDF)</span>
                                </a>
                                <a href="{{ asset('docs/NAW_PropertyFlow_Product_Profile.pdf') }}" target="_blank" class="px-5 py-3 rounded-full bg-white/10 hover:bg-white/20 text-white font-bold text-xs uppercase tracking-wider flex items-center gap-2 border border-white/15 transition-all">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Product Profile (PDF)</span>
                                </a>
                                <a href="{{ asset('docs/NAW_PropertyFlow_Affiliate_Guide.pdf') }}" target="_blank" class="px-5 py-3 rounded-full bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 font-bold text-xs uppercase tracking-wider flex items-center gap-2 border border-amber-500/40 transition-all">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Partner Guide (PDF)</span>
                                </a>
                            </div>
                        </div>

                        <!-- PDF Preview Badge Box -->
                        <div class="bg-navy-950/80 p-6 rounded-2xl border border-white/10 text-center w-full lg:w-72 flex flex-col items-center">
                            <div class="w-16 h-20 bg-brand-500/20 border border-brand-500/40 rounded-lg flex flex-col items-center justify-center text-brand-400 mb-3 shadow-lg">
                                <span class="font-display font-black text-xl">PDF</span>
                                <span class="text-[9px] font-bold text-slate-400">A4 Format</span>
                            </div>
                            <div class="text-white font-bold text-sm">Abuja Developer Edition</div>
                            <div class="text-slate-400 text-xs mt-1">Verified Spec 2026.1</div>
                            <div class="text-emerald-400 text-[10px] font-bold mt-2">✓ Free Data Migration Included</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             PRICING & PILOT PACKAGES
        ========================================================================== -->
        <div id="pricing" class="py-24 relative z-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-brand-500 font-bold tracking-widest uppercase text-xs mb-2">Transparent Local Pricing</h2>
                    <p class="text-3xl sm:text-5xl font-extrabold text-white font-display mb-4">Predictable Naira Investment</p>
                    <p class="text-slate-400 text-sm sm:text-base">No volatile US Dollar exchange rates. Full data ownership, zero vendor lock-in, and turnkey onboarding.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto items-stretch">
                    
                    <!-- Starter Package -->
                    <div class="glass-panel rounded-3xl p-8 border border-white/10 relative flex flex-col">
                        <h3 class="text-2xl font-bold text-white font-display mb-2">Starter Agency</h3>
                        <p class="text-slate-400 text-xs mb-6">For boutique real estate agencies & emerging brokers.</p>
                        <div class="mb-6">
                            <span class="text-3xl sm:text-4xl font-extrabold text-white font-display">₦75,000</span>
                            <span class="text-slate-400 text-xs">/month</span>
                        </div>
                        <a href="#" @click.prevent="openDemoModal('Starter Package (₦75k)')" class="block w-full py-3 px-4 bg-white/10 hover:bg-white/20 text-white font-bold text-center rounded-xl transition-colors mb-6 text-xs uppercase tracking-wider">Book Starter Pilot</a>
                        
                        <ul class="space-y-3.5 text-xs text-slate-300 flex-1">
                            <li class="flex items-center"><svg class="w-4 h-4 text-emerald-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> Up to 2 Active Estates / Projects</li>
                            <li class="flex items-center"><svg class="w-4 h-4 text-emerald-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> Lead Pipeline & Inspection Booking</li>
                            <li class="flex items-center"><svg class="w-4 h-4 text-emerald-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> Milestone Installments & Receipts</li>
                            <li class="flex items-center"><svg class="w-4 h-4 text-emerald-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> Realtor Commission Tracking</li>
                            <li class="flex items-center opacity-40"><svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Automated Document Factory</li>
                            <li class="flex items-center opacity-40"><svg class="w-4 h-4 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Native Staff Payroll Engine</li>
                        </ul>
                    </div>

                    <!-- Developer Growth (Popular) -->
                    <div class="glass-panel rounded-3xl p-8 border-2 border-brand-500 relative flex flex-col transform md:-translate-y-3 bg-gradient-to-b from-brand-900/40 to-navy-950 shadow-2xl shadow-brand-500/20">
                        <div class="absolute top-0 inset-x-0 transform -translate-y-1/2 flex justify-center">
                            <span class="bg-brand-500 text-navy-950 text-[10px] font-black px-3.5 py-1 uppercase tracking-wider rounded-full shadow-lg">Most Popular for Developers</span>
                        </div>
                        <h3 class="text-2xl font-bold text-white font-display mb-2">Growth Developer</h3>
                        <p class="text-slate-400 text-xs mb-6">For active estate developers scaling in Abuja.</p>
                        <div class="mb-6">
                            <span class="text-3xl sm:text-4xl font-extrabold text-white font-display">₦195,000</span>
                            <span class="text-slate-400 text-xs">/month</span>
                        </div>
                        <a href="#" @click.prevent="openDemoModal('Growth Developer (₦195k)')" class="block w-full py-3.5 px-4 bg-brand-500 hover:bg-brand-600 shadow-lg shadow-brand-500/30 text-navy-950 font-extrabold text-center rounded-xl transition-all mb-6 text-xs uppercase tracking-wider">Book Growth Pilot</a>
                        
                        <ul class="space-y-3.5 text-xs text-slate-300 flex-1">
                            <li class="flex items-center"><svg class="w-4 h-4 text-emerald-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> Up to 10 Active Estates & Layouts</li>
                            <li class="flex items-center"><svg class="w-4 h-4 text-emerald-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> <strong class="text-white">Smart Plot Allocation Engine</strong></li>
                            <li class="flex items-center"><svg class="w-4 h-4 text-emerald-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> <strong class="text-white">1-Click Legal Document Factory</strong></li>
                            <li class="flex items-center"><svg class="w-4 h-4 text-emerald-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> <strong class="text-white">Diaspora Investor Portal</strong></li>
                            <li class="flex items-center"><svg class="w-4 h-4 text-emerald-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> Multi-Tier Realtor Split Ledgers</li>
                            <li class="flex items-center"><svg class="w-4 h-4 text-emerald-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> Free Spreadsheet Data Migration</li>
                        </ul>
                    </div>

                    <!-- Enterprise Suite -->
                    <div class="glass-panel rounded-3xl p-8 border border-white/10 relative flex flex-col">
                        <h3 class="text-2xl font-bold text-white font-display mb-2">Abuja Enterprise</h3>
                        <p class="text-slate-400 text-xs mb-6">Complete ERP for high-volume developers & conglomerates.</p>
                        <div class="mb-6">
                            <span class="text-3xl sm:text-4xl font-extrabold text-white font-display">Custom</span>
                            <span class="text-slate-400 text-xs">/annual</span>
                        </div>
                        <a href="#" @click.prevent="openDemoModal('Enterprise Suite (Custom)')" class="block w-full py-3 px-4 bg-white/10 hover:bg-white/20 text-white font-bold text-center rounded-xl transition-colors mb-6 text-xs uppercase tracking-wider">Request Custom Proposal</a>
                        
                        <ul class="space-y-3.5 text-xs text-slate-300 flex-1">
                            <li class="flex items-center"><svg class="w-4 h-4 text-emerald-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> Unlimited Estates & Plots</li>
                            <li class="flex items-center"><svg class="w-4 h-4 text-brand-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> <strong class="text-brand-300">All 12 Modules Included</strong></li>
                            <li class="flex items-center"><svg class="w-4 h-4 text-brand-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> Full HR, Leave & Bank Payroll</li>
                            <li class="flex items-center"><svg class="w-4 h-4 text-brand-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> Multi-Branch Operations Support</li>
                            <li class="flex items-center"><svg class="w-4 h-4 text-emerald-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> On-Site Staff & Realtor Training</li>
                            <li class="flex items-center"><svg class="w-4 h-4 text-emerald-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> Dedicated Account Manager in Abuja</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             CONTACT & REGIONAL FOOTER SECTION
        ========================================================================== -->
        <div id="contact" class="py-20 relative z-10 border-t border-white/10 bg-navy-950">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
                    
                    <!-- Direct Line -->
                    <div class="glass-card p-6 rounded-2xl border border-white/5 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-brand-500/20 text-brand-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Direct Call / WhatsApp</div>
                            <a href="tel:+2348101358139" class="text-sm font-bold text-white hover:text-brand-400 transition-colors">+234 810 135 8139</a>
                            <div class="text-[11px] text-slate-400 mt-0.5">Mon – Sat, 8am – 6pm WAT</div>
                        </div>
                    </div>

                    <!-- Email Support -->
                    <div class="glass-card p-6 rounded-2xl border border-white/5 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Executive Inquiries</div>
                            <a href="mailto:sales@nawpropertyflow.com.ng" class="text-sm font-bold text-white hover:text-brand-400 transition-colors">sales@nawpropertyflow.com.ng</a>
                            <div class="text-[11px] text-slate-400 mt-0.5">Enterprise proposals & demos</div>
                        </div>
                    </div>

                    <!-- Regional Office -->
                    <div class="glass-card p-6 rounded-2xl border border-white/5 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">FCT Regional Hub</div>
                            <div class="text-sm font-bold text-white">Wuse II / CBD, Abuja</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Federal Capital Territory, Nigeria</div>
                        </div>
                    </div>

                    <!-- Official Web Portal -->
                    <div class="glass-card p-6 rounded-2xl border border-white/5 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Official Web Portal</div>
                            <a href="https://www.nawpropertyflow.com.ng" class="text-sm font-bold text-white hover:text-brand-400 transition-colors">www.nawpropertyflow.com.ng</a>
                            <div class="text-[11px] text-emerald-400 mt-0.5">256-bit SSL Cloud Protected</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </main>

    <!-- FOOTER -->
    <footer class="border-t border-white/5 bg-navy-950 py-10 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center space-x-3">
                <span class="font-extrabold text-xl tracking-tight text-white font-display">NAW <span class="text-brand-500">PropertyFlow</span></span>
                <span class="text-slate-500 text-xs">|</span>
                <span class="text-slate-400 text-xs">Abuja Enterprise Edition</span>
            </div>
            <div class="text-slate-500 text-xs md:text-right">
                <p>&copy; {{ date('Y') }} NAW PropertyFlow Technologies Ltd. All rights reserved.</p>
                <p class="mt-1 text-slate-400">Powering Nigeria's Next-Generation Real Estate Enterprises.</p>
            </div>
        </div>
    </footer>

    <!-- Book Demo / Partner Registration Modal -->
    <div x-show="demoModalOpen" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-cloak>
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-navy-950/85 backdrop-blur-md" @click="demoModalOpen = false"></div>
        
        <!-- Modal Container -->
        <div class="relative w-full max-w-lg glass-panel rounded-3xl p-8 border border-brand-500/30 shadow-2xl z-10 bg-navy-900/95 text-left">
            <!-- Close Button -->
            <button @click="demoModalOpen = false" class="absolute top-6 right-6 text-slate-400 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <div class="mb-6">
                <div class="text-brand-400 text-[10px] font-bold uppercase tracking-widest mb-1">Direct Registration & Walkthrough</div>
                <h3 class="text-2xl font-extrabold text-white font-display mb-1.5" x-text="demoPackage.includes('Partner') || demoPackage.includes('Affiliate') ? 'Affiliate Partner Registration' : 'Schedule a Product Demo'">Schedule a Product Demo</h3>
                <p class="text-slate-400 text-xs leading-relaxed">Enter your contact details to connect directly with our Abuja enterprise onboarding team on WhatsApp.</p>
            </div>
            
            <form @submit.prevent="submitDemoForm()">
                <div class="space-y-4">
                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Your Full Name / Title</label>
                        <input type="text" id="name" x-model="clientName" required
                               class="w-full bg-navy-950/70 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all"
                               placeholder="e.g. Chief / Barr. / Engr. Chukwudi Eze">
                    </div>
                    
                    <!-- Company / Agency Name -->
                    <div>
                        <label for="company" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Real Estate Firm / Law Chambers / Agency</label>
                        <input type="text" id="company" x-model="clientCompany" required
                               class="w-full bg-navy-950/70 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all"
                               placeholder="e.g. Prime Abuja Estates Ltd. / Independent Broker">
                    </div>
                    
                    <!-- Phone Number -->
                    <div>
                        <label for="phone" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Direct Phone / WhatsApp Line</label>
                        <input type="tel" id="phone" x-model="clientPhone" required
                               class="w-full bg-navy-950/70 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all"
                               placeholder="e.g. +234 810 135 8139">
                    </div>
                    
                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Official Email Address</label>
                        <input type="email" id="email" x-model="clientEmail" required
                               class="w-full bg-navy-950/70 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all"
                               placeholder="e.g. partner@company.com">
                    </div>
                    
                    <!-- Selected Package / Program -->
                    <div>
                        <label for="package" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Select Program / Tier</label>
                        <select id="package" x-model="demoPackage"
                                class="w-full bg-navy-950 border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all">
                            <option value="Affiliate / Channel Partner Registration" class="bg-navy-950">🌟 Affiliate Partner Program (Earn 15% Monthly)</option>
                            <option value="Abuja Enterprise Edition" class="bg-navy-950">🏢 Developer Demo: Enterprise Edition (Full Suite)</option>
                            <option value="Growth Developer (₦195k/mo)" class="bg-navy-950">🏢 Developer Demo: Growth Plan (₦195,000/mo)</option>
                            <option value="Starter Agency (₦75k/mo)" class="bg-navy-950">🏢 Agency Demo: Starter Plan (₦75,000/mo)</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="submit"
                            class="w-full py-3.5 rounded-xl bg-gradient-to-r from-brand-600 via-brand-500 to-amber-400 hover:from-brand-500 hover:to-amber-300 text-navy-950 font-extrabold text-sm shadow-lg shadow-brand-500/25 transition-all flex items-center justify-center gap-2 uppercase tracking-wide">
                        <span>Submit & Connect on WhatsApp</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
