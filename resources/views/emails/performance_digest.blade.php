<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8fafc; margin: 0; padding: 0; }
        .wrapper { max-width: 640px; margin: 30px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
        .header { background: linear-gradient(135deg, #FEA500 0%, #ff7b00 100%); padding: 32px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; font-weight: 800; }
        .header p { color: rgba(255,255,255,.8); margin: 8px 0 0; font-size: 13px; }
        .body { padding: 32px; }
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin: 24px 0; }
        .stat-box { background: #f1f5f9; border-radius: 12px; padding: 16px; text-align: center; }
        .stat-box .val { font-size: 28px; font-weight: 800; color: #0f172a; }
        .stat-box .lbl { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .05em; margin-top: 4px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        .table th { background: #f8fafc; padding: 10px 14px; text-align: left; font-size: 11px; text-transform: uppercase; color: #94a3b8; letter-spacing: .05em; }
        .table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700; }
        .badge-gold { background: #fef3c7; color: #92400e; }
        .badge-silver { background: #f1f5f9; color: #475569; }
        .badge-bronze { background: #ffedd5; color: #9a3412; }
        .footer { padding: 20px 32px; border-top: 1px solid #f1f5f9; text-align: center; color: #94a3b8; font-size: 12px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>📊 Weekly Performance Digest</h1>
        <p>{{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }} — Team Performance Report</p>
    </div>
    <div class="body">
        <p style="color:#334155">Hi {{ $manager->name }},</p>
        <p style="color:#64748b;font-size:14px">Here's a summary of your team's performance for <strong>{{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</strong>.</p>

        <div class="stat-grid">
            <div class="stat-box">
                <div class="val">{{ $totalSales }}</div>
                <div class="lbl">Total Sales</div>
            </div>
            <div class="stat-box">
                <div class="val" style="color:#FEA500">{{ $leaderboard->count() }}</div>
                <div class="lbl">Active Agents</div>
            </div>
            <div class="stat-box">
                <div class="val" style="font-size:18px;color:#059669">₦{{ number_format($totalRevenue/1000000,1) }}M</div>
                <div class="lbl">Total Revenue</div>
            </div>
        </div>

        @if($topPerformer)
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:16px;margin-bottom:24px">
            <p style="font-size:12px;font-weight:700;color:#92400e;text-transform:uppercase;margin:0 0 6px">🏆 Top Performer</p>
            <p style="font-size:18px;font-weight:800;color:#0f172a;margin:0">{{ $topPerformer->name }}</p>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0">{{ $topPerformer->sales_count }} sales · ₦{{ number_format($topPerformer->revenue_total) }} revenue</p>
        </div>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Agent</th>
                    <th>Sales</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaderboard->take(10) as $i => $exec)
                <tr>
                    <td>
                        @if($i === 0) <span class="badge badge-gold">🥇 1st</span>
                        @elseif($i === 1) <span class="badge badge-silver">🥈 2nd</span>
                        @elseif($i === 2) <span class="badge badge-bronze">🥉 3rd</span>
                        @else #{{ $i + 1 }}
                        @endif
                    </td>
                    <td style="font-weight:600;color:#0f172a">{{ $exec->name }}</td>
                    <td>{{ $exec->sales_count }}</td>
                    <td style="font-weight:700;color:#FEA500">₦{{ number_format($exec->revenue_total) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="footer">
        This is an automated digest from NAW PropertyFlow CRM · {{ now()->format('d M Y') }}
    </div>
</div>
</body>
</html>
