<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $companyName }} - Retail Sales Team Performance Report ({{ $periodLabel }})</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 8mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.4;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .screen-toolbar {
            background-color: #0f172a;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: {{ $isBuckcrest ? '#946E19' : '#0284c7' }};
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: {{ $isBuckcrest ? '#785712' : '#0369a1' }};
        }

        .btn-secondary {
            background-color: #334155;
            color: #f1f5f9;
        }
        .btn-secondary:hover {
            background-color: #475569;
        }

        .report-page {
            max-width: 1400px;
            margin: 20px auto;
            background: #ffffff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        /* Letterhead Header */
        .report-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 2px solid {{ $isBuckcrest ? '#946E19' : '#0f172a' }};
            padding-bottom: 16px;
            margin-bottom: 16px;
        }

        .brand-title {
            font-size: 22px;
            font-weight: 900;
            color: {{ $isBuckcrest ? '#946E19' : '#0f172a' }};
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }

        .report-title {
            font-size: 14px;
            font-weight: 800;
            color: #334155;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-meta {
            text-align: right;
            font-size: 10px;
            color: #64748b;
        }

        .period-pill {
            display: inline-block;
            background-color: {{ $isBuckcrest ? '#fef3c7' : '#e0f2fe' }};
            color: {{ $isBuckcrest ? '#92400e' : '#0369a1' }};
            border: 1px solid {{ $isBuckcrest ? '#fde68a' : '#bae6fd' }};
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 11px;
            margin-bottom: 4px;
        }

        /* Executive KPI Summary Bar */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }

        .kpi-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            background-color: #f8fafc;
        }

        .kpi-label {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
        }

        .kpi-value {
            font-size: 16px;
            font-weight: 900;
            color: #0f172a;
            margin-top: 2px;
        }

        .kpi-highlight {
            background-color: {{ $isBuckcrest ? '#fef3c7' : '#ecfdf5' }};
            border-color: {{ $isBuckcrest ? '#fde68a' : '#a7f3d0' }};
        }
        .kpi-highlight .kpi-value {
            color: {{ $isBuckcrest ? '#92400e' : '#047857' }};
        }

        /* Scorecard Matrix Table */
        table.matrix-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-bottom: 20px;
        }

        table.matrix-table th, 
        table.matrix-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 6px;
            vertical-align: middle;
        }

        table.matrix-table thead tr.super-header th {
            background-color: #e2e8f0;
            color: #0f172a;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
            text-align: center;
        }

        table.matrix-table thead tr.sub-header th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 800;
            font-size: 8.5px;
            text-transform: uppercase;
        }

        table.matrix-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        table.matrix-table tbody tr:hover {
            background-color: #f1f5f9;
        }

        table.matrix-table tfoot tr {
            background-color: #e2e8f0;
            font-weight: 900;
            border-top: 2px solid #64748b;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }

        /* Sign-off Blocks */
        .signatures-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-top: 36px;
            padding-top: 16px;
            border-top: 1px dashed #cbd5e1;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            width: 80%;
            margin: 36px auto 8px auto;
            border-bottom: 1.5px solid #475569;
        }

        .signature-role {
            font-weight: 800;
            font-size: 10px;
            color: #0f172a;
            text-transform: uppercase;
        }

        .signature-sub {
            font-size: 9px;
            color: #64748b;
        }

        @media print {
            .screen-toolbar {
                display: none !important;
            }
            body {
                background: #ffffff;
            }
            .report-page {
                margin: 0;
                padding: 0;
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
            }
            table.matrix-table {
                font-size: 8.5px;
            }
            table.matrix-table th, table.matrix-table td {
                padding: 4px 4px;
            }
        }
    </style>
