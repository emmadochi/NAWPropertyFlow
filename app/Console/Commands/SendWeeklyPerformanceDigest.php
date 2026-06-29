<?php

namespace App\Console\Commands;

use App\Services\PerformanceService;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyPerformanceDigest extends Command
{
    protected $signature   = 'hr:weekly-digest';
    protected $description = 'Send weekly performance digest to managers and admins';

    public function handle(PerformanceService $service): int
    {
        $month = now()->month;
        $year  = now()->year;

        $digest    = $service->monthlyDigest($month, $year);
        $managers  = User::whereIn('role', ['super_admin', 'company_admin', 'sales_manager'])->get();

        foreach ($managers as $manager) {
            try {
                Mail::send('emails.performance_digest', array_merge($digest, ['manager' => $manager]), function ($m) use ($manager) {
                    $m->to($manager->email, $manager->name)
                      ->subject('📊 Weekly Performance Digest — ' . now()->format('d M Y'));
                });
                $this->info("Sent digest to {$manager->email}");
            } catch (\Exception $e) {
                $this->error("Failed to send to {$manager->email}: " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
