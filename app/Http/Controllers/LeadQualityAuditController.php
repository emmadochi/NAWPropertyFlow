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
