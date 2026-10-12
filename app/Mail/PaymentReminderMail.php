<?php

namespace App\Mail;

use App\Models\PaymentMilestone;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Mail\Traits\TenantMailDefaults;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels, TenantMailDefaults;

    public $milestone;

    /**
     * Create a new message instance.
     */
    public function __construct(PaymentMilestone $milestone)
    {
        $this->milestone = $milestone;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $companyName = $this->resolveCompanyName();

        return new Envelope(
            from: $this->resolveFromAddress(),
            subject: "Payment Milestone Due Reminder - {$companyName} ({$this->milestone->label})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reminder',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
