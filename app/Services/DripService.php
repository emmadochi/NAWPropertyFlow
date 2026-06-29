<?php

namespace App\Services;

use App\Models\DripSequence;
use App\Models\DripEnrollment;
use App\Models\DripStep;
use App\Models\Lead;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\CampaignMail;

class DripService
{
    /**
     * Enroll a lead into a drip sequence (idempotent).
     */
    public function enroll(DripSequence $sequence, Lead $lead): ?DripEnrollment
    {
        if (!$sequence->is_active) {
            return null;
        }

        $firstStep = $sequence->steps()->where('is_active', true)->first();

        if (!$firstStep) {
            return null;
        }

        $nextSendAt = now()->addDays($firstStep->delay_days)->addHours($firstStep->delay_hours);

        return DripEnrollment::firstOrCreate(
            ['drip_sequence_id' => $sequence->id, 'lead_id' => $lead->id],
            [
                'current_step_id' => $firstStep->id,
                'status'          => 'active',
                'next_send_at'    => $nextSendAt,
                'enrolled_at'     => now(),
            ]
        );
    }

    /**
     * Enroll a lead into all sequences matching a trigger event.
     */
    public function triggerFor(Lead $lead, string $triggerEvent): void
    {
        DripSequence::where('trigger_event', $triggerEvent)
            ->where('is_active', true)
            ->each(fn (DripSequence $seq) => $this->enroll($seq, $lead));
    }

    /**
     * Process all due drip enrollments (called by scheduler).
     */
    public function processDue(): int
    {
        $processed = 0;

        DripEnrollment::where('status', 'active')
            ->where('next_send_at', '<=', now())
            ->with(['lead', 'currentStep', 'sequence.steps'])
            ->chunkById(50, function ($enrollments) use (&$processed) {
                foreach ($enrollments as $enrollment) {
                    try {
                        $this->sendStep($enrollment);
                        $processed++;
                    } catch (\Exception $e) {
                        Log::error('Drip step failed', [
                            'enrollment_id' => $enrollment->id,
                            'error'         => $e->getMessage(),
                        ]);
                    }
                }
            });

        return $processed;
    }

    private function sendStep(DripEnrollment $enrollment): void
    {
        $step = $enrollment->currentStep;
        $lead = $enrollment->lead;

        if (!$step || !$lead) {
            $enrollment->update(['status' => 'cancelled']);
            return;
        }

        // Send the message
        if ($step->type === 'email' && $lead->email) {
            Mail::to($lead->email, $lead->full_name)->send(
                new CampaignMail($step->subject ?? 'Message from NAW PropertyFlow', $step->body)
            );
        }

        // Advance to next step
        $nextStep = $enrollment->sequence->steps()
            ->where('step_order', '>', $step->step_order)
            ->where('is_active', true)
            ->first();

        if ($nextStep) {
            $enrollment->update([
                'current_step_id' => $nextStep->id,
                'next_send_at'    => now()->addDays($nextStep->delay_days)->addHours($nextStep->delay_hours),
            ]);
        } else {
            // Sequence complete
            $enrollment->update(['status' => 'completed', 'completed_at' => now()]);
        }
    }
}
