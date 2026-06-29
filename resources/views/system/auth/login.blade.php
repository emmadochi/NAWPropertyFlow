<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Admin — NAW PropertyFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --brand: #f97316;
            --brand-dark: #ea6f0d;
            --dark-950: #020617;
            --dark-900: #0f172a;
            --dark-800: #1e293b;
            --dark-700: #334155;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-950);
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .glow {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.15;
            pointer-events: none;
        }
        .glow-1 { width: 600px; height: 600px; background: var(--brand); top: -200px; left: -200px; }
        .glow-2 { width: 400px; height: 400px; background: #7c3aed; bottom: -150px; right: -100px; }

        .card {
            position: relative;
            z-index: 10;
            background: rgba(15,23,42,0.8);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 48px;
            width: 100%;
            max-width: 420px;
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        .logo-icon {
            width: 44px; height: 44px;
            background: var(--brand);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; font-weight: 800; color: white;
        }
        .logo-text { font-size: 18px; font-weight: 700; color: white; }
        .logo-sub  { font-size: 11px; color: #64748b; font-weight: 500; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 32px; }

        h1 { font-size: 24px; font-weight: 700; color: white; margin-bottom: 6px; }
        p  { font-size: 14px; color: #64748b; margin-bottom: 28px; }

        .badge {
            display: inline-block;
            background: rgba(249,115,22,0.15);
            border: 1px solid rgba(249,115,22,0.3);
            color: var(--brand);
            font-size: 11px; font-weight: 600;
            padding: 3px 10px; border-radius: 999px;
            letter-spacing: 0.08em; text-transform: uppercase;
            margin-bottom: 20px;
        }

        label { display: block; font-size: 13px; font-weight: 500; color: #94a3b8; margin-bottom: 6px; }
        input[type=email], input[type=password] {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: white;
            font-size: 15px;
            padding: 13px 16px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: 'Inter', sans-serif;
            margin-bottom: 18px;
        }
        input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(249,115,22,0.15);
        }
        .btn {
            width: 100%;
            background: var(--brand);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 15px; font-weight: 700;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            font-family: 'Inter', sans-serif;
        }
        .btn:hover { background: var(--brand-dark); transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }

        .error {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.25);
            color: #fca5a5;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 18px;
        }
        .remember {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: #64748b;
            margin-bottom: 20px;
            cursor: pointer;
        }
        .remember input { width: auto; margin: 0; }
    </style>
</head>
<body>
    <div class="glow glow-1"></div>
    <div class="glow glow-2"></div>

    <div class="card">
        <div class="logo">
            <div class="logo-icon">N</div>
            <span class="logo-text">NAW PropertyFlow</span>
        </div>
        <div class="logo-sub">SaaS System Administration</div>

        <span class="badge">⚡ Master Control</span>
        <h1>System Admin Login</h1>
        <p>Secure access for NAW World Technologies team only.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        @if (session('success'))
            <div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.25);color:#86efac;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:18px;">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('system.login.submit') }}">
            @csrf
            <div>
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@nawworld.com">
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••••••">
            </div>
            <label class="remember">
                <input type="checkbox" name="remember"> Keep me signed in
            </label>
            <button type="submit" class="btn">Sign In to System Panel</button>
        </form>
    </div>
</body>
</html>
