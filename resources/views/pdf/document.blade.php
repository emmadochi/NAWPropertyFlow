<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 100px 60px 80px 60px;
        }
        * {
            font-family: 'DejaVu Sans', sans-serif;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
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
        /* Multi-Page handling & Page Breaks */
        .page-break {
            page-break-after: always;
            break-after: page;
        }
    </style>
</head>
<body>
    @php
        $settings = rescue(fn() => \App\Models\CompanySetting::first(), null);
    @endphp

    <header>
        @if($settings && $settings->letterhead_header)
            {!! $settings->letterhead_header !!}
        @else
            <div class="header-logo">
                @if($settings && $settings->logo_path && file_exists(public_path('storage/' . $settings->logo_path)))
                    <img src="{{ public_path('storage/' . $settings->logo_path) }}" style="height: 30px; vertical-align: middle; margin-right: 8px;">
                @endif
                {{ $settings->company_name ?? config('app.name') }}
            </div>
            <div class="header-sub">
                {{ $settings->address ?? '' }} | {{ $settings->email ?? config('mail.from.address') }}
            </div>
        @endif
    </header>

    <footer>
        @if($settings && $settings->letterhead_footer)
            {!! $settings->letterhead_footer !!}
        @else
            <div>{{ $settings ? $settings->company_name : config('app.name') }} &bull; Confidential Real Estate Conveyance</div>
        @endif
    </footer>

    <div class="content">
        {!! $content !!}
    </div>

    {{-- DomPDF Dynamic Page Counter script --}}
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT;
            $size = 8;
            $font = $fontMetrics->getFont("DejaVu Sans");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 25;
            $pdf->page_text($x, $y, $text, $font, $size, array(0.5, 0.5, 0.5));
        }
    </script>
</body>
</html>
