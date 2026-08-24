<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NAW PropertyFlow CRM — The Complete Operating System for Nigerian Real Estate Developers & Brokerages</title>
    
    <!-- Primary Meta Tags -->
    <meta name="title" content="NAW PropertyFlow CRM — The Operating System for Nigerian Real Estate Developers">
    <meta name="description" content="Automate plot allocations, milestone installments, multi-tier realtor commissions, diaspora investor portals, legal document generation, and payroll. Built specifically for Nigerian Real Estate Developers.">
    <meta name="keywords" content="Real Estate CRM Nigeria, Abuja Real Estate Software, Plot Allocation Management, Realtor Commission Tracker, Diaspora Property Portal, Nigerian Payroll for Developers">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.nawpropertyflow.com.ng">
    <meta property="og:title" content="NAW PropertyFlow CRM — The Operating Engine for Nigerian Real Estate">
    <meta property="og:description" content="Eliminate spreadsheet leakages, track milestone installments, automate realtor commissions, and build 100% diaspora investor trust.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Pure CSS Framework -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #030B17;
            color: #e2e8f0;
            overflow-x: hidden;
        }
        h1, h2, h3, h4, .font-display {
            font-family: 'Space Grotesk', sans-serif;
        }
        .text-gradient-gold {
            background: linear-gradient(135deg, #FFFFFF 0%, #FEA500 50%, #F59E0B 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .text-gradient-emerald {
            background: linear-gradient(135deg, #FFFFFF 0%, #34D399 50%, #10B981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .glass-nav {
            background: rgba(3, 11, 23, 0.90);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card {
            background: rgba(11, 37, 69, 0.45);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            border-color: rgba(254, 165, 0, 0.35);
            transform: translateY(-2px);
        }
        .glow-sphere-1 {
            position: absolute;
            top: -150px;
            left: 50%;
            transform: translateX(-50%);
            width: 750px;
            height: 500px;
            background: radial-gradient(circle, rgba(254, 165, 0, 0.15) 0%, rgba(11, 37, 69, 0.05) 60%, transparent 80%);
            pointer-events: none;
            z-index: 0;
        }
        .glow-sphere-2 {
            position: absolute;
            top: 40%;
            right: -100px;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.12) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }
        .btn-primary-demo {
            background: linear-gradient(135deg, #FEA500 0%, #D4AF37 100%);
            color: #030B17;
            font-weight: 800;
            box-shadow: 0 10px 25px -5px rgba(254, 165, 0, 0.4);
            transition: all 0.25s ease;
        }
        .btn-primary-demo:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(254, 165, 0, 0.6);
            background: linear-gradient(135deg, #FFB733 0%, #E5BE48 100%);
        }
        .interactive-plot {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .interactive-plot:hover {
            transform: scale(1.05);
            z-index: 20;
        }
    </style>
</head>
<body x-data="landingApp()">

    <!-- Ambient Glows -->
    <div class="glow-sphere-1"></div>
    <div class="glow-sphere-2"></div>

    <!-- =========================================================================
         MODERN STREAMLINED NAVIGATION HEADER
    ========================================================================== -->
    <header class="fixed top-0 left-0 right-0 z-50 glass-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4">
                
                <!-- Brand Logo -->
                <a href="{{ url('/') }}" class="flex items-center space-x-3 group flex-shrink-0">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-300 flex items-center justify-center text-slate-950 font-bold shadow-lg shadow-amber-500/25 group-hover:scale-105 transition-transform flex-shrink-0">
                        <svg class="w-6 h-6 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <div class="font-extrabold text-lg sm:text-xl tracking-tight text-white font-display flex items-center gap-1.5 whitespace-nowrap">
                            <span>NAW</span>
                            <span class="text-amber-400">PropertyFlow</span>
                        </div>
                        <span class="text-[9px] uppercase font-bold tracking-widest text-slate-400 whitespace-nowrap">Enterprise Real Estate ERP</span>
                    </div>
                </a>

                <!-- Desktop Menu Links (Clean, Perfectly Spaced) -->
                <nav class="hidden lg:flex items-center space-x-6 xl:space-x-8">
                    <a href="#features" class="text-xs xl:text-sm font-semibold text-slate-300 hover:text-amber-400 transition-colors whitespace-nowrap">Features</a>
                    <a href="#plot-engine" class="text-xs xl:text-sm font-semibold text-slate-300 hover:text-amber-400 transition-colors whitespace-nowrap">Plot Engine</a>
                    <a href="#roi-calculator" class="text-xs xl:text-sm font-semibold text-slate-300 hover:text-amber-400 transition-colors flex items-center gap-1.5 whitespace-nowrap">
                        <span>ROI Calculator</span>
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">New</span>
                    </a>
                    <a href="#affiliates" class="text-xs xl:text-sm font-semibold text-amber-300 hover:text-amber-400 transition-colors whitespace-nowrap flex items-center gap-1">
                        <span>Partner Program</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    </a>
                    <a href="#pricing" class="text-xs xl:text-sm font-semibold text-slate-300 hover:text-amber-400 transition-colors whitespace-nowrap">Pricing</a>
                    <a href="#contact" class="text-xs xl:text-sm font-semibold text-slate-300 hover:text-amber-400 transition-colors whitespace-nowrap">Contact</a>
                </nav>

                <!-- Right Action Buttons -->
                <div class="hidden md:flex items-center space-x-3 flex-shrink-0">
                    <a href="https://demo.nawpropertyflow.com.ng" class="btn-primary-demo px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 whitespace-nowrap shadow-lg">
                        <span>🚀 Launch Live Demo</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                    <button @click="openDemoModal('VIP Executive Meeting')" class="px-4 py-2.5 rounded-full glass-card hover:bg-white/10 text-xs font-bold text-slate-200 border border-slate-700 whitespace-nowrap cursor-pointer">
                        Book VIP Meeting
                    </button>
                    <a href="{{ route('system.login') }}" class="text-xs font-bold text-slate-400 hover:text-white transition-colors px-2 py-1 whitespace-nowrap">
                        System Login
                    </a>
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="lg:hidden flex items-center space-x-2">
                    <a href="https://demo.nawpropertyflow.com.ng" class="btn-primary-demo px-3 py-1.5 rounded-full text-[11px] font-bold uppercase">
                        Demo
                    </a>
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-slate-400 hover:text-white focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div x-show="mobileMenuOpen" x-transition class="lg:hidden glass-nav border-t border-slate-800 px-6 py-6 space-y-4">
            <a href="#features" @click="mobileMenuOpen = false" class="block text-base font-semibold text-slate-200">Core Features</a>
            <a href="#plot-engine" @click="mobileMenuOpen = false" class="block text-base font-semibold text-slate-200">Plot Engine</a>
            <a href="#roi-calculator" @click="mobileMenuOpen = false" class="block text-base font-semibold text-slate-200">ROI Calculator</a>
            <a href="#affiliates" @click="mobileMenuOpen = false" class="block text-base font-semibold text-amber-300">Partner & Earn 15%</a>
            <a href="#pricing" @click="mobileMenuOpen = false" class="block text-base font-semibold text-slate-200">Pricing</a>
            <a href="#contact" @click="mobileMenuOpen = false" class="block text-base font-semibold text-slate-200">Contact</a>
            <div class="pt-4 border-t border-slate-800 flex flex-col space-y-3">
                <a href="https://demo.nawpropertyflow.com.ng" class="btn-primary-demo text-center py-3 rounded-xl text-sm uppercase">
                    🚀 Launch Interactive Demo
                </a>
                <button @click="openDemoModal('Mobile Demo Request'); mobileMenuOpen = false" class="text-center py-3 rounded-xl glass-card text-white text-xs font-bold uppercase">
                    Book VIP Walkthrough
                </button>
                <a href="tel:+2348101358139" class="text-center text-xs font-bold text-amber-400 py-1">Call: +234 810 135 8139</a>
            </div>
        </div>
    </header>


    <!-- =========================================================================
         HERO SECTION (High-Impact Proptech Showcase)
    ========================================================================== -->
    <section class="relative pt-36 pb-20 md:pt-44 md:pb-28 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            
            <!-- Live Badge -->
            <div class="inline-flex items-center space-x-2 px-4 py-1.5 rounded-full border border-amber-500/30 bg-amber-500/10 text-amber-300 text-xs font-bold uppercase tracking-wider mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>The Operating System for Abuja & Nigerian Real Estate</span>
            </div>

            <!-- Main Headline -->
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-white mb-6 font-display max-w-5xl mx-auto leading-tight">
                Automate Plot Allocations, Enforce Installments & <span class="text-gradient-gold">Eliminate Commission Disputes.</span>
            </h1>

            <p class="text-lg sm:text-xl text-slate-300 max-w-3xl mx-auto mb-10 leading-relaxed font-normal">
                Replace spreadsheet chaos with enterprise precision: real-time estate plot maps, automated 3–24 month milestone collections, 1-click legal deeds, diaspora buyer portals, and native Naira payroll.
            </p>

            <!-- Hero Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16 max-w-md sm:max-w-none mx-auto">
                <a href="https://demo.nawpropertyflow.com.ng" class="w-full sm:w-auto btn-primary-demo px-8 py-4 rounded-full text-sm uppercase tracking-wide flex items-center justify-center gap-2">
                    <span>⚡ Try Interactive Live Demo</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
                <button @click="openDemoModal('Executive Consultation')" class="w-full sm:w-auto px-7 py-4 rounded-full glass-card hover:bg-white/10 text-white font-bold text-sm flex items-center justify-center gap-2 border border-slate-700 cursor-pointer">
                    <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Schedule Executive Meeting</span>
                </button>
                <a href="{{ asset('docs/NAW_PropertyFlow_Abuja_Prospectus.pdf') }}" target="_blank" class="w-full sm:w-auto px-6 py-4 rounded-full glass-card hover:bg-white/10 text-slate-300 hover:text-white font-semibold text-xs transition-all flex items-center justify-center gap-2 border border-slate-800">
                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Download PDF Prospectus</span>
                </a>
            </div>

            <!-- Live KPI Counter Badges -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
                <div class="glass-card p-5 rounded-2xl text-center">
                    <div class="text-3xl font-extrabold text-amber-400 font-display">100%</div>
                    <div class="text-xs text-slate-400 font-medium mt-1">Zero Double Allocations</div>
                </div>
                <div class="glass-card p-5 rounded-2xl text-center">
                    <div class="text-3xl font-extrabold text-emerald-400 font-display">₦0</div>
                    <div class="text-xs text-slate-400 font-medium mt-1">Realtor Split Calculation Errors</div>
                </div>
                <div class="glass-card p-5 rounded-2xl text-center">
                    <div class="text-3xl font-extrabold text-blue-400 font-display">35%+</div>
                    <div class="text-xs text-slate-400 font-medium mt-1">Faster Milestone Debt Recovery</div>
                </div>
                <div class="glass-card p-5 rounded-2xl text-center">
                    <div class="text-3xl font-extrabold text-purple-400 font-display">3 Sec</div>
                    <div class="text-xs text-slate-400 font-medium mt-1">1-Click Legal Document Factory</div>
                </div>
            </div>

        </div>
    </section>


    <!-- =========================================================================
         INTERACTIVE ESTATE PLOT MAP VISUALIZER
    ========================================================================== -->
    <section id="plot-engine" class="py-20 relative bg-slate-950/60 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="text-xs font-extrabold uppercase tracking-widest text-amber-400">Live Inventory Intelligence</span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-white mt-2 font-display">Interactive Plot Allocation Engine</h2>
                <p class="text-sm sm:text-base text-slate-400 mt-3">Click on any plot below to see how NAW PropertyFlow prevents double allocations in real time.</p>
            </div>

            <!-- Plot Simulator Card -->
            <div class="glass-card p-6 sm:p-8 rounded-3xl max-w-4xl mx-auto border border-slate-800">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-6 border-b border-slate-800">
                    <div>
                        <h4 class="font-bold text-lg text-white font-display">Guzape Hills Luxury Scheme — Phase 2 (Abuja)</h4>
                        <p class="text-xs text-slate-400">Total Plots: 18 | Available: 7 | Reserved: 4 | Sold: 7</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-semibold">
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Available</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Reserved</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-500"></span> Sold Out</span>
                    </div>
                </div>

                <!-- 18 Plot Grid Simulation -->
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 mb-6">
                    <template x-for="plot in plots" :key="plot.id">
                        <button @click="selectPlot(plot)" 
                                :class="{
                                    'bg-emerald-950/40 border-emerald-500/60 text-emerald-300': plot.status === 'Available',
                                    'bg-amber-950/40 border-amber-500/60 text-amber-300': plot.status === 'Reserved',
                                    'bg-rose-950/40 border-rose-500/60 text-rose-300 opacity-60': plot.status === 'Sold',
                                    'ring-2 ring-amber-400 scale-105': selectedPlot.id === plot.id
                                }"
                                class="interactive-plot p-3 rounded-xl border text-center font-bold text-xs">
                            <div class="text-sm mb-0.5" x-text="plot.number"></div>
                            <div class="text-[10px] uppercase tracking-wider" x-text="plot.status"></div>
                        </button>
                    </template>
                </div>

                <!-- Selected Plot Details Banner -->
                <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <div class="text-xs text-slate-400">Selected Plot Inspector:</div>
                        <div class="text-base font-bold text-white font-display" x-text="selectedPlot.number + ' — ' + selectedPlot.type + ' (' + selectedPlot.size + ' SQM)'"></div>
                        <div class="text-xs text-amber-400 font-semibold" x-text="'Price: ' + selectedPlot.price + ' | Status: ' + selectedPlot.status"></div>
                    </div>
                    <a href="https://demo.nawpropertyflow.com.ng" class="btn-primary-demo px-6 py-2.5 rounded-xl text-xs uppercase tracking-wider">
                        Inspect in Live CRM →
                    </a>
                </div>
            </div>

        </div>
    </section>


    <!-- =========================================================================
         INTERACTIVE ROI / REVENUE RECOVERY CALCULATOR
    ========================================================================== -->
    <section id="roi-calculator" class="py-20 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="text-xs font-extrabold uppercase tracking-widest text-emerald-400">Financial Value Engine</span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-white mt-2 font-display">Revenue Recovery Calculator</h2>
                <p class="text-sm sm:text-base text-slate-400 mt-3">Estimate how much revenue your brokerage or development firm loses annually to spreadsheet leakages, delayed collections, and commission calculations.</p>
            </div>

            <div class="glass-card p-6 sm:p-10 rounded-3xl max-w-3xl mx-auto border border-slate-800">
                
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-300">Annual Sales Volume (₦ Millions):</label>
                        <span class="text-xl font-extrabold text-amber-400 font-display" x-text="'₦' + formatNumber(annualSales) + ',000,000'"></span>
                    </div>
                    <input type="range" min="50" max="2000" step="50" x-model="annualSales" class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-amber-500">
                    <div class="flex justify-between text-[11px] text-slate-500 mt-1">
                        <span>₦50 Million</span>
                        <span>₦500M</span>
                        <span>₦1 Billion</span>
                        <span>₦2 Billion+</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                    <div class="p-4 rounded-2xl bg-rose-950/20 border border-rose-500/20 text-center">
                        <div class="text-xs text-rose-400 font-semibold">Prevented Leakages</div>
                        <div class="text-2xl font-extrabold text-white font-display mt-1" x-text="'₦' + formatNumber(annualSales * 0.04) + 'M'"></div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Disputes & Double Allocation</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-blue-950/20 border border-blue-500/20 text-center">
                        <div class="text-xs text-blue-400 font-semibold">Faster Cash Inflow</div>
                        <div class="text-2xl font-extrabold text-white font-display mt-1" x-text="'₦' + formatNumber(annualSales * 0.12) + 'M'"></div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Automated Milestones</div>
                    </div>
                    <div class="p-4 rounded-2xl bg-emerald-950/20 border border-emerald-500/20 text-center">
                        <div class="text-xs text-emerald-400 font-semibold">Total Value Recovered</div>
                        <div class="text-2xl font-extrabold text-emerald-400 font-display mt-1" x-text="'₦' + formatNumber(annualSales * 0.16) + 'M'"></div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Per Year with NAW CRM</div>
                    </div>
                </div>

                <div class="text-center">
                    <a href="https://demo.nawpropertyflow.com.ng" class="btn-primary-demo inline-block px-8 py-3.5 rounded-full text-xs uppercase tracking-wider font-extrabold">
                        Experience ROI on Live Demo →
                    </a>
                </div>

            </div>

        </div>
    </section>


    <!-- =========================================================================
         CORE ENTERPRISE MODULES
    ========================================================================== -->
    <section id="features" class="py-20 bg-slate-950/60 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-amber-400">Complete ERP Modules</span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-white mt-2 font-display">Engineered Specifically for Nigerian Real Estate</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="glass-card p-8 rounded-3xl">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-xl mb-6 border border-amber-500/20">📍</div>
                    <h3 class="text-xl font-bold text-white font-display mb-2">Plot & Inventory Manager</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Prevent double allocations. Manage off-plan developments, terrace duplexes, and estate plots with real-time reserve locks and 3D map views.</p>
                </div>

                <div class="glass-card p-8 rounded-3xl">
                    <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-xl mb-6 border border-blue-500/20">💳</div>
                    <h3 class="text-xl font-bold text-white font-display mb-2">Automated Milestone Billing</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Break property payments into 3 to 24-month structured installment plans with automated WhatsApp/SMS due payment alerts.</p>
                </div>

                <div class="glass-card p-8 rounded-3xl">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl mb-6 border border-emerald-500/20">🤝</div>
                    <h3 class="text-xl font-bold text-white font-display mb-2">Multi-Tier Realtor Commissions</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Automated split calculation for external realtors, brokerages, and in-house agents. Eliminates commission disputes instantly.</p>
                </div>

                <div class="glass-card p-8 rounded-3xl">
                    <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-xl mb-6 border border-purple-500/20">📄</div>
                    <h3 class="text-xl font-bold text-white font-display mb-2">1-Click Legal Document Factory</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Generate instant Deeds of Assignment, Provisional Allocation Letters, and Official Receipts pre-filled with client and plot data.</p>
                </div>

                <div class="glass-card p-8 rounded-3xl">
                    <div class="w-12 h-12 rounded-2xl bg-teal-500/10 text-teal-400 flex items-center justify-center text-xl mb-6 border border-teal-500/20">🌍</div>
                    <h3 class="text-xl font-bold text-white font-display mb-2">Diaspora Buyer Portal</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Provide international buyers in the UK, USA, and Canada a dedicated portal to track building construction, verify receipts, and download title deeds.</p>
                </div>

                <div class="glass-card p-8 rounded-3xl">
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-xl mb-6 border border-rose-500/20">💼</div>
                    <h3 class="text-xl font-bold text-white font-display mb-2">Nigerian HR & Naira Payroll</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Integrated employee salary structures, daily work report reviews, leave approvals, and 1-click bank CSV disbursement exports.</p>
                </div>

            </div>

        </div>
    </section>


    <!-- =========================================================================
         AFFILIATE & PARTNER PROGRAM (Earn 15% Monthly)
    ========================================================================== -->
    <section id="affiliates" class="py-20 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="glass-card p-8 sm:p-14 rounded-3xl border border-amber-500/30 relative overflow-hidden bg-gradient-to-b from-navy-950 via-[#0B2545]/60 to-navy-950">
                
                <div class="max-w-3xl">
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold uppercase tracking-wider mb-4">
                        <span>🌟 Channel Partner & Consultant Network</span>
                    </div>
                    <h2 class="text-3xl sm:text-5xl font-extrabold text-white font-display mb-4">
                        Earn <span class="text-gradient-gold">15% Recurring Commission</span> for Every Developer You Introduce
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed mb-8">
                        Are you a real estate consultant, property lawyer, surveyor, or tech integrator in Nigeria? Introduce developers and real estate brokerages to NAW PropertyFlow and earn 15% recurring monthly revenue on every active subscription.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                        <div class="p-4 rounded-2xl bg-navy-950/80 border border-white/5">
                            <div class="text-xl font-bold text-amber-400 font-display">15% Commission</div>
                            <div class="text-xs text-slate-400 mt-1">Paid monthly per active developer</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-navy-950/80 border border-white/5">
                            <div class="text-xl font-bold text-emerald-400 font-display">Zero Upfront Cost</div>
                            <div class="text-xs text-slate-400 mt-1">Free partner marketing materials</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-navy-950/80 border border-white/5">
                            <div class="text-xl font-bold text-blue-400 font-display">Dedicated Account Rep</div>
                            <div class="text-xs text-slate-400 mt-1">Direct technical support for your clients</div>
                        </div>
                    </div>

                    <button @click="openDemoModal('Affiliate / Channel Partner Registration')" class="btn-primary-demo px-8 py-4 rounded-full text-xs font-extrabold uppercase tracking-wide cursor-pointer">
                        Register as Channel Partner →
                    </button>
                </div>

            </div>
        </div>
    </section>


    <!-- =========================================================================
         PILOT PRICING PACKAGES (Monthly / Discounted Annual)
    ========================================================================== -->
    <section id="pricing" class="py-20 bg-slate-950/60 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-10">
                <span class="text-xs font-extrabold uppercase tracking-widest text-amber-400">Transparent & Affordable Pricing</span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-white mt-2 font-display">Plans Built for Every Nigerian Real Estate Team</h2>
                <p class="text-sm sm:text-base text-slate-400 mt-3">Transparent Nigerian Naira billing, dedicated local onboarding in Abuja & Lagos, and automated cloud backups.</p>

                <!-- Billing Cycle Toggle Switch -->
                <div class="mt-8 inline-flex items-center p-1.5 rounded-full bg-slate-900 border border-slate-800">
                    <button @click="billingCycle = 'monthly'" 
                            :class="billingCycle === 'monthly' ? 'bg-amber-500 text-slate-950 font-extrabold shadow-md' : 'text-slate-400 hover:text-white font-semibold'"
                            class="px-5 py-2 rounded-full text-xs transition-all uppercase tracking-wider cursor-pointer">
                        Monthly Billing
                    </button>
                    <button @click="billingCycle = 'annual'" 
                            :class="billingCycle === 'annual' ? 'bg-amber-500 text-slate-950 font-extrabold shadow-md' : 'text-slate-400 hover:text-white font-semibold'"
                            class="px-5 py-2 rounded-full text-xs transition-all uppercase tracking-wider flex items-center gap-1.5 cursor-pointer">
                        <span>Annual Billing</span>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-500 text-slate-950">Save 17% (2 Mo Free)</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto items-stretch">
                
                <!-- 1. Agency Starter (₦20,000/mo or ₦200,000/yr) -->
                <div class="glass-card p-8 rounded-3xl flex flex-col justify-between border border-slate-800">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Agency Starter</span>
                        
                        <div class="mt-3 mb-1">
                            <template x-if="billingCycle === 'monthly'">
                                <div>
                                    <h3 class="text-3xl font-extrabold text-white font-display">₦20,000 <span class="text-xs text-slate-400 font-normal">/ month</span></h3>
                                    <div class="text-[11px] text-slate-400 mt-1">Billed monthly (₦240,000/yr)</div>
                                </div>
                            </template>
                            <template x-if="billingCycle === 'annual'">
                                <div>
                                    <h3 class="text-3xl font-extrabold text-emerald-400 font-display">₦200,000 <span class="text-xs text-slate-400 font-normal">/ year</span></h3>
                                    <div class="text-[11px] text-emerald-400 mt-1 font-semibold">⚡ Save ₦40,000 (Pay for 10 months only)</div>
                                </div>
                            </template>
                        </div>

                        <p class="text-xs text-slate-400 mt-3 mb-6">For emerging real estate brokerages, marketing teams, and sales agencies.</p>
                        
                        <ul class="space-y-3 text-xs text-slate-300 mb-8 border-t border-slate-800/80 pt-6">
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Up to 5 Staff User Accounts</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Unlimited Lead Pipeline CRM</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Multi-Tier Realtor Commission Splits</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Site Inspection Calendar & Follow-Ups</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> WhatsApp Lead Direct Connect</li>
                        </ul>
                    </div>
                    <button @click="openDemoModal(billingCycle === 'annual' ? 'Agency Starter (Annual ₦200k/yr)' : 'Agency Starter (Monthly ₦20k/mo)')" class="w-full py-3 rounded-xl glass-card hover:bg-white/10 text-xs font-bold text-white uppercase border border-slate-700 cursor-pointer">
                        Get Started
                    </button>
                </div>

                <!-- 2. Growth Developer (₦30,000/mo or ₦300,000/yr) - Featured -->
                <div class="glass-card p-8 rounded-3xl flex flex-col justify-between border-2 border-amber-500/60 relative bg-slate-900/95 shadow-2xl">
                    <div class="absolute -top-3.5 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-amber-500 to-amber-400 text-slate-950 text-[10px] font-extrabold uppercase px-4 py-1 rounded-full tracking-widest shadow-md whitespace-nowrap">
                        ⭐ Most Popular in Abuja & Lagos
                    </div>
                    <div>
                        <span class="text-xs font-bold text-amber-400 uppercase tracking-widest">Growth Developer</span>
                        
                        <div class="mt-3 mb-1">
                            <template x-if="billingCycle === 'monthly'">
                                <div>
                                    <h3 class="text-3xl font-extrabold text-white font-display">₦30,000 <span class="text-xs text-slate-400 font-normal">/ month</span></h3>
                                    <div class="text-[11px] text-slate-400 mt-1">Billed monthly (₦360,000/yr)</div>
                                </div>
                            </template>
                            <template x-if="billingCycle === 'annual'">
                                <div>
                                    <h3 class="text-3xl font-extrabold text-amber-400 font-display">₦300,000 <span class="text-xs text-slate-400 font-normal">/ year</span></h3>
                                    <div class="text-[11px] text-emerald-400 mt-1 font-semibold">⚡ Save ₦60,000 (Pay for 10 months only)</div>
                                </div>
                            </template>
                        </div>

                        <p class="text-xs text-slate-300 mt-3 mb-6">For active property developers managing multiple ongoing residential schemes.</p>
                        
                        <ul class="space-y-3 text-xs text-slate-200 mb-8 border-t border-slate-800/80 pt-6">
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold">✓</span> Up to 20 Staff Accounts</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold">✓</span> Interactive Plot Allocation & Reserve Locks</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold">✓</span> Automated 3–24 Mo. Milestone Installment Billing</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold">✓</span> 1-Click Deed of Assignment & Receipt Factory</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold">✓</span> Diaspora Buyer Portal (UK/USA/Canada Tracking)</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold">✓</span> Anti-Double Allocation Safeguard</li>
                        </ul>
                    </div>
                    <button @click="openDemoModal(billingCycle === 'annual' ? 'Growth Developer (Annual ₦300k/yr)' : 'Growth Developer (Monthly ₦30k/mo)')" class="w-full btn-primary-demo py-3.5 rounded-xl text-xs uppercase tracking-wider font-extrabold cursor-pointer">
                        Start Growth Trial
                    </button>
                </div>

                <!-- 3. Full Enterprise (Custom / Annual) -->
                <div class="glass-card p-8 rounded-3xl flex flex-col justify-between border border-slate-800">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Full Enterprise</span>
                        
                        <div class="mt-3 mb-1">
                            <h3 class="text-3xl font-extrabold text-white font-display">Custom <span class="text-xs text-slate-400 font-normal">/ annual</span></h3>
                            <div class="text-[11px] text-slate-400 mt-1">Dedicated cloud or private server setup</div>
                        </div>

                        <p class="text-xs text-slate-400 mt-3 mb-6">Complete enterprise ERP suite with white-labeling, custom features, and on-premise training.</p>
                        
                        <ul class="space-y-3 text-xs text-slate-300 mb-8 border-t border-slate-800/80 pt-6">
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Unlimited Staff & Multi-Branch Support</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Full Nigerian HR, Payroll & Daily Work Reports</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Custom Brand Domain & Complete White-Labeling</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Dedicated Account Manager & On-Site Staff Training</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-400 font-bold">✓</span> Custom API Integrations (Banks & Payment Gateways)</li>
                        </ul>
                    </div>
                    <button @click="openDemoModal('Enterprise Custom Edition')" class="w-full py-3 rounded-xl glass-card hover:bg-white/10 text-xs font-bold text-white uppercase border border-slate-700 cursor-pointer">
                        Contact Enterprise Sales
                    </button>
                </div>

            </div>

        </div>
    </section>


    <!-- =========================================================================
         DIRECT CONTACT & FOOTER
    ========================================================================== -->
    <footer id="contact" class="py-16 border-t border-slate-900 bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12 pb-12 border-b border-slate-900">
                <div class="md:col-span-2">
                    <div class="font-extrabold text-xl tracking-tight text-white font-display flex items-center gap-1.5 mb-3">
                        <span>NAW</span>
                        <span class="text-amber-400">PropertyFlow</span>
                    </div>
                    <p class="text-xs text-slate-400 max-w-sm leading-relaxed mb-4">
                        The definitive operating system and ERP for Nigerian property developers, estate schemes, and commercial brokerages.
                    </p>
                    <div class="text-xs text-slate-300 font-semibold space-y-1">
                        <div>📍 Abuja Office: Suite D7, Kuriftu Plaza, Jabi, Abuja FCT</div>
                        <div>📞 Direct Line: <a href="tel:+2348101358139" class="text-amber-400 hover:underline">+234 810 135 8139</a></div>
                        <div>✉️ Email: <a href="mailto:info@nawpropertyflow.com.ng" class="text-amber-400 hover:underline">info@nawpropertyflow.com.ng</a></div>
                    </div>
                </div>

                <div>
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Quick Navigation</h5>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="#features" class="hover:text-amber-400">Core Modules</a></li>
                        <li><a href="#plot-engine" class="hover:text-amber-400">Plot Visualizer</a></li>
                        <li><a href="#roi-calculator" class="hover:text-amber-400">ROI Calculator</a></li>
                        <li><a href="#affiliates" class="hover:text-amber-400">Partner Program</a></li>
                        <li><a href="#pricing" class="hover:text-amber-400">Pricing Packages</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Interactive Portals</h5>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="https://demo.nawpropertyflow.com.ng" class="text-amber-400 font-bold hover:underline">🚀 Live Demo Sandbox</a></li>
                        <li><a href="{{ route('system.login') }}" class="hover:text-white">System Admin Portal</a></li>
                        <li><a href="{{ asset('docs/NAW_PropertyFlow_Abuja_Prospectus.pdf') }}" target="_blank" class="hover:text-white">Download PDF Guide</a></li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500">
                <div>© {{ date('Y') }} NAW PropertyFlow Technologies Ltd. All Rights Reserved.</div>
                <div class="mt-4 sm:mt-0 text-slate-400">Powering Nigeria's Next-Generation Real Estate Enterprises.</div>
            </div>
        </div>
    </footer>


    <!-- =========================================================================
         DIRECT WHATSAPP & DEMO BOOKING MODAL
    ========================================================================== -->
    <div x-show="demoModalOpen" 
         x-cloak
         style="display: none;"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-cloak>
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-950/85 backdrop-blur-md" @click="demoModalOpen = false"></div>
        
        <!-- Modal Container -->
        <div class="relative w-full max-w-lg glass-card rounded-3xl p-8 border border-amber-500/30 shadow-2xl z-10 bg-slate-900 text-left">
            <button @click="demoModalOpen = false" class="absolute top-6 right-6 text-slate-400 hover:text-white transition-colors cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <div class="mb-6">
                <div class="text-amber-400 text-[10px] font-bold uppercase tracking-widest mb-1">Direct Consultation & Onboarding</div>
                <h3 class="text-2xl font-extrabold text-white font-display mb-1.5" x-text="demoPackage.includes('Partner') || demoPackage.includes('Affiliate') ? 'Partner Registration' : 'Schedule Executive Consultation'">Schedule Executive Consultation</h3>
                <p class="text-slate-400 text-xs leading-relaxed">Enter your contact details to connect directly with our enterprise onboarding team on WhatsApp.</p>
            </div>
            
            <form @submit.prevent="submitDemoForm()">
                <div class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Full Name / Title</label>
                        <input type="text" x-model="clientName" required
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all"
                               placeholder="e.g. Chief / Barr. / Engr. Chukwudi Eze">
                    </div>
                    
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Company / Real Estate Firm</label>
                        <input type="text" x-model="clientCompany" required
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all"
                               placeholder="e.g. Prime Abuja Estates Ltd. / Independent Broker">
                    </div>
                    
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Phone / WhatsApp Line</label>
                        <input type="tel" x-model="clientPhone" required
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all"
                               placeholder="e.g. +234 810 135 8139">
                    </div>
                    
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Official Email</label>
                        <input type="email" x-model="clientEmail" required
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all"
                               placeholder="e.g. director@company.ng">
                    </div>
                    
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Selected Package / Program</label>
                        <select x-model="demoPackage"
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition-all">
                            <option value="Growth Developer (₦30k/mo or ₦300k/yr)">🏢 Growth Developer Plan (₦30,000/mo or ₦300k/yr)</option>
                            <option value="Agency Starter (₦20k/mo or ₦200k/yr)">🏢 Agency Starter Plan (₦20,000/mo or ₦200k/yr)</option>
                            <option value="Enterprise Custom Edition">🏢 Full Enterprise Custom Edition (Dedicated Setup)</option>
                            <option value="Affiliate / Channel Partner Registration">🌟 Channel Partner Program (Earn 15% Monthly)</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="submit"
                            class="w-full py-3.5 rounded-xl btn-primary-demo text-sm font-extrabold uppercase tracking-wide cursor-pointer flex items-center justify-center gap-2">
                        <span>Submit & Connect on WhatsApp</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Alpine.js Data Controller -->
    <script>
        function landingApp() {
            return {
                mobileMenuOpen: false,
                demoModalOpen: false,
                billingCycle: 'monthly',
                demoPackage: 'Growth Developer (₦30k/mo or ₦300k/yr)',
                clientName: '',
                clientCompany: '',
                clientPhone: '',
                clientEmail: '',
                annualSales: 300,
                plots: [
                    { id: 1, number: 'Plot 01', type: '5-Bed Detached', size: '450', price: '₦45M', status: 'Sold' },
                    { id: 2, number: 'Plot 02', type: '5-Bed Detached', size: '450', price: '₦45M', status: 'Sold' },
                    { id: 3, number: 'Plot 03', type: '4-Bed Terrace', size: '250', price: '₦25M', status: 'Available' },
                    { id: 4, number: 'Plot 04', type: '4-Bed Terrace', size: '250', price: '₦25M', status: 'Reserved' },
                    { id: 5, number: 'Plot 05', type: '4-Bed Terrace', size: '250', price: '₦25M', status: 'Available' },
                    { id: 6, number: 'Plot 06', type: '3-Bed Terrace', size: '150', price: '₦18M', status: 'Sold' },
                    { id: 7, number: 'Plot 07', type: '3-Bed Terrace', size: '150', price: '₦18M', status: 'Available' },
                    { id: 8, number: 'Plot 08', type: '3-Bed Terrace', size: '150', price: '₦18M', status: 'Reserved' },
                    { id: 9, number: 'Plot 09', type: '5-Bed Detached', size: '450', price: '₦45M', status: 'Sold' },
                    { id: 10, number: 'Plot 10', type: '4-Bed Terrace', size: '250', price: '₦25M', status: 'Available' },
                    { id: 11, number: 'Plot 11', type: '4-Bed Terrace', size: '250', price: '₦25M', status: 'Available' },
                    { id: 12, number: 'Plot 12', type: '5-Bed Detached', size: '450', price: '₦45M', status: 'Reserved' },
                    { id: 13, number: 'Plot 13', type: '3-Bed Terrace', size: '150', price: '₦18M', status: 'Sold' },
                    { id: 14, number: 'Plot 14', type: '3-Bed Terrace', size: '150', price: '₦18M', status: 'Available' },
                    { id: 15, number: 'Plot 15', type: '4-Bed Terrace', size: '250', price: '₦25M', status: 'Sold' },
                    { id: 16, number: 'Plot 16', type: '5-Bed Detached', size: '450', price: '₦45M', status: 'Reserved' },
                    { id: 17, number: 'Plot 17', type: '3-Bed Terrace', size: '150', price: '₦18M', status: 'Available' },
                    { id: 18, number: 'Plot 18', type: '5-Bed Detached', size: '450', price: '₦45M', status: 'Sold' },
                ],
                selectedPlot: { id: 3, number: 'Plot 03', type: '4-Bed Terrace', size: '250', price: '₦25M', status: 'Available' },
                
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
                        '🏢 Company / Agency: ' + this.clientCompany,
                        '📞 Phone: ' + this.clientPhone,
                        '✉️ Email: ' + this.clientEmail,
                        '🎯 Plan / Program: ' + this.demoPackage
                    ].join('\n');

                    const url = 'https://wa.me/2348101358139?text=' + encodeURIComponent(msg);
                    window.open(url, '_blank');
                    this.demoModalOpen = false;
                },

                selectPlot(plot) {
                    this.selectedPlot = plot;
                },

                formatNumber(val) {
                    return Math.round(val).toLocaleString();
                }
            }
        }
    </script>
</body>
</html>
