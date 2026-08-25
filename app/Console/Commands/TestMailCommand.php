<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test {email=emmadochi@gmail.com}';
    protected $description = 'Send a test email to verify SMTP configuration';

    public function handle(): int
    {
        $recipient = $this->argument('email');
        $this->info("Attempting to send test email to: {$recipient} via SMTP...");

        try {
            Mail::raw(
                "Hello Emmanuel,\n\nThis is a verified test email from your NAW PropertyFlow CRM installation.\nYour SMTP connection via mail.nawpropertyflow.com.ng is working 100% perfectly!\n\nTimestamp: " . now()->toDateTimeString(),
                function ($message) use ($recipient) {
                    $message->to($recipient)
                            ->subject('✅ NAW PropertyFlow CRM - SMTP Connection Verified');
                }
            );

            $this->info("🎉 SUCCESS: Test email successfully sent to {$recipient}!");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("❌ FAILED to send email: " . $e->getMessage());
            $this->line("Stack trace: " . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
