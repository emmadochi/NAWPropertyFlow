<?php

namespace App\Mail;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class PaymentInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;

    /**
     * Create a new message instance.
     */
    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice & Payment Confirmation - Ref #INV-PF-' . $this->sale->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];
        
        // If there's a payment receipt file on storage, attach it to the email
        if ($this->sale->payment_receipt && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->sale->payment_receipt)) {
            $attachments[] = Attachment::fromPath(\Illuminate\Support\Facades\Storage::disk('public')->path($this->sale->payment_receipt))
                ->as('payment_receipt.' . pathinfo($this->sale->payment_receipt, PATHINFO_EXTENSION));
        }

        return $attachments;
    }
}
