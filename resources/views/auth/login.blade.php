<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - NAW PropertyFlow CRM</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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
                            500: '#FEA500',
                            600: '#e09200',
                            700: '#b87700',
                            800: '#8f5c00',
                            900: '#664200'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f8fafc; }
        .logo-icon { width: 44px; height: 44px; max-width: 44px; max-height: 44px; }
        .quick-role-btn { transition: all 0.2s ease; }
        .quick-role-btn:hover { transform: translateY(-1px); }
    </style>
</head>
<body class="bg-slate-50 font-sans min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden flex flex-col p-8 md:p-10 my-8">
        
        <!-- Header -->
        <div class="flex flex-col items-center mb-6 text-center">
            @php
                $company = \App\Models\CompanySetting::first();
                $displayName = $company->company_name ?? config('app.name', 'NAW PropertyFlow CRM');
                $logoUrl = ($company && $company->logo_path) ? asset('storage/' . $company->logo_path) : null;
            @endphp
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $displayName }}" class="h-14 w-auto object-contain mb-4" style="max-height: 56px;">
            @else
                <div class="p-3 bg-amber-50 text-amber-500 rounded-2xl mb-4 flex items-center justify-center border border-amber-100 logo-icon">
                    <svg class="w-7 h-7 text-amber-500" style="width: 28px; height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            @endif
            <h1 class="text-2xl font-extrabold text-slate-900 leading-tight">Welcome Back</h1>
            <p class="text-xs text-slate-500 mt-1">Enter your credentials to access <span class="font-semibold text-slate-700">{{ $displayName }}</span></p>
        </div>

        @if(session('success'))
        <div class="mb-4 bg-emerald-50 text-emerald-700 px-4 py-3 rounded-xl border border-emerald-100 text-xs font-medium">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->has('email'))
        <div class="mb-4 bg-rose-50 text-rose-700 px-4 py-3 rounded-xl border border-rose-100 text-xs font-medium">
            {{ $errors->first('email') }}
        </div>
        @endif

        <!-- Form -->
        <form action="{{ route('login') }}" method="POST" class="space-y-4" id="loginForm">
            @csrf
            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email', 'superadmin@propertyflow.com') }}" required autofocus
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-sm text-slate-800 transition-colors"
                       placeholder="e.g. superadmin@propertyflow.com">
            </div>

            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
                    <a href="{{ route('password.request') }}" class="text-xs font-bold text-amber-600 hover:text-amber-700 transition-colors">Forgot?</a>
                </div>
                <input type="password" name="password" id="password" value="password" required
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-sm text-slate-800 transition-colors"
                       placeholder="••••••••">
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" checked class="w-4 h-4 text-amber-500 border-slate-300 rounded focus:ring-amber-500">
                <label for="remember" class="ml-2 text-xs text-slate-600">Keep me logged in</label>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-extrabold py-3.5 px-4 rounded-xl shadow-lg shadow-amber-500/25 transition-all text-sm uppercase tracking-wide cursor-pointer">
                Sign In to CRM
            </button>
        </form>

        <!-- 1-Click Interactive Demo Role Selector -->
        <div class="mt-6 pt-5 border-t border-slate-100">
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">⚡ 1-Click Demo Logins:</span>
                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">Live Sandbox</span>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" onclick="fillAndLogin('superadmin@propertyflow.com', 'password')" class="quick-role-btn text-center p-2 rounded-xl bg-slate-50 hover:bg-amber-50 border border-slate-200/80 hover:border-amber-300 text-[11px] font-bold text-slate-700 hover:text-amber-800 cursor-pointer">
                    <span class="block text-base mb-0.5">👑</span>
                    Super Admin
                </button>
                <button type="button" onclick="fillAndLogin('manager@propertyflow.com', 'password')" class="quick-role-btn text-center p-2 rounded-xl bg-slate-50 hover:bg-blue-50 border border-slate-200/80 hover:border-blue-300 text-[11px] font-bold text-slate-700 hover:text-blue-800 cursor-pointer">
                    <span class="block text-base mb-0.5">📊</span>
                    Manager
                </button>
                <button type="button" onclick="fillAndLogin('se1@propertyflow.com', 'password')" class="quick-role-btn text-center p-2 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-200/80 hover:border-emerald-300 text-[11px] font-bold text-slate-700 hover:text-emerald-800 cursor-pointer">
                    <span class="block text-base mb-0.5">💼</span>
                    Sales Agent
                </button>
            </div>
        </div>

    </div>

    <script>
        function fillAndLogin(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            document.getElementById('loginForm').submit();
        }
    </script>
</body>
</html>
