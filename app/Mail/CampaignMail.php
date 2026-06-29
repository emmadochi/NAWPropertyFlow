<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $html;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $html, ?string $fromAddress = null, ?string $fromName = null)
    {
        $this->subject = $subject;
        $this->html = $html;

        if ($fromAddress) {
            $this->from($fromAddress, $fromName ?? config('mail.from.name'));
        }
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->html($this->html)->subject($this->subject);
    }
}
