<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignContact;
use App\Services\CampaignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    protected $campaignService;

    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }

    public function index()
    {
        $campaigns = Campaign::with('creator', 'branch')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        // Let's get list of branches for segment filter if needed, and list of users/sales officers
        $users = \App\Models\User::orderBy('name')->get();
        return view('campaigns.create', compact('users'));
    }

    public function store(Request $request)
    {
        if ($request->input('type') !== 'email') {
            $request->merge(['body' => $request->input('body_plain')]);
        }

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:email,sms,whatsapp',
            'subject'          => 'required_if:type,email|nullable|string|max:255',
            'body'             => 'required|string',
            'from_name'        => 'nullable|string|max:255',
            'from_email'       => 'nullable|email|max:255',
            'audience_status'  => 'nullable|string|max:50',
            'audience_source'  => 'nullable|string|max:50',
        ]);

        $filters = [];
        if (!empty($validated['audience_status'])) {
            $filters['status'] = $validated['audience_status'];
        }
        if (!empty($validated['audience_source'])) {
            $filters['lead_source'] = $validated['audience_source'];
        }

        $campaign = Campaign::create([
            'name'             => $validated['name'],
            'type'             => $validated['type'],
            'status'           => 'draft',
            'subject'          => $validated['subject'] ?? null,
            'body'             => $validated['body'],
            'from_name'        => $validated['from_name'] ?? null,
            'from_email'       => $validated['from_email'] ?? null,
            'audience_segment' => !empty($filters) ? 'custom' : 'all',
            'audience_filters' => $filters,
            'created_by'       => Auth::id() ?? 1,
            'branch_id'        => Auth::user()?->branch_id,
        ]);

        // Pre-calculate audience
        $this->campaignService->prepareContacts($campaign);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaign created as draft. Review details below.');
    }

    public function previewAudience(Request $request)
    {
        $type = $request->input('type', 'email');
        $status = $request->input('audience_status');
        $source = $request->input('audience_source');

        $query = \App\Models\Lead::query()->withoutGlobalScopes();
        if ($type === 'email') {
            $query->whereNotNull('email')->where('email', '!=', '');
        } else {
            $query->whereNotNull('phone_number')->where('phone_number', '!=', '');
        }

        if ($status) $query->where('status', $status);
        if ($source) $query->where('lead_source', $source);

        return response()->json(['count' => $query->count()]);
    }

    /**
     * Send an instant test email of the draft campaign to the current logged-in user or specified test email.
     */
    public function sendTest(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
            'subject'    => 'required|string',
            'body'       => 'required|string',
        ]);

        $testRecipient = $request->input('test_email');
        $subject       = '[TEST PREVIEW] ' . $request->input('subject');
        $htmlBody      = $request->input('body');

        // Replace sample tags
        $placeholders = [
            '@{{name}}'           => Auth::user()->name,
            '@{{email}}'          => $testRecipient,
            '@{{phone}}'          => Auth::user()->phone_number ?? '+234 800 000 0000',
            '@{{property_name}}'  => 'RICAF Signature Court, Ikoyi',
            '@{{property_price}}' => '₦185,000,000',
            '@{{company_name}}'   => \App\Models\CompanySetting::getCached()?->company_name ?? 'RICAF Nigeria Limited',
            '@{{company_phone}}'  => \App\Models\CompanySetting::getCached()?->phone ?? '+234 800 RICAF CRM',
            '@{{company_email}}'  => \App\Models\CompanySetting::getCached()?->email ?? 'info@ricafltd.com',
            '@{{current_date}}'   => now()->format('d M, Y'),
            '@{{unsubscribe_url}}'=> '#',
        ];
        $finalHtml = str_replace(array_keys($placeholders), array_values($placeholders), $htmlBody);

        try {
            \Illuminate\Support\Facades\Mail::html($finalHtml, function ($message) use ($testRecipient, $subject) {
                $message->to($testRecipient)
                        ->subject($subject);
            });

            return response()->json([
                'success' => true,
                'message' => "✅ Test email successfully delivered to {$testRecipient}!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Mail Error: " . $e->getMessage()
            ], 500);
        }
    }

    public function show(Campaign $campaign)
    {
        $campaign->load('creator', 'branch');
        $analytics = $this->campaignService->analytics($campaign);
        return view('campaigns.show', compact('campaign', 'analytics'));
    }

    public function send(Campaign $campaign)
    {
        if (!in_array($campaign->status, ['draft', 'paused'])) {
            return redirect()->route('campaigns.show', $campaign)
                ->with('error', 'Campaign cannot be sent from its current status.');
        }

        // Prepare contacts just in case they aren't prepared or were modified
        $this->campaignService->prepareContacts($campaign);

        if ($campaign->audience_count === 0) {
            return redirect()->route('campaigns.show', $campaign)
                ->with('error', 'Cannot dispatch campaign with zero recipients.');
        }

        $this->campaignService->dispatch($campaign);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaign dispatch started in background.');
    }

    public function previewAudience(Request $request)
    {
        $validated = $request->validate([
            'type'             => 'required|in:email,sms,whatsapp',
            'audience_status'  => 'nullable|string',
            'audience_source'  => 'nullable|string',
        ]);

        $filters = [];
        if (!empty($validated['audience_status'])) {
            $filters['status'] = $validated['audience_status'];
        }
        if (!empty($validated['audience_source'])) {
            $filters['lead_source'] = $validated['audience_source'];
        }

        // Temporary campaign instance to run audience builder
        $campaign = new Campaign([
            'type'             => $validated['type'],
            'audience_filters' => $filters,
            'branch_id'        => Auth::user()?->branch_id,
        ]);

        $count = $this->campaignService->buildAudience($campaign)->count();

        return response()->json(['count' => $count]);
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return redirect()->route('campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }

    public function trackOpen($token)
    {
        $contact = CampaignContact::where('tracking_token', $token)->first();

        if ($contact && $contact->status !== 'opened' && $contact->status !== 'clicked') {
            $contact->update([
                'status' => 'opened',
                'opened_at' => now(),
            ]);
            $contact->campaign()->increment('opened_count');
        }

        // Return 1x1 transparent pixel response
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return response($pixel, 200, ['Content-Type' => 'image/gif']);
    }

    public function trackClick($token)
    {
        $contact = CampaignContact::where('tracking_token', $token)->first();

        if ($contact) {
            if ($contact->status !== 'clicked') {
                $contact->update([
                    'status' => 'clicked',
                    'clicked_at' => now(),
                ]);
                
                // If it wasn't opened, mark opened too
                if (!$contact->opened_at) {
                    $contact->update(['opened_at' => now()]);
                    $contact->campaign()->increment('opened_count');
                }

                $contact->campaign()->increment('clicked_count');
            }
        }

        // Redirect to portal/site dashboard or landing page
        return redirect('/');
    }
}
