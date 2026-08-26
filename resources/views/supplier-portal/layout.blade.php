<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Supplier Portal') | Partner Workspace</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased font-sans flex flex-col min-h-screen">
    <!-- Navbar -->
    <nav class="bg-slate-900 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-6">
                    <a href="{{ route('supplier.dashboard') }}" class="flex items-center gap-2 font-black text-base text-white tracking-tight">
                        <span class="p-1.5 rounded-lg bg-brand-600/20 text-brand-400 border border-brand-500/30">🏢</span>
                        <span>{{ Auth::guard('supplier')->user()->supplier->name }}</span>
                    </a>

                    <div class="hidden md:flex items-center space-x-1">
                        <a href="{{ route('supplier.dashboard') }}" class="px-3 py-2 rounded-xl text-xs font-bold {{ request()->routeIs('supplier.dashboard') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('supplier.purchase-orders.index') }}" class="px-3 py-2 rounded-xl text-xs font-bold {{ request()->routeIs('supplier.purchase-orders.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                            Purchase Orders
                        </a>
                        <a href="{{ route('supplier.invoices.index') }}" class="px-3 py-2 rounded-xl text-xs font-bold {{ request()->routeIs('supplier.invoices.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                            My Invoices
                        </a>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-400 hidden sm:inline">{{ Auth::guard('supplier')->user()->name }}</span>
                    <form method="POST" action="{{ route('supplier.logout') }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-950/50 border border-emerald-800 text-emerald-300 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
