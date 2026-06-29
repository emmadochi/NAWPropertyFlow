<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Dashboard — NAW PropertyFlow SaaS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --brand: #f97316;
            --brand-dark: #ea6f0d;
            --brand-light: #fed7aa;
            --dark-950: #020617;
            --dark-900: #0f172a;
            --dark-800: #1e293b;
            --dark-700: #334155;
            --dark-600: #475569;
            --text: #e2e8f0;
            --text-muted: #64748b;
        }
        body { font-family: 'Inter', sans-serif; background: var(--dark-950); color: var(--text); min-height: 100vh; }

        /* ── Sidebar ── */
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; width: 260px;
            background: var(--dark-900);
            border-right: 1px solid rgba(255,255,255,0.06);
            display: flex; flex-direction: column;
            z-index: 100;
        }
        .sidebar-logo {
            padding: 24px 20px;
            display: flex; align-items: center; gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .logo-icon { width: 40px; height: 40px; background: var(--brand); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; color: white; flex-shrink: 0; }
        .logo-info { min-width: 0; }
        .logo-name  { font-size: 15px; font-weight: 700; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .logo-tag   { font-size: 10px; color: var(--brand); font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }

        .sidebar-section { padding: 20px 12px 8px; font-size: 10px; font-weight: 700; color: var(--text-muted); letter-spacing: 0.12em; text-transform: uppercase; }
        .sidebar-nav { padding: 0 12px; flex: 1; }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 10px;
            font-size: 14px; font-weight: 500; color: #94a3b8;
            cursor: pointer; transition: all 0.15s;
            margin-bottom: 2px; text-decoration: none;
        }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: white; }
        .nav-item.active { background: rgba(249,115,22,0.15); color: var(--brand); }
        .nav-icon { width: 18px; text-align: center; flex-shrink: 0; }

        .sidebar-footer { padding: 16px 12px; border-top: 1px solid rgba(255,255,255,0.06); }
        .admin-info { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .admin-avatar { width: 36px; height: 36px; background: var(--brand); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; color: white; flex-shrink: 0; }
        .admin-name { font-size: 13px; font-weight: 600; color: white; }
        .admin-role { font-size: 11px; color: var(--text-muted); }
        .logout-btn {
            display: flex; align-items: center; gap: 8px;
            width: 100%; padding: 9px 12px; border-radius: 8px;
            background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);
            color: #fca5a5; font-size: 13px; font-weight: 500;
            cursor: pointer; transition: all 0.15s; font-family: 'Inter', sans-serif;
        }
        .logout-btn:hover { background: rgba(239,68,68,0.2); }

        /* ── Main ── */
        .main { margin-left: 260px; min-height: 100vh; }
        .topbar {
            padding: 20px 32px; display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .topbar-title { font-size: 22px; font-weight: 700; color: white; }
        .topbar-sub   { font-size: 14px; color: var(--text-muted); margin-top: 2px; }
        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--brand); color: white; border: none;
            padding: 11px 22px; border-radius: 10px; font-size: 14px; font-weight: 600;
            cursor: pointer; transition: all 0.15s; text-decoration: none; font-family: 'Inter', sans-serif;
        }
        .btn-primary:hover { background: var(--brand-dark); transform: translateY(-1px); }

        .content { padding: 28px 32px; }

        /* ── Alerts ── */
        .alert-success {
            background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2);
            color: #86efac; border-radius: 12px; padding: 14px 18px;
            font-size: 14px; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;
        }

        /* ── Stats Grid ── */
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 28px; }
        .stat-card {
            background: var(--dark-900); border: 1px solid rgba(255,255,255,0.07);
            border-radius: 14px; padding: 20px;
        }
        .stat-label { font-size: 12px; font-weight: 500; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px; }
        .stat-value { font-size: 30px; font-weight: 800; color: white; line-height: 1; }
        .stat-card.orange { border-color: rgba(249,115,22,0.2); }
        .stat-card.orange .stat-value { color: var(--brand); }

        /* ── Table ── */
        .table-card {
            background: var(--dark-900); border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px; overflow: hidden;
        }
        .table-header {
            padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex; align-items: center; justify-content: space-between;
        }
        .table-title { font-size: 16px; font-weight: 700; color: white; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 12px 20px; text-align: left; font-size: 11px; font-weight: 600; color: var(--text-muted); letter-spacing: 0.08em; text-transform: uppercase; border-bottom: 1px solid rgba(255,255,255,0.06); }
        td { padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.04); font-size: 14px; color: #cbd5e1; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,0.02); }

        .company-name { font-weight: 600; color: white; }
        .badge {
            display: inline-block; padding: 3px 10px; border-radius: 999px;
            font-size: 11px; font-weight: 600; letter-spacing: 0.05em; text-transform: capitalize;
        }
        .badge-starter      { background: rgba(100,116,139,0.2); color: #94a3b8; }
        .badge-professional { background: rgba(59,130,246,0.18); color: #93c5fd; }
        .badge-enterprise   { background: rgba(168,85,247,0.15); color: #c4b5fd; }
        .badge-active       { background: rgba(34,197,94,0.15); color: #86efac; }
        .badge-suspended    { background: rgba(239,68,68,0.15); color: #fca5a5; }

        .actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .btn-sm {
            padding: 6px 12px; border-radius: 7px; font-size: 12px; font-weight: 600;
            cursor: pointer; border: 1px solid; transition: all 0.15s; font-family: 'Inter', sans-serif;
            text-decoration: none; display: inline-block;
        }
        .btn-suspend  { background: rgba(249,115,22,0.1);  border-color: rgba(249,115,22,0.3);  color: #fdba74; }
        .btn-suspend:hover  { background: rgba(249,115,22,0.2); }
        .btn-activate { background: rgba(34,197,94,0.1);   border-color: rgba(34,197,94,0.3);   color: #86efac; }
        .btn-activate:hover { background: rgba(34,197,94,0.2); }
        .btn-delete   { background: rgba(239,68,68,0.1);   border-color: rgba(239,68,68,0.3);   color: #fca5a5; }
        .btn-delete:hover   { background: rgba(239,68,68,0.2); }
        .btn-upgrade  { background: rgba(168,85,247,0.12); border-color: rgba(168,85,247,0.3);  color: #c4b5fd; position: relative; }
        .btn-upgrade:hover  { background: rgba(168,85,247,0.22); }
        .login-link { color: var(--brand); font-size: 12px; text-decoration: none; font-weight: 500; }
        .login-link:hover { text-decoration: underline; }

        /* ── Plan Upgrade Dropdown ── */
        .upgrade-dropdown {
            display: none; position: absolute; top: calc(100% + 6px); right: 0;
            background: #1e293b; border: 1px solid rgba(255,255,255,0.12);
            border-radius: 12px; padding: 14px; min-width: 220px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5); z-index: 999;
        }
        .upgrade-dropdown.open { display: block; }
        .dropdown-title { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px; }
        .plan-option {
            display: flex; align-items: center; justify-content: space-between;
            padding: 9px 12px; border-radius: 8px; margin-bottom: 6px;
            cursor: pointer; border: 1px solid transparent; transition: all 0.15s;
            background: rgba(255,255,255,0.03);
        }
        .plan-option:last-child { margin-bottom: 0; }
        .plan-option:hover { background: rgba(255,255,255,0.07); border-color: rgba(255,255,255,0.1); }
        .plan-option.current { border-color: rgba(249,115,22,0.4); background: rgba(249,115,22,0.08); cursor: default; }
        .plan-name { font-size: 13px; font-weight: 600; color: #e2e8f0; }
        .plan-desc { font-size: 10px; color: #64748b; margin-top: 1px; }
        .plan-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 99px; }
        .plan-badge.starter      { background: rgba(100,116,139,0.25); color: #94a3b8; }
        .plan-badge.professional { background: rgba(59,130,246,0.2);   color: #93c5fd; }
        .plan-badge.enterprise   { background: rgba(168,85,247,0.2);   color: #c4b5fd; }

        .alert-info {
            background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.25);
            color: #93c5fd; border-radius: 12px; padding: 14px 18px;
            font-size: 14px; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;
        }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
        .empty-state-icon { font-size: 40px; margin-bottom: 12px; }
        .empty-state-title { font-size: 16px; font-weight: 600; color: #94a3b8; margin-bottom: 6px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">N</div>
            <div class="logo-info">
                <div class="logo-name">NAW PropertyFlow</div>
                <div class="logo-tag">⚡ SaaS Control Panel</div>
            </div>
        </div>

        <div class="sidebar-nav">
            <div class="sidebar-section">Management</div>
            <a href="{{ route('system.dashboard') }}" class="nav-item active">
                <span class="nav-icon">🏢</span> All Companies
            </a>
            <a href="{{ route('system.tenants.create') }}" class="nav-item">
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
                <button type="submit" class="logout-btn">
                    <span>🚪</span> Sign Out
                </button>
            </form>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title">Company Dashboard</div>
                <div class="topbar-sub">{{ now()->format('l, F j Y') }} · {{ $stats['total'] }} companies registered</div>
            </div>
            <a href="{{ route('system.tenants.create') }}" class="btn-primary">
                <span>➕</span> Register New Company
            </a>
        </div>

        <div class="content">
            @if (session('success'))
                <div class="alert-success">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif
            @if (session('info'))
                <div class="alert-info">
                    <span>ℹ️</span> {{ session('info') }}
                </div>
            @endif

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card orange">
                    <div class="stat-label">Total Companies</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Active</div>
                    <div class="stat-value">{{ $stats['active'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Starter</div>
                    <div class="stat-value">{{ $stats['starter'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Professional</div>
                    <div class="stat-value">{{ $stats['professional'] }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Enterprise</div>
                    <div class="stat-value">{{ $stats['enterprise'] }}</div>
                </div>
            </div>

            <!-- Companies Table -->
            <div class="table-card">
                <div class="table-header">
                    <div class="table-title">Registered Companies</div>
                </div>

                @if ($tenants->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">🏗️</div>
                        <div class="empty-state-title">No companies yet</div>
                        <p style="font-size:13px; margin-top: 6px;">Click "Register New Company" to onboard your first client.</p>
                    </div>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Package</th>
                                <th>Admin</th>
                                <th>Subdomain</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tenants as $tenant)
                                <tr>
                                    <td><span class="company-name">{{ $tenant->company_name }}</span></td>
                                    <td>
                                        <span class="badge badge-{{ $tenant->package_tier }}">{{ $tenant->tier_label }}</span>
                                    </td>
                                    <td>
                                        <div style="font-size:13px; font-weight:500; color:#e2e8f0;">{{ $tenant->admin_name }}</div>
                                        <div style="font-size:12px; color:#64748b;">{{ $tenant->admin_email }}</div>
                                    </td>
                                    <td>
                                        <a href="http://{{ $tenant->domains->first()?->domain }}:8000/login" target="_blank" class="login-link">
                                            {{ $tenant->domains->first()?->domain }} ↗
                                        </a>
                                    </td>
                                    <td>
                                        @if ($tenant->is_active)
                                            <span class="badge badge-active">● Active</span>
                                        @else
                                            <span class="badge badge-suspended">⏸ Suspended</span>
                                        @endif
                                    </td>
                                    <td style="font-size:13px; color:#64748b;">{{ $tenant->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="actions">
                                            {{-- Change Plan Dropdown --}}
                                            <div style="position:relative;" id="upgrade-wrap-{{ $tenant->id }}">
                                                <button type="button"
                                                    class="btn-sm btn-upgrade"
                                                    onclick="toggleUpgradeDropdown('{{ $tenant->id }}')"
                                                    title="Change Plan">
                                                    ⚡ Plan
                                                </button>
                                                <div class="upgrade-dropdown" id="upgrade-dropdown-{{ $tenant->id }}">
                                                    <div class="dropdown-title">Change Plan for {{ $tenant->company_name }}</div>
                                                    @foreach(['starter' => ['label'=>'Starter','desc'=>'Core CRM','badge'=>'starter'], 'professional' => ['label'=>'Professional','desc'=>'+ Marketing & Docs','badge'=>'professional'], 'enterprise' => ['label'=>'Enterprise','desc'=>'+ HR & File Manager','badge'=>'enterprise']] as $tier => $meta)
                                                        @if($tenant->package_tier === $tier)
                                                            <div class="plan-option current">
                                                                <div>
                                                                    <div class="plan-name">{{ $meta['label'] }}</div>
                                                                    <div class="plan-desc">{{ $meta['desc'] }}</div>
                                                                </div>
                                                                <span class="plan-badge {{ $meta['badge'] }}">Current</span>
                                                            </div>
                                                        @else
                                                            <form method="POST" action="{{ route('system.tenants.upgrade', $tenant) }}" style="margin:0">
                                                                @csrf @method('PATCH')
                                                                <input type="hidden" name="package_tier" value="{{ $tier }}">
                                                                <button type="submit" class="plan-option" style="width:100%;text-align:left;background:none;font-family:'Inter',sans-serif;" onclick="return confirm('Change {{ $tenant->company_name }} to the {{ $meta[\"label\"] }} plan?')">
                                                                    <div>
                                                                        <div class="plan-name">{{ $meta['label'] }}</div>
                                                                        <div class="plan-desc">{{ $meta['desc'] }}</div>
                                                                    </div>
                                                                    <span class="plan-badge {{ $meta['badge'] }}">Switch</span>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>

                                            {{-- Suspend / Activate --}}
                                            <form method="POST" action="{{ route('system.tenants.toggle', $tenant) }}" style="display:inline;">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn-sm {{ $tenant->is_active ? 'btn-suspend' : 'btn-activate' }}">
                                                    {{ $tenant->is_active ? 'Suspend' : 'Activate' }}
                                                </button>
                                            </form>

                                            {{-- Delete --}}
                                            <form method="POST" action="{{ route('system.tenants.destroy', $tenant) }}" style="display:inline;" onsubmit="return confirm('This will permanently DELETE the database for {{ $tenant->company_name }}. Are you absolutely sure?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-sm btn-delete">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </main>

    <script>
        function toggleUpgradeDropdown(tenantId) {
            // Close all other open dropdowns first
            document.querySelectorAll('.upgrade-dropdown.open').forEach(function(el) {
                if (el.id !== 'upgrade-dropdown-' + tenantId) {
                    el.classList.remove('open');
                }
            });
            // Toggle the clicked one
            const dd = document.getElementById('upgrade-dropdown-' + tenantId);
            if (dd) dd.classList.toggle('open');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('[id^="upgrade-wrap-"]')) {
                document.querySelectorAll('.upgrade-dropdown.open').forEach(function(el) {
                    el.classList.remove('open');
                });
            }
        });

        // Auto-dismiss flash alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert-success, .alert-info').forEach(function(el) {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>
