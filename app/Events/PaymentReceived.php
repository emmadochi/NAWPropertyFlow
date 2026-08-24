<?php

namespace App\Events;

use App\Models\PaymentMilestone;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived
{
    use Dispatchable, SerializesModels;

    public $milestone;
    public bool $sendNotification;

    public function __construct(PaymentMilestone $milestone, bool $sendNotification = true)
    {
        $this->milestone = $milestone;
        $this->sendNotification = $sendNotification;
    }
}
