<?php

namespace App\Console\Commands;

use App\Services\DripService;
use Illuminate\Console\Command;

class ProcessDripSteps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drip:process-steps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and dispatch due drip campaign steps to enrolled leads';

    /**
     * Execute the console command.
     */
    public function handle(DripService $dripService): int
    {
        $this->info('Starting drip campaign step processor...');
        $processed = $dripService->processDue();
        $this->info("Successfully processed {$processed} drip steps.");

        return Command::SUCCESS;
    }
}
