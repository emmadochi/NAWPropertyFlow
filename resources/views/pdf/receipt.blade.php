<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Payment Receipt</title>
    <style>
        * { margin: 0; padding: 0; }
        body { font-family: "DejaVu Sans", Arial, sans-serif; color: #1e293b; font-size: 11px; line-height: 1.5; background: #fff; }

        /* ══ HEADER (full-table layout, no floats — DomPDF safe) ══ */
        .hdr-table { width: 100%; background: #0f172a; padding: 20px 28px; border-collapse: collapse; }
        .hdr-left  { width: 54%; vertical-align: middle; padding-right: 10px; }
        .hdr-right { width: 46%; vertical-align: middle; text-align: right; }
        .company-name { font-size: 17px; font-weight: bold; color: #ffffff; }
        .company-sub  { font-size: 8.5px; color: #94a3b8; margin-top: 2px; }
        .logo-img     { height: 36px; vertical-align: middle; margin-bottom: 4px; display: block; }
        .receipt-label { font-size: 21px; font-weight: bold; color: #FEA500; letter-spacing: 0.5px; text-transform: uppercase; }
        .receipt-meta  { font-size: 8.5px; color: #94a3b8; margin-top: 5px; line-height: 1.8; }
        .receipt-meta strong { color: #e2e8f0; }

        /* ══ STRIPE ══ */
        .stripe { background: #FEA500; height: 4px; width: 100%; font-size: 0; line-height: 0; }

        /* ══ BODY ══ */
        .body { padding: 20px 28px; }

        /* Status */
        .status { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 5px; padding: 7px 12px; margin-bottom: 16px; }
        .status-t { width: 100%; border-collapse: collapse; }
        .status-ok { font-size: 11px; font-weight: bold; color: #15803d; }
        .status-dt { font-size: 9px; color: #64748b; text-align: right; }

        /* Info grid */
        .info-t { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .col-l { width: 48%; vertical-align: top; padding-right: 12px; }
        .col-r { width: 48%; vertical-align: top; padding-left: 12px; border-left: 2px solid #f1f5f9; }
        .sec-lbl { font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #FEA500; border-bottom: 1px solid #fde68a; padding-bottom: 3px; margin-bottom: 7px; }
        .info-name { font-size: 13px; font-weight: bold; color: #0f172a; margin-bottom: 3px; }
        .info-row  { font-size: 9.5px; color: #475569; margin-bottom: 2px; }
        .info-row strong { color: #1e293b; }

        /* Items table */
        .items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .items thead tr { background: #0f172a; color: #fff; }
        .items thead th { padding: 8px 9px; font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; }
        .items tbody td { padding: 9px 9px; border-bottom: 1px solid #e2e8f0; font-size: 10px; vertical-align: top; }
        .items tbody tr:nth-child(even) td { background: #f8fafc; }
        .items .sub-row td { background: #fafafa !important; }
        .desc-main { font-weight: bold; color: #0f172a; }
        .desc-sub  { font-size: 8.5px; color: #64748b; margin-top: 2px; }
        .tr { text-align: right; }
        .tc { text-align: center; }
        .paid-val { font-weight: bold; color: #059669; font-size: 11.5px; }
        .int-row td  { background: #fffbeb !important; color: #92400e; font-size: 9.5px; }
        .tax-row td  { background: #f0f9ff !important; color: #0369a1; font-size: 9.5px; }
        .vat-row td  { background: #f0f9ff !important; color: #0369a1; font-size: 9.5px; }
        .charge-lbl  { font-style: italic; }

        /* Bottom 3-col */
        .bottom-t { width: 100%; border-collapse: collapse; }
        .b-sig    { width: 38%; vertical-align: top; padding-right: 10px; }
        .b-qr     { width: 24%; vertical-align: top; padding: 0 8px; }
        .b-totals { width: 38%; vertical-align: top; padding-left: 10px; }

        /* Signature */
        .sig-box { border: 1px solid #e2e8f0; border-radius: 5px; padding: 10px 12px; background: #fafafa; }
        .sig-title { font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; color: #94a3b8; margin-bottom: 10px; }
        .sig-line-wrap { margin-top: 36px; }
        .sig-line  { border-top: 1.5px solid #1e293b; margin-bottom: 4px; }
        .sig-label { font-size: 8px; color: #64748b; }
        .stamp-area { margin-top: 12px; background: #f0fdf4; border: 1.5px dashed #86efac; border-radius: 4px; height: 44px; }
        .stamp-lbl  { font-size: 7.5px; font-weight: bold; color: #16a34a; text-transform: uppercase; padding: 4px 6px; letter-spacing: 0.4px; }

        /* QR */
        .qr-box { border: 1px solid #e2e8f0; border-radius: 5px; padding: 8px; text-align: center; background: #f8fafc; }
        .qr-img { width: 105px; height: 105px; display: block; margin: 0 auto 4px; }
        .qr-lbl { font-size: 7px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .qr-no  { font-size: 6.5px; color: #94a3b8; margin-top: 2px; }

        /* Totals */
        .totals-box { border: 1px solid #e2e8f0; border-radius: 5px; overflow: hidden; }
        .tot-hdr { background: #0f172a; color: #fff; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; padding: 6px 9px; }
        .tot-t { width: 100%; border-collapse: collapse; }
        .tot-t td { padding: 6px 9px; border-bottom: 1px solid #f1f5f9; font-size: 9.5px; }
        .tot-t tr:last-child td { border-bottom: none; }
        .tot-lbl { color: #64748b; width: 58%; }
        .tot-val { font-weight: bold; color: #0f172a; text-align: right; }
        .row-base   td { background: #f8fafc; }
        .row-total  td { background: #fffcf5; border-top: 2px solid #fde68a !important; }
        .row-paid   td { background: #f0fdf4; }
        .row-bal    td { background: #fff7ed; border-top: 2px solid #fed7aa !important; }
        .green  { color: #059669 !important; }
        .orange { color: #FEA500 !important; }

        /* Footer */
        .ftr-t { width: 100%; background: #0f172a; padding: 12px 28px; border-collapse: collapse; margin-top: 18px; }
        .ftr-l { width: 65%; font-size: 8px; color: #64748b; vertical-align: middle; }
        .ftr-r { width: 35%; font-size: 8.5px; color: #FEA500; text-align: right; vertical-align: middle; font-weight: bold; }

        .clear { clear: both; }
    </style>
</head>
<body>
@php
    $settings    = rescue(fn() => \App\Models\CompanySetting::first(), null);
    $recNo       = $receiptNo ?? ('REC-' . str_pad($milestone->id, 6, '0', STR_PAD_LEFT));
    $paidDate    = $milestone->paid_at ? $milestone->paid_at->format('d M Y, h:i A') : date('d M Y, h:i A');
    $hasInterest = ($paymentPlan->interest_amount ?? 0) > 0 || ($paymentPlan->interest_rate_pct ?? 0) > 0;
    $hasVat      = ($paymentPlan->vat_amount ?? 0) > 0;
    $hasTax      = ($paymentPlan->tax_amount ?? 0) > 0;
    $baseVal     = $paymentPlan->base_deal_value ?? $paymentPlan->total_amount;
    $qrUri       = $qrCodeUri ?? null;
    $coName      = $settings->company_name ?? config('app.name');
@endphp

{{-- ══ HEADER ══ --}}
<table class="hdr-table" style="background:#0f172a;">
    <tr>
        <td class="hdr-left">
            @if($settings && $settings->logo_path && file_exists(public_path('storage/' . $settings->logo_path)))
                <img src="{{ public_path('storage/' . $settings->logo_path) }}" class="logo-img">
            @endif
            <div class="company-name">{{ $coName }}</div>
            <div class="company-sub">{{ $settings->address ?? 'Property Development &amp; Sales' }}</div>
            <div class="company-sub" style="margin-top:2px;">
                {{ $settings->phone ?? '' }}@if($settings->phone && $settings->email) &nbsp;|&nbsp; @endif{{ $settings->email ?? '' }}
            </div>
        </td>
        <td class="hdr-right">
            <div class="receipt-label">Payment Receipt</div>
            <div class="receipt-meta">
                <strong>{{ $recNo }}</strong><br>
                Date: {{ $paidDate }}<br>
                Method: {{ $milestone->payment_method ?? 'Bank Transfer' }}
                @if($milestone->bank_reference)<br>Ref: <strong>{{ $milestone->bank_reference }}</strong>@endif
            </div>
        </td>
    </tr>
</table>
<div class="stripe">&nbsp;</div>

<div class="body">

    {{-- ══ STATUS BAR ══ --}}
    <div class="status">
        <table class="status-t">
            <tr>
                <td class="status-ok">&#10003; &nbsp;Payment Verified &amp; Confirmed</td>
                <td class="status-dt">Transaction Date: {{ $paidDate }}</td>
            </tr>
        </table>
    </div>

    {{-- ══ CLIENT / PROPERTY INFO ══ --}}
    <table class="info-t">
        <tr>
            <td class="col-l">
                <div class="sec-lbl">Received From (Client)</div>
                <div class="info-name">{{ $lead->full_name }}</div>
                <div class="info-row">Email: <strong>{{ $lead->email }}</strong></div>
                <div class="info-row">Phone: <strong>{{ $lead->phone_number }}</strong></div>
                @if(!empty($lead->address))
                <div class="info-row">Address: {{ $lead->address }}</div>
                @endif
            </td>
            <td class="col-r">
                <div class="sec-lbl">Property &amp; Transaction Details</div>
                <div class="info-row"><strong>{{ $property->name }}</strong>@if($sale->propertyUnit) &mdash; Unit #{{ $sale->propertyUnit->unit_number }}@endif</div>
                <div class="info-row">Location: <strong>{{ $property->location ?? 'N/A' }}</strong></div>
                <div class="info-row">Milestone: <strong>{{ $milestone->label }}</strong></div>
                <div class="info-row">Plan Type: <strong>{{ ucfirst($paymentPlan->plan_type ?? 'installment') }}</strong></div>
                @if($paymentPlan->duration_months)
                <div class="info-row">Duration: <strong>{{ $paymentPlan->duration_months }} Months</strong></div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ══ LINE ITEMS TABLE ══ --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:44%; text-align:left;">Description</th>
                <th style="width:18%; text-align:center;">Plan Type</th>
                <th style="width:19%; text-align:right;">Amount Due</th>
                <th style="width:19%; text-align:right;">Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            {{-- Main payment row --}}
            <tr>
                <td>
                    <div class="desc-main">{{ $milestone->label }}</div>
                    <div class="desc-sub">{{ $property->name }}@if($sale->propertyUnit) &mdash; Unit #{{ $sale->propertyUnit->unit_number }}@endif</div>
                    @if($milestone->bank_reference)
                    <div class="desc-sub">Bank Ref: {{ $milestone->bank_reference }}</div>
                    @endif
                </td>
                <td class="tc" style="font-size:9px; color:#475569;">
                    {{ ucfirst($paymentPlan->plan_type ?? 'installment') }}
                    @if($paymentPlan->duration_months)<br><span style="color:#FEA500; font-weight:bold;">{{ $paymentPlan->duration_months }} Months</span>@endif
                </td>
                <td class="tr">&#8358;{{ number_format($milestone->amount_due, 2) }}</td>
                <td class="tr paid-val">&#8358;{{ number_format($milestone->amount_paid, 2) }}</td>
            </tr>

            {{-- Interest surcharge row --}}
            @if($hasInterest)
            <tr class="int-row">
                <td colspan="2" class="charge-lbl">+ Tenure Interest Surcharge ({{ $paymentPlan->interest_rate_pct ?? '0' }}%)</td>
                <td class="tr" style="color:#92400e;">&#8358;{{ number_format($paymentPlan->interest_amount, 2) }}</td>
                <td class="tr" style="color:#92400e;">&mdash;</td>
            </tr>
            @endif

            {{-- VAT row --}}
            @if($hasVat)
            <tr class="vat-row">
                <td colspan="2" class="charge-lbl">+ Value Added Tax — VAT ({{ $paymentPlan->vat_rate_pct ?? '7.5' }}%)</td>
                <td class="tr" style="color:#0369a1;">&#8358;{{ number_format($paymentPlan->vat_amount ?? 0, 2) }}</td>
                <td class="tr" style="color:#0369a1;">&mdash;</td>
            </tr>
            @endif

            {{-- Statutory tax row --}}
            @if($hasTax)
            <tr class="tax-row">
                <td colspan="2" class="charge-lbl">+ Statutory Tax / WHT</td>
                <td class="tr" style="color:#0369a1;">&#8358;{{ number_format($paymentPlan->tax_amount ?? 0, 2) }}</td>
                <td class="tr" style="color:#0369a1;">&mdash;</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- ══ BOTTOM: SIGNATURE | QR | TOTALS ══ --}}
    <table class="bottom-t">
        <tr>

            {{-- SIGNATURE --}}
            <td class="b-sig">
                <div class="sig-box">
                    <div class="sig-title">Authorised Signature</div>

                    <div class="sig-line-wrap">
                        <div class="sig-line"></div>
                        <div class="sig-label">Authorised Signatory &mdash; {{ $coName }}</div>
                    </div>

                    <div class="stamp-area">
                        <div class="stamp-lbl">Official Stamp</div>
                    </div>
                </div>
            </td>

            {{-- QR CODE --}}
            <td class="b-qr">
                <div class="qr-box">
                    @if(!empty($qrUri))
                        <img src="{{ $qrUri }}" class="qr-img" alt="QR">
                    @else
                        <div style="width:105px;height:105px;background:#f1f5f9;margin:0 auto 4px;"></div>
                    @endif
                    <div class="qr-lbl">Scan to Verify</div>
                    <div class="qr-no">{{ $recNo }}</div>
                </div>
            </td>

            {{-- TOTALS --}}
            <td class="b-totals">
                <div class="totals-box">
                    <div class="tot-hdr">Payment Summary</div>
                    <table class="tot-t">
                        @if($baseVal && ($hasInterest || $hasVat || $hasTax))
                        <tr class="row-base">
                            <td class="tot-lbl">Base Property Price:</td>
                            <td class="tot-val">&#8358;{{ number_format($baseVal, 2) }}</td>
                        </tr>
                        @endif
                        @if($hasInterest)
                        <tr>
                            <td class="tot-lbl" style="color:#92400e;">+ Interest ({{ $paymentPlan->interest_rate_pct }}%):</td>
                            <td class="tot-val" style="color:#92400e;">+&#8358;{{ number_format($paymentPlan->interest_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($hasVat)
                        <tr>
                            <td class="tot-lbl" style="color:#0369a1;">+ VAT ({{ $paymentPlan->vat_rate_pct ?? '7.5' }}%):</td>
                            <td class="tot-val" style="color:#0369a1;">+&#8358;{{ number_format($paymentPlan->vat_amount ?? 0, 2) }}</td>
                        </tr>
                        @endif
                        @if($hasTax)
                        <tr>
                            <td class="tot-lbl" style="color:#0369a1;">+ Tax / WHT:</td>
                            <td class="tot-val" style="color:#0369a1;">+&#8358;{{ number_format($paymentPlan->tax_amount ?? 0, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="row-total">
                            <td class="tot-lbl" style="font-weight:bold; color:#0f172a;">Total Contract Value:</td>
                            <td class="tot-val" style="font-size:11px;">&#8358;{{ number_format($paymentPlan->total_amount, 2) }}</td>
                        </tr>
                        <tr class="row-paid">
                            <td class="tot-lbl green" style="font-weight:bold;">&#10003; This Receipt (Paid):</td>
                            <td class="tot-val green" style="font-size:11px;">&#8358;{{ number_format($milestone->amount_paid, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="tot-lbl">Total Paid to Date:</td>
                            <td class="tot-val green">&#8358;{{ number_format($paymentPlan->amount_paid, 2) }}</td>
                        </tr>
                        <tr class="row-bal">
                            <td class="tot-lbl orange" style="font-weight:bold;">Remaining Balance:</td>
                            <td class="tot-val orange" style="font-size:11px;">&#8358;{{ number_format($paymentPlan->balance, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>

        </tr>
    </table>

</div>

{{-- ══ FOOTER ══ --}}
<table class="ftr-t" style="background:#0f172a;">
    <tr>
        <td class="ftr-l">
            <strong style="color:#e2e8f0;">{{ $coName }}</strong><br>
            This receipt is electronically generated and valid as official proof of payment.<br>
            Enquiries: {{ $settings->email ?? config('mail.from.address') }}@if($settings->phone ?? '') &nbsp;|&nbsp; {{ $settings->phone }}@endif
        </td>
        <td class="ftr-r">
            {{ $recNo }}<br>
            <span style="font-size:7.5px; color:#64748b;">{{ date('d M Y') }}</span>
        </td>
    </tr>
</table>

</body>
</html>
