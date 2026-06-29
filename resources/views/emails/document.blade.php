<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $document->title }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f6f9fc;
            margin: 0;
            padding: 0;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #eef2f6;
        }
        .header {
            background-color: #FEA500;
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }
        .body {
            padding: 30px;
            line-height: 1.6;
        }
        .body p {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .btn-box {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            background-color: #FEA500;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            display: inline-block;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 11px;
            color: #888888;
            border-top: 1px solid #eef2f6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Document Transmittal</h1>
        </div>
        <div class="body">
            <p>Dear {{ $document->lead->full_name }},</p>
            <p>Please find attached the compiled document: <strong>{{ $document->title }}</strong> generated for your reference regarding your property interest.</p>
            <p>You can also download or view the document online by clicking the button below:</p>
            
            <div class="btn-box">
                <a href="{{ asset('storage/' . $document->pdf_path) }}" target="_blank" class="btn" style="color: #ffffff;">View Document Online</a>
            </div>

            <p>If you have any questions or require further assistance, please do not hesitate to contact our sales team.</p>
            <p>Best regards,<br><strong>NAW Properties Team</strong></p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} NAW Properties Ltd. All rights reserved.<br>Plot 12, Admiralty Way, Lekki Phase 1, Lagos, Nigeria.</p>
        </div>
    </div>
</body>
</html>
