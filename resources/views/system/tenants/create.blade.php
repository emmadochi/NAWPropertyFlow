<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New Company — NAW PropertyFlow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --brand: #f97316; --brand-dark: #ea6f0d;
            --dark-950: #020617; --dark-900: #0f172a; --dark-800: #1e293b; --dark-700: #334155;
            --text: #e2e8f0; --text-muted: #64748b;
        }
        body { font-family: 'Inter', sans-serif; background: var(--dark-950); color: var(--text); min-height: 100vh; }

        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; width: 260px;
            background: var(--dark-900); border-right: 1px solid rgba(255,255,255,0.06);
            display: flex; flex-direction: column; z-index: 100;
        }
        .sidebar-logo { padding: 24px 20px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .logo-icon { width: 40px; height: 40px; background: var(--brand); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; color: white; flex-shrink: 0; }
        .logo-name { font-size: 15px; font-weight: 700; color: white; }
        .logo-tag  { font-size: 10px; color: var(--brand); font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }

        .sidebar-section { padding: 20px 12px 8px; font-size: 10px; font-weight: 700; color: var(--text-muted); letter-spacing: 0.12em; text-transform: uppercase; }
        .sidebar-nav { padding: 0 12px; flex: 1; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 14px; font-weight: 500; color: #94a3b8; cursor: pointer; transition: all 0.15s; margin-bottom: 2px; text-decoration: none; }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: white; }
        .nav-item.active { background: rgba(249,115,22,0.15); color: var(--brand); }
        .nav-icon { width: 18px; text-align: center; flex-shrink: 0; }

        .sidebar-footer { padding: 16px 12px; border-top: 1px solid rgba(255,255,255,0.06); }
        .admin-info { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .admin-avatar { width: 36px; height: 36px; background: var(--brand); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; color: white; flex-shrink: 0; }
        .admin-name { font-size: 13px; font-weight: 600; color: white; }
        .admin-role { font-size: 11px; color: var(--text-muted); }
        .logout-btn { display: flex; align-items: center; gap: 8px; width: 100%; padding: 9px 12px; border-radius: 8px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #fca5a5; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.15s; font-family: 'Inter', sans-serif; }
        .logout-btn:hover { background: rgba(239,68,68,0.2); }

        .main { margin-left: 260px; min-height: 100vh; }
        .topbar { padding: 20px 32px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .topbar-title { font-size: 22px; font-weight: 700; color: white; }
        .topbar-sub { font-size: 14px; color: var(--text-muted); margin-top: 2px; }
        .back-btn { display: inline-flex; align-items: center; gap: 8px; color: #94a3b8; font-size: 14px; font-weight: 500; text-decoration: none; padding: 9px 16px; border-radius: 9px; border: 1px solid rgba(255,255,255,0.1); transition: all 0.15s; }
        .back-btn:hover { background: rgba(255,255,255,0.05); color: white; }

        .content { padding: 32px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; max-width: 860px; }
        .form-section {
            background: var(--dark-900); border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px; padding: 28px;
        }
        .section-title { font-size: 15px; font-weight: 700; color: white; margin-bottom: 4px; }
        .section-sub { font-size: 13px; color: var(--text-muted); margin-bottom: 22px; }

        label { display: block; font-size: 13px; font-weight: 500; color: #94a3b8; margin-bottom: 6px; }
        .form-group { margin-bottom: 18px; }
        input[type=text], input[type=email], input[type=password], select {
            width: 100%; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px; color: white; font-size: 14px; padding: 12px 14px;
            outline: none; transition: border-color 0.2s, box-shadow 0.2s;
            font-family: 'Inter', sans-serif; appearance: none;
        }
        input:focus, select:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(249,115,22,0.15); }
        input::placeholder { color: #475569; }
        .error-msg { font-size: 12px; color: #fca5a5; margin-top: 5px; }

        .subdomain-wrapper { display: flex; align-items: center; }
        .subdomain-prefix { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-right: none; border-radius: 10px 0 0 10px; padding: 12px 14px; font-size: 14px; color: #64748b; white-space: nowrap; }
        .subdomain-wrapper input { border-radius: 0 10px 10px 0; }
        .subdomain-suffix { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-left: none; border-radius: 0 10px 10px 0; padding: 12px 14px; font-size: 14px; color: #64748b; white-space: nowrap; }
        .subdomain-inner { display: flex; align-items: center; }
        .subdomain-inner input { border-radius: 0; border-right: none; }

        .tier-cards { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }
        .tier-card { border: 2px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 14px; cursor: pointer; transition: all 0.15s; }
        .tier-card:hover { border-color: rgba(249,115,22,0.4); }
        .tier-card input[type=radio] { display: none; }
        .tier-card.selected { border-color: var(--brand); background: rgba(249,115,22,0.08); }
        .tier-name { font-size: 13px; font-weight: 700; color: white; margin-bottom: 3px; }
        .tier-desc { font-size: 11px; color: var(--text-muted); }

        .submit-section { max-width: 860px; margin-top: 24px; display: flex; align-items: center; gap: 14px; }
        .btn-submit {
            background: var(--brand); color: white; border: none; border-radius: 12px;
            padding: 14px 32px; font-size: 15px; font-weight: 700;
            cursor: pointer; transition: all 0.15s; font-family: 'Inter', sans-serif;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-submit:hover { background: var(--brand-dark); transform: translateY(-1px); }
        .submit-note { font-size: 13px; color: var(--text-muted); }

        .info-box { background: rgba(249,115,22,0.08); border: 1px solid rgba(249,115,22,0.2); border-radius: 10px; padding: 14px 16px; margin-top: 20px; font-size: 13px; color: #fdba74; line-height: 1.5; }
        .info-box strong { display: block; margin-bottom: 4px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">N</div>
            <div>
                <div class="logo-name">NAW PropertyFlow</div>
                <div class="logo-tag">⚡ SaaS Control Panel</div>
            </div>
        </div>
        <div class="sidebar-nav">
            <div class="sidebar-section">Management</div>
            <a href="{{ route('system.dashboard') }}" class="nav-item">
                <span class="nav-icon">🏢</span> All Companies
            </a>
            <a href="{{ route('system.tenants.create') }}" class="nav-item active">
                <span class="nav-icon">➕</span> Register Company
            </a>
        </div>
        <div class="sidebar-footer">
            <div class="admin-info">
                <div class="admin-avatar">{{ strtoupper(substr(auth('system_admin')->user()->name, 0, 1)) }}</div>
                <div>
                    <div class="admin-name">{{ auth('system_admin')->user()->name }}</div>
                    <div class="admin-role">System Administrator</div>
                </div>
            </div>
            <form method="POST" action="{{ route('system.logout') }}">
                @csrf
                <button type="submit" class="logout-btn"><span>🚪</span> Sign Out</button>
            </form>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Register New Company</div>
                <div class="topbar-sub">Provision a new isolated CRM environment for a real estate agency</div>
            </div>
            <a href="{{ route('system.dashboard') }}" class="back-btn">← Back to Dashboard</a>
        </div>

        <div class="content">
            <form method="POST" action="{{ route('system.tenants.store') }}" id="registerForm">
                @csrf

                <div class="form-grid">
                    <!-- Company Info -->
                    <div class="form-section">
                        <div class="section-title">🏢 Company Information</div>
                        <div class="section-sub">Details about the real estate agency</div>

                        <div class="form-group">
                            <label>Company / Agency Name *</label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="e.g. Lagos Prime Realty Ltd" required>
                            @error('company_name') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label>Subdomain *</label>
                            <div class="subdomain-inner">
                                <div class="subdomain-prefix">http://</div>
                                <input type="text" name="subdomain" value="{{ old('subdomain') }}" placeholder="lagosprimeRealty" required pattern="[a-zA-Z0-9\-]+" oninput="updatePreview(this.value)">
                                <div class="subdomain-suffix">.localhost:8000</div>
                            </div>
                            @error('subdomain') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label>Package Tier *</label>
                            <div class="tier-cards" id="tierCards">
                                <label class="tier-card" id="card-starter">
                                    <input type="radio" name="package_tier" value="starter" {{ old('package_tier','starter') === 'starter' ? 'checked' : '' }} onchange="selectTier('starter')">
                                    <div class="tier-name">Starter</div>
                                    <div class="tier-desc">CRM only</div>
                                </label>
                                <label class="tier-card" id="card-professional">
                                    <input type="radio" name="package_tier" value="professional" {{ old('package_tier') === 'professional' ? 'checked' : '' }} onchange="selectTier('professional')">
                                    <div class="tier-name">Professional</div>
                                    <div class="tier-desc">CRM + Marketing + Docs</div>
                                </label>
                                <label class="tier-card" id="card-enterprise">
                                    <input type="radio" name="package_tier" value="enterprise" {{ old('package_tier') === 'enterprise' ? 'checked' : '' }} onchange="selectTier('enterprise')">
                                    <div class="tier-name">Enterprise</div>
                                    <div class="tier-desc">All features + HR</div>
                                </label>
                            </div>
                            @error('package_tier') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Admin Info -->
                    <div class="form-section">
                        <div class="section-title">👤 Company Admin Account</div>
                        <div class="section-sub">First login credentials for the agency owner</div>

                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="admin_name" value="{{ old('admin_name') }}" placeholder="e.g. Chidi Okonkwo" required>
                            @error('admin_name') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="admin@lagosprimeRealty.com" required>
                            @error('admin_email') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label>Temporary Password *</label>
                            <input type="password" name="admin_password" placeholder="Min 8 characters" required minlength="8">
                            @error('admin_password') <div class="error-msg">{{ $message }}</div> @enderror
                        </div>

                        <div class="info-box">
                            <strong>🚀 What happens next?</strong>
                            A new isolated MySQL database will be created instantly.
                            All CRM tables (leads, properties, HR, etc.) will be migrated automatically.
                            The agency admin can log in at their subdomain immediately.
                        </div>
                    </div>
                </div>

                <div class="submit-section">
                    <button type="submit" class="btn-submit">
                        <span>🏗️</span> Provision Company
                    </button>
                    <span class="submit-note">This will create a new MySQL database and run all migrations automatically.</span>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Highlight selected tier card
        function selectTier(tier) {
            document.querySelectorAll('.tier-card').forEach(c => c.classList.remove('selected'));
            document.getElementById('card-' + tier).classList.add('selected');
        }
        // Init on load
        const checked = document.querySelector('input[name="package_tier"]:checked');
        if (checked) selectTier(checked.value);
    </script>
</body>
</html>
