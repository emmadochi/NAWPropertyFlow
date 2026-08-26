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
    </div>
</body>
</html>
