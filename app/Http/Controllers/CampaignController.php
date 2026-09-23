<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignContact;
use App\Models\Property;
use App\Services\CampaignService;
use App\Mail\CampaignMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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

        // Get properties that have images for instant 1-click insertion into campaigns
        $properties = Property::withoutGlobalScopes()
            ->select('id', 'name', 'estate_name', 'images', 'price', 'location')
            ->whereNotNull('images')
            ->orderBy('name')
            ->get();

        return view('campaigns.create', compact('users', 'properties'));
    }

    /**
     * Async image upload handler for WYSIWYG editor and attachments.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,jpg,webp,gif|max:10240',
        ]);

        if (!$request->hasFile('image') || !$request->file('image')->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid image file received.',
            ], 422);
        }

        $file = $request->file('image');
        $originalName = $file->getClientOriginalName();
        $mime = $file->getClientMimeType();
        $bytes = $file->getSize();

        // Store under storage/app/public/campaigns/images
        $path = $file->store('campaigns/images', 'public');
        $url = asset('storage/' . $path);
        $formattedSize = $this->formatBytes($bytes);

        return response()->json([
            'success'   => true,
            'url'       => $url,
            'path'      => $path,
            'name'      => $originalName,
            'size'      => $formattedSize,
            'mime'      => $mime,
            'message'   => 'Image uploaded successfully.',
        ]);
    }

    public function store(Request $request)
    {
        if ($request->input('type') !== 'email') {
            $request->merge(['body' => $request->input('body_plain')]);
        }

        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'type'                 => 'required|in:email,sms,whatsapp',
            'subject'              => 'required_if:type,email|nullable|string|max:255',
            'body'                 => 'required|string',
            'from_name'            => 'nullable|string|max:255',
            'from_email'           => 'nullable|email|max:255',
            'audience_status'      => 'nullable|string|max:50',
            'audience_source'      => 'nullable|string|max:50',
            'attachments.*'        => 'nullable|file|mimes:jpeg,png,jpg,webp,gif,pdf|max:10240',
            'existing_attachments' => 'nullable|string',
        ]);

        $filters = [];
        if (!empty($validated['audience_status'])) {
            $filters['status'] = $validated['audience_status'];
        }
        if (!empty($validated['audience_source'])) {
            $filters['lead_source'] = $validated['audience_source'];
        }

        // Process attachments (both file uploads and existing inline attachments)
        $attachments = [];

        if (!empty($request->input('existing_attachments'))) {
            $decoded = json_decode($request->input('existing_attachments'), true);
            if (is_array($decoded)) {
                foreach ($decoded as $att) {
                    if (!empty($att['path']) && !empty($att['name'])) {
                        $attachments[] = [
                            'name' => $att['name'],
                            'path' => $att['path'],
                            'size' => $att['size'] ?? 'N/A',
                            'mime' => $att['mime'] ?? 'image/jpeg',
                            'url'  => !empty($att['url']) ? $att['url'] : asset('storage/' . $att['path']),
                        ];
                    }
                }
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('campaigns/attachments', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $this->formatBytes($file->getSize()),
                        'mime' => $file->getClientMimeType(),
                        'url'  => asset('storage/' . $path),
                    ];
                }
            }
        }

        $campaign = Campaign::create([
            'name'             => $validated['name'],
            'type'             => $validated['type'],
            'status'           => 'draft',
            'subject'          => $validated['subject'] ?? null,
            'body'             => $validated['body'],
            'from_name'        => $validated['from_name'] ?? null,
            'from_email'       => $validated['from_email'] ?? null,
            'attachments'      => !empty($attachments) ? $attachments : null,
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

    /**
     * Send an instant test email of the draft campaign to the current logged-in user or specified test email.
     */
    public function sendTest(Request $request)
    {
        $request->validate([
            'test_email'        => 'required|email',
            'subject'           => 'required|string',
            'body'              => 'required|string',
            'attachments_json'  => 'nullable|string',
        ]);

        $testRecipient = $request->input('test_email');
        $subject       = '[TEST PREVIEW] ' . $request->input('subject');
        $htmlBody      = $request->input('body');

        // Parse any preview attachments
        $attachments = [];
        if ($request->filled('attachments_json')) {
            $decoded = json_decode($request->input('attachments_json'), true);
            if (is_array($decoded)) {
                $attachments = $decoded;
            }
        }

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
            Mail::to($testRecipient)->send(
                new CampaignMail(
                    $subject,
                    $finalHtml,
                    $request->input('from_email'),
                    $request->input('from_name'),
                    $attachments
                )
            );

            $attachmentNotice = !empty($attachments) ? ' (' . count($attachments) . ' attachment(s) included)' : '';

            return response()->json([
                'success' => true,
                'message' => "✅ Test email successfully delivered to {$testRecipient}!{$attachmentNotice}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Mail Error: " . $e->getMessage()
            ], 500);
        }
    }

    private function formatBytes($bytes, $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function show(Campaign $campaign)
    {
        $campaign->load('creator', 'branch');
        $analytics = $this->campaignService->analytics($campaign);
        return view('campaigns.show', compact('campaign', 'analytics'));
    }

    public function analyticsOverview()
    {
        // Overall KPI Totals
        $totalCampaigns = Campaign::count();
        $totalAudience = (int) Campaign::sum('audience_count');
        $totalSent = (int) Campaign::sum('sent_count');
        $totalOpened = (int) Campaign::sum('opened_count');
        $totalClicked = (int) Campaign::sum('clicked_count');
        $totalUnsubscribed = (int) Campaign::sum('unsubscribed_count');
        $failedCount = CampaignContact::where('status', 'failed')->count();

        $avgOpenRate = $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 1) : 0;
        $avgClickRate = $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 1) : 0;
        $deliveryRate = $totalAudience > 0 ? round(($totalSent / $totalAudience) * 100, 1) : 0;

        // Channel breakdown (email, sms, whatsapp)
        $channelCounts = Campaign::select(
            'type',
            DB::raw('count(*) as count'),
            DB::raw('COALESCE(sum(sent_count), 0) as total_sent'),
            DB::raw('COALESCE(sum(opened_count), 0) as total_opened'),
            DB::raw('COALESCE(sum(clicked_count), 0) as total_clicked')
        )
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        // Status breakdown
        $statusCounts = Campaign::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Monthly sent & engagement trend (last 6 months)
        $driver = DB::connection()->getDriverName();
        $dateExpr = $driver === 'sqlite' ? "strftime('%Y-%m', created_at)" : 'DATE_FORMAT(created_at, "%Y-%m")';
        $monthLabelExpr = $driver === 'sqlite' ? "strftime('%b %Y', created_at)" : 'DATE_FORMAT(created_at, "%b %Y")';

        $monthlyData = Campaign::where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw("{$dateExpr} as ym"),
                DB::raw("{$monthLabelExpr} as month_label"),
                DB::raw('count(*) as campaign_count'),
                DB::raw('COALESCE(sum(sent_count), 0) as total_sent'),
                DB::raw('COALESCE(sum(opened_count), 0) as total_opened'),
                DB::raw('COALESCE(sum(clicked_count), 0) as total_clicked')
            )
            ->groupBy('ym', 'month_label')
            ->orderBy('ym')
            ->get();

        // Top 5 campaigns by open rate (where sent_count > 0)
        $topCampaigns = Campaign::where('sent_count', '>', 0)
            ->orderByRaw('(opened_count / sent_count) DESC')
            ->limit(5)
            ->get();

        // Recent 5 campaigns
        $recentCampaigns = Campaign::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('campaigns.analytics', compact(
            'totalCampaigns',
            'totalAudience',
            'totalSent',
            'totalOpened',
            'totalClicked',
            'totalUnsubscribed',
            'failedCount',
            'avgOpenRate',
            'avgClickRate',
            'deliveryRate',
            'channelCounts',
            'statusCounts',
            'monthlyData',
            'topCampaigns',
            'recentCampaigns'
        ));
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
