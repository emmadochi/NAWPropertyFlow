<?php

namespace App\Events;

use App\Models\Inspection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InspectionCompleted
{
    use Dispatchable, SerializesModels;

    public $inspection;

    public function __construct(Inspection $inspection)
    {
        $this->inspection = $inspection;
    }
}
