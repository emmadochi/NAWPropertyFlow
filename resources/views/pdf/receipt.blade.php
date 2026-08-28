<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="utf-8">
    <title>Payment Receipt #{{ $milestone->id }}</title>
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #1e293b; line-height: 1.5; margin: 0; padding: 20px; font-size: 13px; }
        .receipt-header { border-bottom: 3px solid #FEA500; padding-bottom: 20px; margin-bottom: 30px; }
        .company-title { font-size: 24px; font-weight: bold; color: #0f172a; }
        .company-accent { color: #FEA500; }
        .receipt-title { font-size: 20px; text-transform: uppercase; color: #475569; font-weight: 700; text-align: right; }
        .grid { width: 100%; margin-bottom: 30px; }
        .grid td { vertical-align: top; }
        .col-50 { width: 50%; }
        .section-title { font-size: 12px; font-weight: bold; text-transform: uppercase; color: #64748b; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 10px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .info-table th { background-color: #f8fafc; text-align: left; padding: 10px; font-weight: bold; font-size: 11px; color: #64748b; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        .info-table td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .total-box { float: right; width: 320px; background-color: #fffcf5; border: 1px solid #ffe6b3; border-radius: 8px; padding: 15px; margin-top: 20px; }
        .total-row { width: 100%; margin-bottom: 8px; }
        .total-row:last-child { margin-bottom: 0; font-weight: bold; font-size: 15px; border-top: 1px solid #ffe6b3; padding-top: 8px; }
        .total-label { display: inline-block; width: 160px; color: #64748b; }
        .total-val { text-align: right; display: inline-block; width: 140px; color: #0f172a; }
        .total-accent { color: #FEA500 !important; }
        .footer { margin-top: 100px; border-top: 1px solid #e2e8f0; padding-top: 20px; font-size: 10px; color: #94a3b8; text-align: center; }
        .stamp { border: 2px dashed #FEA500; color: #FEA500; font-size: 16px; font-weight: bold; text-transform: uppercase; padding: 8px 15px; display: inline-block; transform: rotate(-5deg); margin-top: 20px; border-radius: 5px; }
    </style>
</head>
<body>
    @php
        $settings = rescue(fn() => \App\Models\CompanySetting::first(), null);
    @endphp
    <table class="grid" style="border: none;">
        <tr>
            <td class="col-50">
                <div class="company-title">
                    @if($settings && $settings->logo_path && file_exists(public_path('storage/' . $settings->logo_path)))
                        <img src="{{ public_path('storage/' . $settings->logo_path) }}" style="height: 35px; vertical-align: middle; margin-right: 8px;">
                    @endif
                    {{ $settings->company_name ?? config('app.name') }}
                </div>
                <div style="font-size: 12px; color: #64748b; margin-top: 5px;">
                    {{ $settings->address ?? '' }}<br>
                    {{ $settings->email ?? config('mail.from.address') }} | {{ $settings->phone ?? '' }}
                </div>
            </td>
            <td class="col-50" style="text-align: right;">
                <div class="receipt-title">Payment Receipt</div>
                <div style="font-size: 12px; color: #64748b; margin-top: 5px;">
                    Receipt No: REC-{{ str_pad($milestone->id, 6, '0', STR_PAD_LEFT) }}<br>
                    Date: {{ $milestone->paid_at ? $milestone->paid_at->format('d M Y, h:i A') : date('d M Y') }}
                </div>
            </td>
        </tr>
    </table>

    <div class="receipt-header"></div>

    <table class="grid" style="border: none; margin-bottom: 20px;">
        <tr>
            <td class="col-50" style="padding-right: 20px;">
                <div class="section-title">Received From</div>
                <div style="font-size: 15px; font-weight: bold; color: #0f172a;">{{ $lead->full_name }}</div>
                <div style="color: #475569; margin-top: 4px;">
                    Email: {{ $lead->email }}<br>
                    Phone: {{ $lead->phone_number }}
                    @if(!empty($lead->address))
                    <br>Address: {{ $lead->address }}
                    @endif
                </div>
            </td>
            <td class="col-50">
                <div class="section-title">Payment Details</div>
                <div style="color: #475569;">
                    Property: <strong>{{ $property->name }}</strong><br>
                    Milestone Tranche: {{ $milestone->label }}<br>
                    Payment Method: Bank Transfer<br>
                    Reference: <strong>{{ $milestone->bank_reference ?? 'N/A' }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <thead>
            <tr>
                <th style="width: 45%;">Description</th>
                <th style="width: 25%; text-align: right;">Amount Due</th>
                <th style="width: 30%; text-align: right;">Amount Paid (This Receipt)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $milestone->label }}</strong><br>
                    <span style="font-size: 11px; color: #64748b;">
                        Property: {{ $property->name }} @if(!empty($sale->propertyUnit)) - Unit #{{ $sale->propertyUnit->unit_number }} @endif
                    </span>
                </td>
                <td style="text-align: right;">₦{{ number_format($milestone->amount_due, 2) }}</td>
                <td style="text-align: right; font-weight: bold; color: #059669;">₦{{ number_format($milestone->amount_paid, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @php
        $hasInterest = ($paymentPlan->interest_amount && $paymentPlan->interest_amount > 0) || ($paymentPlan->interest_rate_pct && $paymentPlan->interest_rate_pct > 0);
        $hasVat = ($paymentPlan->vat_amount && $paymentPlan->vat_amount > 0) || ($sale->vat_amount && $sale->vat_amount > 0);
        $hasTax = ($paymentPlan->tax_amount && $paymentPlan->tax_amount > 0) || ($sale->tax_amount && $sale->tax_amount > 0);
    @endphp

    <table class="grid" style="border: none;">
        <tr>
            <td class="col-50" style="padding-right: 15px;">
                <div class="stamp">Payment Confirmed</div>

                @if($hasInterest || $hasVat || $hasTax)
                <div style="margin-top: 20px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; font-size: 11px; color: #475569;">
                    <strong style="color: #0f172a; display: block; margin-bottom: 6px; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px;">Contract Pricing Structure:</strong>
                    
                    @if($paymentPlan->base_deal_value && $paymentPlan->base_deal_value > 0)
                    <div style="margin-bottom: 4px; overflow: hidden;">
                        <span>Base Property Value:</span>
                        <strong style="float: right; color: #1e293b;">₦{{ number_format($paymentPlan->base_deal_value, 2) }}</strong>
                    </div>
                    @endif

                    @if($hasInterest)
                    <div style="margin-bottom: 4px; color: #b45309; overflow: hidden;">
                        <span>+ Tenure Interest ({{ $paymentPlan->interest_rate_pct ?? '0' }}% Surcharge):</span>
                        <strong style="float: right;">+₦{{ number_format($paymentPlan->interest_amount, 2) }}</strong>
                    </div>
                    @endif

                    @if($hasVat)
                    <div style="margin-bottom: 4px; color: #0369a1; overflow: hidden;">
                        <span>+ Value Added Tax (VAT {{ $paymentPlan->vat_rate_pct ?? '7.5' }}%):</span>
                        <strong style="float: right;">+₦{{ number_format($paymentPlan->vat_amount ?? $sale->vat_amount, 2) }}</strong>
                    </div>
                    @endif

                    @if($hasTax)
                    <div style="margin-bottom: 4px; color: #4338ca; overflow: hidden;">
                        <span>Statutory Tax / WHT:</span>
                        <strong style="float: right;">₦{{ number_format($paymentPlan->tax_amount ?? $sale->tax_amount, 2) }}</strong>
                    </div>
                    @endif
                </div>
                @endif
            </td>
            <td class="col-50">
                <div class="total-box">
                    @if($paymentPlan->base_deal_value && $paymentPlan->base_deal_value > 0 && ($hasInterest || $hasVat || $hasTax))
                    <div class="total-row" style="font-size: 11px;">
                        <span class="total-label" style="color: #64748b;">Base Price:</span>
                        <span class="total-val">₦{{ number_format($paymentPlan->base_deal_value, 2) }}</span>
                    </div>
                    @endif

                    @if($hasInterest)
                    <div class="total-row" style="font-size: 11px; color: #b45309;">
                        <span class="total-label" style="color: #b45309;">+ Interest ({{ $paymentPlan->interest_rate_pct }}%):</span>
                        <span class="total-val" style="color: #b45309;">+₦{{ number_format($paymentPlan->interest_amount, 2) }}</span>
                    </div>
                    @endif

                    @if($hasVat)
                    <div class="total-row" style="font-size: 11px; color: #0369a1;">
                        <span class="total-label" style="color: #0369a1;">+ VAT ({{ $paymentPlan->vat_rate_pct ?? '7.5' }}%):</span>
                        <span class="total-val" style="color: #0369a1;">+₦{{ number_format($paymentPlan->vat_amount ?? $sale->vat_amount, 2) }}</span>
                    </div>
                    @endif

                    @if($hasTax)
                    <div class="total-row" style="font-size: 11px; color: #4338ca;">
                        <span class="total-label" style="color: #4338ca;">Tax:</span>
                        <span class="total-val" style="color: #4338ca;">₦{{ number_format($paymentPlan->tax_amount ?? $sale->tax_amount, 2) }}</span>
                    </div>
                    @endif

                    <div class="total-row" style="border-top: 1px solid #ffe6b3; padding-top: 6px; margin-top: 4px;">
                        <span class="total-label" style="font-weight: bold; color: #0f172a;">Total Contract Value:</span>
                        <span class="total-val" style="font-weight: bold; color: #0f172a;">₦{{ number_format($paymentPlan->total_amount, 2) }}</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label" style="color: #059669; font-weight: bold;">Amount Paid (Receipt):</span>
                        <span class="total-val" style="color: #059669; font-weight: bold;">₦{{ number_format($milestone->amount_paid, 2) }}</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Total Paid to Date:</span>
                        <span class="total-val">₦{{ number_format($paymentPlan->amount_paid, 2) }}</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label total-accent">Remaining Balance:</span>
                        <span class="total-val total-accent">₦{{ number_format($paymentPlan->balance, 2) }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Thank you for choosing {{ $settings ? $settings->company_name : config('app.name') }}. This receipt is automatically generated and digitally stamped as proof of payment.<br>
        For inquiries, contact {{ $settings->email ?? config('mail.from.address') }}.
    </div>
</body>
</html>
