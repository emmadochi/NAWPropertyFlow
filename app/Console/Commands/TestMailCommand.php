<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\CompanySetting;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test {email=emmadochi@gmail.com}';
    protected $description = 'Send a beautifully branded test email to verify SMTP configuration';

    public function handle(): int
    {
        $recipient = $this->argument('email');
        $this->info("Attempting to send luxury branded test email to: {$recipient} via SMTP...");

        try {
            $company = CompanySetting::first();
            $companyName = $company ? $company->company_name : 'NAW PropertyFlow CRM';
            $companyAddress = $company ? ($company->address ?? 'Maitama, Abuja, Nigeria') : 'Maitama, Abuja, Nigeria';
            $companyEmail = $company ? ($company->email ?? 'info@nawpropertyflow.com.ng') : 'info@nawpropertyflow.com.ng';
            $timestamp = now()->format('l, F j, Y - g:i A (T)');

            $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SMTP Verification - {$companyName}</title>
</head>
<body style="margin:0; padding:0; background-color:#f8fafc; font-family:'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:#334155;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; padding: 40px 15px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background-color:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 10px 25px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
          
          <!-- Header Banner -->
          <tr>
            <td style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 35px 30px; text-align: center; border-bottom: 4px solid #FEA500;">
              <h1 style="margin:0; font-size:22px; font-weight:900; color:#ffffff; letter-spacing: -0.5px;">{$companyName}</h1>
              <p style="margin:5px 0 0; font-size:12px; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:1px;">Official CRM Mail Delivery System</p>
            </td>
          </tr>

          <!-- Main Body -->
          <tr>
            <td style="padding: 40px 35px;">
              <div style="display:inline-block; padding: 6px 14px; background-color:#ecfdf5; border: 1px solid #a7f3d0; border-radius: 999px; font-size:12px; font-weight:800; color:#059669; margin-bottom: 20px;">
                ✅ SMTP Handshake Successful
              </div>

              <h2 style="margin:0 0 15px; font-size:20px; font-weight:800; color:#0f172a;">Hello Emmanuel,</h2>
              <p style="margin:0 0 20px; font-size:14px; line-height:1.7; color:#475569;">
                Your email transport layer is <strong>fully connected and operational</strong>. All automated communications—including <strong>Deeds of Assignment, Payment Receipts, Marketing Drip Sequences, and Payslips</strong>—are now active and will deliver with complete corporate branding.
              </p>

              <!-- Diagnostic Card -->
              <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; margin: 25px 0; overflow:hidden;">
                <tr>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Mail Server Host</td>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:12px; font-weight:800; color:#0f172a; text-align:right;">mail.nawpropertyflow.com.ng</td>
                </tr>
                <tr>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Sender Address</td>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:12px; font-weight:800; color:#FEA500; text-align:right;">{$companyEmail}</td>
                </tr>
                <tr>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Protocol & Security</td>
                  <td style="padding:12px 18px; border-bottom:1px solid #e2e8f0; font-size:12px; font-weight:800; color:#059669; text-align:right;">SMTP / SSL (Port 465)</td>
                </tr>
                <tr>
                  <td style="padding:12px 18px; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Delivery Verified At</td>
                  <td style="padding:12px 18px; font-size:12px; font-weight:700; color:#475569; text-align:right;">{$timestamp}</td>
                </tr>
              </table>

              <!-- CTA Button -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 30px;">
                <tr>
                  <td align="center">
                    <a href="https://demo.nawpropertyflow.com.ng" style="display:inline-block; padding: 14px 32px; background: linear-gradient(135deg, #FEA500 0%, #e09400 100%); color:#ffffff; font-size:14px; font-weight:800; text-decoration:none; border-radius:12px; box-shadow:0 6px 15px rgba(254,165,0,0.35);">
                      Open CRM Dashboard &rarr;
                    </a>
                  </td>
                </tr>
              </table>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#f1f5f9; padding: 25px 35px; text-align:center; border-top:1px solid #e2e8f0;">
              <p style="margin:0 0 5px; font-size:12px; font-weight:700; color:#475569;">{$companyName}</p>
              <p style="margin:0; font-size:11px; color:#94a3b8;">{$companyAddress} &bull; {$companyEmail}</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

            Mail::html($html, function ($message) use ($recipient, $companyName) {
                $message->to($recipient)
                        ->subject("✨ Verified: {$companyName} Mail System Online");
            });

            $this->info("🎉 SUCCESS: Luxury branded test email successfully sent to {$recipient}!");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ FAILED to send email: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
