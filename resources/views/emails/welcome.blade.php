<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to NAW PropertyFlow</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; bg-color: #f6f9fc; color: #334155; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f6f9fc; padding: 40px 0; }
        .container { max-width: 600px; background-color: #ffffff; margin: 0 auto; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background-color: #ffffff; padding: 30px; border-bottom: 1px solid #f1f5f9; text-align: center; }
        .logo-text { font-size: 24px; font-weight: 800; color: #0f172a; margin: 0; }
        .logo-accent { color: #FEA500; }
        .content { padding: 40px 30px; line-height: 1.6; }
        .greeting { font-size: 20px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 20px; }
        .body-text { font-size: 15px; color: #475569; margin-bottom: 24px; }
        .highlight-box { background-color: #fffcf5; border: 1px solid #ffe6b3; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .highlight-title { font-weight: 700; font-size: 14px; color: #8c5000; margin-top: 0; margin-bottom: 8px; text-transform: uppercase; }
        .btn-container { text-align: center; margin: 30px 0; }
        .btn { display: inline-block; background-color: #FEA500; color: #ffffff !important; font-weight: 700; font-size: 15px; padding: 12px 30px; border-radius: 10px; text-decoration: none; box-shadow: 0 4px 6px rgba(254, 165, 0, 0.15); transition: background-color 0.2s; }
        .footer { background-color: #f8fafc; padding: 30px; text-align: center; border-top: 1px solid #f1f5f9; }
        .footer-text { font-size: 12px; color: #94a3b8; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <div class="logo-text">NAW <span class="logo-accent">PropertyFlow</span></div>
            </div>
            
            <div class="content">
                <div class="greeting">Hello {{ $lead->full_name }},</div>
                <p class="body-text">Thank you for reaching out to us. We have received your inquiry and are excited to assist you in finding your perfect property portfolio.</p>
                
                @if($lead->propertyInterest)
                <div class="highlight-box">
                    <div class="highlight-title">Your Property Interest</div>
                    <div style="font-size: 15px; font-weight: bold; color: #0f172a;">{{ $lead->propertyInterest->name }}</div>
                    <div style="font-size: 13px; color: #475569; margin-top: 4px;">Location: {{ $lead->propertyInterest->location }}</div>
                    <div style="font-size: 13px; color: #475569;">Price: ₦{{ number_format($lead->propertyInterest->price, 2) }}</div>
                </div>
                @endif

                <p class="body-text">
                    One of our professional sales officers, 
                    <strong>{{ $lead->assignedOfficer ? $lead->assignedOfficer->name : 'our sales support desk' }}</strong>, 
                    has been assigned to your request and will contact you via call or WhatsApp shortly.
                </p>

                <div class="btn-container">
                    <a href="{{ config('app.url') }}" class="btn">Explore More Layouts</a>
                </div>

                <p class="body-text" style="margin-bottom: 0;">Warm regards,<br><strong>NAW PropertyFlow CRM Team</strong></p>
            </div>
            
            <div class="footer">
                <p class="footer-text">© {{ date('Y') }} NAW PropertyFlow CRM. All rights reserved.</p>
                <p class="footer-text" style="margin-top: 6px;">You are receiving this because you filled out a lead form on our website.</p>
            </div>
        </div>
    </div>
</body>
</html>
