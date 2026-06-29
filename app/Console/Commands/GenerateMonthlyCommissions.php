<?php

namespace App\Console\Commands;

use App\Models\Commission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GenerateMonthlyCommissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commissions:monthly-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and log a monthly summary report of sales agent commissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

        $commissions = Commission::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->with(['user', 'sale.property'])
            ->get();

        $this->info("Commission Summary for: " . $startOfLastMonth->format('F Y'));
        $this->info("---------------------------------------------");

        if ($commissions->isEmpty()) {
            $this->warn("No commissions recorded for the period.");
            return Command::SUCCESS;
        }

        $summary = [];
        foreach ($commissions as $comm) {
            $agentName = $comm->user->name;
            if (!isset($summary[$agentName])) {
                $summary[$agentName] = [
                    'pending' => 0,
                    'approved' => 0,
                    'paid' => 0,
                    'total' => 0,
                ];
            }

            $summary[$agentName]['total'] += $comm->calculated_amount;
            $summary[$agentName][$comm->status] += $comm->calculated_amount;
        }

        foreach ($summary as $agent => $stats) {
            $this->line("Agent: {$agent}");
            $this->line(" - Pending: ₦" . number_format($stats['pending'], 2));
            $this->line(" - Approved: ₦" . number_format($stats['approved'], 2));
            $this->line(" - Paid: ₦" . number_format($stats['paid'], 2));
            $this->line(" - Total: ₦" . number_format($stats['total'], 2));
            $this->line("---------------------------------------------");
        }

        Log::info("Monthly commission report generated for " . $startOfLastMonth->format('F Y'));

        return Command::SUCCESS;
    }
}
