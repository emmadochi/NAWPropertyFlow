<?php

namespace App\Events;

use App\Models\Sale;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DealWon
{
    use Dispatchable, SerializesModels;

    public $sale;
    public bool $sendNotification;

    public function __construct(Sale $sale, bool $sendNotification = true)
    {
        $this->sale = $sale;
        $this->sendNotification = $sendNotification;
    }
}
