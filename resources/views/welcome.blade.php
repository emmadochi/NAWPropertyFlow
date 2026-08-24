<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NAW PropertyFlow CRM — The Operating System for Nigerian Real Estate</title>
    
    <!-- Primary Meta Tags -->
    <meta name="description" content="Automate plot allocations, milestone installment collections, multi-tier realtor commissions, diaspora investor portals, legal document generation, and payroll. Built specifically for Nigerian Real Estate Developers.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Pure CSS Framework -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
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
        .text-gradient-blue {
            background: linear-gradient(135deg, #FFFFFF 0%, #60A5FA 50%, #3B82F6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .glass-nav {
            background: rgba(3, 11, 23, 0.85);
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
            border-color: rgba(254, 165, 0, 0.3);
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
         MODERN CLEAN NAVIGATION
    ========================================================================== -->
    <header class="fixed top-0 left-0 right-0 z-50 glass-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <!-- Logo -->
                <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-300 flex items-center justify-center text-slate-950 font-bold shadow-lg shadow-amber-500/25 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="font-extrabold text-xl tracking-tight text-white font-display flex items-center gap-1.5">
                            <span>NAW</span>
                            <span class="text-amber-400">PropertyFlow</span>
                        </div>
                        <div class="text-[10px] uppercase font-bold tracking-widest text-slate-400">Enterprise Real Estate ERP</div>
                    </div>
                </a>

                <!-- Desktop Menu Links -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-sm font-semibold text-slate-300 hover:text-amber-400 transition-colors">Core Features</a>
                    <a href="#plot-engine" class="text-sm font-semibold text-slate-300 hover:text-amber-400 transition-colors">Plot Allocation Engine</a>
                    <a href="#roi-calculator" class="text-sm font-semibold text-slate-300 hover:text-amber-400 transition-colors flex items-center gap-1">
                        <span>ROI Calculator</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">New</span>
                    </a>
                    <a href="#pricing" class="text-sm font-semibold text-slate-300 hover:text-amber-400 transition-colors">Pricing</a>
                    <a href="#contact" class="text-sm font-semibold text-slate-300 hover:text-amber-400 transition-colors">Contact</a>
                </nav>

                <!-- Right Action / CTA Buttons -->
                <div class="hidden lg:flex items-center space-x-4">
                    <a href="https://demo.nawpropertyflow.com.ng" class="btn-primary-demo px-5 py-2.5 rounded-full text-xs uppercase tracking-wider flex items-center gap-2">
                        <span>🚀 Launch Live Demo</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                    <a href="{{ route('system.login') }}" class="text-xs font-bold text-slate-400 hover:text-white transition-colors px-2 py-1">
                        System Login
                    </a>
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden glass-nav border-t border-slate-800 px-6 py-6 space-y-4">
            <a href="#features" @click="mobileMenuOpen = false" class="block text-base font-semibold text-slate-200">Core Features</a>
            <a href="#plot-engine" @click="mobileMenuOpen = false" class="block text-base font-semibold text-slate-200">Plot Engine</a>
            <a href="#roi-calculator" @click="mobileMenuOpen = false" class="block text-base font-semibold text-slate-200">ROI Calculator</a>
            <a href="#pricing" @click="mobileMenuOpen = false" class="block text-base font-semibold text-slate-200">Pricing</a>
            <div class="pt-4 border-t border-slate-800 flex flex-col space-y-3">
                <a href="https://demo.nawpropertyflow.com.ng" class="btn-primary-demo text-center py-3 rounded-xl text-sm uppercase">
                    🚀 Launch Interactive Demo
                </a>
                <a href="{{ route('system.login') }}" class="text-center text-xs font-bold text-slate-400 py-2">System Login</a>
            </div>
        </div>
    </header>


    <!-- =========================================================================
         HERO SECTION (High-Impact & Engaging)
    ========================================================================== -->
    <section class="relative pt-36 pb-20 md:pt-44 md:pb-28 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            
            <!-- Live Badge -->
            <div class="inline-flex items-center space-x-2 px-4 py-1.5 rounded-full border border-amber-500/30 bg-amber-500/10 text-amber-300 text-xs font-bold uppercase tracking-wider mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>The All-in-One Real Estate ERP Built for Nigerian Developers</span>
            </div>

            <!-- Main Headline -->
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-white mb-6 font-display max-w-5xl mx-auto leading-tight">
                Automate Plot Allocations, Enforce Installments & <span class="text-gradient-gold">Eliminate Commission Disputes.</span>
            </h1>

            <p class="text-lg sm:text-xl text-slate-300 max-w-3xl mx-auto mb-10 leading-relaxed font-normal">
                Replace spreadsheet chaos with enterprise precision: real-time estate plot maps, automated 3–24 month milestone collections, 1-click legal deeds, and multi-tier realtor splits.
            </p>

            <!-- Hero Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16 max-w-md sm:max-w-none mx-auto">
                <a href="https://demo.nawpropertyflow.com.ng" class="w-full sm:w-auto btn-primary-demo px-8 py-4 rounded-full text-sm uppercase tracking-wide flex items-center justify-center gap-2">
                    <span>⚡ Try Interactive Live Demo</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
                <a href="#roi-calculator" class="w-full sm:w-auto px-7 py-4 rounded-full glass-card hover:bg-white/10 text-slate-200 font-bold text-sm flex items-center justify-center gap-2 border border-slate-700">
                    <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Calculate Revenue Recovery</span>
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
                    <div class="text-xs text-slate-400 font-medium mt-1">Realtor Calculation Disputes</div>
                </div>
                <div class="glass-card p-5 rounded-2xl text-center">
                    <div class="text-3xl font-extrabold text-blue-400 font-display">35%+</div>
                    <div class="text-xs text-slate-400 font-medium mt-1">Faster Milestone Debt Recovery</div>
                </div>
                <div class="glass-card p-5 rounded-2xl text-center">
                    <div class="text-3xl font-extrabold text-purple-400 font-display">1-Click</div>
                    <div class="text-xs text-slate-400 font-medium mt-1">Instant Deed of Assignment</div>
                </div>
            </div>

        </div>
    </section>


    <!-- =========================================================================
         INTERACTIVE ESTATE PLOT MAP VISUALIZER (Engaging Proptech Widget)
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
                        <h4 class="font-bold text-lg text-white font-display">Guzape Hills Luxury Scheme — Phase 2</h4>
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
                        Inspect in Full CRM →
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
                <span class="text-xs font-extrabold uppercase tracking-widest text-emerald-400">Business Value Engine</span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-white mt-2 font-display">Revenue Recovery Calculator</h2>
                <p class="text-sm sm:text-base text-slate-400 mt-3">Estimate how much revenue your brokerage or development firm loses annually to manual spreadsheets, delayed follow-ups, and commission errors.</p>
            </div>

            <!-- Interactive Calculator Box -->
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

                <!-- Calculation Output Grid -->
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
                        Unlock This ROI on Live Demo →
                    </a>
                </div>

            </div>

        </div>
    </section>


    <!-- =========================================================================
         CORE MODULES SHOWCASE
    ========================================================================== -->
    <section id="features" class="py-20 bg-slate-950/60 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-widest text-amber-400">Complete ERP Capabilities</span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-white mt-2 font-display">Engineered for Nigerian Real Estate</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- 1. Plots & Units -->
                <div class="glass-card p-8 rounded-3xl">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-xl mb-6 border border-amber-500/20">📍</div>
                    <h3 class="text-xl font-bold text-white font-display mb-2">Plot & Inventory Manager</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Prevent disastrous double allocations. Manage off-plan developments, terrace duplexes, and estate plots with live reserve locks.</p>
                </div>

                <!-- 2. Milestone Billing -->
                <div class="glass-card p-8 rounded-3xl">
                    <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-xl mb-6 border border-blue-500/20">💳</div>
                    <h3 class="text-xl font-bold text-white font-display mb-2">Automated Milestone Billing</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Break high-ticket property sales into 3 to 24-month structured payment plans. Automated WhatsApp & SMS payment reminders.</p>
                </div>

                <!-- 3. Realtor Splits -->
                <div class="glass-card p-8 rounded-3xl">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl mb-6 border border-emerald-500/20">🤝</div>
                    <h3 class="text-xl font-bold text-white font-display mb-2">Multi-Tier Realtor Commissions</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Instant automated commission calculations for external realtors and internal sales agents. Eliminates payment disputes.</p>
                </div>

                <!-- 4. Document Factory -->
                <div class="glass-card p-8 rounded-3xl">
                    <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-xl mb-6 border border-purple-500/20">📄</div>
                    <h3 class="text-xl font-bold text-white font-display mb-2">1-Click Legal Document Factory</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Generate instant Deed of Assignment, Allocation Letters, and Official Receipts pre-filled with client and plot details.</p>
                </div>

                <!-- 5. Diaspora Portal -->
                <div class="glass-card p-8 rounded-3xl">
                    <div class="w-12 h-12 rounded-2xl bg-teal-500/10 text-teal-400 flex items-center justify-center text-xl mb-6 border border-teal-500/20">🌍</div>
                    <h3 class="text-xl font-bold text-white font-display mb-2">Diaspora Buyer Portal</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Provide international buyers in the UK, USA, and Canada a dedicated tracking dashboard with live site construction updates.</p>
                </div>

                <!-- 6. HR & Payroll -->
                <div class="glass-card p-8 rounded-3xl">
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-xl mb-6 border border-rose-500/20">💼</div>
                    <h3 class="text-xl font-bold text-white font-display mb-2">Nigerian HR & Naira Payroll</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Integrated salary structures, staff onboarding, daily work submission reviews, leave approvals, and bank disbursement exports.</p>
                </div>

            </div>

        </div>
    </section>


    <!-- =========================================================================
         CALL TO ACTION FOOTER
    ========================================================================== -->
    <footer id="contact" class="py-16 border-t border-slate-900 bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="glass-card p-8 sm:p-12 rounded-3xl text-center max-w-4xl mx-auto border border-amber-500/30 mb-12">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white font-display">Ready to Elevate Your Real Estate Firm?</h2>
                <p class="text-sm sm:text-base text-slate-300 mt-2 max-w-xl mx-auto mb-8">Test the full live demo right now with our 1-click role switcher. No registration required.</p>
                <a href="https://demo.nawpropertyflow.com.ng" class="btn-primary-demo inline-block px-10 py-4 rounded-full text-sm uppercase tracking-wide font-extrabold">
                    🚀 Launch Interactive Demo Now
                </a>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 pt-8 border-t border-slate-900">
                <div>© {{ date('Y') }} NAW PropertyFlow CRM. All Rights Reserved.</div>
                <div class="flex space-x-6 mt-4 sm:mt-0">
                    <a href="tel:+2348101358139" class="hover:text-amber-400">+234 810 135 8139</a>
                    <a href="https://demo.nawpropertyflow.com.ng" class="hover:text-amber-400">Live Demo Portal</a>
                    <a href="{{ route('system.login') }}" class="hover:text-amber-400">System Admin</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Alpine.js Data Script -->
    <script>
        function landingApp() {
            return {
                mobileMenuOpen: false,
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
