<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignContact;
use App\Models\Lead;
use App\Jobs\SendCampaignEmailJob;
use App\Jobs\SendCampaignSmsJob;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Bus;

class CampaignService
{
    /**
     * Build audience lead IDs from campaign filters.
     */
    public function buildAudience(Campaign $campaign): \Illuminate\Support\Collection
    {
        $filters = $campaign->audience_filters ?? [];

        $query = Lead::query()->withoutGlobalScopes(); // bypass branch scope for global campaigns

        // Apply branch scope if campaign is branch-specific
        if ($campaign->branch_id) {
            $query->where('branch_id', $campaign->branch_id);
        }

        // Apply audience segment filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['lead_source'])) {
            $query->where('lead_source', $filters['lead_source']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        // Only leads with an email (for email campaigns)
        if ($campaign->type === 'email') {
            $query->whereNotNull('email')->where('email', '!=', '');
        }

        // Only leads with a phone (for SMS/WhatsApp)
        if (in_array($campaign->type, ['sms', 'whatsapp'])) {
            $query->whereNotNull('phone_number')->where('phone_number', '!=', '');
        }

        return $query->pluck('id');
    }

    /**
     * Prepare campaign contacts and update audience_count.
     */
    public function prepareContacts(Campaign $campaign): int
    {
        $leadIds = $this->buildAudience($campaign);

        foreach ($leadIds as $leadId) {
            CampaignContact::firstOrCreate(
                ['campaign_id' => $campaign->id, 'lead_id' => $leadId],
                ['status' => 'pending', 'tracking_token' => Str::uuid()]
            );
        }

        $count = $campaign->contacts()->count();
        $campaign->update(['audience_count' => $count]);

        return $count;
    }

    /**
     * Dispatch send jobs for all pending contacts.
     */
    public function dispatch(Campaign $campaign): void
    {
        $campaign->update(['status' => 'sending', 'sent_at' => now()]);

        $contacts = $campaign->contacts()->where('status', 'pending')->with('lead')->get();

        $jobs = $contacts->map(function (CampaignContact $contact) use ($campaign) {
            return match ($campaign->type) {
                'email'    => new SendCampaignEmailJob($campaign, $contact),
                'sms'      => new SendCampaignSmsJob($campaign, $contact),
                'whatsapp' => new SendCampaignSmsJob($campaign, $contact), // same job, different channel
                default    => null,
            };
        })->filter();

        Bus::batch($jobs->all())
            ->finally(function () use ($campaign) {
                $campaign->update(['status' => 'sent']);
            })
            ->dispatch();
    }

    /**
     * Get analytics summary for a campaign.
     */
    public function analytics(Campaign $campaign): array
    {
        return [
            'audience_count'    => $campaign->audience_count,
            'sent_count'        => $campaign->sent_count,
            'opened_count'      => $campaign->opened_count,
            'clicked_count'     => $campaign->clicked_count,
            'unsubscribed_count'=> $campaign->unsubscribed_count,
            'failed_count'      => $campaign->contacts()->where('status', 'failed')->count(),
            'open_rate'         => $campaign->openRate(),
            'click_rate'        => $campaign->clickRate(),
            'delivery_rate'     => $campaign->sent_count > 0
                ? round(($campaign->sent_count / max($campaign->audience_count, 1)) * 100, 1)
                : 0,
        ];
    }
}
