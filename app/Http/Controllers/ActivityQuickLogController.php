<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityQuickLogController extends Controller
{
    /**
     * Asynchronous click-to-dial or click-to-WhatsApp background timestamp logger.
     */
    public function logClick(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'channel' => 'required|string|in:whatsapp,call,sms',
        ]);

        $user = Auth::user();
        $channel = strtolower($validated['channel']);

        $lead->last_contacted_at = now();
        $lead->last_contact_channel = $channel;

        // Advance initial 'New' lead to 'Contacted' automatically
        if ($lead->status === 'New') {
            $lead->status = 'Contacted';
        }
        $lead->save();

        $channelTitle = $channel === 'whatsapp' ? 'WhatsApp' : ucfirst($channel);
        LeadActivity::create([
            'lead_id'       => $lead->id,
            'user_id'       => $user->id,
            'activity_type' => "{$channelTitle} Touchpoint",
            'description'   => "{$channelTitle} contact initiated by {$user->name} via CRM quick action.",
        ]);

        return response()->json([
            'success'           => true,
            'lead_id'           => $lead->id,
            'channel'           => $channel,
            'new_status'        => $lead->status,
            'last_contacted_at' => $lead->last_contacted_at->format('d M Y, h:i A'),
        ]);
    }

    /**
     * 1-Click Milestone / Outcome Logger for ongoing conversations and calls.
     */
    public function logOutcome(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'channel'             => 'required|string|in:whatsapp,call,meeting,office_visit,sms',
            'outcome'             => 'required|string|max:100',
            'notes'               => 'nullable|string|max:1000',
            'next_follow_up_date' => 'nullable|date',
        ]);

        $user = Auth::user();
        $channel = $validated['channel'];
        $outcome = $validated['outcome'];
        $notes = $validated['notes'] ?? '';

        $lead->last_contacted_at = now();
        $lead->last_contact_channel = $channel;

        // Meaningful state progression
        if (!in_array($lead->status, ['Closed Won', 'Closed Lost'])) {
            if ($outcome === 'scheduled_inspection') {
                $lead->status = 'Inspection Scheduled';
            } elseif ($outcome === 'negotiation') {
                $lead->status = 'Negotiation';
            } elseif ($outcome === 'payment_promised') {
                $lead->status = 'Payment Processing';
            } elseif ($lead->status === 'New') {
                $lead->status = 'Contacted';
            }
        }
        $lead->save();

        // Human-readable labels for standard outcomes
        $outcomeLabels = [
            'active_chat'          => 'Active Ongoing Discussion',
            'shared_brochure'      => 'Shared Price List & Brochure',
            'scheduled_inspection' => 'Site Inspection Booked',
            'office_visit'         => 'Completed Office Visit',
            'negotiation'          => 'Price / Unit Negotiation',
            'payment_promised'     => 'Commitment to Pay Received',
            'call_back_later'      => 'Client Requested Call Back',
            'switched_off'         => 'Number Switched Off / Busy',
            'not_interested'       => 'Client Not Interested',
        ];

        $outcomeText = $outcomeLabels[$outcome] ?? ucwords(str_replace('_', ' ', $outcome));
        $channelTitle = $channel === 'whatsapp' ? 'WhatsApp' : ucwords(str_replace('_', ' ', $channel));

        $description = "{$channelTitle}: {$outcomeText}";
        if (!empty($notes)) {
            $description .= " — " . $notes;
        }

        LeadActivity::create([
            'lead_id'       => $lead->id,
            'user_id'       => $user->id,
            'activity_type' => "{$channelTitle} Engagement",
            'description'   => $description,
        ]);

        // Automatically schedule follow-up if date is set
        if (!empty($validated['next_follow_up_date'])) {
            FollowUp::create([
                'lead_id'  => $lead->id,
                'type'     => in_array($channel, ['meeting', 'office_visit']) ? 'Meeting' : 'Call',
                'due_date' => $validated['next_follow_up_date'],
                'notes'    => "Follow-up regarding {$outcomeText}" . (!empty($notes) ? ": {$notes}" : ""),
                'status'   => 'Pending',
            ]);
        }

        return response()->json([
            'success'           => true,
            'lead_id'           => $lead->id,
            'new_status'        => $lead->status,
            'description'       => $description,
            'last_contacted_at' => $lead->last_contacted_at->format('d M Y, h:i A'),
        ]);
    }

    /**
     * Batch Daily Pulse Logger (e.g. at end of day, confirming chats with 10 leads in 1 click).
     */
    public function dailyPulse(Request $request)
    {
        $validated = $request->validate([
            'lead_ids'   => 'required|array|min:1',
            'lead_ids.*' => 'exists:leads,id',
            'channel'    => 'required|string|in:whatsapp,call,meeting',
            'summary'    => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $channel = $validated['channel'];
        $channelTitle = $channel === 'whatsapp' ? 'WhatsApp' : ucfirst($channel);
        $summary = $validated['summary'] ?? "Ongoing active {$channelTitle} chat confirmed during daily log.";

        $leads = Lead::whereIn('id', $validated['lead_ids'])->get();

        foreach ($leads as $lead) {
            // Check authorization: sales executives can only pulse their own or unassigned leads
            if (in_array($user->role, ['sales_executive', 'sales_agent']) && $lead->assigned_to && $lead->assigned_to !== $user->id) {
                continue;
            }

            $lead->last_contacted_at = now();
            $lead->last_contact_channel = $channel;
            if ($lead->status === 'New') {
                $lead->status = 'Contacted';
            }
            $lead->save();

            LeadActivity::create([
                'lead_id'       => $lead->id,
                'user_id'       => $user->id,
                'activity_type' => "{$channelTitle} Pulse",
                'description'   => $summary,
            ]);
        }

        return response()->json([
            'success'       => true,
            'updated_count' => $leads->count(),
            'message'       => "Successfully logged {$channelTitle} activity for " . $leads->count() . " leads.",
        ]);
    }
}
