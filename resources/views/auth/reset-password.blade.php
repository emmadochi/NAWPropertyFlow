<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Set New Password - {{ config('app.name', 'RICAF PropertyFlow CRM') }}</title>
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
                            500: '#F37021',
                            600: '#ea580c',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans min-h-screen flex items-center justify-center p-4">

    @php $companySetting = \App\Models\CompanySetting::getCached(); @endphp

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden p-8 md:p-10">
        
        <!-- Header & Logo -->
        <div class="flex flex-col items-center mb-8">
            @if($companySetting?->logo_path)
                <img src="{{ asset('storage/' . $companySetting->logo_path) }}" alt="Logo" class="h-14 object-contain mb-4">
            @else
                <div class="w-14 h-14 rounded-2xl bg-orange-50 text-brand-500 flex items-center justify-center font-black text-xl mb-4 border border-orange-100">
                    {{ substr($companySetting->company_name ?? 'CO', 0, 2) }}
                </div>
            @endif
            <h1 class="text-2xl font-extrabold text-gray-900 leading-tight">Create New Password</h1>
            <p class="text-xs text-gray-500 mt-1.5 text-center">Please enter and confirm your new secure password below.</p>
        </div>

        @if($errors->any())
        <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-2xl text-xs space-y-1">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
        @endif

        <!-- Form -->
        <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Account Email</label>
                <input type="email" value="{{ $email }}" disabled
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 font-semibold text-xs cursor-not-allowed">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">New Password</label>
                <input type="password" name="password" id="password" required autofocus
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-2 focus:ring-orange-100 outline-none text-sm text-gray-800 transition-colors"
                       placeholder="••••••••">
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Confirm New Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-2 focus:ring-orange-100 outline-none text-sm text-gray-800 transition-colors"
                       placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-brand-500 to-brand-600 hover:from-brand-600 hover:to-brand-700 text-white font-extrabold py-3.5 px-4 rounded-xl shadow-lg shadow-brand-500/20 transition-all text-sm tracking-wide mt-2">
                Save & Login
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-xs font-bold text-gray-500 hover:text-brand-600 transition-colors">← Back to Login</a>
        </div>

    </div>

</body>
</html>
