<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'NAW PropertyFlow CRM') }} - Property Management Made Sexy</title>
    
    <!-- Primary Meta Tags -->
    <meta name="title" content="NAW PropertyFlow CRM - The Ultimate Property Management OS">
    <meta name="description" content="Automate your real estate sales, track installments, and generate documents instantly. Built specifically for Nigerian Developers.">

    <!-- Open Graph / Facebook / LinkedIn -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="NAW PropertyFlow CRM - The Ultimate Property Management OS">
    <meta property="og:description" content="Automate your real estate sales, track installments, and generate documents instantly. Built specifically for Nigerian Developers.">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/') }}">
    <meta property="twitter:title" content="NAW PropertyFlow CRM - The Ultimate Property Management OS">
    <meta property="twitter:description" content="Automate your real estate sales, track installments, and generate documents instantly. Built specifically for Nigerian Developers.">
    <meta property="twitter:image" content="{{ asset('images/og-image.jpg') }}">

    <!-- Favicon (Fallback to generic if not present) -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏢</text></svg>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fffcf5',
                            100: '#fff5e0',
                            200: '#ffe6b3',
                            300: '#ffd080',
                            400: '#ffb54d',
                            500: '#FEA500', // Core orange accent
                            600: '#e09200',
                            700: '#b87700',
                            800: '#8f5c00',
                            900: '#664200'
                        },
                        dark: {
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    },
                    animation: {
                        'blob': 'blob 7s infinite',
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
    
    <!-- Alpine.js Data Component -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pageApp', () => ({
                mobileMenuOpen: false,
                scrolled: false,
                demoModalOpen: false,
                demoPackage: 'General',
                clientName: '',
                clientCompany: '',
                clientPhone: '',
                clientEmail: '',

                openDemoModal(pkg = 'General') {
                    this.demoPackage = pkg;
                    this.demoModalOpen = true;
                    this.mobileMenuOpen = false;
                },

                submitDemoForm() {
                    const msg = [
                        'Hello! I would like to book a free demo for NAW PropertyFlow.',
                        '',
                        'Name: ' + this.clientName,
                        'Company/Agency: ' + this.clientCompany,
                        'Phone: ' + this.clientPhone,
                        'Email: ' + this.clientEmail,
                        'Preferred Package: ' + this.demoPackage
                    ].join('\n');

                    const url = 'https://wa.me/2348000000000?text=' + encodeURIComponent(msg);
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
        .glass-panel {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .text-gradient {
            background: linear-gradient(135deg, #FEA500 0%, #ffb54d 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="bg-dark-950 text-slate-200 antialiased selection:bg-brand-500 selection:text-white" x-data="pageApp()" @scroll.window="scrolled = (window.pageYOffset > 20)">

    <!-- Background Glow Effects -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-brand-500/20 rounded-full mix-blend-screen filter blur-[100px] animate-blob"></div>
        <div class="absolute top-[20%] right-[-10%] w-96 h-96 bg-purple-500/10 rounded-full mix-blend-screen filter blur-[100px] animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-[-20%] left-[20%] w-[500px] h-[500px] bg-brand-600/10 rounded-full mix-blend-screen filter blur-[120px] animate-blob animation-delay-4000"></div>
    </div>

    <!-- Navigation -->
    <nav class="fixed w-full z-50 transition-all duration-300" :class="{'glass-panel py-3 shadow-2xl shadow-black/50': scrolled, 'bg-transparent py-5': !scrolled}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-xl bg-brand-500 flex items-center justify-center text-white font-bold shadow-lg shadow-brand-500/30">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <span class="font-extrabold text-2xl tracking-tight text-white">NAW <span class="text-brand-500">PropertyFlow</span></span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-sm font-semibold text-slate-300 hover:text-white transition-colors">Features</a>
                    <a href="#pricing" class="text-sm font-semibold text-slate-300 hover:text-white transition-colors">Pricing</a>
                    <a href="#testimonials" class="text-sm font-semibold text-slate-300 hover:text-white transition-colors">Testimonials</a>
                </div>

                <!-- Auth Buttons -->
                <div class="hidden md:flex items-center space-x-4">
                    @auth('system_admin')
                        <a href="{{ route('system.dashboard') }}" class="text-sm font-bold text-white hover:text-brand-400 transition-colors">System Dashboard</a>
                    @else
                        <a href="{{ route('system.login') }}" class="text-sm font-bold text-slate-300 hover:text-white transition-colors">System Login</a>
                        <a href="#" @click.prevent="openDemoModal('General')" class="px-5 py-2.5 rounded-full bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm shadow-lg shadow-brand-500/25 transition-all transform hover:-translate-y-0.5">
                            Book a Free Demo
                        </a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-slate-300 hover:text-white focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-transition x-cloak class="md:hidden glass-panel border-t border-white/10 absolute w-full left-0 top-full shadow-2xl">
            <div class="px-4 pt-2 pb-6 space-y-2 flex flex-col">
                <a href="#features" @click="mobileMenuOpen = false" class="block px-3 py-3 rounded-lg text-base font-medium text-slate-200 hover:bg-white/5">Features</a>
                <a href="#pricing" @click="mobileMenuOpen = false" class="block px-3 py-3 rounded-lg text-base font-medium text-slate-200 hover:bg-white/5">Pricing</a>
                <a href="#testimonials" @click="mobileMenuOpen = false" class="block px-3 py-3 rounded-lg text-base font-medium text-slate-200 hover:bg-white/5">Testimonials</a>
                <div class="pt-4 mt-2 border-t border-white/10 flex flex-col space-y-3">
                    @auth('system_admin')
                        <a href="{{ route('system.dashboard') }}" class="block text-center px-5 py-3 rounded-xl bg-white/10 text-white font-bold">System Dashboard</a>
                    @else
                        <a href="{{ route('system.login') }}" class="block text-center px-5 py-3 rounded-xl bg-white/5 text-white font-bold">System Login</a>
                        <a href="#" @click.prevent="openDemoModal('General')" class="block text-center px-5 py-3 rounded-xl bg-brand-500 text-white font-bold shadow-lg shadow-brand-500/25">Book a Free Demo</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="relative z-10 pt-32 pb-16 sm:pt-40 sm:pb-24 lg:pb-32 overflow-hidden">
        
        <!-- HERO SECTION -->
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center animate-fade-in-up">
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full border border-brand-500/30 bg-brand-500/10 text-brand-400 text-sm font-semibold mb-8">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
                </span>
                <span>NAWPropertyFlow 2.0 is Live!</span>
            </div>
            
            <h1 class="mx-auto max-w-4xl font-extrabold text-5xl sm:text-7xl tracking-tight text-white mb-6">
                Automate Your Real Estate Sales, Track Installments, and <span class="text-gradient">Never Lose a Lead.</span>
            </h1>
            
            <p class="mx-auto max-w-2xl text-lg sm:text-xl text-slate-400 mb-10">
                Built specifically for Nigerian Developers. Streamline your sales process from lead generation to final payment.
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                <a href="#" @click.prevent="openDemoModal('General')" class="w-full sm:w-auto px-8 py-4 rounded-full bg-brand-500 hover:bg-brand-600 text-white font-bold text-lg shadow-xl shadow-brand-500/30 transition-all transform hover:-translate-y-1 focus:ring-4 focus:ring-brand-500/50 flex items-center justify-center">
                    Book a Free Demo
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
                <a href="#features" class="w-full sm:w-auto px-8 py-4 rounded-full glass-panel hover:bg-white/10 text-white font-bold text-lg transition-all flex items-center justify-center">
                    Explore Features
                </a>
            </div>
            
            <!-- Dashboard Mockup Image/Graphic -->
            <div class="mt-20 relative mx-auto max-w-5xl rounded-xl shadow-2xl shadow-black/80 ring-1 ring-white/10 glass-panel p-2 transform rotate-[1deg] hover:rotate-0 transition-transform duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-dark-950 via-transparent to-transparent z-10 bottom-0 rounded-xl h-1/3 mt-auto pointer-events-none"></div>
                <div class="bg-dark-900 rounded-lg overflow-hidden flex flex-col h-[400px] sm:h-[600px] relative border border-white/5">
                    <!-- Browser Chrome -->
                    <div class="bg-dark-950 border-b border-white/5 px-4 py-3 flex items-center gap-2">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-rose-500"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        </div>
                        <div class="mx-auto bg-dark-800 rounded-md px-3 py-1.5 text-[10px] text-slate-400 font-mono flex items-center w-64 text-center justify-center">
                            <svg class="w-3 h-3 mr-1 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                            app.nawpropertyflow.com
                        </div>
                    </div>
                    <!-- Real Data UI Representation -->
                    <div class="flex flex-1 overflow-hidden bg-[#0B0F19] text-sm text-slate-300">
                        <!-- Sidebar -->
                        <div class="w-48 hidden sm:flex flex-col gap-2 p-4 border-r border-white/5 bg-dark-950/50">
                            <div class="font-bold text-white mb-4 flex items-center gap-2"><svg class="w-5 h-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg> Main Menu</div>
                            <div class="py-2 px-3 bg-brand-500/20 text-brand-400 rounded-lg font-semibold flex items-center gap-2"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg> Dashboard</div>
                            <div class="py-2 px-3 hover:bg-white/5 rounded-lg flex items-center gap-2"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg> Leads</div>
                            <div class="py-2 px-3 hover:bg-white/5 rounded-lg flex items-center gap-2"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg> Properties</div>
                            <div class="py-2 px-3 hover:bg-white/5 rounded-lg flex items-center gap-2"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Invoices</div>
                            <div class="mt-auto py-2 px-3 hover:bg-white/5 rounded-lg flex items-center gap-2"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Settings</div>
                        </div>
                        
                        <!-- Main Content -->
                        <div class="flex-1 flex flex-col p-4 sm:p-6 gap-6 overflow-hidden relative">
                            <!-- Top bar -->
                            <div class="flex justify-between items-center">
                                <div>
                                    <h2 class="text-xl font-bold text-white">Overview</h2>
                                    <p class="text-xs text-slate-400">Welcome back, Adewale. Here's what's happening today.</p>
                                </div>
                                <div class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-lg cursor-pointer text-xs transition-colors hidden sm:flex items-center gap-2 shadow-lg shadow-brand-500/20">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg> New Lead
                                </div>
                            </div>
                            
                            <!-- Stats Cards -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="glass-panel rounded-xl border border-white/5 p-4 flex flex-col relative overflow-hidden group">
                                    <div class="absolute top-0 right-0 w-24 h-24 bg-brand-500/10 rounded-full blur-xl group-hover:bg-brand-500/20 transition-all"></div>
                                    <div class="text-slate-400 text-xs font-semibold mb-1">Total Revenue</div>
                                    <div class="text-2xl font-bold text-white mb-2">₦450.5M</div>
                                    <div class="text-xs text-emerald-400 flex items-center gap-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg> +12.5% this month</div>
                                </div>
                                <div class="glass-panel rounded-xl border border-white/5 p-4 flex flex-col relative overflow-hidden group">
                                    <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/20 transition-all"></div>
                                    <div class="text-slate-400 text-xs font-semibold mb-1">Active Leads</div>
                                    <div class="text-2xl font-bold text-white mb-2">1,248</div>
                                    <div class="text-xs text-emerald-400 flex items-center gap-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg> +42 new this week</div>
                                </div>
                                <div class="glass-panel rounded-xl border border-white/5 p-4 flex flex-col relative overflow-hidden group">
                                    <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition-all"></div>
                                    <div class="text-slate-400 text-xs font-semibold mb-1">Properties Sold</div>
                                    <div class="text-2xl font-bold text-white mb-2">84</div>
                                    <div class="text-xs text-blue-400 flex items-center gap-1">6 pending closure</div>
                                </div>
                            </div>
                            
                            <!-- Recent Activity Table -->
                            <div class="flex-1 glass-panel rounded-xl border border-white/5 flex flex-col overflow-hidden">
                                <div class="p-4 border-b border-white/5 flex justify-between items-center">
                                    <h3 class="font-bold text-white text-sm">Recent Transactions</h3>
                                    <span class="text-xs text-brand-400 cursor-pointer hover:underline">View All</span>
                                </div>
                                <div class="flex-1 p-0 flex flex-col overflow-hidden">
                                    <div class="grid grid-cols-3 sm:grid-cols-4 px-4 py-2 bg-white/5 text-xs font-semibold text-slate-400">
                                        <div class="col-span-1">Client</div>
                                        <div class="col-span-1 hidden sm:block">Property</div>
                                        <div class="col-span-1">Amount</div>
                                        <div class="col-span-1 text-right">Status</div>
                                    </div>
                                    <div class="grid grid-cols-3 sm:grid-cols-4 px-4 py-3 border-b border-white/5 items-center hover:bg-white/[0.02] transition-colors">
                                        <div class="col-span-1 flex items-center gap-2 text-white font-medium"><div class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center text-[10px]">CO</div> Chioma Okafor</div>
                                        <div class="col-span-1 text-slate-300 hidden sm:block truncate">Lekki Phase 1 Villa</div>
                                        <div class="col-span-1 text-slate-300 font-mono">₦120M</div>
                                        <div class="col-span-1 text-right"><span class="px-2 py-1 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-bold">Paid</span></div>
                                    </div>
                                    <div class="grid grid-cols-3 sm:grid-cols-4 px-4 py-3 border-b border-white/5 items-center hover:bg-white/[0.02] transition-colors">
                                        <div class="col-span-1 flex items-center gap-2 text-white font-medium"><div class="w-6 h-6 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center text-[10px]">IB</div> Ibrahim Balogun</div>
                                        <div class="col-span-1 text-slate-300 hidden sm:block truncate">Eko Atlantic Plot 4</div>
                                        <div class="col-span-1 text-slate-300 font-mono">₦85.5M</div>
                                        <div class="col-span-1 text-right"><span class="px-2 py-1 rounded bg-amber-500/20 text-amber-400 text-[10px] font-bold">Installment</span></div>
                                    </div>
                                    <div class="grid grid-cols-3 sm:grid-cols-4 px-4 py-3 border-b border-white/5 items-center hover:bg-white/[0.02] transition-colors">
                                        <div class="col-span-1 flex items-center gap-2 text-white font-medium"><div class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center text-[10px]">AN</div> Ada Nwachukwu</div>
                                        <div class="col-span-1 text-slate-300 hidden sm:block truncate">Abuja Maitama Duplex</div>
                                        <div class="col-span-1 text-slate-300 font-mono">₦300M</div>
                                        <div class="col-span-1 text-right"><span class="px-2 py-1 rounded bg-brand-500/20 text-brand-400 text-[10px] font-bold text-nowrap">Pending Review</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PROBLEM/SOLUTION SECTION -->
        <div id="problem-solution" class="py-24 relative z-10 border-t border-white/5 bg-black/20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-brand-500 font-bold tracking-wide uppercase text-sm mb-2">The Old Way vs The New Way</h2>
                    <p class="text-3xl sm:text-4xl font-extrabold text-white mb-4">Stop using messy spreadsheets.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <!-- The Old Way (Problem) -->
                    <div class="glass-panel p-8 rounded-2xl border-t border-red-500/30 bg-red-950/10">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center text-red-400 mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-white">The Old Way</h3>
                        </div>
                        <ul class="space-y-4 text-slate-300">
                            <li class="flex items-start">
                                <span class="text-red-400 mr-3 mt-1">✗</span>
                                <span><strong>Messy Excel Sheets:</strong> Data scattered across multiple files, making it hard to track leads and inventory.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-400 mr-3 mt-1">✗</span>
                                <span><strong>Missed Follow-ups:</strong> Relying on memory or sticky notes leads to forgotten callbacks and lost deals.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-400 mr-3 mt-1">✗</span>
                                <span><strong>Manual Documents:</strong> Spending hours copy-pasting client details into Word documents for contracts.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-red-400 mr-3 mt-1">✗</span>
                                <span><strong>Lost Installment Records:</strong> Tracking payment plans manually leads to disputes and cash flow issues.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- The New Way (Solution) -->
                    <div class="glass-panel p-8 rounded-2xl border-t border-brand-500/50 bg-brand-900/10 shadow-lg shadow-brand-500/10">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-full bg-brand-500/20 flex items-center justify-center text-brand-400 mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <h3 class="text-2xl font-bold text-white">The NAW Pro Flow Way</h3>
                        </div>
                        <ul class="space-y-4 text-slate-300">
                            <li class="flex items-start">
                                <span class="text-brand-400 mr-3 mt-1">✓</span>
                                <span><strong>Automated CRM:</strong> All your leads, properties, and interactions in one clean dashboard.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-brand-400 mr-3 mt-1">✓</span>
                                <span><strong>Never Miss a Lead:</strong> Automated drip campaigns and follow-up reminders keep your pipeline hot.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-brand-400 mr-3 mt-1">✓</span>
                                <span><strong>Instant Documents:</strong> Generate perfectly formatted contracts and NDAs with one click.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-brand-400 mr-3 mt-1">✓</span>
                                <span><strong>Track Installments:</strong> Automated payment schedules and receipts keep your cash flow predictable.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- FEATURES SECTION -->
        <div id="features" class="py-24 sm:py-32 relative z-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-brand-500 font-bold tracking-wide uppercase text-sm mb-2">Everything you need</h2>
                    <p class="text-3xl sm:text-4xl font-extrabold text-white mb-4">A complete ecosystem for real estate</p>
                    <p class="text-slate-400 text-lg">Stop paying for 5 different software tools. NAWPropertyFlow combines your CRM, HR, Documents, and Marketing into one seamless platform.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Feature 1 -->
                    <div class="glass-panel p-8 rounded-2xl hover:bg-white/5 transition-colors border-t border-white/10 group">
                        <div class="w-12 h-12 rounded-xl bg-brand-500/20 flex items-center justify-center text-brand-400 mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">CRM & Pipeline</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">Manage properties, track leads, and close deals faster with our visual pipeline and follow-up automations.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="glass-panel p-8 rounded-2xl hover:bg-white/5 transition-colors border-t border-white/10 group">
                        <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center text-blue-400 mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Document Engine</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">Generate contracts, NDAs, and proposals automatically from templates using your CRM data.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="glass-panel p-8 rounded-2xl hover:bg-white/5 transition-colors border-t border-white/10 group">
                        <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center text-purple-400 mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">HR & KPIs</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">Track agent performance, manage leave requests, and set department targets in a unified HR portal.</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="glass-panel p-8 rounded-2xl hover:bg-white/5 transition-colors border-t border-white/10 group">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center text-emerald-400 mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Marketing Automation</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">Run powerful drip campaigns and email blasts directly to your leads without leaving the system.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRICING SECTION -->
        <div id="pricing" class="py-24 relative z-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-brand-500 font-bold tracking-wide uppercase text-sm mb-2">Simple Pricing</h2>
                    <p class="text-3xl sm:text-4xl font-extrabold text-white mb-4">Choose the right tier for your agency</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto items-center">
                    
                    <!-- Starter Package -->
                    <div class="glass-panel rounded-3xl p-8 border border-white/10 relative">
                        <h3 class="text-2xl font-bold text-white mb-2">Starter</h3>
                        <p class="text-slate-400 text-sm mb-6">Perfect for small agencies getting started.</p>
                        <div class="mb-6">
                            <span class="text-4xl font-extrabold text-white">$49</span>
                            <span class="text-slate-400">/mo</span>
                        </div>
                        <a href="#" @click.prevent="openDemoModal('Starter')" class="block w-full py-3 px-4 bg-white/10 hover:bg-white/20 text-white font-bold text-center rounded-xl transition-colors mb-8">Book a Free Demo</a>
                        
                        <ul class="space-y-4 text-sm text-slate-300">
                            <li class="flex items-center"><svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Leads Management</li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Properties & Inventory</li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Standard Reporting</li>
                            <li class="flex items-center opacity-40"><svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Document Generation</li>
                            <li class="flex items-center opacity-40"><svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> HR & KPI Management</li>
                        </ul>
                    </div>

                    <!-- Professional Package -->
                    <div class="glass-panel rounded-3xl p-8 border-2 border-brand-500 relative transform md:-translate-y-4 bg-gradient-to-b from-brand-900/40 to-transparent shadow-2xl shadow-brand-500/20">
                        <div class="absolute top-0 inset-x-0 transform -translate-y-1/2 flex justify-center">
                            <span class="bg-brand-500 text-white text-xs font-bold px-3 py-1 uppercase tracking-wider rounded-full">Most Popular</span>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-2">Professional</h3>
                        <p class="text-slate-400 text-sm mb-6">For growing teams that need automation.</p>
                        <div class="mb-6">
                            <span class="text-4xl font-extrabold text-white">$149</span>
                            <span class="text-slate-400">/mo</span>
                        </div>
                        <a href="#" @click.prevent="openDemoModal('Professional')" class="block w-full py-3 px-4 bg-brand-500 hover:bg-brand-600 shadow-lg shadow-brand-500/30 text-white font-bold text-center rounded-xl transition-colors mb-8">Book a Free Demo</a>
                        
                        <ul class="space-y-4 text-sm text-slate-300">
                            <li class="flex items-center"><svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Everything in Starter</li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> <span class="font-bold text-white">Document Generation</span></li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> <span class="font-bold text-white">File Manager</span></li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Unlimited Templates</li>
                            <li class="flex items-center opacity-40"><svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> HR & KPI Management</li>
                        </ul>
                    </div>

                    <!-- Enterprise Package -->
                    <div class="glass-panel rounded-3xl p-8 border border-white/10 relative">
                        <h3 class="text-2xl font-bold text-white mb-2">Enterprise</h3>
                        <p class="text-slate-400 text-sm mb-6">The ultimate suite for large organizations.</p>
                        <div class="mb-6">
                            <span class="text-4xl font-extrabold text-white">$399</span>
                            <span class="text-slate-400">/mo</span>
                        </div>
                        <a href="#" @click.prevent="openDemoModal('Enterprise')" class="block w-full py-3 px-4 bg-white/10 hover:bg-white/20 text-white font-bold text-center rounded-xl transition-colors mb-8">Book a Free Demo</a>
                        
                        <ul class="space-y-4 text-sm text-slate-300">
                            <li class="flex items-center"><svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Everything in Professional</li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-brand-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> <span class="font-bold text-brand-300">HR & Performance</span></li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-brand-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> <span class="font-bold text-brand-300">Marketing Campaigns</span></li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-brand-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Advanced Analytics</li>
                            <li class="flex items-center"><svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Dedicated Support</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- TESTIMONIALS SECTION -->
        <div id="testimonials" class="py-24 border-y border-white/5 bg-black/20 relative z-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl font-extrabold text-white mb-4">Loved by Top Agencies</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    <div class="glass-panel p-8 rounded-2xl border border-white/5 relative">
                        <svg class="absolute top-6 right-6 w-8 h-8 text-white/10" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" /></svg>
                        <p class="text-slate-300 mb-6 italic">"Since switching to the Enterprise tier, our HR team and Sales team finally communicate. Generating contracts takes 2 seconds now. Absolute game changer."</p>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-brand-400 to-brand-600 flex items-center justify-center text-white font-bold text-lg">CE</div>
                            <div>
                                <h4 class="text-white font-bold">Chukwudi Eze</h4>
                                <p class="text-brand-500 text-sm">CEO, Lekki Homes Realty</p>
                            </div>
                        </div>
                    </div>
                    <div class="glass-panel p-8 rounded-2xl border border-white/5 relative">
                        <svg class="absolute top-6 right-6 w-8 h-8 text-white/10" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" /></svg>
                        <p class="text-slate-300 mb-6 italic">"The document generator on the Professional package saved us thousands in admin costs. It's beautiful, fast, and does exactly what it says."</p>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600 flex items-center justify-center text-white font-bold text-lg">AB</div>
                            <div>
                                <h4 class="text-white font-bold">Aisha Bello</h4>
                                <p class="text-emerald-500 text-sm">Broker, Abuja Prime Properties</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- FOOTER -->
    <footer class="border-t border-white/5 bg-dark-950 py-12 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center space-x-2">
                <span class="font-bold text-xl tracking-tight text-white">NAW <span class="text-brand-500">PropertyFlow</span></span>
            </div>
            <div class="text-slate-500 text-sm md:text-right">
                <p>&copy; {{ date('Y') }} NAW PropertyFlow CRM. All rights reserved.</p>
                <p class="mt-2 font-medium text-slate-400">A product by NAW World Technologies Limited.<br>Registered & Built in Nigeria.</p>
            </div>
        </div>
    </footer>

    <!-- Book Demo Modal -->
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
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="demoModalOpen = false"></div>
        
        <!-- Modal Content -->
        <div class="relative w-full max-w-lg glass-panel rounded-3xl p-8 border border-white/10 shadow-2xl z-10 bg-dark-950/90 text-left">
            <!-- Close Button -->
            <button @click="demoModalOpen = false" class="absolute top-6 right-6 text-slate-400 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <div class="mb-6">
                <h3 class="text-2xl font-extrabold text-white mb-2">Book a Free Demo</h3>
                <p class="text-slate-400 text-sm">Tell us a bit about your agency, and we'll connect you directly on WhatsApp to schedule your personalized walkthrough.</p>
            </div>
            
            <form @submit.prevent="submitDemoForm()">
                <div class="space-y-4">
                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Full Name</label>
                        <input type="text" id="name" x-model="clientName" required
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all"
                               placeholder="e.g. Chukwudi Eze">
                    </div>
                    
                    <!-- Company / Agency Name -->
                    <div>
                        <label for="company" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Company / Agency</label>
                        <input type="text" id="company" x-model="clientCompany" required
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all"
                               placeholder="e.g. Lekki Homes Realty">
                    </div>
                    
                    <!-- Phone Number -->
                    <div>
                        <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Phone Number</label>
                        <input type="tel" id="phone" x-model="clientPhone" required
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all"
                               placeholder="e.g. +234 803 123 4567">
                    </div>
                    
                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Email Address</label>
                        <input type="email" id="email" x-model="clientEmail" required
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all"
                               placeholder="e.g. name@company.com">
                    </div>
                    
                    <!-- Selected Package -->
                    <div>
                        <label for="package" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Interested Package</label>
                        <select id="package" x-model="demoPackage"
                                class="w-full bg-dark-900 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all">
                            <option value="General" class="bg-dark-950">General Demo / Consult</option>
                            <option value="Starter" class="bg-dark-950">Starter Package ($49/mo)</option>
                            <option value="Professional" class="bg-dark-950">Professional Package ($149/mo)</option>
                            <option value="Enterprise" class="bg-dark-950">Enterprise Package ($399/mo)</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-8">
                    <button type="submit"
                            class="w-full py-4 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-bold text-md shadow-lg shadow-brand-500/20 transition-all flex items-center justify-center gap-2">
                        Proceed to WhatsApp
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
