<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Forgot Password - NAW PropertyFlow CRM</title>
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
</head>
<body class="bg-gray-50 font-sans min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden p-8 md:p-10">
        
        <!-- Header -->
        <div class="flex flex-col items-center mb-8">
            <span class="p-3 bg-brand-100 text-brand-500 rounded-2xl mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-5 4a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
            </span>
            <h1 class="text-2xl font-bold text-gray-900 leading-tight">Reset Password</h1>
            <p class="text-sm text-gray-500 mt-1.5 text-center">Enter your email address and we'll send you a password reset link.</p>
        </div>

        @if(session('success'))
        <div class="mb-4 bg-emerald-50 text-emerald-700 px-4 py-3 rounded-xl border border-emerald-100 text-sm font-medium">
            {{ session('success') }}
        </div>
        @endif

        <!-- Form -->
        <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Email Address</label>
                <input type="email" name="email" id="email" required autofocus
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none text-sm text-gray-800 transition-colors"
                       placeholder="e.g. admin@propertyflow.com">
            </div>

            <button type="submit" class="w-full bg-brand-500 hover:bg-brand-600 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-brand-500/20 hover:shadow-brand-600/30 transition-all text-sm tracking-wide">
                Send Reset Link
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm font-bold text-brand-600 hover:text-brand-700 transition-colors">Return to Login</a>
        </div>

    </div>

</body>
</html>
