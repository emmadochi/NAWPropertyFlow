<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Milestone Reminder</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f6f9fc; color: #334155; margin: 0; padding: 0; }
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
        .table-milestone { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .table-milestone th { text-align: left; font-size: 13px; text-transform: uppercase; color: #64748b; padding-bottom: 8px; width: 40%; }
        .table-milestone td { font-size: 14px; color: #0f172a; padding: 8px 0; border-top: 1px solid #f1f5f9; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <div class="logo-text">NAW <span class="logo-accent">PropertyFlow</span></div>
            </div>
            
            <div class="content">
                <div class="greeting">Dear {{ $milestone->paymentPlan->sale->lead->full_name }},</div>
                <p class="body-text">This is a polite reminder regarding an upcoming payment milestone for your property purchase: <strong>{{ $milestone->paymentPlan->sale->property->name }}</strong>.</p>
                
                <div class="highlight-box">
                    <div class="highlight-title">Milestone Details</div>
                    <table class="table-milestone">
                        <tr>
                            <th>Milestone Label</th>
                            <td>{{ $milestone->label }}</td>
                        </tr>
                        <tr>
                            <th>Amount Due</th>
                            <td style="font-weight: 700; color: #FEA500;">₦{{ number_format($milestone->amount_due, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Due Date</th>
                            <td style="font-weight: bold; color: #e11d48;">{{ $milestone->due_date->format('F d, Y') }}</td>
                        </tr>
                        <tr>
                            <th>Remaining Balance</th>
                            <td>₦{{ number_format($milestone->paymentPlan->balance, 2) }}</td>
                        </tr>
                    </table>
                </div>

                <p class="body-text">
                    Please make the payment to the designated company bank account and upload your payment proof or send it to your assigned sales executive, 
                    <strong>{{ $milestone->paymentPlan->sale->salesOfficer ? $milestone->paymentPlan->sale->salesOfficer->name : 'NAW Sales Desk' }}</strong>.
                </p>

                <p class="body-text" style="margin-bottom: 0;">If you have already made this payment, please disregard this reminder as we verify and process your confirmation.<br><br>Warm regards,<br><strong>NAW PropertyFlow CRM Finance Team</strong></p>
            </div>
            
            <div class="footer">
                <p class="footer-text">© {{ date('Y') }} NAW PropertyFlow CRM. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
