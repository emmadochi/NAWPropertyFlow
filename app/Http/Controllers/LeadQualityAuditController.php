<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadQualityAuditController extends Controller
{
    /** Hot lead statuses used consistently across methods */
    protected const HOT_STATUSES = ['Negotiation', 'Payment Processing', 'Inspection Scheduled'];

    /**
     * Lead Quality & Executive Audit Board dashboard.
     */
    public function index(Request $request)
    {
        $user   = Auth::user();
        $isExec = $user->role === 'sales_executive' || $user->isSalesExecutive();
        $isAdminOrManager = in_array($user->role, ['super_admin', 'company_admin', 'sales_manager', 'hr']);

        // --- Resolve officer filter ---
        $officerId = $isExec ? $user->id : ($request->input('officer_id') ?: null);

        // --- Resolve time period ---
        $period = $request->input('period', 'this_week');
        [$startDate, $endDate] = $this->resolvePeriod($period, $request);

        // --- Base query (manual branch scoping — bypasses global scope deliberately) ---
        $baseQuery = Lead::withoutGlobalScopes();
        if (!in_array($user->role, ['super_admin', 'company_admin'])) {
            if ($user->branch_id) {
                $baseQuery->where('branch_id', $user->branch_id);
            }
        }
        if ($officerId) {
            $baseQuery->where('assigned_to', $officerId);
        }
        $baseQuery->whereBetween('created_at', [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')]);

        // --- KPI Aggregates ---
        $total        = (clone $baseQuery)->count();
        $flagged      = (clone $baseQuery)->where('is_flagged_fake', true)->count();
        $unreachable  = (clone $baseQuery)->where('unreachable_count', '>=', 1)->where('is_flagged_fake', false)->count();
        $contacted    = (clone $baseQuery)->whereNotNull('last_contacted_at')->where('is_flagged_fake', false)->count();
        $uncontacted  = (clone $baseQuery)->whereNull('last_contacted_at')->count();
        $closedWon    = (clone $baseQuery)->where('status', 'Closed Won')->count();
        $convRate     = $total > 0 ? round(($closedWon / $total) * 100, 1) : 0;

        // Incomplete phone: digit count < 11 (MySQL safe approach using CHAR_LENGTH)
        $driver = DB::connection()->getDriverName();
        $incomplete = $driver === 'sqlite'
            ? (clone $baseQuery)->whereRaw("LENGTH(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone_number,'0',''),'1',''),'2',''),'3',''),'4',''),'5',''),'6',''),'7',''),'8',''),'9','')) < LENGTH(phone_number) - 10")->count()
            : (clone $baseQuery)->whereRaw("CHAR_LENGTH(REGEXP_REPLACE(COALESCE(phone_number,''), '[^0-9]', '')) < 11")->count();

        // WhatsApp Only: phone call failed/missing/incomplete but valid WhatsApp line exists
        $whatsappOnlyQuery = (clone $baseQuery)
            ->where(function ($q) use ($driver) {
                $q->where('unreachable_count', '>=', 1)
                  ->orWhereNull('phone_number')
                  ->orWhere('phone_number', '');
                if ($driver === 'sqlite') {
                    $q->orWhereRaw("LENGTH(REPLACE(phone_number,'','')) < 11");
                } else {
                    $q->orWhereRaw("CHAR_LENGTH(REGEXP_REPLACE(COALESCE(phone_number,''), '[^0-9]', '')) < 11");
                }
            })
            ->whereNotNull('whatsapp_number')
            ->where('whatsapp_number', '!=', '')
            ->where('is_flagged_fake', false);

        if ($driver === 'sqlite') {
            $whatsappOnlyQuery->whereRaw("LENGTH(REPLACE(whatsapp_number,'','')) >= 10");
        } else {
            $whatsappOnlyQuery->whereRaw("CHAR_LENGTH(REGEXP_REPLACE(COALESCE(whatsapp_number,''), '[^0-9]', '')) >= 10");
        }
        $whatsappOnly = $whatsappOnlyQuery->count();

        // --- Temperature Breakdown ---
        $hot  = (clone $baseQuery)->whereIn('status', self::HOT_STATUSES)->count();
        $cold = (clone $baseQuery)->where(function ($q) {
            $q->whereNull('last_contacted_at')
              ->orWhere('last_contacted_at', '<', now()->subDays(14));
        })->where('is_flagged_fake', false)->count();
        $warm = max(0, $total - $hot - $cold - $flagged);

        // --- Average Contact Speed (hours from lead creation to first contact) ---
        $avgContactHoursRaw = $driver === 'sqlite'
            ? (clone $baseQuery)->whereNotNull('last_contacted_at')
                ->selectRaw("AVG((strftime('%s', last_contacted_at) - strftime('%s', created_at)) / 3600.0) as avg_hours")
                ->value('avg_hours')
            : (clone $baseQuery)->whereNotNull('last_contacted_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, last_contacted_at)) as avg_hours')
                ->value('avg_hours');
        $avgContactHours = $avgContactHoursRaw ? round($avgContactHoursRaw, 1) : null;

        // --- Health breakdown for chart ---
        $healthBreakdown = [
            'Verified Active'   => max(0, $contacted - $unreachable),
            'WhatsApp Only'     => $whatsappOnly,
            'Unreachable'       => max(0, $unreachable - $whatsappOnly),
            'Flagged Fake'      => $flagged,
            'Incomplete Number' => $incomplete,
            'Never Contacted'   => $uncontacted,
        ];

        // --- Temperature breakdown for chart ---
        $temperatureBreakdown = [
            'Hot 🔥'  => $hot,
            'Warm 🌤️' => $warm,
            'Cold ❄️' => $cold + $flagged,
        ];

        // --- Executive Comparison Table (admin/manager only) ---
        $executiveComparison = [];
        if ($isAdminOrManager && !$officerId) {
            $executives = User::whereIn('role', ['sales_executive', 'telemarketer', 'sales_manager'])
                ->where(function ($q) { $q->where('status', 'active')->orWhereNull('status'); })
                ->when(
                    $user->branch_id && !in_array($user->role, ['super_admin', 'company_admin']),
                    fn ($q) => $q->where('branch_id', $user->branch_id)
                )
                ->orderBy('name')
                ->get();

            foreach ($executives as $exec) {
                $eq = Lead::withoutGlobalScopes()
                    ->where('assigned_to', $exec->id)
                    ->whereBetween('created_at', [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')]);
                $execTotal = (clone $eq)->count();
                if ($execTotal === 0) continue;

                $execFlagged   = (clone $eq)->where('is_flagged_fake', true)->count();
                $execHot       = (clone $eq)->whereIn('status', self::HOT_STATUSES)->count();
                $execWon       = (clone $eq)->where('status', 'Closed Won')->count();
                $execContacted = (clone $eq)->whereNotNull('last_contacted_at')->count();
                $fakePct       = round(($execFlagged / $execTotal) * 100, 1);
                $convRateExec  = round(($execWon / $execTotal) * 100, 1);

                $executiveComparison[] = [
                    'officer'        => $exec,
                    'total'          => $execTotal,
                    'flagged'        => $execFlagged,
                    'hot'            => $execHot,
                    'won'            => $execWon,
                    'contacted'      => $execContacted,
                    'fake_pct'       => $fakePct,
                    'conv_rate'      => $convRateExec,
                    'is_high_risk'   => ($fakePct >= 30 && $execTotal >= 5),
                ];
            }
            usort($executiveComparison, fn ($a, $b) => $b['fake_pct'] <=> $a['fake_pct']);
        }

        // --- Detailed lead log (paginated) ---
        $detailQuery = (clone $baseQuery)->with([
            'assignedOfficer',
            'activities' => fn ($q) => $q->latest()->limit(1),
        ]);

        if ($request->filled('health')) {
            match ($request->input('health')) {
                'whatsapp_only' => $detailQuery->where(function ($q) use ($driver) {
                        $q->where('unreachable_count', '>=', 1)
                          ->orWhereNull('phone_number')
                          ->orWhere('phone_number', '');
                        if ($driver === 'sqlite') {
                            $q->orWhereRaw("LENGTH(REPLACE(phone_number,'','')) < 11");
                        } else {
                            $q->orWhereRaw("CHAR_LENGTH(REGEXP_REPLACE(COALESCE(phone_number,''), '[^0-9]', '')) < 11");
                        }
                    })
                    ->whereNotNull('whatsapp_number')
                    ->where('whatsapp_number', '!=', '')
                    ->when($driver === 'sqlite',
                        fn ($q) => $q->whereRaw("LENGTH(REPLACE(whatsapp_number,'','')) >= 10"),
                        fn ($q) => $q->whereRaw("CHAR_LENGTH(REGEXP_REPLACE(COALESCE(whatsapp_number,''), '[^0-9]', '')) >= 10")
                    )
                    ->where('is_flagged_fake', false),
                'flagged'     => $detailQuery->where('is_flagged_fake', true),
                'unreachable' => $detailQuery->where('unreachable_count', '>=', 1)->where('is_flagged_fake', false),
                'uncontacted' => $detailQuery->whereNull('last_contacted_at'),
                'incomplete'  => $driver === 'sqlite'
                    ? $detailQuery->whereRaw("LENGTH(REPLACE(phone_number,'','')) < 1") // fallback for sqlite
                    : $detailQuery->whereRaw("CHAR_LENGTH(REGEXP_REPLACE(COALESCE(phone_number,''), '[^0-9]', '')) < 11"),
                default       => null,
            };
        }

        if ($request->filled('temp')) {
            match ($request->input('temp')) {
                'hot'  => $detailQuery->whereIn('status', self::HOT_STATUSES),
                'cold' => $detailQuery->where(function ($q) {
                    $q->whereNull('last_contacted_at')
                      ->orWhere('last_contacted_at', '<', now()->subDays(14));
                })->where('is_flagged_fake', false),
                'flagged_cold' => $detailQuery->where('is_flagged_fake', true),
                default => null,
            };
        }

        $leads = $detailQuery->orderByRaw("FIELD(is_flagged_fake, 1) DESC, last_contacted_at ASC")->paginate(20)->withQueryString();

        // --- Officers dropdown ---
        $officers = User::whereIn('role', ['sales_executive', 'telemarketer', 'sales_manager'])
            ->where(fn ($q) => $q->where('status', 'active')->orWhereNull('status'))
            ->when(
                $user->branch_id && !in_array($user->role, ['super_admin', 'company_admin']),
                fn ($q) => $q->where('branch_id', $user->branch_id)
            )
            ->orderBy('name')
            ->get();

        return view('leads.quality_audit', compact(
            'total', 'flagged', 'unreachable', 'whatsappOnly', 'contacted', 'uncontacted',
            'incomplete', 'closedWon', 'convRate',
            'hot', 'warm', 'cold',
            'avgContactHours',
            'healthBreakdown', 'temperatureBreakdown',
            'executiveComparison',
            'leads', 'officers',
            'officerId', 'period', 'startDate', 'endDate',
            'isExec', 'isAdminOrManager'
        ));
    }

    /**
     * CSV Export of current filtered lead set.
     */
    public function export(Request $request): StreamedResponse
    {
        $user      = Auth::user();
        $isExec    = $user->role === 'sales_executive' || $user->isSalesExecutive();
        $officerId = $isExec ? $user->id : ($request->input('officer_id') ?: null);
        $period    = $request->input('period', 'this_week');
        [$startDate, $endDate] = $this->resolvePeriod($period, $request);

        $query = Lead::withoutGlobalScopes()->with(['assignedOfficer', 'activities' => fn ($q) => $q->latest()->limit(1)]);
        if (!in_array($user->role, ['super_admin', 'company_admin']) && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }
        if ($officerId) {
            $query->where('assigned_to', $officerId);
        }
        $query->whereBetween('created_at', [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')]);

        $filename = 'lead_quality_audit_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Full Name', 'Phone Health', 'Temperature', 'Status',
                'Lead Source', 'Assigned To', 'Branch',
                'Last Contact Date', 'Last Channel',
                'Flagged Fake', 'Flagged Reason', 'Unreachable Count',
                'Last Activity', 'Created At',
            ]);

            $query->chunk(200, function ($chunk) use ($handle) {
                foreach ($chunk as $lead) {
                    $lastActivity = $lead->activities->first();
                    fputcsv($handle, [
                        $lead->full_name,
                        $lead->phone_health,
                        $lead->computed_temperature,
                        $lead->status,
                        $lead->lead_source,
                        $lead->assignedOfficer?->name ?? 'Unassigned',
                        $lead->branch?->name ?? '—',
                        $lead->last_contacted_at?->format('d M Y H:i') ?? 'Never',
                        $lead->last_contact_channel ?? '—',
                        $lead->is_flagged_fake ? 'Yes' : 'No',
                        $lead->flagged_reason ?? '—',
                        $lead->unreachable_count ?? 0,
                        $lastActivity?->description ?? '—',
                        $lead->created_at->format('d M Y'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Manager quick-action: clear a lead's fraud flag inline.
     */
    public function clearFlag(Request $request, Lead $lead)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['super_admin', 'company_admin', 'sales_manager', 'hr'])) {
            abort(403, 'Unauthorized action.');
        }

        $lead->update([
            'is_flagged_fake'   => false,
            'flagged_reason'    => null,
            'flagged_at'        => null,
            'unreachable_count' => 0,
        ]);

        LeadActivity::create([
            'lead_id'       => $lead->id,
            'user_id'       => $user->id,
            'activity_type' => 'Verification Update',
            'description'   => "Fraud flag manually cleared by {$user->name} after review. Number verified as active.",
        ]);

        return back()->with('success', "✅ Fraud flag cleared for {$lead->full_name}. Lead restored to active pipeline.");
    }

    /**
     * Manager quick-action: reassign lead to a different exec.
     */
    public function reassign(Request $request, Lead $lead)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['super_admin', 'company_admin', 'sales_manager', 'hr'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate(['officer_id' => 'required|exists:users,id']);
        $oldOfficer = $lead->assignedOfficer?->name ?? 'Unassigned';
        $newOfficer = User::findOrFail($request->officer_id);

        $lead->update(['assigned_to' => $request->officer_id]);

        LeadActivity::create([
            'lead_id'       => $lead->id,
            'user_id'       => $user->id,
            'activity_type' => 'Reassignment',
            'description'   => "Lead reassigned from {$oldOfficer} → {$newOfficer->name} by {$user->name}.",
        ]);

        return back()->with('success', "Lead reassigned to {$newOfficer->name} successfully.");
    }

    /**
     * Manager bulk-action: batch reassign multiple leads to a new sales executive.
     */
    public function bulkReassign(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['super_admin', 'company_admin', 'sales_manager', 'hr'])) {
            abort(403, 'Unauthorized action. Only administrators and managers can bulk reassign leads.');
        }

        $request->validate([
            'lead_ids'   => 'required|array|min:1',
            'lead_ids.*' => 'required|integer|exists:leads,id',
            'officer_id' => 'required|exists:users,id',
        ]);

        $newOfficer = User::findOrFail($request->officer_id);

        // Branch-scoping protection for branch managers
        $query = Lead::withoutGlobalScopes()->whereIn('id', $request->lead_ids);
        if (!in_array($user->role, ['super_admin', 'company_admin']) && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        $leads = $query->with('assignedOfficer')->get();
        if ($leads->isEmpty()) {
            return back()->with('error', 'No matching leads found for reassignment under your branch authorization.');
        }

        $count = 0;
        foreach ($leads as $lead) {
            $oldOfficer = $lead->assignedOfficer?->name ?? 'Unassigned';
            $lead->update(['assigned_to' => $newOfficer->id]);

            LeadActivity::create([
                'lead_id'       => $lead->id,
                'user_id'       => $user->id,
                'activity_type' => 'Bulk Reassignment',
                'description'   => "Lead bulk-reassigned from {$oldOfficer} → {$newOfficer->name} by {$user->name} via Executive Audit Board.",
            ]);
            $count++;
        }

        return back()->with('success', "✅ Successfully reassigned {$count} lead(s) to {$newOfficer->name}.");
    }

    /**
     * Set lead temperature manually (AJAX / Alpine.js).
     */
    public function setTemperature(Request $request, Lead $lead)
    {
        $user = Auth::user();
        $isExec = $user->role === 'sales_executive' || $user->isSalesExecutive();

        // Enforce ownership: sales executive can only update their own assigned leads
        if ($isExec && $lead->assigned_to !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only update the temperature of your own assigned leads.',
            ], 403);
        }

        $request->validate(['temperature' => 'required|in:hot,warm,cold']);
        $lead->update(['lead_temperature' => $request->temperature]);

        return response()->json([
            'success'     => true,
            'temperature' => $request->temperature,
            'lead_id'     => $lead->id,
        ]);
    }

    /**
     * Populate realistic demonstration audit dataset for testing all Lead Quality & Executive features.
     */
    public function seedDemoData(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['super_admin', 'company_admin', 'sales_manager'])) {
            abort(403, 'Unauthorized');
        }

        $branchId = !in_array($user->role, ['super_admin', 'company_admin']) 
            ? $user->branch_id 
            : (session('selected_branch_id') !== 'all' ? session('selected_branch_id') : null);

        // Ensure 3 distinct test sales executives exist for comparison
        $exec1 = User::firstOrCreate(
            ['email' => 'chidinma.audit@bhl.com'],
            [
                'name' => 'Chidinma Okafor (Senior Executive)',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'sales_executive',
                'job_title' => 'Senior Sales Executive',
                'department' => 'Marketing & Sales',
                'phone_number' => '08033019842',
                'branch_id' => $branchId,
                'status' => 'active',
            ]
        );

        $exec2 = User::firstOrCreate(
            ['email' => 'tunde.audit@bhl.com'],
            [
                'name' => 'Tunde Bakare (Field Realtor)',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'sales_executive',
                'job_title' => 'Realtor & Acquisitions Agent',
                'department' => 'Marketing & Sales',
                'phone_number' => '08023194821',
                'branch_id' => $branchId,
                'status' => 'active',
            ]
        );

        $exec3 = User::firstOrCreate(
            ['email' => 'amina.audit@bhl.com'],
            [
                'name' => 'Amina Danjuma (Direct Sales)',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'sales_executive',
                'job_title' => 'Sales Representative',
                'department' => 'Marketing & Sales',
                'phone_number' => '08061234509',
                'branch_id' => $branchId,
                'status' => 'active',
            ]
        );

        // Remove any previous demo records to prevent duplicates
        $oldDemoIds = Lead::withoutGlobalScopes()->where('lead_source', 'like', '[Demo/Test]%')->pluck('id');
        LeadActivity::whereIn('lead_id', $oldDemoIds)->delete();
        Lead::withoutGlobalScopes()->whereIn('id', $oldDemoIds)->delete();

        $now = now();

        $demoLeads = [
            // --- HOT LEADS (6) ---
            [
                'full_name' => 'Chief Adekunle Adeleke',
                'phone_number' => '08033019842',
                'whatsapp_number' => '08033019842',
                'email' => 'adekunle.demo@bhl.com',
                'budget_range' => '₦180M - ₦250M',
                'preferred_location' => 'Lekki Phase 1, Lagos',
                'outreach_location' => 'Victoria Island Office',
                'lead_source' => '[Demo/Test] Referral - Existing Client',
                'status' => 'Negotiation',
                'assigned_to' => $exec1->id,
                'notes' => 'High-net-worth investor. Looking for a 4-bedroom detached duplex with BQ. Drafting contract of sale.',
                'created_at' => $now->copy()->subDays(2)->subHours(5),
                'last_contacted_at' => $now->copy()->subDays(2)->subHours(3),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Dr. Fatima Al-Hassan',
                'phone_number' => '08023194821',
                'whatsapp_number' => '08023194821',
                'email' => 'fatima.demo@bhl.com',
                'budget_range' => '₦120M - ₦160M',
                'preferred_location' => 'Guzape District, Abuja',
                'outreach_location' => 'Banex Plaza Roadshow',
                'lead_source' => '[Demo/Test] Google Search Ads',
                'status' => 'Payment Processing',
                'assigned_to' => $exec1->id,
                'notes' => 'Medical director. Making initial 30% milestone deposit on 3-bedroom penthouse.',
                'created_at' => $now->copy()->subDays(3)->subHours(8),
                'last_contacted_at' => $now->copy()->subDays(3)->subHours(6),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Engr. Babatunde Fashina',
                'phone_number' => '08055123984',
                'whatsapp_number' => '08055123984',
                'email' => 'tunde.demo@bhl.com',
                'budget_range' => '₦65M - ₦90M',
                'preferred_location' => 'Epe Waterfront Corridor',
                'outreach_location' => 'Lekki Phase 1 Booth',
                'lead_source' => '[Demo/Test] Facebook Real Estate Campaign',
                'status' => 'Inspection Scheduled',
                'assigned_to' => $exec1->id,
                'notes' => 'Site inspection scheduled for Saturday 10:00 AM. 2 commercial expressway plots.',
                'created_at' => $now->copy()->subDays(1)->subHours(4),
                'last_contacted_at' => $now->copy()->subDays(1)->subHours(2),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Alhaji Ibrahim Danjuma',
                'phone_number' => '08061234509',
                'whatsapp_number' => '08061234509',
                'email' => 'danjuma.demo@bhl.com',
                'budget_range' => '₦200M - ₦350M',
                'preferred_location' => 'Maitama, Abuja',
                'outreach_location' => 'Transcorp Hilton Roadshow',
                'lead_source' => '[Demo/Test] Billboard - Airport Road',
                'status' => 'Negotiation',
                'assigned_to' => $exec2->id,
                'notes' => 'Commercial developer. Negotiating square meter land pricing in Maitama core.',
                'created_at' => $now->copy()->subDays(4)->subHours(9),
                'last_contacted_at' => $now->copy()->subDays(4)->subHours(6),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Senator (Mrs) Bisi Akintola',
                'phone_number' => '08034567890',
                'whatsapp_number' => '08034567890',
                'email' => 'bisi.demo@bhl.com',
                'budget_range' => '₦250M - ₦400M',
                'preferred_location' => 'Asokoro, Abuja',
                'outreach_location' => 'Abuja Airport Lounge',
                'lead_source' => '[Demo/Test] Direct Marketing',
                'status' => 'Negotiation',
                'assigned_to' => $exec1->id,
                'notes' => 'VIP investor. Luxury smart villa with private pool. High priority transaction.',
                'created_at' => $now->copy()->subDays(2)->subHours(7),
                'last_contacted_at' => $now->copy()->subDays(2)->subHours(5),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Prince Adewale Adelekan',
                'phone_number' => '08028765432',
                'whatsapp_number' => '08028765432',
                'email' => 'adewale.demo@bhl.com',
                'budget_range' => '₦150M - ₦220M',
                'preferred_location' => 'Epe Waterfront Corridor',
                'outreach_location' => 'Lekki Tollgate Billboard',
                'lead_source' => '[Demo/Test] Billboard - Lekki-Epe Tollgate',
                'status' => 'Negotiation',
                'assigned_to' => $exec2->id,
                'notes' => 'Acquiring 5-acre waterfront tract for boutique resort. Contract drafting stage.',
                'created_at' => $now->copy()->subDays(3)->subHours(10),
                'last_contacted_at' => $now->copy()->subDays(3)->subHours(7),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],

            // --- WHATSAPP ONLY LEADS (4) ---
            [
                'full_name' => 'Capt. Charles Emeka (UK Diaspora)',
                'phone_number' => '02079460192',
                'whatsapp_number' => '08139982145',
                'email' => 'charles.demo@bhl.com',
                'budget_range' => '₦95M - ₦130M',
                'preferred_location' => 'Ikoyi / Banana Island View',
                'outreach_location' => 'Diaspora Virtual Expo',
                'lead_source' => '[Demo/Test] Diaspora Virtual Expo',
                'status' => 'Contacted',
                'assigned_to' => $exec1->id,
                'notes' => 'Diaspora pilot. UK landline cannot be reached by local calls. WhatsApp only line. Brother in Lagos inspecting site.',
                'created_at' => $now->copy()->subDays(3)->subHours(4),
                'last_contacted_at' => $now->copy()->subDays(3)->subHours(2),
                'unreachable_count' => 1,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Mr. Somtochukwu Obi (US Diaspora)',
                'phone_number' => '01415555267',
                'whatsapp_number' => '08071239845',
                'email' => 'somto.demo@bhl.com',
                'budget_range' => '₦80M - ₦110M',
                'preferred_location' => 'Ibeju-Lekki (Dangote Refinery Zone)',
                'outreach_location' => 'Diaspora Virtual Expo',
                'lead_source' => '[Demo/Test] Instagram Sponsored Ad',
                'status' => 'Follow Up',
                'assigned_to' => $exec2->id,
                'notes' => 'US tech lead. Calls fail on local SIM. Engaged via WhatsApp. Buying 4 plots along Coastal Highway.',
                'created_at' => $now->copy()->subDays(2)->subHours(6),
                'last_contacted_at' => $now->copy()->subDays(2)->subHours(3),
                'unreachable_count' => 1,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Dr. Ken Ekwueme (Canada)',
                'phone_number' => '01604555891',
                'whatsapp_number' => '08162234901',
                'email' => 'ken.demo@bhl.com',
                'budget_range' => '₦110M - ₦150M',
                'preferred_location' => 'Lekki Phase 1, Lagos',
                'outreach_location' => 'Web Webinar Registration',
                'lead_source' => '[Demo/Test] Google Search Ads',
                'status' => 'Contacted',
                'assigned_to' => $exec2->id,
                'notes' => 'Doctor in Calgary. Local phone uncallable. Communicates exclusively on WhatsApp. Shortlet apartment investment.',
                'created_at' => $now->copy()->subDays(1)->subHours(5),
                'last_contacted_at' => $now->copy()->subDays(1)->subHours(2),
                'unreachable_count' => 2,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Mr. Austin Okeke (Houston)',
                'phone_number' => '01713555982',
                'whatsapp_number' => '08083344556',
                'email' => 'austin.demo@bhl.com',
                'budget_range' => '₦140M - ₦190M',
                'preferred_location' => 'Lekki Phase 1, Lagos',
                'outreach_location' => 'Diaspora Virtual Expo',
                'lead_source' => '[Demo/Test] Diaspora Virtual Expo',
                'status' => 'Follow Up',
                'assigned_to' => $exec1->id,
                'notes' => 'Oil & gas engineer in Houston. Phone unreachable. Sent drone video walkthrough via WhatsApp.',
                'created_at' => $now->copy()->subDays(4)->subHours(5),
                'last_contacted_at' => $now->copy()->subDays(4)->subHours(1),
                'unreachable_count' => 1,
                'is_flagged_fake' => false,
            ],

            // --- WARM NURTURE LEADS (6) ---
            [
                'full_name' => 'Mrs. Ngozi Okonjo-Eze',
                'phone_number' => '08149021876',
                'whatsapp_number' => '08149021876',
                'email' => 'ngozi.demo@bhl.com',
                'budget_range' => '₦45M - ₦60M',
                'preferred_location' => 'Sangotedo / Monastery Road',
                'outreach_location' => 'Novare Mall Outreach',
                'lead_source' => '[Demo/Test] Instagram Sponsored Ad',
                'status' => 'Contacted',
                'assigned_to' => $exec1->id,
                'notes' => 'Corporate executive. Wants 2-bedroom terrace with 12-month payment plan.',
                'created_at' => $now->copy()->subDays(2)->subHours(8),
                'last_contacted_at' => $now->copy()->subDays(2)->subHours(4),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Barrister Zainab Mohammed',
                'phone_number' => '08099882211',
                'whatsapp_number' => '08099882211',
                'email' => 'zainab.demo@bhl.com',
                'budget_range' => '₦70M - ₦95M',
                'preferred_location' => 'Katampe Extension, Abuja',
                'outreach_location' => 'Abuja Trade Fair Stand',
                'lead_source' => '[Demo/Test] Referral - Existing Client',
                'status' => 'Follow Up',
                'assigned_to' => $exec1->id,
                'notes' => 'Legal practitioner. Reviewing layout survey and C of O documents.',
                'created_at' => $now->copy()->subDays(3)->subHours(6),
                'last_contacted_at' => $now->copy()->subDays(3)->subHours(3),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Hajia Amina Bello',
                'phone_number' => '08187654321',
                'whatsapp_number' => '08187654321',
                'email' => 'amina.demo@bhl.com',
                'budget_range' => '₦55M - ₦75M',
                'preferred_location' => 'Wuye District, Abuja',
                'outreach_location' => 'Abuja City Mall Stand',
                'lead_source' => '[Demo/Test] Facebook Real Estate Campaign',
                'status' => 'Contacted',
                'assigned_to' => $exec2->id,
                'notes' => 'Scheduled inspection for Friday with her property manager. 3-bedroom semi-detached.',
                'created_at' => $now->copy()->subDays(2)->subHours(9),
                'last_contacted_at' => $now->copy()->subDays(2)->subHours(5),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Dr. (Mrs) Yetunde Davies',
                'phone_number' => '08029988776',
                'whatsapp_number' => '08029988776',
                'email' => 'yetunde.demo@bhl.com',
                'budget_range' => '₦85M - ₦115M',
                'preferred_location' => 'GRA Ikeja, Lagos',
                'outreach_location' => 'Ikeja Underbridge Roadshow',
                'lead_source' => '[Demo/Test] Daily Field Prospecting',
                'status' => 'Contacted',
                'assigned_to' => $exec2->id,
                'notes' => 'Chief Medical Officer. 4-bedroom semi-detached with private security post.',
                'created_at' => $now->copy()->subDays(3)->subHours(7),
                'last_contacted_at' => $now->copy()->subDays(3)->subHours(4),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Pastor Jerry Chukwuma',
                'phone_number' => '08145566778',
                'whatsapp_number' => '08145566778',
                'email' => 'jerry.demo@bhl.com',
                'budget_range' => '₦50M - ₦70M',
                'preferred_location' => 'Karsana, Abuja',
                'outreach_location' => 'Kubwa Expressway Roadshow',
                'lead_source' => '[Demo/Test] Direct Marketing',
                'status' => 'Follow Up',
                'assigned_to' => $exec3->id,
                'notes' => 'Site inspection follow-up booked for Sunday afternoon. Looking for dry land with C of O.',
                'created_at' => $now->copy()->subDays(1)->subHours(8),
                'last_contacted_at' => $now->copy()->subDays(1)->subHours(4),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Hon. Chinedu Nwosu',
                'phone_number' => '08031122334',
                'whatsapp_number' => '08031122334',
                'email' => 'chinedu.demo@bhl.com',
                'budget_range' => '₦130M - ₦180M',
                'preferred_location' => 'Guzape District, Abuja',
                'outreach_location' => 'Garki Ultra-Modern Market',
                'lead_source' => '[Demo/Test] Field Outreach',
                'status' => 'Follow Up',
                'assigned_to' => $exec3->id,
                'notes' => 'Payment milestone structured over 6 months on luxury smart villa.',
                'created_at' => $now->copy()->subDays(2)->subHours(4),
                'last_contacted_at' => $now->copy()->subDays(2)->subHours(1),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],

            // --- COLD & DORMANT LEADS (7) - IDEAL FOR BULK REASSIGNMENT TESTING ---
            [
                'full_name' => 'Pastor Samuel Olumide',
                'phone_number' => '08023456789',
                'whatsapp_number' => '08023456789',
                'email' => 'samuel.demo@bhl.com',
                'budget_range' => '₦35M - ₦50M',
                'preferred_location' => 'Ajah / Abraham Adesanya',
                'outreach_location' => 'Jubilee Bridge Roadshow',
                'lead_source' => '[Demo/Test] Daily Field Prospecting',
                'status' => 'New',
                'assigned_to' => $exec3->id,
                'notes' => 'Met at roadshow tent. Wants an off-plan 3-bedroom apartment. Uncontacted lead.',
                'created_at' => $now->copy()->subDays(5),
                'last_contacted_at' => null,
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Arch. David Nwachukwu',
                'phone_number' => '08039871234',
                'whatsapp_number' => '08039871234',
                'email' => 'david.demo@bhl.com',
                'budget_range' => '₦90M - ₦140M',
                'preferred_location' => 'GRA Ikeja, Lagos',
                'outreach_location' => 'Maryland Mall Roadshow',
                'lead_source' => '[Demo/Test] Walk-in Branch Enquiry',
                'status' => 'New',
                'assigned_to' => $exec3->id,
                'notes' => 'Architect looking for joint venture or prime land in Ikeja GRA. Needs callback.',
                'created_at' => $now->copy()->subDays(4),
                'last_contacted_at' => null,
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Mrs. Folashade Balogun',
                'phone_number' => '08101239876',
                'whatsapp_number' => '08101239876',
                'email' => 'folashade.demo@bhl.com',
                'budget_range' => '₦40M - ₦55M',
                'preferred_location' => 'Alagbado / Abule Egba Corridor',
                'outreach_location' => 'Ikeja City Mall Stand',
                'lead_source' => '[Demo/Test] Daily Field Prospecting',
                'status' => 'New',
                'assigned_to' => $exec3->id,
                'notes' => 'Branch manager seeking staff cooperative investment. Ideal for bulk reassignment test.',
                'created_at' => $now->copy()->subDays(3),
                'last_contacted_at' => null,
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Mr. Victor Ogundipe',
                'phone_number' => '08091122445',
                'whatsapp_number' => '08091122445',
                'email' => 'victor.demo@bhl.com',
                'budget_range' => '₦60M - ₦80M',
                'preferred_location' => 'Victoria Island, Lagos',
                'outreach_location' => 'Tech Summit Eko Hotel',
                'lead_source' => '[Demo/Test] Walk-in Branch Enquiry',
                'status' => 'New',
                'assigned_to' => $exec3->id,
                'notes' => 'Fintech director looking for luxury 2-bedroom executive apartment.',
                'created_at' => $now->copy()->subDays(2),
                'last_contacted_at' => null,
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Alhaja Rukayat Sanusi',
                'phone_number' => '08051239988',
                'whatsapp_number' => '08051239988',
                'email' => 'rukayat.demo@bhl.com',
                'budget_range' => '₦30M - ₦45M',
                'preferred_location' => 'Ibeju-Lekki (Free Trade Zone)',
                'outreach_location' => 'Trade Fair Complex Lagos',
                'lead_source' => '[Demo/Test] Daily Field Prospecting',
                'status' => 'New',
                'assigned_to' => $exec2->id,
                'notes' => 'Wants 2 commercial plots near the Deep Sea Port. Dormant lead.',
                'created_at' => $now->copy()->subDays(6),
                'last_contacted_at' => null,
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Elder Michael Adeyemi',
                'phone_number' => '08031238899',
                'whatsapp_number' => '08031238899',
                'email' => 'michael.demo@bhl.com',
                'budget_range' => '₦20M - ₦30M',
                'preferred_location' => 'Epe Expressway Corridor',
                'outreach_location' => 'Epe Fish Market Roadshow',
                'lead_source' => '[Demo/Test] Daily Field Prospecting',
                'status' => 'New',
                'assigned_to' => $exec2->id,
                'notes' => 'Retiree investing gratuity in 1 acre of land for retirement farming.',
                'created_at' => $now->copy()->subDays(5),
                'last_contacted_at' => null,
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Miss Stephanie Braide',
                'phone_number' => '08169900112',
                'whatsapp_number' => '08169900112',
                'email' => 'stephanie.demo@bhl.com',
                'budget_range' => '₦35M - ₦48M',
                'preferred_location' => 'Ajah / Sangotedo, Lagos',
                'outreach_location' => 'Silverbird Galleria Stand',
                'lead_source' => '[Demo/Test] Instagram Sponsored Ad',
                'status' => 'New',
                'assigned_to' => $exec2->id,
                'notes' => 'Creative director seeking starter home (1-bed studio or 2-bed flat).',
                'created_at' => $now->copy()->subDays(3),
                'last_contacted_at' => null,
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],

            // --- FLAGGED FAKE LEADS (2) ---
            [
                'full_name' => 'Spam Bot Test 01',
                'phone_number' => '08000000000',
                'whatsapp_number' => '08000000000',
                'email' => 'spambot1@fakelead.xyz',
                'budget_range' => '₦10M - ₦15M',
                'preferred_location' => 'Unknown',
                'outreach_location' => 'Online Form Injection',
                'lead_source' => '[Demo/Test] Facebook Real Estate Campaign',
                'status' => 'New',
                'assigned_to' => $exec3->id,
                'notes' => 'Suspicious bot submission. Repeated digits, phone dialer unreachable.',
                'created_at' => $now->copy()->subDays(2),
                'last_contacted_at' => null,
                'unreachable_count' => 3,
                'is_flagged_fake' => true,
                'flagged_reason' => 'Repeated invalid digits pattern / unreachable phone line.',
                'flagged_at' => $now->copy()->subDays(1),
            ],
            [
                'full_name' => 'Scam Inquiry Test 02',
                'phone_number' => '09012345',
                'whatsapp_number' => null,
                'email' => 'scammer@fakelead.xyz',
                'budget_range' => '₦500M+',
                'preferred_location' => 'Ikoyi',
                'outreach_location' => 'Web Landing Page',
                'lead_source' => '[Demo/Test] Google Search Ads',
                'status' => 'New',
                'assigned_to' => $exec2->id,
                'notes' => 'Incomplete phone digits. Flagged as bogus inquiry during field audit.',
                'created_at' => $now->copy()->subDays(4),
                'last_contacted_at' => null,
                'unreachable_count' => 3,
                'is_flagged_fake' => true,
                'flagged_reason' => 'Incomplete phone digits (<11) and invalid line.',
                'flagged_at' => $now->copy()->subDays(3),
            ],

            // --- CLOSED WON LEADS (2) ---
            [
                'full_name' => 'Mrs. Blessing Kalu',
                'phone_number' => '08123456701',
                'whatsapp_number' => '08123456701',
                'email' => 'blessing.demo@bhl.com',
                'budget_range' => '₦75M - ₦100M',
                'preferred_location' => 'Sangotedo / Monastery Road',
                'outreach_location' => 'Lekki Conservation Walkway',
                'lead_source' => '[Demo/Test] Facebook Real Estate Campaign',
                'status' => 'Closed Won',
                'assigned_to' => $exec1->id,
                'notes' => 'Deal closed! Purchased 4-bedroom terrace duplex. Allocation letter issued.',
                'created_at' => $now->copy()->subDays(5)->subHours(6),
                'last_contacted_at' => $now->copy()->subDays(5)->subHours(3),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Chief Emeka Ofor',
                'phone_number' => '08027788991',
                'whatsapp_number' => '08027788991',
                'email' => 'emeka.demo@bhl.com',
                'budget_range' => '₦210M - ₦300M',
                'preferred_location' => 'Guzape District, Abuja',
                'outreach_location' => 'Transcorp Hilton Roadshow',
                'lead_source' => '[Demo/Test] Referral - Existing Client',
                'status' => 'Closed Won',
                'assigned_to' => $exec1->id,
                'notes' => 'Deal closed! Paid in full for 5-bedroom luxury smart villa in Guzape.',
                'created_at' => $now->copy()->subDays(6)->subHours(4),
                'last_contacted_at' => $now->copy()->subDays(6)->subHours(1),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
        ];

        $insertedCount = 0;
        foreach ($demoLeads as $data) {
            $data['branch_id'] = $branchId;
            $lead = Lead::create($data);
            $insertedCount++;

            if (!empty($data['last_contacted_at'])) {
                LeadActivity::create([
                    'lead_id' => $lead->id,
                    'user_id' => $data['assigned_to'],
                    'activity_type' => 'Call Logged',
                    'description' => "Initial discovery consultation logged by sales executive. Status: {$data['status']}.",
                    'created_at' => $data['last_contacted_at'],
                ]);
            }
        }

        return redirect()->route('leads.quality-audit')->with('success', "⚡ Successfully generated {$insertedCount} test audit leads across 3 executives! All charts, donuts, WhatsApp-only badges, and risk comparisons are now fully populated.");
    }

    /**
     * Clear all generated test/demo leads safely.
     */
    public function clearDemoData(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['super_admin', 'company_admin', 'sales_manager'])) {
            abort(403, 'Unauthorized');
        }

        $testLeadIds = Lead::withoutGlobalScopes()
            ->where('lead_source', 'like', '[Demo/Test]%')
            ->pluck('id');

        LeadActivity::whereIn('lead_id', $testLeadIds)->delete();
        $deleted = Lead::withoutGlobalScopes()->whereIn('id', $testLeadIds)->delete();

        // Optionally delete test executive accounts
        User::whereIn('email', ['chidinma.audit@bhl.com', 'tunde.audit@bhl.com', 'amina.audit@bhl.com'])->delete();

        return redirect()->route('leads.quality-audit')->with('success', "🧹 Safely removed {$deleted} test/demo lead records and test audit accounts.");
    }

    /**
     * Resolve a human-readable period key to a [Carbon start, Carbon end] pair.
     */
    private function resolvePeriod(string $period, Request $request): array
    {
        return match ($period) {
            'this_week'  => [now()->startOfWeek(), now()->endOfWeek()],
            'last_week'  => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'custom'     => [
                \Carbon\Carbon::parse($request->input('start_date', now()->subDays(30)->format('Y-m-d'))),
                \Carbon\Carbon::parse($request->input('end_date', now()->format('Y-m-d'))),
            ],
            default      => [now()->startOfWeek(), now()->endOfWeek()],
        };
    }
}
