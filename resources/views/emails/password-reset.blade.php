<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - {{ $companySetting->company_name ?? config('app.name') }}</title>
    <style>
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f172a; color: #334155; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #0f172a; padding: 40px 15px; }
        .main-card { max-width: 560px; margin: 0 auto; background-color: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
        .header-band { background: linear-gradient(135deg, #F37021 0%, #ea580c 100%); padding: 36px 30px; text-align: center; }
        .header-logo { max-height: 52px; object-fit: contain; margin-bottom: 12px; }
        .brand-title { font-size: 22px; font-weight: 900; color: #ffffff; margin: 0; letter-spacing: -0.5px; }
        .brand-subtitle { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: rgba(255, 255, 255, 0.85); margin-top: 4px; }
        .content { padding: 40px 36px; line-height: 1.6; }
        .icon-circle { width: 56px; height: 56px; background-color: #fff7ed; border-radius: 16px; border: 1px solid #ffedd5; margin: 0 auto 24px; text-align: center; line-height: 56px; font-size: 26px; }
        .headline { font-size: 22px; font-weight: 800; color: #0f172a; text-align: center; margin: 0 0 12px; }
        .subtitle { font-size: 14px; color: #64748b; text-align: center; margin: 0 0 28px; line-height: 1.5; }
        .cta-container { text-align: center; margin: 32px 0; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #F37021 0%, #ea580c 100%); color: #ffffff !important; font-weight: 800; font-size: 15px; padding: 15px 36px; border-radius: 14px; text-decoration: none; box-shadow: 0 10px 20px -5px rgba(243, 112, 33, 0.4); text-transform: uppercase; letter-spacing: 0.5px; }
        .security-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; font-size: 12px; color: #64748b; margin-top: 30px; line-height: 1.5; }
        .security-box strong { color: #0f172a; }
        .fallback-link { font-size: 11px; color: #94a3b8; word-break: break-all; margin-top: 10px; }
        .fallback-link a { color: #F37021; text-decoration: underline; }
        .footer { background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 30px; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            <!-- Brand Header -->
            <div class="header-band">
                @if($companySetting?->logo_path)
                    <img src="{{ asset('storage/' . $companySetting->logo_path) }}" alt="{{ $companySetting->company_name ?? 'RICAF' }}" class="header-logo">
                @endif
                <h1 class="brand-title">{{ $companySetting->company_name ?? config('app.name', 'RICAF Nigeria Limited') }}</h1>
                <div class="brand-subtitle">Enterprise Real Estate Portal</div>
            </div>

            <!-- Content Area -->
            <div class="content">
                <div class="icon-circle">🔐</div>
                <h2 class="headline">Password Reset Request</h2>
                <p class="subtitle">
                    Hello <strong>{{ $user->name ?? 'Valued User' }}</strong>,<br>
                    We received a request to securely reset the password associated with your account. Click the button below to set a new password.
                </p>

                <div class="cta-container">
                    <a href="{{ $resetUrl }}" class="cta-button" target="_blank">Reset Password Now</a>
                </div>

                <div class="security-box">
                    <p style="margin: 0;"><strong>Security Notice:</strong> This password reset link will expire in <strong>60 minutes</strong>. If you did not make this request, you can safely ignore this email—your account remains completely secure.</p>
                </div>

                <div class="fallback-link">
                    If you're having trouble clicking the button, copy and paste this URL into your web browser:<br>
                    <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p style="margin: 0 0 6px;">{{ $companySetting->address ?? 'Lagos, Nigeria' }}</p>
                <p style="margin: 0;">© {{ date('Y') }} {{ $companySetting->company_name ?? config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
