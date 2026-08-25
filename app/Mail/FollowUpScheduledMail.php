<?php

namespace App\Mail;

use App\Models\FollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FollowUpScheduledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $followUp;
    public $isRescheduled;

    /**
     * Create a new message instance.
     */
    public function __construct(FollowUp $followUp, bool $isRescheduled = false)
    {
        $this->followUp = $followUp;
        $this->isRescheduled = $isRescheduled;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $type = ucfirst($this->followUp->type ?? 'Appointment');
        $prefix = $this->isRescheduled ? "🔄 [Rescheduled] {$type} Confirmation" : "📅 Scheduled: {$type} Confirmation";

        return new Envelope(
            subject: "{$prefix} - NAW PropertyFlow",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.follow_up',
            with: [
                'followUp'      => $this->followUp,
                'lead'          => $this->followUp->lead,
                'isRescheduled' => $this->isRescheduled,
            ],
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
