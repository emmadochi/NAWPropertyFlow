<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Value Your Feedback</title>
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
        .btn-container { text-align: center; margin: 30px 0; }
        .btn { display: inline-block; background-color: #94a3b8; color: #ffffff !important; font-weight: 700; font-size: 15px; padding: 12px 30px; border-radius: 10px; text-decoration: none; }
        .footer { background-color: #f8fafc; padding: 30px; text-align: center; border-top: 1px solid #f1f5f9; }
        .footer-text { font-size: 12px; color: #94a3b8; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            @php
                $__setting = \App\Models\CompanySetting::first();
                $__companyName = $__setting?->company_name ?? 'Buckcrest Havens Limited';
                if (empty($__companyName) || in_array($__companyName, ['Laravel', 'NAW PropertyFlow', 'NAW PropertyFlow CRM'])) {
                    $__companyName = 'Buckcrest Havens Limited';
                }
                $__isBuckcrest = (function_exists('tenant') && in_array(tenant('id'), ['bhl', 'buckcrest']))
                    || str_contains(strtolower($__companyName), 'buckcrest');
            @endphp
            <div class="header">
                @if($__isBuckcrest)
                    <img src="{{ \App\Support\BuckcrestAssets::crestDataUri() }}" alt="{{ $__companyName }}" style="max-height: 52px; object-fit: contain; margin-bottom: 6px;">
                    <div class="logo-text" style="font-size: 20px;">{{ $__companyName }}</div>
                @else
                    <div class="logo-text">{{ $__companyName }}</div>
                @endif
            </div>
            
            <div class="content">
                <div class="greeting">Dear {{ $lead->full_name }},</div>
                <p class="body-text">Thank you for giving us the opportunity to discuss your real estate requirements. We noticed that your inquiry was marked as closed at this time.</p>
                <p class="body-text">We are continuously looking for ways to improve our listings, host communications, and sales support. If you have 2 minutes, we would be extremely grateful if you could share why you decided not to proceed with us.</p>
                
                <div class="btn-container">
                    <a href="{{ config('app.url') }}/feedback" class="btn">Share Brief Feedback</a>
                </div>

                <p class="body-text">We wish you the very best in your search, and hope to have the privilege of serving you in the future when the right opportunity arises.</p>
                <p class="body-text" style="margin-bottom: 0;">Sincerely,<br><strong>{{ $__companyName }} Team</strong></p>
            </div>
            
            <div class="footer">
                <p class="footer-text">© {{ date('Y') }} {{ $__companyName }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
