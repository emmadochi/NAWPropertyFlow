<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Payment Receipt</title>
    <style>
        @page {
            margin: 0px;
            size: A4 portrait;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            color: #1a1a1a;
            font-size: 11px;
            line-height: 1.55;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* HEADER */
        .hdr { width: 100%; border-collapse: collapse; background: #0f172a; }
        .hdr td { padding: 24px 36px; vertical-align: middle; }
        .hdr-l { width: 55%; }
        .hdr-r { width: 45%; text-align: right; }
        .logo   { height: 36px; display: block; margin-bottom: 6px; }
        .co-name { font-size: 17px; font-weight: bold; color: #ffffff; letter-spacing: 0.2px; }
        .co-sub  { font-size: 8.5px; color: #94a3b8; margin-top: 3px; line-height: 1.6; }
        .rec-lbl { font-size: 21px; font-weight: bold; color: #FEA500; letter-spacing: 0.8px; text-transform: uppercase; }
        .rec-no  { font-size: 10.5px; color: #e2e8f0; font-weight: bold; margin-top: 5px; }
        .rec-sub { font-size: 8.5px; color: #94a3b8; margin-top: 3px; line-height: 1.7; }

        /* ACCENT BAR */
        .bar { height: 4px; background: #FEA500; font-size: 0; }

        /* BODY */
        .wrap { padding: 26px 36px 90px 36px; }

        /* DIVIDERS */
        hr { border: none; border-top: 1px solid #e8e8e4; margin: 18px 0; }

        /* CLIENT / PROPERTY TWO-COL */
        .info-t { width: 100%; border-collapse: collapse; margin-bottom: 22px; }
        .info-t td { vertical-align: top; padding: 0; }
        .ic-l { width: 48%; padding-right: 20px; }
        .ic-r { width: 48%; padding-left: 20px; border-left: 1px solid #e8e8e4; }
        .lbl  { font-size: 8px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; color: #94a3b8; margin-bottom: 8px; }
        .cname { font-size: 13.5px; font-weight: bold; color: #0f172a; margin-bottom: 5px; }
        .cdet  { font-size: 9.5px; color: #555; margin-bottom: 3px; }
        .cdet strong { color: #1a1a1a; }

        /* LINE ITEMS */
        .tbl { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .tbl thead th {
            background: #0f172a;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 12px;
        }
        .tbl tbody td {
            padding: 12px 12px;
            border-bottom: 1px solid #efefed;
            font-size: 10px;
            color: #333;
            vertical-align: top;
        }
        .tbl tbody tr:last-child td { border-bottom: none; }
        .tbl .alt td { background: #fafaf8; }
        .tr { text-align: right; }
        .tc { text-align: center; }
        .d-main { font-weight: bold; color: #0f172a; font-size: 11px; }
        .d-sub  { font-size: 8.5px; color: #888; margin-top: 3px; }

        /* BOTTOM 3-COL */
        .bot { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .b-sig  { width: 36%; vertical-align: top; padding-right: 12px; }
        .b-qr   { width: 24%; vertical-align: top; padding: 0 8px; }
        .b-sum  { width: 40%; vertical-align: top; padding-left: 12px; }

        /* SIGNATURE */
        .sig-wrap { border: 1px solid #e8e8e4; padding: 14px 14px 10px; border-radius: 4px; }
        .sig-ttl  { font-size: 7.5px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; color: #94a3b8; margin-bottom: 28px; }
        .sig-line { border-top: 1px solid #1a1a1a; margin-bottom: 4px; }
        .sig-name { font-size: 8px; color: #666; }
        .stamp    { margin-top: 14px; border: 1px dashed #d0d0cc; height: 42px; border-radius: 3px; }
        .stamp-lbl{ font-size: 7.5px; color: #bbb; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 6px; }

        /* QR */
        .qr-wrap { border: 1px solid #e8e8e4; padding: 12px; border-radius: 4px; text-align: center; background: #fafaf8; }
        .qr-img  { width: 108px; height: 108px; display: block; margin: 0 auto 5px; }
        .qr-lbl  { font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.5px; color: #aaa; }
        .qr-no   { font-size: 7.5px; color: #bbb; margin-top: 2px; }

        /* SUMMARY */
        .sum-wrap { border: 1px solid #e8e8e4; border-radius: 4px; overflow: hidden; }
        .sum-hdr  { background: #0f172a; color: #fff; font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px 12px; }
        .sum-t    { width: 100%; border-collapse: collapse; }
        .sum-t td { padding: 8px 12px; border-bottom: 1px solid #efefed; font-size: 9.5px; }
        .sum-t tr:last-child td { border-bottom: none; }
        .s-lbl { color: #666; width: 55%; }
        .s-val { font-weight: bold; color: #1a1a1a; text-align: right; }
        .s-total td { background: #f5f5f2; border-top: 2px solid #e8e8e4 !important; }
        .s-paid  td { background: #f5f5f2; }
        .s-bal   td { border-top: 2px solid #e8e8e4 !important; }
        .s-acc   { color: #FEA500 !important; }

        /* FOOTER (pinned to bottom of page) */
        .ftr { 
            position: fixed; 
            bottom: 0px; 
            left: 0px; 
            right: 0px; 
            width: 100%; 
            border-collapse: collapse; 
            background: #0f172a; 
        }
        .ftr td { padding: 14px 36px; vertical-align: middle; }
        .f-l { font-size: 8px; color: #64748b; }
        .f-l strong { color: #94a3b8; }
        .f-r { text-align: right; font-size: 8.5px; color: #FEA500; font-weight: bold; }
    </style>
</head>
<body>
@php
    $settings = rescue(fn() => \App\Models\CompanySetting::first(), null);
    $recNo    = $receiptNo ?? ('REC-' . str_pad($milestone->id, 6, '0', STR_PAD_LEFT));
    $paidDate = $milestone->paid_at ? $milestone->paid_at->format('d M Y, h:i A') : date('d M Y, h:i A');
    $coName   = $settings->company_name ?? config('app.name');
    $hasInt   = ($paymentPlan->interest_amount ?? 0) > 0;
    $hasVat   = ($paymentPlan->vat_amount ?? 0) > 0;
    $hasTax   = ($paymentPlan->tax_amount ?? 0) > 0;
    $baseVal  = $paymentPlan->base_deal_value ?? 0;
    $qrUri    = $qrCodeUri ?? null;
@endphp

{{-- HEADER --}}
<table class="hdr"><tr>
    <td class="hdr-l">
        @if($settings && $settings->logo_path && file_exists(public_path('storage/' . $settings->logo_path)))
            <img src="{{ public_path('storage/' . $settings->logo_path) }}" class="logo">
        @endif
        <div class="co-name">{{ $coName }}</div>
        <div class="co-sub">
            {{ $settings->address ?? '' }}<br>
            {{ $settings->phone ?? '' }}@if(($settings->phone ?? '') && ($settings->email ?? '')) &nbsp;&bull;&nbsp; @endif{{ $settings->email ?? '' }}
        </div>
    </td>
    <td class="hdr-r">
        <div class="rec-lbl">Payment Receipt</div>
        <div class="rec-no">{{ $recNo }}</div>
        <div class="rec-sub">
            Date: {{ $paidDate }}<br>
            Method: {{ $milestone->payment_method ?? 'Bank Transfer' }}
            @if(!empty($milestone->bank_reference))
                <br>Ref: {{ $milestone->bank_reference }}
            @endif
        </div>
    </td>
</tr></table>
<div class="bar"></div>

<div class="wrap">

    {{-- CLIENT / PROPERTY --}}
    <table class="info-t"><tr>
        <td class="ic-l">
            <div class="lbl">Received From</div>
            <div class="cname">{{ $lead->full_name }}</div>
            <div class="cdet">{{ $lead->email }}</div>
            <div class="cdet">{{ $lead->phone_number }}</div>
            @if(!empty($lead->address))
                <div class="cdet">{{ $lead->address }}</div>
            @endif
        </td>
        <td class="ic-r">
            <div class="lbl">Property Details</div>
            <div class="cdet">
                <strong>{{ $property->name }}</strong>
                @if($sale->propertyUnit)
                    &mdash; Unit #{{ $sale->propertyUnit->unit_number }}
                @endif
            </div>
            <div class="cdet">{{ $property->location ?? '' }}</div>
            <div class="cdet">Milestone: <strong>{{ $milestone->label }}</strong></div>
            <div class="cdet">
                Plan: <strong>{{ ucfirst($paymentPlan->plan_type ?? 'installment') }}
                @if(!empty($paymentPlan->duration_months))
                    &mdash; {{ $paymentPlan->duration_months }} Months
                @endif
                </strong>
            </div>
        </td>
    </tr></table>

    <hr>

    {{-- LINE ITEMS --}}
    <table class="tbl">
        <thead><tr>
            <th style="width:44%; text-align:left;">Description</th>
            <th style="width:20%; text-align:center;">Plan Type</th>
            <th style="width:18%; text-align:right;">Amount Due</th>
            <th style="width:18%; text-align:right;">Amount Paid</th>
        </tr></thead>
        <tbody>
            <tr>
                <td>
                    <div class="d-main">{{ $milestone->label }}</div>
                    <div class="d-sub">
                        {{ $property->name }}
                        @if($sale->propertyUnit)
                            &mdash; Unit #{{ $sale->propertyUnit->unit_number }}
                        @endif
                    </div>
                    @if(!empty($milestone->bank_reference))
                        <div class="d-sub">Ref: {{ $milestone->bank_reference }}</div>
                    @endif
                </td>
                <td class="tc" style="color:#555; font-size:9.5px;">
                    {{ ucfirst($paymentPlan->plan_type ?? 'installment') }}
                    @if(!empty($paymentPlan->duration_months))
                        <br><strong>{{ $paymentPlan->duration_months }} Months</strong>
                    @endif
                </td>
                <td class="tr">&#8358;{{ number_format($milestone->amount_due, 2) }}</td>
                <td class="tr" style="font-weight:bold; color:#0f172a;">&#8358;{{ number_format($milestone->amount_paid, 2) }}</td>
            </tr>
            @if($hasInt)
            <tr class="alt">
                <td colspan="2" style="font-size:9px; color:#555; font-style:italic;">
                    + Interest Surcharge ({{ $paymentPlan->interest_rate_pct ?? 0 }}%)
                </td>
                <td class="tr" style="font-size:9px; color:#555;">&#8358;{{ number_format($paymentPlan->interest_amount, 2) }}</td>
                <td class="tr" style="font-size:9px; color:#bbb;">&mdash;</td>
            </tr>
            @endif
            @if($hasVat)
            <tr class="alt">
                <td colspan="2" style="font-size:9px; color:#555; font-style:italic;">
                    + Value Added Tax ({{ $paymentPlan->vat_rate_pct ?? '7.5' }}%)
                </td>
                <td class="tr" style="font-size:9px; color:#555;">&#8358;{{ number_format($paymentPlan->vat_amount ?? 0, 2) }}</td>
                <td class="tr" style="font-size:9px; color:#bbb;">&mdash;</td>
            </tr>
            @endif
            @if($hasTax)
            <tr class="alt">
                <td colspan="2" style="font-size:9px; color:#555; font-style:italic;">+ Statutory Tax / WHT</td>
                <td class="tr" style="font-size:9px; color:#555;">&#8358;{{ number_format($paymentPlan->tax_amount ?? 0, 2) }}</td>
                <td class="tr" style="font-size:9px; color:#bbb;">&mdash;</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- BOTTOM: SIGNATURE | QR | TOTALS --}}
    <table class="bot"><tr>

        <td class="b-sig">
            <div class="sig-wrap">
                <div class="sig-ttl">Authorised Signature</div>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $coName }}</div>
                <div class="stamp"><div class="stamp-lbl">Official Stamp</div></div>
            </div>
        </td>

        <td class="b-qr">
            <div class="qr-wrap">
                @if(!empty($qrUri))
                    <img src="{{ $qrUri }}" class="qr-img" alt="QR">
                @else
                    <div style="width:105px;height:105px;background:#f0f0ee;margin:0 auto 5px;"></div>
                @endif
                <div class="qr-lbl">Scan to Verify</div>
                <div class="qr-no">{{ $recNo }}</div>
            </div>
        </td>

        <td class="b-sum">
            <div class="sum-wrap">
                <div class="sum-hdr">Summary</div>
                <table class="sum-t">
                    @if($baseVal > 0 && ($hasInt || $hasVat || $hasTax))
                    <tr>
                        <td class="s-lbl">Base Property Price</td>
                        <td class="s-val">&#8358;{{ number_format($baseVal, 2) }}</td>
                    </tr>
                    @endif
                    @if($hasInt)
                    <tr>
                        <td class="s-lbl">+ Interest ({{ $paymentPlan->interest_rate_pct }}%)</td>
                        <td class="s-val">&#8358;{{ number_format($paymentPlan->interest_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($hasVat)
                    <tr>
                        <td class="s-lbl">+ VAT ({{ $paymentPlan->vat_rate_pct ?? '7.5' }}%)</td>
                        <td class="s-val">&#8358;{{ number_format($paymentPlan->vat_amount ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    @if($hasTax)
                    <tr>
                        <td class="s-lbl">+ Tax / WHT</td>
                        <td class="s-val">&#8358;{{ number_format($paymentPlan->tax_amount ?? 0, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="s-total">
                        <td class="s-lbl" style="font-weight:bold; color:#0f172a;">Total Contract Value</td>
                        <td class="s-val">&#8358;{{ number_format($paymentPlan->total_amount, 2) }}</td>
                    </tr>
                    <tr class="s-paid">
                        <td class="s-lbl" style="font-weight:bold; color:#0f172a;">Paid This Receipt</td>
                        <td class="s-val">&#8358;{{ number_format($milestone->amount_paid, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="s-lbl">Total Paid to Date</td>
                        <td class="s-val">&#8358;{{ number_format($paymentPlan->amount_paid, 2) }}</td>
                    </tr>
                    <tr class="s-bal">
                        <td class="s-lbl s-acc" style="font-weight:bold;">Remaining Balance</td>
                        <td class="s-val s-acc">&#8358;{{ number_format($paymentPlan->balance, 2) }}</td>
                    </tr>
                </table>
            </div>
        </td>

    </tr></table>

</div>

{{-- FOOTER --}}
<table class="ftr"><tr>
    <td class="f-l">
        <strong>{{ $coName }}</strong><br>
        This receipt is electronically generated and serves as official proof of payment.<br>
        Enquiries: {{ $settings->email ?? config('mail.from.address') }}@if($settings->phone ?? '') &nbsp;&bull;&nbsp; {{ $settings->phone }}@endif
    </td>
    <td class="f-r">
        {{ $recNo }}<br>
        <span style="font-size:7.5px; color:#64748b; font-weight:normal;">{{ date('d M Y') }}</span>
    </td>
</tr></table>

</body>
</html>
