<?php

namespace App\Mail;

use App\Models\GeneratedDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class GeneratedDocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $document;

    public function __construct(GeneratedDocument $document)
    {
        $this->document = $document;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->document->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.document',
            with: [
                'document' => $this->document,
                'lead'     => $this->document->lead,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];
        
        if ($this->document->pdf_path) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->document->pdf_path)) {
                $attachments[] = Attachment::fromStorageDisk('public', $this->document->pdf_path)
                    ->as(basename($this->document->pdf_path))
                    ->withMime('application/pdf');
            } elseif (file_exists(storage_path('app/public/' . $this->document->pdf_path))) {
                $attachments[] = Attachment::fromPath(storage_path('app/public/' . $this->document->pdf_path))
                    ->as(basename($this->document->pdf_path))
                    ->withMime('application/pdf');
            }
        }

        return $attachments;
    }
}
