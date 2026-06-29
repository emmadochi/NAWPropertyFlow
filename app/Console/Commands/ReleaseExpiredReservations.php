<?php

namespace App\Console\Commands;

use App\Models\PropertyUnit;
use App\Models\Property;
use App\Models\ProjectMilestone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'crm:release-expired-reservations';

    /**
     * The console command description.
     */
    protected $description = 'Release expired property unit reservations and warn of low stock or project milestone delays.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting off-plan reservation audit...');

        // 1. Audit Expired Reservations
        $expiredUnits = PropertyUnit::where('status', 'reserved')
            ->where('reservation_expires_at', '<', now())
            ->get();

        $releasedCount = 0;
        foreach ($expiredUnits as $unit) {
            $leadName = $unit->reservedByLead ? $unit->reservedByLead->name : 'N/A';
            $unitNumber = $unit->unit_number;
            $propertyName = $unit->property->name;

            // Release unit
            $unit->release();

            // Sync parent property units
            $availableCount = $unit->property->units()->where('status', 'available')->count();
            $unit->property->update(['available_units' => $availableCount]);

            $this->info("Released reservation for Unit {$unitNumber} in property '{$propertyName}' (previously reserved by {$leadName}).");
            $releasedCount++;
        }

        $this->info("Audit completed. Released {$releasedCount} expired unit reservations.");

        // 2. Audit Low Stock Warning
        $properties = Property::all();
        foreach ($properties as $property) {
            if ($property->total_units > 0 && $property->available_units <= 2 && $property->available_units > 0) {
                // Warning logged or notified
                Log::warning("Low stock alert: Property '{$property->name}' has only {$property->available_units} units available out of {$property->total_units} total.");
                $this->warn("Low stock alert: Property '{$property->name}' has only {$property->available_units} units left.");
            }
        }

        // 3. Audit Delayed Project Milestones
        $delayedMilestones = ProjectMilestone::where('status', '!=', 'completed')
            ->where('due_date', '<', now())
            ->get();

        foreach ($delayedMilestones as $milestone) {
            if ($milestone->status !== 'delayed') {
                $milestone->update(['status' => 'delayed']);
                $this->error("Milestone delayed: '{$milestone->title}' in project '{$milestone->project->name}' has passed its due date ({$milestone->due_date->format('Y-m-d')}).");
            }
        }

        return Command::SUCCESS;
    }
}
