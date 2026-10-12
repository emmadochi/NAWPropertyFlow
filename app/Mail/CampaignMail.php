<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

use App\Mail\Traits\TenantMailDefaults;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels, TenantMailDefaults;

    public $subject;
    public $html;
    public $attachmentsList;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $html, ?string $fromAddress = null, ?string $fromName = null, array $attachmentsList = [])
    {
        $this->subject = $subject;
        $this->html = $html;
        $this->attachmentsList = $attachmentsList;

        $defaultAddress = $this->resolveFromAddress();
        $finalFromEmail = $fromAddress ?: $defaultAddress->address;
        $finalFromName = $fromName ?: $defaultAddress->name;

        $this->from($finalFromEmail, $finalFromName);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $mail = $this->html($this->html)->subject($this->subject);

        if (!empty($this->attachmentsList)) {
            foreach ($this->attachmentsList as $att) {
                $path = is_array($att) ? ($att['path'] ?? '') : $att;
                $name = is_array($att) ? ($att['name'] ?? null) : null;
                $mime = is_array($att) ? ($att['mime'] ?? null) : null;

                if (!$path) {
                    continue;
                }

                $fullPath = null;
                if (Storage::disk('public')->exists($path)) {
                    $fullPath = Storage::disk('public')->path($path);
                } elseif (file_exists(storage_path('app/public/' . $path))) {
                    $fullPath = storage_path('app/public/' . $path);
                } elseif (file_exists($path)) {
                    $fullPath = $path;
                }

                if ($fullPath && file_exists($fullPath)) {
                    $options = [];
                    if ($name) {
                        $options['as'] = $name;
                    }
                    if ($mime) {
                        $options['mime'] = $mime;
                    }
                    $mail->attach($fullPath, $options);
                }
            }
        }

        return $mail;
    }
}
