<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Supplier Portal Login | NAW PropertyFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 bg-slate-950 text-slate-100 antialiased font-sans">
    <div class="w-full max-w-md space-y-8 bg-slate-900 border border-slate-800 p-8 rounded-3xl shadow-2xl">
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-brand-600/20 text-brand-400 border border-brand-500/30 text-xl font-bold">
                🏢
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Supplier Partner Portal</h1>
            <p class="text-xs text-slate-400">Log in to view purchase orders, check delivery receipts, and submit digital invoices.</p>
        </div>

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-950/40 border border-rose-800 text-rose-300 text-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('supplier.login.submit') }}" class="space-y-4">
            @csrf

            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Portal Login Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="rep@supplier.com"
                       class="w-full py-2.5 px-3 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Password</label>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full py-2.5 px-3 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 text-slate-400">
                    <input type="checkbox" name="remember" class="rounded text-brand-600 focus:ring-brand-500 bg-slate-800 border-slate-700">
                    <span>Keep me logged in</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-brand-600/30 transition-all">
                Sign In to Supplier Portal
            </button>
        </form>

        <!-- 1-Click Supplier Demo Switcher -->
        <div class="pt-4 border-t border-slate-800 space-y-3">
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-amber-500 uppercase tracking-wider">⚡ 1-Click Supplier Logins</span>
                <span class="px-2 py-0.5 rounded-full bg-emerald-950/60 text-emerald-400 text-[10px] font-bold border border-emerald-800/40">Demo Accounts</span>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <button type="button" onclick="fillSupplierLogin('dangote@supplier.com', 'password123')" class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-amber-950/30 border border-slate-700 hover:border-amber-500/50 text-left transition-all text-xs">
                    <span class="block text-base mb-1">🏗️</span>
                    <strong class="block text-white font-bold truncate">Dangote Ltd</strong>
                    <span class="text-[10px] text-slate-400">Cement Supplier</span>
                </button>

                <button type="button" onclick="fillSupplierLogin('steel@supplier.com', 'password123')" class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-amber-950/30 border border-slate-700 hover:border-amber-500/50 text-left transition-all text-xs">
                    <span class="block text-base mb-1">🔩</span>
                    <strong class="block text-white font-bold truncate">African Steel</strong>
                    <span class="text-[10px] text-slate-400">Rebar Supplier</span>
                </button>

                <button type="button" onclick="fillSupplierLogin('blocks@supplier.com', 'password123')" class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-amber-950/30 border border-slate-700 hover:border-amber-500/50 text-left transition-all text-xs">
                    <span class="block text-base mb-1">🧱</span>
                    <strong class="block text-white font-bold truncate">Apex Blocks</strong>
                    <span class="text-[10px] text-slate-400">Masonry Supplier</span>
                </button>
            </div>

            <div class="text-center pt-2">
                <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-brand-400 font-semibold transition-colors">
                    &larr; Return to Main CRM Staff Login
                </a>
            </div>
        </div>
    </div>

    <script>
        function fillSupplierLogin(email, password) {
            document.querySelector('input[name="email"]').value = email;
            document.querySelector('input[name="password"]').value = password;
            document.querySelector('form').submit();
        }
    </script>
</body>
</html>
