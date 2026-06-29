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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\CampaignMail;

class SendCampaignEmailJob implements ShouldQueue
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

        if (!$lead || !$lead->email) {
            $this->contact->update([
                'status' => 'failed',
                'failure_reason' => 'Lead has no email address',
            ]);
            return;
        }

        try {
            // Replace token or placeholders if needed, e.g. {{tracking_url}} or lead fields
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
            ];

            // If there is a tracking token, append to links or replace a placeholder
            if ($this->contact->tracking_token) {
                $trackingUrl = route('campaigns.track.click', ['token' => $this->contact->tracking_token]);
                $replacements['{{click_tracking_url}}'] = $trackingUrl;
                
                // Pixel tracking
                $pixelUrl = route('campaigns.track.open', ['token' => $this->contact->tracking_token]);
                $body .= '<img src="' . $pixelUrl . '" width="1" height="1" style="display:none;" />';
            } else {
                $replacements['{{click_tracking_url}}'] = '#';
            }

            $body = str_replace(array_keys($replacements), array_values($replacements), $body);

            Mail::to($lead->email, $lead->full_name)->send(
                new CampaignMail(
                    $this->campaign->subject ?? 'Campaign Update',
                    $body,
                    $this->campaign->from_email,
                    $this->campaign->from_name
                )
            );

            $this->contact->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $this->campaign->increment('sent_count');
        } catch (\Exception $e) {
            Log::error('SendCampaignEmailJob failed', [
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
