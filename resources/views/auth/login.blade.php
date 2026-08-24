<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - NAW PropertyFlow CRM</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Static Pure CSS Framework (No JS dependency) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #0B2545 0%, #134074 50%, #081C33 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #1e293b;
        }
        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 440px;
            border-radius: 1.5rem;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.15);
            position: relative;
            z-index: 10;
        }
        .logo-box {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #FEA500 0%, #D4AF37 100%);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem auto;
            box-shadow: 0 10px 15px -3px rgba(254, 165, 0, 0.3);
        }
        .logo-box svg { width: 28px; height: 28px; color: #0B2545; }
        .form-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; text-align: center; font-family: 'Space Grotesk', sans-serif; }
        .form-subtitle { font-size: 0.825rem; color: #64748b; text-align: center; margin-top: 0.35rem; margin-bottom: 1.75rem; }
        .form-group { margin-bottom: 1.15rem; }
        .form-label { display: block; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; margin-bottom: 0.4rem; }
        .form-input {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 0.75rem;
            border: 1.5px solid #e2e8f0;
            background-color: #f8fafc;
            font-size: 0.9rem;
            color: #0f172a;
            outline: none;
            transition: all 0.2s ease;
        }
        .form-input:focus {
            border-color: #FEA500;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(254, 165, 0, 0.15);
        }
        .btn-submit {
            width: 100%;
            padding: 0.9rem 1.25rem;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, #FEA500 0%, #E09200 100%);
            color: #ffffff;
            font-weight: 800;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: none;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(254, 165, 0, 0.4);
            transition: all 0.2s ease;
            margin-top: 0.5rem;
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -5px rgba(254, 165, 0, 0.5);
        }
        .quick-roles {
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid #f1f5f9;
        }
        .quick-roles-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        .quick-roles-title {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        .badge-sandbox {
            font-size: 0.65rem;
            font-weight: 700;
            color: #059669;
            background-color: #ecfdf5;
            padding: 0.2rem 0.5rem;
            border-radius: 9999px;
            border: 1px solid #d1fae5;
        }
        .role-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
        }
        .role-btn {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.65rem;
            padding: 0.5rem 0.25rem;
            text-align: center;
            font-size: 0.7rem;
            font-weight: 700;
            color: #334155;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .role-btn:hover {
            background-color: #fffbeb;
            border-color: #fde68a;
            color: #b45309;
            transform: translateY(-1px);
        }
        .role-icon { font-size: 1rem; display: block; margin-bottom: 0.2rem; }
    </style>
</head>
<body>

    <div class="login-card">
        
        <!-- Logo -->
        <div class="logo-box">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
        </div>

        <h1 class="form-title">NAW PropertyFlow</h1>
        <p class="form-subtitle">Enterprise Real Estate Operating System</p>

        @if(session('success'))
        <div style="background-color: #ecfdf5; color: #065f46; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #a7f3d0; font-size: 0.8rem; margin-bottom: 1rem;">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->has('email'))
        <div style="background-color: #fff1f2; color: #9f1239; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid #fecdd3; font-size: 0.8rem; margin-bottom: 1rem;">
            {{ $errors->first('email') }}
        </div>
        @endif

        <!-- Form -->
        <form action="{{ route('login') }}" method="POST" id="loginForm">
            @csrf
            
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email', 'superadmin@propertyflow.com') }}" required autofocus class="form-input" placeholder="e.g. superadmin@propertyflow.com">
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <label for="password" class="form-label" style="margin-bottom: 0;">Password</label>
                    <a href="{{ route('password.request') }}" style="font-size: 0.72rem; font-weight: 700; color: #d97706; text-decoration: none;">Forgot?</a>
                </div>
                <input type="password" name="password" id="password" value="password" required class="form-input" placeholder="••••••••">
            </div>

            <div style="display: flex; align-items: center; margin-bottom: 1.25rem;">
                <input type="checkbox" name="remember" id="remember" checked style="width: 1rem; height: 1rem; accent-color: #FEA500; cursor: pointer;">
                <label for="remember" style="margin-left: 0.5rem; font-size: 0.8rem; color: #64748b; cursor: pointer;">Keep me logged in</label>
            </div>

            <button type="submit" class="btn-submit">
                Sign In to Demo Workspace
            </button>
        </form>

        <!-- 1-Click Role Login Selector -->
        <div class="quick-roles">
            <div class="quick-roles-header">
                <span class="quick-roles-title">⚡ Instant Demo Switcher</span>
                <span class="badge-sandbox">Interactive Demo</span>
            </div>
            <div class="role-grid">
                <button type="button" onclick="fillAndLogin('superadmin@propertyflow.com', 'password')" class="role-btn">
                    <span class="role-icon">👑</span>
                    Super Admin
                </button>
                <button type="button" onclick="fillAndLogin('manager@propertyflow.com', 'password')" class="role-btn">
                    <span class="role-icon">📊</span>
                    Sales Manager
                </button>
                <button type="button" onclick="fillAndLogin('se1@propertyflow.com', 'password')" class="role-btn">
                    <span class="role-icon">💼</span>
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
