<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>Payslip - {{ $payslip->user->name }} - {{ $payslip->payrollBatch->title }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'DejaVu Sans', sans-serif; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1e293b; background: #fff; padding: 30px; }
        .payslip-card { max-width: 800px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #F37021; padding-bottom: 20px; margin-bottom: 25px; }
        .brand h1 { font-size: 20px; font-weight: 800; color: #0f172a; }
        .brand p { font-size: 11px; color: #64748b; margin-top: 4px; }
        .doc-title { text-align: right; }
        .doc-title h2 { font-size: 18px; font-weight: 800; color: #F37021; }
        .doc-title p { font-size: 11px; color: #64748b; margin-top: 2px; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #f8fafc; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; }
        .meta-item { font-size: 12px; }
        .meta-item strong { display: inline-block; width: 120px; color: #475569; }
        .breakdown-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding-bottom: 8px; margin-bottom: 12px; }
        .earnings-title { color: #059669; border-bottom: 1.5px solid #a7f3d0; }
        .deductions-title { color: #e11d48; border-bottom: 1.5px solid #fecdd3; }
        .item-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 12px; }
        .item-row.total { font-weight: 700; border-top: 1px solid #cbd5e1; margin-top: 8px; padding-top: 8px; }
        .net-pay-banner { background: #fff7ed; border: 2px solid #fdba74; border-radius: 8px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .net-pay-banner h3 { font-size: 13px; font-weight: 800; color: #9a3412; text-transform: uppercase; }
        .net-pay-banner .amount { font-size: 22px; font-weight: 900; color: #ea580c; }
        .bank-info { font-size: 11px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 15px; display: flex; justify-content: space-between; }
        .footer-note { font-size: 10px; color: #94a3b8; text-align: center; margin-top: 25px; }
        @media print {
            body { padding: 0; }
            .payslip-card { border: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="max-width: 800px; margin: 0 auto 15px; text-align: right;">
        <button onclick="window.print()" style="background: #F37021; color: #fff; font-weight: bold; border: none; padding: 8px 18px; border-radius: 8px; cursor: pointer;">
            Print / Save as PDF
        </button>
    </div>

    <div class="payslip-card">
        <!-- Header -->
        <div class="header">
            <div class="brand">
                <h1>{{ $companySetting->company_name ?? config('app.name') }}</h1>
                <p>{{ $companySetting->address ?? 'Lagos, Nigeria' }}</p>
                <p>{{ $companySetting->email ?? 'info@ricafltd.com' }} • {{ $companySetting->phone ?? '' }}</p>
            </div>
            <div class="doc-title">
                <h2>OFFICIAL PAYSLIP</h2>
                <p>{{ $payslip->payrollBatch->title }}</p>
                <p>Generated: {{ now()->format('d M Y') }}</p>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="meta-grid">
            <div class="meta-item">
                <p><strong>Employee Name:</strong> {{ $payslip->user->name }}</p>
                <p style="margin-top: 4px;"><strong>Department:</strong> {{ $payslip->user->departmentRelation->name ?? 'General' }}</p>
                <p style="margin-top: 4px;"><strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $payslip->user->role)) }}</p>
            </div>
            <div class="meta-item">
                <p><strong>Bank:</strong> {{ $payslip->bank_name ?? 'N/A' }}</p>
                <p style="margin-top: 4px;"><strong>Account No:</strong> {{ $payslip->account_number ?? 'N/A' }}</p>
                <p style="margin-top: 4px;"><strong>Payment Status:</strong> <span style="font-weight: bold; text-transform: uppercase; color: {{ $payslip->status === 'paid' ? '#059669' : '#d97706' }}">{{ $payslip->status }}</span></p>
            </div>
        </div>

        <!-- Breakdown Grid -->
        <div class="breakdown-grid">
            <!-- Earnings -->
            <div>
                <h4 class="section-title earnings-title">Gross Earnings</h4>
                <div class="item-row">
                    <span>Base Salary</span>
                    <span>₦{{ number_format($payslip->base_salary, 2) }}</span>
                </div>
                @if($payslip->housing_allowance > 0)
                <div class="item-row">
                    <span>Housing Allowance</span>
                    <span>₦{{ number_format($payslip->housing_allowance, 2) }}</span>
                </div>
                @endif
                @if($payslip->transport_allowance > 0)
                <div class="item-row">
                    <span>Transport Allowance</span>
                    <span>₦{{ number_format($payslip->transport_allowance, 2) }}</span>
                </div>
                @endif
                @if($payslip->other_allowances > 0)
                <div class="item-row">
                    <span>Other Allowances</span>
                    <span>₦{{ number_format($payslip->other_allowances, 2) }}</span>
                </div>
                @endif
                <div class="item-row" style="color: #ea580c; font-weight: 600;">
                    <span>Closed Deal Commissions</span>
                    <span>+₦{{ number_format($payslip->commission_amount, 2) }}</span>
                </div>
                <div class="item-row total">
                    <span>Total Gross Earnings</span>
                    <span>₦{{ number_format($payslip->gross_pay, 2) }}</span>
                </div>
            </div>

            <!-- Deductions -->
            <div>
                <h4 class="section-title deductions-title">Statutory & Other Deductions</h4>
                <div class="item-row">
                    <span>PAYE Income Tax</span>
                    <span>-₦{{ number_format($payslip->tax_deduction, 2) }}</span>
                </div>
                <div class="item-row">
                    <span>Pension Contribution</span>
                    <span>-₦{{ number_format($payslip->pension_deduction, 2) }}</span>
                </div>
                @if($payslip->loan_deduction > 0)
                <div class="item-row" style="color: #e11d48;">
                    <span>Loan / Advance Repayment</span>
                    <span>-₦{{ number_format($payslip->loan_deduction, 2) }}</span>
                </div>
                @endif
                @if($payslip->other_deductions > 0)
                <div class="item-row" style="color: #e11d48;">
                    <span>Fines / Penalties</span>
                    <span>-₦{{ number_format($payslip->other_deductions, 2) }}</span>
                </div>
                @endif
                <div class="item-row total">
                    <span>Total Deductions</span>
                    <span style="color: #e11d48;">-₦{{ number_format($payslip->total_deductions, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Net Pay Banner -->
        <div class="net-pay-banner">
            <div>
                <h3>Total Net Salary Payable</h3>
                <p style="font-size: 11px; color: #7c2d12; margin-top: 2px;">(Gross Earnings minus Total Deductions)</p>
            </div>
            <div class="amount">
                ₦{{ number_format($payslip->net_pay, 2) }}
            </div>
        </div>

        <!-- Footer -->
        <div class="bank-info">
            <span>Disbursement Method: Direct Bank Electronic Settlement</span>
            <span>Generated electronically by {{ config('app.name') }}</span>
        </div>

        <p class="footer-note">
            {{ $companySetting->letterhead_footer ?? 'Confidential document. This payslip is valid and authentic without a physical signature.' }}
        </p>
    </div>
</body>
</html>
