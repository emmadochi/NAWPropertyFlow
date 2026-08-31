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
    public function createLead(array $data, ?int $userId = null, bool $sendWelcomeMail = true): Lead
    {
        // Safe-filter data attributes so missing columns in tenant database do not throw errors
        $filteredData = [];
        foreach ($data as $key => $val) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('leads', $key)) {
                $filteredData[$key] = $val;
            }
        }

        $lead = Lead::create(!empty($filteredData) ? $filteredData : $data);

        $currentUserId = $userId ?? Auth::id();

        try {
            $this->logActivity(
                $lead->id,
                $currentUserId,
                'Created',
                'Lead created successfully.'
            );

            if (!empty($lead->assigned_to)) {
                $officer = \App\Models\User::find($lead->assigned_to);
                $officerName = $officer ? $officer->name : 'Sales Officer';
                $this->logActivity(
                    $lead->id,
                    $currentUserId,
                    'Updated',
                    "Lead assigned to Sales Officer: " . $officerName
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Lead activity logging failed', ['error' => $e->getMessage()]);
        }

        if ($sendWelcomeMail && !empty($lead->email)) {
            try {
                Mail::to($lead->email)->send(new WelcomeLeadMail($lead));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Welcome lead mail sending failed', ['error' => $e->getMessage()]);
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
                } catch (\Throwable $e) {
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
            } catch (\Throwable $e) {
                // Ignore or log
            }
        }


    }

    /**
     * Log helper for lead activities.
     */
    public function logActivity(?int $leadId, ?int $userId, string $type, string $description): ?LeadActivity
    {
        if (!$leadId) {
            return null;
        }

        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('lead_activities')) {
                return null;
            }

            $validUserId = $userId;
            if ($validUserId && !\App\Models\User::where('id', $validUserId)->exists()) {
                $validUserId = \App\Models\User::value('id');
            }

            if (!$validUserId) {
                $validUserId = \App\Models\User::value('id');
            }

            if (!$validUserId) {
                return null;
            }

            return LeadActivity::create([
                'lead_id' => $leadId,
                'user_id' => $validUserId,
                'activity_type' => $type,
                'description' => $description,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('LeadActivity log failed:', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
