<?php

namespace App\Mail;

use App\Models\Inspection;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InspectionScheduledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inspection;
    public $isRescheduled;
    public $originalDate;

    /**
     * Create a new message instance.
     */
    public function __construct(Inspection $inspection, bool $isRescheduled = false, $originalDate = null)
    {
        $this->inspection = $inspection;
        $this->isRescheduled = $isRescheduled;
        $this->originalDate = $originalDate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $prefix = $this->isRescheduled ? '🔄 [Rescheduled] Site Inspection Update - ' : '📍 Site Inspection Scheduled - ';

        return new Envelope(
            subject: $prefix . $this->inspection->property->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.inspection',
            with: [
                'inspection'    => $this->inspection,
                'isRescheduled' => $this->isRescheduled,
                'originalDate'  => $this->originalDate,
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
