<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation & Invoice</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; bg-color: #f6f9fc; color: #334155; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f6f9fc; padding: 40px 0; }
        .container { max-width: 600px; background-color: #ffffff; margin: 0 auto; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
        .header { background-color: #ffffff; padding: 30px; border-bottom: 1px solid #f1f5f9; text-align: center; }
        .logo-text { font-size: 24px; font-weight: 800; color: #0f172a; margin: 0; }
        .logo-accent { color: #FEA500; }
        .invoice-badge { display: inline-block; background-color: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 4px 10px; border-radius: 6px; margin-top: 10px; }
        .content { padding: 40px 30px; line-height: 1.6; }
        .greeting { font-size: 20px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 10px; }
        .invoice-card { background-color: #fafafa; border: 1px solid #f1f5f9; border-radius: 12px; padding: 24px; margin: 24px 0; }
        .invoice-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; }
        .invoice-row.total { border-top: 2px solid #e2e8f0; padding-top: 15px; margin-top: 15px; font-size: 18px; font-weight: 800; color: #0f172a; }
        .invoice-label { color: #64748b; font-weight: 500; }
        .invoice-val { color: #0f172a; font-weight: 700; text-align: right; }
        .btn-container { text-align: center; margin: 30px 0; }
        .btn { display: inline-block; background-color: #FEA500; color: #ffffff !important; font-weight: 700; font-size: 15px; padding: 12px 30px; border-radius: 10px; text-decoration: none; box-shadow: 0 4px 6px rgba(254, 165, 0, 0.15); }
        .footer { background-color: #f8fafc; padding: 30px; text-align: center; border-top: 1px solid #f1f5f9; }
        .footer-text { font-size: 12px; color: #94a3b8; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <div class="logo-text">NAW <span class="logo-accent">PropertyFlow</span></div>
                <span class="invoice-badge">Payment Confirmed</span>
            </div>
            
            <div class="content">
                <div class="greeting">Dear {{ $sale->lead->full_name }},</div>
                <p style="font-size: 15px; color: #475569; margin-bottom: 0;">We are pleased to confirm receipt of your payment. Congratulations on acquiring your new property! Below is the summary of your transaction invoice:</p>
                
                <div class="invoice-card">
                    <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.05em; margin-bottom: 15px;">Transaction Details</div>
                    
                    <div class="invoice-row">
                        <span class="invoice-label">Invoice Ref</span>
                        <span class="invoice-val">#INV-PF-{{ $sale->id }}</span>
                    </div>
                    <div class="invoice-row">
                        <span class="invoice-label">Property Description</span>
                        <span class="invoice-val">{{ $sale->property->name }}</span>
                    </div>
                    <div class="invoice-row">
                        <span class="invoice-label">Location</span>
                        <span class="invoice-val">{{ $sale->property->location }}</span>
                    </div>
                    <div class="invoice-row">
                        <span class="invoice-label">Units Purchased</span>
                        <span class="invoice-val">{{ $sale->units_purchased }} unit(s)</span>
                    </div>
                    <div class="invoice-row">
                        <span class="invoice-label">Payment Date</span>
                        <span class="invoice-val">{{ $sale->deal_closed_at ? $sale->deal_closed_at->format('d M Y') : date('d M Y') }}</span>
                    </div>
                    <div class="invoice-row">
                        <span class="invoice-label">Handling Agent</span>
                        <span class="invoice-val">{{ $sale->salesOfficer ? $sale->salesOfficer->name : 'N/A' }}</span>
                    </div>

                    <div class="invoice-row total">
                        <span style="color: #0f172a;">Total Value Paid</span>
                        <span style="color: #10b981;">₦{{ number_format($sale->deal_value, 2) }}</span>
                    </div>
                </div>

                <p style="font-size: 15px; color: #475569;">Our legal team is currently preparing the deed of assignment, allocations documents, and related property catalogs. We will contact you once they are ready for signature and pickup.</p>

                <p style="font-size: 15px; color: #475569; margin-bottom: 0;">Thank you for your business!<br><strong>NAW PropertyFlow CRM Team</strong></p>
            </div>
            
            <div class="footer">
                <p class="footer-text">© {{ date('Y') }} NAW PropertyFlow CRM. All rights reserved.</p>
                <p class="footer-text" style="margin-top: 6px;">Please retain this email copy for your records.</p>
            </div>
        </div>
    </div>
</body>
</html>