</head>
<body>

    <!-- On-screen Action Ribbon -->
    <div class="screen-toolbar no-print">
        <div>
            <span style="font-weight: 800; font-size: 13px;">Executive Landscape Report View</span>
            <span style="color: #94a3b8; font-size: 11px; margin-left: 8px;">Formatted for landscape print and formal board presentation</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-primary">
                <span>🖨️</span> Print / Save PDF
            </button>
            <a href="{{ route('reports.retail.export', request()->all()) }}" class="btn btn-secondary">
                <span>📥</span> Export Excel / CSV
            </a>
            <button onclick="window.close()" class="btn btn-secondary">
                <span>✖️</span> Close
            </button>
        </div>
    </div>

    <div class="report-page">
        <!-- Letterhead Header -->
        <div class="report-header">
            <div>
                <div class="brand-title">{{ $companyName }}</div>
                <div class="report-title">Retail Sales Team - Performance Scorecard Matrix</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                    Department of Sales & Business Development • Weekly Field Audit & Cash Inflow Report
                </div>
            </div>
            <div class="report-meta">
                <div class="period-pill">{{ $periodLabel }}</div>
                <div>Generated: <strong>{{ now()->format('d M Y, h:i A') }}</strong></div>
                <div>Audited By: <strong>{{ $currentUser->name }}</strong></div>
                <div>Branch: <strong>{{ $branchId ? ($branches->firstWhere('id', $branchId)?->name ?? 'All Branches') : 'All Branches' }}</strong></div>
            </div>
        </div>

        <!-- Executive KPI Overview -->
        <div class="kpi-grid">
            <div class="kpi-card kpi-highlight">
                <div class="kpi-label">Total Cash Realized</div>
                <div class="kpi-value font-mono">₦{{ number_format($aggregates['total_realized_revenue'], 0) }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">New Deals Closed</div>
                <div class="kpi-value">{{ number_format($aggregates['actual_payments_count']) }} <span style="font-size: 10px; color: #64748b;">(₦{{ number_format($aggregates['actual_payments_value'], 0) }})</span></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Milestone Top-ups</div>
                <div class="kpi-value">{{ number_format($aggregates['topups_count']) }} <span style="font-size: 10px; color: #64748b;">(₦{{ number_format($aggregates['topups_value'], 0) }})</span></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Site Inspections</div>
                <div class="kpi-value">{{ number_format($aggregates['inspections_count']) }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Verified Calls / WhatsApp</div>
                <div class="kpi-value">{{ number_format($aggregates['calls_count']) }} / {{ number_format($aggregates['whatsapp_count']) }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Contacts Generated</div>
                <div class="kpi-value">{{ number_format($aggregates['new_contacts_count']) }} <span style="font-size: 10px; color: #64748b;">({{ $aggregates['contacts_phone_count'] }} ph)</span></div>
            </div>
        </div>

        <!-- Matrix Table (BSTAN Group Replica) -->
        <table class="matrix-table">
            <thead>
                <tr class="super-header">
                    <th colspan="2">CONSULTANT PROFILE</th>
                    <th colspan="2">LOCATION(S) / EVENT(S)</th>
                    <th colspan="4">PAYMENTS / REVENUE (₦)</th>
                    <th colspan="2">ACTIVITIES</th>
                    <th colspan="4">ENGAGEMENTS (VERIFIED)</th>
                    <th>SUMMARY REPORT</th>
                </tr>
                <tr class="sub-header">
                    <th style="width: 25px;" class="text-center">S/N</th>
                    <th style="width: 140px;">Sales Consultant</th>
                    <th style="width: 160px;">Locations Visited / Canvassed</th>
                    <th style="width: 55px;" class="text-center">Office Visits</th>
                    <th style="width: 95px;" class="text-right">New Sales (₦)</th>
                    <th style="width: 90px;" class="text-right">Top-ups (₦)</th>
                    <th style="width: 90px;" class="text-right">Expected (₦)</th>
                    <th style="width: 100px;" class="text-right">Total Realized (₦)</th>
                    <th style="width: 55px;" class="text-center">Inspections</th>
                    <th style="width: 120px;">Estates Inspected</th>
                    <th style="width: 45px;" class="text-center">Calls</th>
                    <th style="width: 45px;" class="text-center">WA</th>
                    <th style="width: 40px;" class="text-center">SMS</th>
                    <th style="width: 65px;" class="text-center">Contacts</th>
                    <th>Observations & Follow-up Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($matrixRows as $idx => $row)
                @php
                    $u = $row['user'];
                @endphp
                <tr>
                    <td class="text-center" style="font-weight: 700; color: #64748b;">{{ $idx + 1 }}</td>
                    <td style="font-weight: 700;">
                        {{ $u->name }}
                        <div style="font-size: 8px; font-weight: 500; color: #64748b;">{{ $u->branch ? $u->branch->name : 'Main Office' }}</div>
                    </td>
                    <td>{{ $row['canvassing_locations'] }}</td>
                    <td class="text-center font-mono">{{ $row['office_visits_count'] ?: '—' }}</td>
                    
                    <!-- Payments -->
                    <td class="text-right font-mono" style="font-weight: 600;">
                        {{ $row['actual_payments_value'] > 0 ? number_format($row['actual_payments_value'], 0) : '—' }}
                    </td>
                    <td class="text-right font-mono" style="font-weight: 600;">
                        {{ $row['topups_value'] > 0 ? number_format($row['topups_value'], 0) : '—' }}
                    </td>
                    <td class="text-right font-mono" style="color: #64748b;">
                        {{ $row['expected_payments_notes'] ?: ($row['expected_payments_count'] > 0 ? $row['expected_payments_count'] . ' pending' : '—') }}
                    </td>
                    <td class="text-right font-mono" style="font-weight: 800; background-color: #f0fdf4;">
                        {{ $row['total_revenue'] > 0 ? number_format($row['total_revenue'], 0) : '0' }}
                    </td>

                    <!-- Activities -->
                    <td class="text-center font-mono" style="font-weight: 700;">{{ $row['inspections_count'] ?: '—' }}</td>
                    <td>{{ $row['project_locations_inspected'] }}</td>

                    <!-- Engagements -->
                    <td class="text-center font-mono">{{ $row['calls_count'] }}</td>
                    <td class="text-center font-mono">{{ $row['whatsapp_count'] }}</td>
                    <td class="text-center font-mono">{{ $row['sms_count'] }}</td>
                    <td class="text-center font-mono">
                        {{ $row['new_contacts_count'] }}
                        <div style="font-size: 7.5px; color: #64748b;">({{ $row['contacts_phone_count'] }} ph)</div>
                    </td>

                    <!-- Summary -->
                    <td style="font-size: 8.5px; color: #334155;">{{ $row['observations_recommendations'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="15" class="text-center" style="padding: 24px; color: #94a3b8;">No consultant records found for this period.</td>
                </tr>
                @endforelse
            </tbody>
            @if(count($matrixRows) > 0)
            <tfoot>
                <tr>
                    <td colspan="2" style="text-transform: uppercase;">TOTAL TEAM AGGREGATE</td>
                    <td style="font-size: 8px; color: #64748b;">ALL TERRITORIES</td>
                    <td class="text-center font-mono">{{ number_format($aggregates['office_visits_count']) }}</td>
                    <td class="text-right font-mono">₦{{ number_format($aggregates['actual_payments_value'], 0) }}</td>
                    <td class="text-right font-mono">₦{{ number_format($aggregates['topups_value'], 0) }}</td>
                    <td class="text-right font-mono" style="color: #64748b;">{{ $aggregates['expected_payments_count'] }} pipeline</td>
                    <td class="text-right font-mono" style="font-size: 10.5px; background-color: #dcfce7;">₦{{ number_format($aggregates['total_realized_revenue'], 0) }}</td>
                    <td class="text-center font-mono">{{ number_format($aggregates['inspections_count']) }}</td>
                    <td style="font-size: 8px; color: #64748b;">ALL SITES</td>
                    <td class="text-center font-mono">{{ number_format($aggregates['calls_count']) }}</td>
                    <td class="text-center font-mono">{{ number_format($aggregates['whatsapp_count']) }}</td>
                    <td class="text-center font-mono">{{ number_format($aggregates['sms_count']) }}</td>
                    <td class="text-center font-mono">{{ number_format($aggregates['new_contacts_count']) }}</td>
                    <td style="font-size: 8px; color: #64748b; font-style: italic;">Verified CRM aggregate transaction metrics.</td>
                </tr>
            </tfoot>
            @endif
        </table>

        <!-- Executive Signatures Block -->
        <div class="signatures-grid">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-role">Sales Consultant / Lead Auditor</div>
                <div class="signature-sub">Date & Verification Stamp</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-role">Head of Retail Sales</div>
                <div class="signature-sub">Performance Review & Sign-off</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-role">Managing Director / CEO</div>
                <div class="signature-sub">Executive Approval</div>
            </div>
        </div>
    </div>

</body>
</html>
