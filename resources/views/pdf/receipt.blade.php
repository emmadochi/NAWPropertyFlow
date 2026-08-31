<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Payment Receipt {{ $receiptNo ?? ('REC-' . str_pad($milestone->id, 6, '0', STR_PAD_LEFT)) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.5;
            background: #ffffff;
        }

        /* ── Page wrapper ── */
        .page { padding: 0; }

        /* ── Header band ── */
        .header-band {
            background: #0f172a;
            padding: 22px 30px 18px;
            width: 100%;
        }
        .header-inner { width: 100%; }
        .header-left { float: left; width: 55%; }
        .header-right { float: right; width: 44%; text-align: right; }
        .clear { clear: both; }

        .logo-img { height: 38px; vertical-align: middle; margin-bottom: 5px; }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 0.3px;
        }
        .company-sub {
            font-size: 9px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .receipt-label {
            font-size: 22px;
            font-weight: bold;
            color: #FEA500;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .receipt-meta {
            font-size: 9px;
            color: #94a3b8;
            margin-top: 5px;
            line-height: 1.7;
        }
        .receipt-meta strong { color: #e2e8f0; }

        /* ── Orange stripe ── */
        .orange-stripe {
            background: #FEA500;
            height: 4px;
            width: 100%;
        }

        /* ── Body padding ── */
        .body-wrap { padding: 22px 30px; }

        /* ── Status badge ── */
        .status-bar {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 8px 14px;
            margin-bottom: 18px;
            overflow: hidden;
        }
        .status-icon { float: left; font-size: 13px; color: #16a34a; font-weight: bold; }
        .status-text { float: left; margin-left: 8px; font-size: 11px; color: #15803d; font-weight: bold; }
        .status-date { float: right; font-size: 10px; color: #64748b; margin-top: 1px; }

        /* ── Two-column info grid ── */
        .info-grid { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .info-grid td { vertical-align: top; padding: 0; }
        .info-col-left  { width: 48%; padding-right: 14px; }
        .info-col-right { width: 48%; padding-left: 14px; border-left: 2px solid #f1f5f9; }

        .section-label {
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #FEA500;
            border-bottom: 1px solid #fde68a;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }
        .info-name { font-size: 14px; font-weight: bold; color: #0f172a; margin-bottom: 4px; }
        .info-row  { font-size: 10px; color: #475569; margin-bottom: 3px; }
        .info-row strong { color: #1e293b; }

        /* ── Line items table ── */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .items-table thead tr {
            background: #0f172a;
            color: #ffffff;
        }
        .items-table thead th {
            padding: 8px 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table thead th:first-child { border-radius: 4px 0 0 0; }
        .items-table thead th:last-child  { border-radius: 0 4px 0 0; }
        .items-table tbody tr { background: #ffffff; }
        .items-table tbody tr:nth-child(even) { background: #f8fafc; }
        .items-table tbody td {
            padding: 10px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10.5px;
            vertical-align: top;
        }
        .items-table .desc-main { font-weight: bold; color: #0f172a; }
        .items-table .desc-sub  { font-size: 9px; color: #64748b; margin-top: 2px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .paid-amount { font-weight: bold; color: #059669; font-size: 12px; }

        /* ── Summary + QR bottom section ── */
        .bottom-grid { width: 100%; border-collapse: collapse; }
        .bottom-left  { width: 35%; vertical-align: top; padding-right: 14px; }
        .bottom-center { width: 28%; vertical-align: top; padding: 0 10px; }
        .bottom-right { width: 37%; vertical-align: top; padding-left: 10px; }

        /* QR box */
        .qr-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
            background: #f8fafc;
        }
        .qr-box img { width: 100px; height: 100px; display: block; margin: 0 auto 4px; }
        .qr-label { font-size: 7.5px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Summary totals */
        .totals-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }
        .totals-box .totals-header {
            background: #0f172a;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 10px;
        }
        .totals-row {
            padding: 6px 10px;
            border-bottom: 1px solid #f1f5f9;
            overflow: hidden;
            font-size: 10px;
        }
        .totals-row:last-child { border-bottom: none; }
        .totals-row.highlight { background: #fffcf5; }
        .totals-row.paid-row  { background: #f0fdf4; }
        .totals-row.balance-row { background: #fff7ed; }
        .t-label { float: left; color: #64748b; }
        .t-val   { float: right; font-weight: bold; color: #0f172a; }
        .t-val.green   { color: #059669; }
        .t-val.orange  { color: #FEA500; }
        .t-val.slate   { color: #0f172a; }

        /* Signature section */
        .signature-section {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            background: #fafafa;
        }
        .sig-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            margin-bottom: 8px;
        }
        .sig-line {
            border-top: 1.5px solid #1e293b;
            margin-top: 32px;
            margin-bottom: 4px;
        }
        .sig-name-label {
            font-size: 8px;
            color: #64748b;
        }
        .sig-box { margin-bottom: 14px; }
        .sig-box:last-child { margin-bottom: 0; }

        /* ── Footer ── */
        .footer-band {
            background: #0f172a;
            padding: 12px 30px;
            margin-top: 20px;
        }
        .footer-left  { float: left; font-size: 8.5px; color: #64748b; width: 65%; }
        .footer-right { float: right; font-size: 8.5px; color: #FEA500; text-align: right; width: 34%; }
        .watermark {
            position: fixed;
            bottom: 90px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 48px;
            font-weight: bold;
            color: rgba(16,185,129,0.06);
            text-transform: uppercase;
            letter-spacing: 18px;
            pointer-events: none;
        }
    </style>
</head>
<body>
@php
    $settings   = rescue(fn() => \App\Models\CompanySetting::first(), null);
    $recNo      = $receiptNo ?? ('REC-' . str_pad($milestone->id, 6, '0', STR_PAD_LEFT));
    $paidDate   = $milestone->paid_at ? $milestone->paid_at->format('d M Y, h:i A') : date('d M Y, h:i A');
    $hasInterest = ($paymentPlan->interest_amount ?? 0) > 0 || ($paymentPlan->interest_rate_pct ?? 0) > 0;
    $hasVat      = ($paymentPlan->vat_amount ?? 0) > 0;
    $hasTax      = ($paymentPlan->tax_amount ?? 0) > 0;
    $baseVal     = $paymentPlan->base_deal_value ?? $paymentPlan->total_amount;
    $qrUri       = $qrCodeUri ?? null;
@endphp

<div class="watermark">PAYMENT CONFIRMED</div>

{{-- ═══ HEADER ═══ --}}
<div class="header-band">
    <table class="header-inner" style="border:none; width:100%;">
        <tr>
            <td class="header-left">
                @if($settings && $settings->logo_path && file_exists(public_path('storage/' . $settings->logo_path)))
                    <img src="{{ public_path('storage/' . $settings->logo_path) }}" class="logo-img"><br>
                @endif
                <div class="company-name">{{ $settings->company_name ?? config('app.name') }}</div>
                <div class="company-sub">{{ $settings->address ?? 'Property Development & Sales' }}</div>
                <div class="company-sub" style="margin-top:3px;">{{ $settings->phone ?? '' }} &nbsp;|&nbsp; {{ $settings->email ?? '' }}</div>
            </td>
            <td class="header-right">
                <div class="receipt-label">Payment Receipt</div>
                <div class="receipt-meta">
                    <strong>{{ $recNo }}</strong><br>
                    Date: {{ $paidDate }}<br>
                    Method: {{ $milestone->payment_method ?? 'Bank Transfer' }}<br>
                    @if($milestone->bank_reference)
                    Ref: <strong>{{ $milestone->bank_reference }}</strong>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>
<div class="orange-stripe"></div>

<div class="body-wrap">

    {{-- ── STATUS BAR ── --}}
    <div class="status-bar">
        <span class="status-icon">&#10003;</span>
        <span class="status-text">Payment Verified &amp; Confirmed</span>
        <span class="status-date">Transaction Date: {{ $paidDate }}</span>
        <div class="clear"></div>
    </div>

    {{-- ── RECEIVED FROM / PAYMENT DETAILS ── --}}
    <table class="info-grid">
        <tr>
            <td class="info-col-left">
                <div class="section-label">Received From (Client)</div>
                <div class="info-name">{{ $lead->full_name }}</div>
                <div class="info-row">Email: <strong>{{ $lead->email }}</strong></div>
                <div class="info-row">Phone: <strong>{{ $lead->phone_number }}</strong></div>
                @if(!empty($lead->address))
                <div class="info-row">Address: {{ $lead->address }}</div>
                @endif
            </td>
            <td class="info-col-right">
                <div class="section-label">Property &amp; Transaction Details</div>
                <div class="info-row"><strong>{{ $property->name }}</strong>
                    @if($sale->propertyUnit) &mdash; Unit #{{ $sale->propertyUnit->unit_number }}@endif
                </div>
                <div class="info-row">Location: <strong>{{ $property->location ?? 'N/A' }}</strong></div>
                <div class="info-row">Milestone: <strong>{{ $milestone->label }}</strong></div>
                <div class="info-row">Plan Type: <strong>{{ ucfirst($paymentPlan->plan_type ?? 'installment') }}</strong></div>
                @if($paymentPlan->duration_months)
                <div class="info-row">Duration: <strong>{{ $paymentPlan->duration_months }} Months</strong></div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ── LINE ITEMS TABLE ── --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:46%; text-align:left;">Description</th>
                <th style="width:20%; text-align:center;">Plan Type</th>
                <th style="width:17%; text-align:right;">Amount Due</th>
                <th style="width:17%; text-align:right;">Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="desc-main">{{ $milestone->label }}</div>
                    <div class="desc-sub">{{ $property->name }} @if($sale->propertyUnit)&mdash; Unit #{{ $sale->propertyUnit->unit_number }}@endif</div>
                    @if($milestone->bank_reference)
                    <div class="desc-sub">Bank Ref: {{ $milestone->bank_reference }}</div>
                    @endif
                </td>
                <td class="text-center" style="font-size:9px; color:#475569;">
                    {{ ucfirst($paymentPlan->plan_type ?? 'installment') }}<br>
                    @if($paymentPlan->duration_months)
                    <span style="color:#FEA500; font-weight:bold;">{{ $paymentPlan->duration_months }} Months</span>
                    @endif
                </td>
                <td class="text-right" style="font-size:11px;">&#8358;{{ number_format($milestone->amount_due, 2) }}</td>
                <td class="text-right paid-amount">&#8358;{{ number_format($milestone->amount_paid, 2) }}</td>
            </tr>

            @if($hasInterest)
            <tr>
                <td colspan="2" style="font-size:9px; color:#b45309; padding: 5px 10px;">
                    + Tenure Interest Surcharge ({{ $paymentPlan->interest_rate_pct ?? '0' }}%)
                </td>
                <td colspan="2" class="text-right" style="font-size:9px; color:#b45309; padding: 5px 10px;">
                    +&#8358;{{ number_format($paymentPlan->interest_amount, 2) }}
                </td>
            </tr>
            @endif

            @if($hasVat)
            <tr>
                <td colspan="2" style="font-size:9px; color:#0369a1; padding: 5px 10px;">
                    + Value Added Tax (VAT {{ $paymentPlan->vat_rate_pct ?? '7.5' }}%)
                </td>
                <td colspan="2" class="text-right" style="font-size:9px; color:#0369a1; padding: 5px 10px;">
                    +&#8358;{{ number_format($paymentPlan->vat_amount ?? 0, 2) }}
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- ── BOTTOM: SIGNATURE | QR | TOTALS ── --}}
    <table class="bottom-grid" style="border:none;">
        <tr>

            {{-- SIGNATURE BLOCK --}}
            <td class="bottom-left">
                <div class="signature-section">
                    <div class="sig-title">Authorised Signatures</div>

                    <div class="sig-box">
                        <div class="sig-line"></div>
                        <div class="sig-name-label">Authorised Signatory &mdash; {{ $settings->company_name ?? 'Company' }}</div>
                    </div>

                    <div class="sig-box">
                        <div class="sig-line"></div>
                        <div class="sig-name-label">Client Acknowledgement &mdash; {{ $lead->full_name }}</div>
                    </div>

                    <div style="margin-top:8px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:4px; padding:6px 8px;">
                        <div style="font-size:8px; font-weight:bold; color:#15803d; text-transform:uppercase; margin-bottom:2px;">Official Stamp</div>
                        <div style="height:38px; border:1.5px dashed #86efac; border-radius:4px;"></div>
                    </div>
                </div>
            </td>

            {{-- QR CODE --}}
            <td class="bottom-center">
                <div class="qr-box">
                    @if(!empty($qrUri))
                        <img src="{{ $qrUri }}" alt="QR Code">
                    @else
                        <div style="width:100px;height:100px;background:#f1f5f9;border:1px solid #e2e8f0;margin:0 auto 4px;display:flex;align-items:center;justify-content:center;">
                            <span style="font-size:8px;color:#94a3b8;">QR</span>
                        </div>
                    @endif
                    <div class="qr-label">Scan to Verify</div>
                    <div style="font-size:7px; color:#94a3b8; margin-top:2px;">{{ $recNo }}</div>
                </div>
            </td>

            {{-- TOTALS SUMMARY --}}
            <td class="bottom-right">
                <div class="totals-box">
                    <div class="totals-header">Payment Summary</div>

                    @if($baseVal && ($hasInterest || $hasVat || $hasTax))
                    <div class="totals-row">
                        <span class="t-label">Base Property Price:</span>
                        <span class="t-val slate">&#8358;{{ number_format($baseVal, 2) }}</span>
                        <div class="clear"></div>
                    </div>
                    @endif

                    @if($hasInterest)
                    <div class="totals-row">
                        <span class="t-label" style="color:#b45309;">+ Interest ({{ $paymentPlan->interest_rate_pct }}%):</span>
                        <span class="t-val" style="color:#b45309;">+&#8358;{{ number_format($paymentPlan->interest_amount, 2) }}</span>
                        <div class="clear"></div>
                    </div>
                    @endif

                    @if($hasVat)
                    <div class="totals-row">
                        <span class="t-label" style="color:#0369a1;">+ VAT ({{ $paymentPlan->vat_rate_pct ?? '7.5' }}%):</span>
                        <span class="t-val" style="color:#0369a1;">+&#8358;{{ number_format($paymentPlan->vat_amount ?? 0, 2) }}</span>
                        <div class="clear"></div>
                    </div>
                    @endif

                    <div class="totals-row highlight" style="border-top:2px solid #fde68a;">
                        <span class="t-label" style="font-weight:bold; color:#0f172a;">Total Contract Value:</span>
                        <span class="t-val slate" style="font-size:12px;">&#8358;{{ number_format($paymentPlan->total_amount, 2) }}</span>
                        <div class="clear"></div>
                    </div>

                    <div class="totals-row paid-row">
                        <span class="t-label" style="color:#15803d; font-weight:bold;">&#10003; This Receipt (Paid):</span>
                        <span class="t-val green" style="font-size:12px;">&#8358;{{ number_format($milestone->amount_paid, 2) }}</span>
                        <div class="clear"></div>
                    </div>

                    <div class="totals-row">
                        <span class="t-label">Total Paid to Date:</span>
                        <span class="t-val green">&#8358;{{ number_format($paymentPlan->amount_paid, 2) }}</span>
                        <div class="clear"></div>
                    </div>

                    <div class="totals-row balance-row" style="border-top:2px solid #fed7aa;">
                        <span class="t-label" style="color:#c2410c; font-weight:bold;">Remaining Balance:</span>
                        <span class="t-val orange" style="font-size:12px;">&#8358;{{ number_format($paymentPlan->balance, 2) }}</span>
                        <div class="clear"></div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

</div>{{-- end body-wrap --}}

{{-- ═══ FOOTER ═══ --}}
<div class="footer-band">
    <div class="footer-left">
        <strong style="color:#e2e8f0;">{{ $settings->company_name ?? config('app.name') }}</strong><br>
        This receipt is electronically generated and is valid as official proof of payment.<br>
        For enquiries contact: {{ $settings->email ?? config('mail.from.address') }} | {{ $settings->phone ?? '' }}
    </div>
    <div class="footer-right">
        <strong>{{ $recNo }}</strong><br>
        {{ date('d M Y') }}
    </div>
    <div class="clear"></div>
</div>

</body>
</html>
