<?php

namespace App\Events;

use App\Models\PaymentMilestone;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentReceived
{
    use Dispatchable, SerializesModels;

    public $milestone;

    public function __construct(PaymentMilestone $milestone)
    {
        $this->milestone = $milestone;
    }
}
