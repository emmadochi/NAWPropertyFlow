<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Inspection Scheduled</title>
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
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .details-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .details-table td.label { font-weight: 700; color: #475569; width: 35%; }
        .details-table td.value { color: #0f172a; }
        .notes-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; font-size: 13px; color: #64748b; margin-top: 15px; }
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
            </div>
            
            <div class="content">
                <div class="greeting">Hi {{ $inspection->lead->full_name }},</div>
                <p class="body-text">We have successfully scheduled your site inspection tour. Below are the details of your upcoming visit:</p>
                
                <table class="details-table">
                    <tr>
                        <td class="label">Property</td>
                        <td class="value" style="font-weight: bold;">{{ $inspection->property->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Estate Name</td>
                        <td class="value">{{ $inspection->property->estate_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Location</td>
                        <td class="value">{{ $inspection->property->location }}</td>
                    </tr>
                    <tr>
                        <td class="label">Date & Time</td>
                        <td class="value" style="color: #FEA500; font-weight: bold;">
                            {{ $inspection->inspection_date->format('l, d M Y at h:i A') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Assigned Host</td>
                        <td class="value">
                            {{ $inspection->assignedOfficer ? $inspection->assignedOfficer->name : 'NAW Sales Officer' }}
                            @if($inspection->assignedOfficer && $inspection->assignedOfficer->phone_number)
                            ({{ $inspection->assignedOfficer->phone_number }})
                            @endif
                        </td>
                    </tr>
                </table>

                @if($inspection->notes)
                <div class="notes-box">
                    <strong>Logistics / Tour Notes:</strong><br>
                    {{ $inspection->notes }}
                </div>
                @endif

                <p class="body-text" style="margin-top: 24px;">Please ensure you arrive on time. If you need to cancel or reschedule, please contact your host or reply directly to this email.</p>

                <p class="body-text" style="margin-bottom: 0;">See you soon,<br><strong>NAW PropertyFlow CRM Team</strong></p>
            </div>
            
            <div class="footer">
                <p class="footer-text">© {{ date('Y') }} NAW PropertyFlow CRM. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
