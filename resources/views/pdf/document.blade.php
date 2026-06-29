<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 100px 60px 80px 60px;
        }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color: #333;
        }
        /* Header and Footer styles */
        header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 50px;
            border-bottom: 1px solid #eee;
            text-align: center;
            padding-bottom: 5px;
        }
        footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 35px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 10px;
            color: #888;
            padding-top: 5px;
        }
        .header-logo {
            font-weight: bold;
            font-size: 16px;
            color: #FEA500;
        }
        .header-sub {
            font-size: 10px;
            color: #777;
        }
        /* Document content styling */
        .content {
            margin-top: 10px;
        }
        h1, h2, h3, h4 {
            color: #111;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 20px;
            border-bottom: 2px solid #FEA500;
            padding-bottom: 5px;
            text-align: center;
        }
        p {
            margin-bottom: 12px;
            text-align: justify;
        }
        /* Tables styles for payments/milestones list */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f7f7f7;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .text-right {
            text-align: right;
        }
        .signature-section {
            margin-top: 50px;
            width: 100%;
        }
        .signature-box {
            width: 45%;
            float: left;
            border-top: 1px solid #333;
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
        }
        .signature-box.right {
            float: right;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>
    @php
        $settings = \App\Models\CompanySetting::first();
    @endphp

    <header>
        @if($settings && $settings->letterhead_header)
            {!! $settings->letterhead_header !!}
        @else
            <div class="header-logo">
                @if($settings && $settings->logo_path && file_exists(public_path('storage/' . $settings->logo_path)))
                    <img src="{{ public_path('storage/' . $settings->logo_path) }}" style="height: 30px; vertical-align: middle; margin-right: 8px;">
                @endif
                {{ $settings->company_name ?? 'NAW Properties' }}
            </div>
            <div class="header-sub">
                {{ $settings->address ?? 'Plot 12, Admiralty Way, Lekki Phase 1, Lagos, Nigeria' }} | {{ $settings->email ?? 'info@nawproperties.com' }}
            </div>
        @endif
    </header>

    <footer>
        @if($settings && $settings->letterhead_footer)
            {!! $settings->letterhead_footer !!}
        @else
            <div>Generated automatically by {{ $settings ? $settings->company_name : 'NAW Properties' }} | Page <span class="page-number"></span></div>
        @endif
    </footer>

    <div class="content">
        {!! $content !!}
    </div>

</body>
</html>
