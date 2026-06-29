<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignContact;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $campaign;
    public $contact;

    /**
     * Create a new job instance.
     */
    public function __construct(Campaign $campaign, CampaignContact $contact)
    {
        $this->campaign = $campaign;
        $this->contact = $contact;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $lead = $this->contact->lead;

        if (!$lead || !$lead->phone_number) {
            $this->contact->update([
                'status' => 'failed',
                'failure_reason' => 'Lead has no phone number',
            ]);
            return;
        }

        try {
            $body = $this->campaign->body;
            
            $company = \App\Models\CompanySetting::first();
            $prop = $lead->propertyInterest;
            $agent = $lead->assignedOfficer;
            
            $replacements = [
                '{{name}}'              => $lead->full_name,
                '{{email}}'             => $lead->email ?? 'N/A',
                '{{phone}}'             => $lead->phone_number,
                '{{address}}'           => $lead->preferred_location ?? 'N/A',
                '{{lead_source}}'       => $lead->lead_source,
                '{{lead_status}}'       => $lead->status,
                '{{assigned_agent}}'    => $agent ? $agent->name : 'N/A',
                '{{property_name}}'     => $prop ? $prop->name : 'N/A',
                '{{property_type}}'     => $prop ? $prop->property_type : 'N/A',
                '{{property_location}}' => $prop ? ($prop->estate_name ?? $prop->location) : 'N/A',
                '{{property_city}}'     => $prop ? $prop->location : 'N/A',
                '{{property_price}}'    => $prop ? '₦' . number_format($prop->price, 2) : 'N/A',
                '{{property_size}}'     => 'N/A',
                '{{property_unit_type}}' => 'N/A',
                '{{company_name}}'      => $company ? $company->company_name : 'NAW PropertyFlow CRM',
                '{{company_phone}}'     => $company ? ($company->phone ?? 'N/A') : 'N/A',
                '{{company_email}}'     => $company ? ($company->email ?? 'N/A') : 'N/A',
                '{{current_date}}'      => now()->format('F d, Y'),
                '{{promo_expiry_date}}' => now()->addDays(14)->format('F d, Y'),
                '{{open_day_date}}'     => now()->addDays(7)->format('F d, Y'),
                '{{unsubscribe_url}}'   => route('login'),
                '{{click_tracking_url}}' => '#',
            ];

            if ($this->contact->tracking_token) {
                $replacements['{{click_tracking_url}}'] = route('campaigns.track.click', ['token' => $this->contact->tracking_token]);
            }

            $body = str_replace(array_keys($replacements), array_values($replacements), $body);

            // Log SMS simulation
            Log::info("Simulating SMS/WhatsApp campaign delivery", [
                'campaign_id' => $this->campaign->id,
                'type' => $this->campaign->type,
                'recipient' => $lead->phone_number,
                'body' => $body,
            ]);

            // For simulation, we instantly mark as sent/delivered
            $this->contact->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $this->campaign->increment('sent_count');
        } catch (\Exception $e) {
            Log::error('SendCampaignSmsJob failed', [
                'campaign_id' => $this->campaign->id,
                'contact_id' => $this->contact->id,
                'error' => $e->getMessage()
            ]);

            $this->contact->update([
                'status' => 'failed',
                'failure_reason' => $e->getMessage(),
            ]);
        }
    }
}
