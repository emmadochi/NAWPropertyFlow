<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeLeadMail;
use App\Mail\PoliteClosingMail;

class LeadService
{
    /**
     * Create a new lead and log activity.
     */
    public function createLead(array $data, ?int $userId = null): Lead
    {
        $lead = Lead::create($data);

        $this->logActivity(
            $lead->id,
            $userId ?? Auth::id() ?? 1, // Fallback if no auth user (e.g. API/Seeder)
            'Created',
            'Lead created successfully.'
        );

        if (!empty($lead->assigned_to)) {
            $this->logActivity(
                $lead->id,
                $userId ?? Auth::id() ?? 1,
                'Updated',
                "Lead assigned to Sales Officer: " . $lead->assignedOfficer->name
            );
        }

        if ($lead->email) {
            try {
                Mail::to($lead->email)->send(new WelcomeLeadMail($lead));
            } catch (\Exception $e) {
                // Ignore or log mail errors locally
            }
        }



        return $lead;
    }

    /**
     * Update lead details and log activity.
     */
    public function updateLead(Lead $lead, array $data, ?int $userId = null): Lead
    {
        $originalStatus = $lead->status;
        $originalAssignee = $lead->assigned_to;

        $lead->update($data);

        $currentUserId = $userId ?? Auth::id() ?? 1;

        if ($originalStatus !== $lead->status) {
            $this->logActivity(
                $lead->id,
                $currentUserId,
                'Status Changed',
                "Status changed from '{$originalStatus}' to '{$lead->status}'"
            );

            if ($lead->status === 'Closed Lost' && $lead->email) {
                try {
                    Mail::to($lead->email)->send(new PoliteClosingMail($lead));
                } catch (\Exception $e) {
                    // Ignore or log
                }
            }


        }

        if ($originalAssignee !== $lead->assigned_to) {
            $assigneeName = $lead->assignedOfficer ? $lead->assignedOfficer->name : 'Unassigned';
            $this->logActivity(
                $lead->id,
                $currentUserId,
                'Updated',
                "Assigned officer updated to: {$assigneeName}"
            );
        }

        return $lead;
    }

    /**
     * Update lead status explicitly.
     */
    public function updateStatus(Lead $lead, string $status, ?int $userId = null): void
    {
        $originalStatus = $lead->status;
        if ($originalStatus === $status) {
            return;
        }

        $lead->status = $status;
        $lead->save();

        $this->logActivity(
            $lead->id,
            $userId ?? Auth::id() ?? 1,
            'Status Changed',
            "Status changed from '{$originalStatus}' to '{$status}'"
        );

        if ($status === 'Closed Lost' && $lead->email) {
            try {
                Mail::to($lead->email)->send(new PoliteClosingMail($lead));
            } catch (\Exception $e) {
                // Ignore or log
            }
        }


    }

    /**
     * Log helper for lead activities.
     */
    public function logActivity(int $leadId, int $userId, string $type, string $description): LeadActivity
    {
        return LeadActivity::create([
            'lead_id' => $leadId,
            'user_id' => $userId,
            'activity_type' => $type,
            'description' => $description,
        ]);
    }
}
