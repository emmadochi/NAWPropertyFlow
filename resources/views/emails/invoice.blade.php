<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Official Payment Confirmation & Invoice</title>
</head>
<body style="margin:0; padding:0; background-color:#f8fafc; font-family:'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:#334155;">
  @php $__setting = \App\Models\CompanySetting::first(); @endphp
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; padding: 40px 15px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background-color:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 10px 25px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
          
          <!-- Header Banner -->
          <tr>
            <td style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 35px 30px; text-align: center; border-bottom: 4px solid #FEA500;">
              @if($__setting?->logo_path && file_exists(public_path('storage/' . $__setting->logo_path)))
                <img src="{{ asset('storage/' . $__setting->logo_path) }}" alt="{{ $__setting->company_name ?? config('app.name') }}" style="max-height: 48px; object-fit: contain; margin-bottom: 8px;">
              @else
                <h1 style="margin:0; font-size:22px; font-weight:900; color:#ffffff; letter-spacing: -0.5px;">{{ $__setting?->company_name ?? 'NAW PropertyFlow' }}</h1>
              @endif
              <p style="margin:5px 0 0; font-size:12px; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:1px;">Official Payment Confirmation &amp; Invoice</p>
            </td>
          </tr>

          <!-- Main Body -->
          <tr>
            <td style="padding: 40px 35px;">
              <div style="display:inline-block; padding: 6px 14px; background-color:#ecfdf5; border: 1px solid #a7f3d0; border-radius: 999px; font-size:12px; font-weight:800; color:#059669; margin-bottom: 20px;">
                💳 Payment Verified &amp; Logged
              </div>

              <h2 style="margin:0 0 15px; font-size:20px; font-weight:800; color:#0f172a;">Dear {{ $sale->lead->full_name }},</h2>
              <p style="margin:0 0 20px; font-size:14px; line-height:1.7; color:#475569;">
                We are pleased to confirm receipt of your transaction. Congratulations on your property allocation! Below is your official transaction invoice breakdown:
              </p>

              <!-- Invoice Card -->
              <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; margin: 25px 0; overflow:hidden;">
                <tr>
                  <td colspan="2" style="background-color:#0f172a; padding:12px 18px; font-size:11px; font-weight:800; color:#FEA500; text-transform:uppercase; letter-spacing:0.5px;">
                    Invoice Ref: #INV-PF-{{ $sale->id }}
                  </td>
                </tr>
                <tr>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Property Scheme</td>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:13px; font-weight:800; color:#0f172a; text-align:right;">{{ $sale->property->name }}</td>
                </tr>
                <tr>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Location</td>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:12px; font-weight:700; color:#475569; text-align:right;">{{ $sale->property->location }}</td>
                </tr>
                <tr>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Units Purchased</td>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:12px; font-weight:700; color:#0f172a; text-align:right;">{{ $sale->units_purchased }} unit(s)</td>
                </tr>
                <tr>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Payment Date</td>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:12px; font-weight:700; color:#475569; text-align:right;">{{ $sale->deal_closed_at ? $sale->deal_closed_at->format('d M Y') : date('d M Y') }}</td>
                </tr>
                <tr>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Assigned Executive</td>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:12px; font-weight:700; color:#475569; text-align:right;">{{ $sale->salesOfficer ? $sale->salesOfficer->name : 'Executive Desk' }}</td>
                </tr>
                <tr>
                  <td style="padding:14px 18px; background-color:#ecfdf5; font-size:12px; font-weight:800; color:#065f46; text-transform:uppercase;">Total Amount Paid</td>
                  <td style="padding:14px 18px; background-color:#ecfdf5; font-size:16px; font-weight:900; color:#059669; text-align:right;">₦{{ number_format($sale->deal_value, 2) }}</td>
                </tr>
              </table>

              <p style="margin:0 0 20px; font-size:14px; line-height:1.7; color:#475569;">
                Our legal and conveyancing department is preparing your deed of assignment and title documentation. You can monitor your purchase and download receipts directly from your Client Portal.
              </p>

              <!-- CTA Button -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 30px;">
                <tr>
                  <td align="center">
                    <a href="{{ route('buyer.portal', ['token' => $sale->lead->portal_token ?? 'view']) }}" style="display:inline-block; padding: 14px 32px; background: linear-gradient(135deg, #FEA500 0%, #e09400 100%); color:#ffffff; font-size:14px; font-weight:800; text-decoration:none; border-radius:12px; box-shadow:0 6px 15px rgba(254,165,0,0.35);">
                      View In Client Portal &rarr;
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:30px 0 0; font-size:13px; color:#64748b;">
                Thank you for your trust,<br>
                <strong style="color:#0f172a;">Finance &amp; Operations Desk</strong>
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#f1f5f9; padding: 25px 35px; text-align:center; border-top:1px solid #e2e8f0;">
              <p style="margin:0 0 5px; font-size:12px; font-weight:700; color:#475569;">{{ $__setting?->company_name ?? config('app.name') }}</p>
              <p style="margin:0; font-size:11px; color:#94a3b8;">{{ $__setting?->address ?? 'Nigeria' }} &bull; {{ $__setting?->email ?? config('mail.from.address') }}</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
