<?php

namespace App\Console\Commands;

use App\Models\PaymentMilestone;
use App\Mail\PaymentReminderMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckPaymentDueDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:check-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check payment milestone due dates and send reminder notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        
        // 1. Mark overdue milestones
        $overdueMilestones = PaymentMilestone::where('status', '!=', 'paid')
            ->where('due_date', '<', $today)
            ->get();

        foreach ($overdueMilestones as $milestone) {
            if ($milestone->status !== 'overdue') {
                $milestone->status = 'overdue';
                $milestone->save();
                $this->info("Milestone #{$milestone->id} ('{$milestone->label}') marked as OVERDUE.");
            }
        }

        // 2. Query milestones due in 7 days, 1 day, or overdue today
        $milestones = PaymentMilestone::whereIn('status', ['pending', 'partial', 'overdue'])->get();

        foreach ($milestones as $milestone) {
            $lead = $milestone->paymentPlan->sale->lead;
            if (!$lead || !$lead->email) {
                continue;
            }

            $diffInDays = (int) $today->diffInDays(Carbon::parse($milestone->due_date), false);

            // Due in 7 days, due in 1 day, or exactly today / overdue (once a week for overdue)
            if ($diffInDays === 7 || $diffInDays === 1 || ($diffInDays <= 0 && $today->dayOfWeek === Carbon::MONDAY)) {
                try {
                    Mail::to($lead->email)->send(new PaymentReminderMail($milestone));
                    
                    $this->info("Payment reminder sent to {$lead->full_name} for milestone: {$milestone->label} (due in {$diffInDays} days).");
                    Log::info("Payment reminder sent to {$lead->email} for milestone: {$milestone->id}");
                } catch (\Exception $e) {
                    Log::error("Failed to send payment reminder to {$lead->email}: " . $e->getMessage());
                }
            }
        }

        return Command::SUCCESS;
    }
}
