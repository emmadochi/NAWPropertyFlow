<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Inspection;
use App\Models\FollowUp;
use App\Models\Sale;
use App\Models\PaymentMilestone;
use App\Models\SalesWeeklyFieldLog;
use App\Models\User;
use App\Models\Branch;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RetailPerformanceController extends Controller
{
    /**
     * Display the Retail Team Weekly & Monthly Performance Scorecard Matrix.
     */
    public function index(Request $request)
    {
        $data = $this->buildMatrixData($request);
        return view('reports.retail_weekly', $data);
    }

    /**
     * Render the Executive Landscape Print & PDF view.
     */
    public function printExecutive(Request $request)
    {
        $data = $this->buildMatrixData($request);
        return view('reports.retail_print', $data);
    }

    /**
     * Save or update off-site canvassing field logs for a consultant.
     */
    public function saveFieldLog(Request $request)
    {
        if (!Schema::hasTable('sales_weekly_field_logs')) {
            $msg = 'Please run "php artisan tenants:migrate" on the server to enable weekly canvassing logs.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'user_id'                      => 'required|exists:users,id',
            'year'                         => 'required|integer|min:2020|max:2035',
            'month'                        => 'required|integer|min:1|max:12',
            'week_number'                  => 'required|integer|min:1|max:5',
            'canvassing_locations'         => 'nullable|string|max:1000',
            'office_visits_count'          => 'nullable|integer|min:0',
            'expected_payments_count'      => 'nullable|integer|min:0',
            'expected_payments_notes'      => 'nullable|string|max:2000',
            'observations_recommendations' => 'nullable|string|max:2000',
            'manager_feedback'             => 'nullable|string|max:2000',
        ]);

        $log = SalesWeeklyFieldLog::updateOrCreate(
            [
                'user_id'     => $validated['user_id'],
                'year'        => $validated['year'],
                'month'       => $validated['month'],
                'week_number' => $validated['week_number'],
            ],
            [
                'canvassing_locations'         => $validated['canvassing_locations'] ?? null,
                'office_visits_count'          => $validated['office_visits_count'] ?? 0,
                'expected_payments_count'      => $validated['expected_payments_count'] ?? 0,
                'expected_payments_notes'      => $validated['expected_payments_notes'] ?? null,
                'observations_recommendations' => $validated['observations_recommendations'] ?? null,
                'manager_feedback'             => $validated['manager_feedback'] ?? null,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Weekly field activity log saved successfully.',
                'log'     => $log,
            ]);
        }

        return back()->with('success', 'Weekly field log recorded successfully.');
    }

    /**
     * AJAX Drill-down endpoint to audit real CRM records behind any metric count.
     */
    public function drilldown(Request $request)
    {
        $userId    = $request->get('user_id');
        $metric    = $request->get('metric');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        if (!$userId || !$metric || !$startDate || !$endDate) {
            return response()->json(['error' => 'Missing required filter parameters.'], 422);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'Consultant not found.'], 404);
        }

        $items = [];
        $title = '';

        switch ($metric) {
            case 'calls':
                $title = "Calls Logged by {$user->name}";
                $followUps = rescue(fn() => FollowUp::whereHas('lead', fn($q) => $q->where('assigned_to', $userId))
                    ->whereIn('type', ['call', 'phone'])
                    ->whereBetween('due_date', [$startDate, $endDate])
                    ->with('lead')
                    ->latest('due_date')
                    ->get(), collect());

                foreach ($followUps as $f) {
                    $items[] = [
                        'title'       => $f->lead ? $f->lead->full_name : 'Direct Call',
                        'phone'       => $f->lead ? $f->lead->phone_number : 'N/A',
                        'subtitle'    => $f->notes ?: 'Scheduled prospect phone call',
                        'badge'       => 'Call Follow-up',
                        'date'        => $f->due_date ? Carbon::parse($f->due_date)->format('d M, Y h:i A') : $f->created_at->format('d M, Y h:i A'),
                        'link'        => $f->lead_id ? route('leads.show', $f->lead_id) : null,
                    ];
                }

                $activities = rescue(fn() => LeadActivity::where('user_id', $userId)
                    ->where('activity_type', 'like', '%Call%')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->with('lead')
                    ->latest()
                    ->get(), collect());

                foreach ($activities as $a) {
                    $items[] = [
                        'title'       => $a->lead ? $a->lead->full_name : 'Call Activity',
                        'phone'       => $a->lead ? $a->lead->phone_number : 'N/A',
                        'subtitle'    => $a->description,
                        'badge'       => 'Outreach Call',
                        'date'        => $a->created_at->format('d M, Y h:i A'),
                        'link'        => $a->lead_id ? route('leads.show', $a->lead_id) : null,
                    ];
                }
                break;

            case 'whatsapp':
                $title = "WhatsApp Outreach by {$user->name}";
                $followUps = rescue(fn() => FollowUp::whereHas('lead', fn($q) => $q->where('assigned_to', $userId))
                    ->where('type', 'whatsapp')
                    ->whereBetween('due_date', [$startDate, $endDate])
                    ->with('lead')
                    ->latest('due_date')
                    ->get(), collect());

                foreach ($followUps as $f) {
                    $items[] = [
                        'title'       => $f->lead ? $f->lead->full_name : 'WhatsApp Chat',
                        'phone'       => $f->lead ? $f->lead->phone_number : 'N/A',
                        'subtitle'    => $f->notes ?: 'WhatsApp broadcast/chat engagement',
                        'badge'       => 'WhatsApp Chat',
                        'date'        => $f->due_date ? Carbon::parse($f->due_date)->format('d M, Y h:i A') : $f->created_at->format('d M, Y h:i A'),
                        'link'        => $f->lead_id ? route('leads.show', $f->lead_id) : null,
                    ];
                }

                $waActivities = rescue(fn() => LeadActivity::where('user_id', $userId)
                    ->where('activity_type', 'like', '%WhatsApp%')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->with('lead')
                    ->latest()
                    ->get(), collect());

                foreach ($waActivities as $a) {
                    $items[] = [
                        'title'       => $a->lead ? $a->lead->full_name : 'WhatsApp Discussion',
                        'phone'       => $a->lead ? $a->lead->phone_number : 'N/A',
                        'subtitle'    => $a->description,
                        'badge'       => 'WhatsApp Touch',
                        'date'        => $a->created_at->format('d M, Y h:i A'),
                        'link'        => $a->lead_id ? route('leads.show', $a->lead_id) : null,
                    ];
                }
                break;

            case 'inspections':
                $title = "Site Inspections Conducted by {$user->name}";
                $inspections = rescue(fn() => Inspection::where('assigned_to', $userId)
                    ->whereBetween('inspection_date', [$startDate, $endDate])
                    ->with(['lead', 'property'])
                    ->latest('inspection_date')
                    ->get(), collect());

                foreach ($inspections as $ins) {
                    $items[] = [
                        'title'       => ($ins->lead ? $ins->lead->full_name : 'Prospect') . ' → ' . ($ins->property ? ($ins->property->estate_name ?? $ins->property->name) : 'Estate Tour'),
                        'phone'       => $ins->lead ? $ins->lead->phone_number : 'N/A',
                        'subtitle'    => "Status: {$ins->status} • Location: " . ($ins->property ? ($ins->property->location ?? 'Site') : 'Abuja Site'),
                        'badge'       => $ins->status,
                        'date'        => Carbon::parse($ins->inspection_date)->format('d M, Y h:i A'),
                        'link'        => $ins->lead_id ? route('leads.show', $ins->lead_id) : null,
                    ];
                }
                break;

            case 'leads':
                $title = "Contacts & Prospects Captured by {$user->name}";
                $leads = rescue(fn() => Lead::where('assigned_to', $userId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->latest()
                    ->get(), collect());

                foreach ($leads as $l) {
                    $items[] = [
                        'title'       => $l->full_name,
                        'phone'       => $l->phone_number . ($l->email ? " • {$l->email}" : ''),
                        'subtitle'    => "Source: {$l->lead_source}" . (!empty($l->outreach_location) ? " • Territory: {$l->outreach_location}" : ''),
                        'badge'       => ucfirst($l->status),
                        'date'        => $l->created_at->format('d M, Y'),
                        'link'        => route('leads.show', $l->id),
                    ];
                }
                break;

            case 'sales':
                $title = "Closed Deals by {$user->name}";
                $sales = rescue(fn() => Sale::where('sales_officer_id', $userId)
                    ->where('status', 'Closed Won')
                    ->whereBetween('deal_closed_at', [$startDate, $endDate])
                    ->with(['lead', 'property'])
                    ->latest('deal_closed_at')
                    ->get(), collect());

                foreach ($sales as $s) {
                    $items[] = [
                        'title'       => ($s->lead ? $s->lead->full_name : 'Buyer') . ' — ₦' . number_format($s->deal_value, 2),
                        'phone'       => $s->lead ? $s->lead->phone_number : 'N/A',
                        'subtitle'    => "Property: " . ($s->property ? ($s->property->estate_name ?? $s->property->name) : 'Allocated Unit'),
                        'badge'       => 'Closed Deal',
                        'date'        => $s->deal_closed_at ? Carbon::parse($s->deal_closed_at)->format('d M, Y') : $s->created_at->format('d M, Y'),
                        'link'        => route('sales.show', $s->id),
                    ];
                }
                break;

            case 'topups':
                $title = "Milestone Top-up Installments for {$user->name}";
                $milestones = rescue(fn() => PaymentMilestone::whereHas('paymentPlan.sale', function($q) use ($userId) {
                    $q->where('sales_officer_id', $userId);
                })->where('status', 'Paid')
                  ->whereBetween('paid_at', [$startDate, $endDate])
                  ->with(['paymentPlan.sale.lead', 'paymentPlan.sale.property'])
                  ->latest('paid_at')
                  ->get(), collect());

                foreach ($milestones as $m) {
                    $sale = $m->paymentPlan?->sale;
                    $val = (float) ($m->amount_paid ?: $m->amount_due);
                    $items[] = [
                        'title'       => ($sale && $sale->lead ? $sale->lead->full_name : 'Client') . ' — ₦' . number_format($val, 2),
                        'phone'       => $sale && $sale->lead ? $sale->lead->phone_number : 'N/A',
                        'subtitle'    => "Milestone: " . ($m->label ?? 'Installment') . " • Ref: " . ($m->bank_reference ?? 'Verified'),
                        'badge'       => 'Paid Milestone',
                        'date'        => $m->paid_at ? Carbon::parse($m->paid_at)->format('d M, Y') : 'N/A',
                        'link'        => $sale ? route('sales.show', $sale->id) : null,
                    ];
                }
                break;
        }

        return response()->json([
            'success'   => true,
            'title'     => $title,
            'count'     => count($items),
            'items'     => $items,
        ]);
    }

    /**
     * Core Data Aggregator for Matrix views and exports.
     */
    protected function buildMatrixData(Request $request): array
    {
        $periodType = $request->get('period_type', 'weekly');
        $year       = (int) $request->get('year', now()->year);
        $month      = (int) $request->get('month', now()->month);
        $weekNumber = $request->get('week_number', 'all'); // 1-5 or 'all'
        $branchId   = $request->get('branch_id');

        // Determine date boundaries
        if ($periodType === 'monthly' || $weekNumber === 'all') {
            $startDate   = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate     = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            $monthName   = $startDate->format('F');
            $periodLabel = "{$monthName} {$year} (Monthly Summary)";
            $activeWeek  = 'all';
        } else {
            $week = max(1, min(5, (int) $weekNumber));
            $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
            
            $startDay = (($week - 1) * 7) + 1;
            $endDay   = min($week * 7, $daysInMonth);
            if ($week >= 5) {
                $startDay = 29;
                $endDay   = $daysInMonth;
            }

            $startDate   = Carbon::createFromDate($year, $month, $startDay)->startOfDay();
            $endDate     = Carbon::createFromDate($year, $month, $endDay)->endOfDay();
            $monthName   = $startDate->format('F');
            $periodLabel = "{$monthName} {$year} — Week {$week} Summary ({$startDate->format('d M')} - {$endDate->format('d M')})";
            $activeWeek  = $week;
        }

        $currentUser = Auth::user();
        $isExecutive = in_array($currentUser->role ?? '', ['sales_executive', 'sales_agent', 'marketer']);

        // Query sales consultants & retail marketers
        $consultantsQuery = User::where(function($q) {
            $q->whereIn('role', ['sales_executive', 'sales_agent', 'sales_manager', 'marketer'])
              ->orWhere('job_title', 'like', '%Sales%')
              ->orWhere('job_title', 'like', '%Marketer%')
              ->orWhere('job_title', 'like', '%Consultant%')
              ->orWhere('job_title', 'like', '%Relationship%');
        })->where(function($q) {
            $q->where('status', 'active')->orWhereNull('status');
        });

        if ($branchId) {
            $consultantsQuery->where('branch_id', $branchId);
        } elseif ($currentUser && $currentUser->branch_id && !in_array($currentUser->role, ['super_admin', 'company_admin'])) {
            $consultantsQuery->where('branch_id', $currentUser->branch_id);
        }

        $consultants = $consultantsQuery->orderBy('name', 'asc')->get();

        // Safely load existing field logs (if table exists)
        $fieldLogs = collect();
        if (Schema::hasTable('sales_weekly_field_logs')) {
            $fieldLogsQuery = SalesWeeklyFieldLog::where('year', $year)->where('month', $month);
            if ($activeWeek !== 'all') {
                $fieldLogsQuery->where('week_number', $activeWeek);
            }
            $fieldLogs = rescue(fn() => $fieldLogsQuery->get()->groupBy('user_id'), collect());
        }

        $hasOutreachCol = Schema::hasColumn('leads', 'outreach_location');

        $matrixRows = [];
        $aggregates = [
            'actual_payments_count'   => 0,
            'actual_payments_value'   => 0,
            'topups_count'            => 0,
            'topups_value'            => 0,
            'expected_payments_count' => 0,
            'inspections_count'       => 0,
            'office_visits_count'     => 0,
            'calls_count'             => 0,
            'sms_count'               => 0,
            'whatsapp_count'          => 0,
            'new_contacts_count'      => 0,
            'contacts_phone_count'    => 0,
            'contacts_email_count'    => 0,
            'total_realized_revenue'  => 0,
        ];

        foreach ($consultants as $consultant) {
            $userLogs = $fieldLogs->get($consultant->id) ?? collect();
            $primaryLog = $userLogs->first();

            // 1. Canvassing locations
            $canvassingLocations = $primaryLog ? $primaryLog->canvassing_locations : null;
            if (!$canvassingLocations) {
                $distinctLeadLocs = [];
                if ($hasOutreachCol) {
                    $distinctLeadLocs = rescue(fn() => Lead::where('assigned_to', $consultant->id)
                        ->whereNotNull('outreach_location')
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->pluck('outreach_location')
                        ->unique()
                        ->values()
                        ->all(), []);
                }
                $canvassingLocations = !empty($distinctLeadLocs) ? implode(', ', $distinctLeadLocs) : 'Territory Prospecting';
            }

            // 2. Payments & Top-ups (Verified Finance Records)
            $actualPaymentsCount = rescue(fn() => Sale::where('sales_officer_id', $consultant->id)
                ->where('status', 'Closed Won')
                ->whereBetween('deal_closed_at', [$startDate, $endDate])
                ->count(), 0);

            $actualPaymentsValue = (float) rescue(fn() => Sale::where('sales_officer_id', $consultant->id)
                ->where('status', 'Closed Won')
                ->whereBetween('deal_closed_at', [$startDate, $endDate])
                ->sum('deal_value'), 0);

            $topupsCount = rescue(fn() => PaymentMilestone::whereHas('paymentPlan.sale', function($q) use ($consultant) {
                $q->where('sales_officer_id', $consultant->id);
            })->where('status', 'Paid')
              ->whereBetween('paid_at', [$startDate, $endDate])
              ->count(), 0);

            $topupsValue = (float) rescue(fn() => PaymentMilestone::whereHas('paymentPlan.sale', function($q) use ($consultant) {
                $q->where('sales_officer_id', $consultant->id);
            })->where('status', 'Paid')
              ->whereBetween('paid_at', [$startDate, $endDate])
              ->sum('amount_paid'), 0);

            // 3. Expected payments
            $expectedCount = $primaryLog && $primaryLog->expected_payments_count > 0
                ? $primaryLog->expected_payments_count
                : rescue(fn() => Lead::where('assigned_to', $consultant->id)
                    ->whereIn('status', ['qualified', 'proposal_sent', 'negotiating'])
                    ->whereBetween('updated_at', [$startDate, $endDate])
                    ->count(), 0);

            $expectedContacts = $primaryLog && $primaryLog->expected_payments_notes
                ? $primaryLog->expected_payments_notes
                : rescue(fn() => Lead::where('assigned_to', $consultant->id)
                    ->whereIn('status', ['qualified', 'proposal_sent', 'negotiating'])
                    ->limit(4)
                    ->pluck('phone_number')
                    ->filter()
                    ->implode(', '), '');

            // 4. Inspections & Estates
            $inspectionsCount = rescue(fn() => Inspection::where('assigned_to', $consultant->id)
                ->whereBetween('inspection_date', [$startDate, $endDate])
                ->count(), 0);

            $projectLocations = rescue(fn() => Inspection::where('assigned_to', $consultant->id)
                ->whereBetween('inspection_date', [$startDate, $endDate])
                ->with('property')
                ->get()
                ->map(fn($i) => $i->property ? ($i->property->estate_name ?? $i->property->location ?? $i->property->name) : null)
                ->filter()
                ->unique()
                ->values()
                ->all(), []);

            $projectLocationsText = !empty($projectLocations) ? implode(', ', $projectLocations) : 'Office Briefings';

            // 5. Office Visits
            $officeVisits = $primaryLog && $primaryLog->office_visits_count > 0
                ? $primaryLog->office_visits_count
                : rescue(fn() => LeadActivity::where('user_id', $consultant->id)
                    ->where(fn($q) => $q->where('activity_type', 'like', '%Office%')->orWhere('description', 'like', '%Office%'))
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(), 0);

            // 6. Engagements (Calls, SMS, WhatsApp)
            $callsCount = rescue(fn() => FollowUp::whereHas('lead', fn($q) => $q->where('assigned_to', $consultant->id))
                ->whereIn('type', ['call', 'phone'])
                ->whereBetween('due_date', [$startDate, $endDate])
                ->count(), 0)
                + rescue(fn() => LeadActivity::where('user_id', $consultant->id)
                    ->where('activity_type', 'like', '%Call%')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(), 0);

            $smsCount = rescue(fn() => FollowUp::whereHas('lead', fn($q) => $q->where('assigned_to', $consultant->id))
                ->where('type', 'sms')
                ->whereBetween('due_date', [$startDate, $endDate])
                ->count(), 0)
                + rescue(fn() => LeadActivity::where('user_id', $consultant->id)
                    ->where('activity_type', 'like', '%SMS%')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(), 0);

            $whatsappCount = rescue(fn() => FollowUp::whereHas('lead', fn($q) => $q->where('assigned_to', $consultant->id))
                ->where('type', 'whatsapp')
                ->whereBetween('due_date', [$startDate, $endDate])
                ->count(), 0)
                + rescue(fn() => LeadActivity::where('user_id', $consultant->id)
                    ->where('activity_type', 'like', '%WhatsApp%')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(), 0);

            // 7. Contacts generated
            $newContactsCount = rescue(fn() => Lead::where('assigned_to', $consultant->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(), 0);

            $contactsPhoneCount = rescue(fn() => Lead::where('assigned_to', $consultant->id)
                ->whereNotNull('phone_number')
                ->where('phone_number', '!=', '')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(), 0);

            $contactsEmailCount = rescue(fn() => Lead::where('assigned_to', $consultant->id)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(), 0);

            // 8. Observations
            $observations = $primaryLog ? ($primaryLog->observations_recommendations ?: $primaryLog->manager_feedback) : null;
            if (!$observations) {
                if ($actualPaymentsCount > 0) {
                    $observations = "Achieved deal closure in period. Escalating new pipeline prospects.";
                } elseif ($inspectionsCount > 0) {
                    $observations = "Conducted {$inspectionsCount} site tours. Actively issuing payment plans.";
                } else {
                    $observations = "Outreach canvassing active. Nurturing assigned database prospects.";
                }
            }

            $totalRevenue = $actualPaymentsValue + $topupsValue;

            $row = [
                'user'                         => $consultant,
                'canvassing_locations'         => $canvassingLocations,
                'actual_payments_count'        => $actualPaymentsCount,
                'actual_payments_value'        => $actualPaymentsValue,
                'topups_count'                 => $topupsCount,
                'topups_value'                 => $topupsValue,
                'expected_payments_count'      => $expectedCount,
                'expected_payments_notes'      => $expectedContacts,
                'inspections_count'            => $inspectionsCount,
                'project_locations_inspected'  => $projectLocationsText,
                'office_visits_count'          => $officeVisits,
                'calls_count'                  => $callsCount,
                'sms_count'                    => $smsCount,
                'whatsapp_count'               => $whatsappCount,
                'new_contacts_count'           => $newContactsCount,
                'contacts_phone_count'         => $contactsPhoneCount,
                'contacts_email_count'         => $contactsEmailCount,
                'total_revenue'                => $totalRevenue,
                'observations_recommendations' => $observations,
                'field_log'                    => $primaryLog,
            ];

            $matrixRows[] = $row;

            // Aggregates
            $aggregates['actual_payments_count']   += $actualPaymentsCount;
            $aggregates['actual_payments_value']   += $actualPaymentsValue;
            $aggregates['topups_count']            += $topupsCount;
            $aggregates['topups_value']            += $topupsValue;
            $aggregates['expected_payments_count'] += $expectedCount;
            $aggregates['inspections_count']       += $inspectionsCount;
            $aggregates['office_visits_count']     += $officeVisits;
            $aggregates['calls_count']             += $callsCount;
            $aggregates['sms_count']               += $smsCount;
            $aggregates['whatsapp_count']          += $whatsappCount;
            $aggregates['new_contacts_count']      += $newContactsCount;
            $aggregates['contacts_phone_count']    += $contactsPhoneCount;
            $aggregates['contacts_email_count']    += $contactsEmailCount;
            $aggregates['total_realized_revenue']  += $totalRevenue;
        }

        // Sort by total revenue descending, then inspections, then leads
        usort($matrixRows, function($a, $b) {
            return ($b['total_revenue'] <=> $a['total_revenue'])
                ?: (($b['inspections_count'] <=> $a['inspections_count'])
                ?: ($b['new_contacts_count'] <=> $a['new_contacts_count']));
        });

        $branches = rescue(fn() => Branch::orderBy('name', 'asc')->get(), collect());

        // Tenant Branding Resolution
        $setting     = rescue(fn() => CompanySetting::getCached(), null);
        $tenantId    = function_exists('tenant') ? (tenant('id') ?? session('tenant_id') ?? '') : '';
        $host        = request()->getHost();
        $companyName = $setting?->company_name ?? (function_exists('tenant') && tenant() ? tenant()?->name : 'Buckcrest Havens Limited');
        $isBuckcrest = in_array($tenantId, ['bhl', 'buckcrest'])
            || str_contains($host, 'bhl')
            || str_contains($host, 'buckcrest')
            || str_contains(strtolower($companyName), 'buckcrest');

        return compact(
            'matrixRows',
            'aggregates',
            'periodType',
            'year',
            'month',
            'activeWeek',
            'periodLabel',
            'startDate',
            'endDate',
            'branches',
            'branchId',
            'isExecutive',
            'currentUser',
            'companyName',
            'isBuckcrest'
        );
    }

    /**
     * Export the Matrix to cleanly formatted CSV/Excel without scientific notation bugs.
     */
    public function export(Request $request)
    {
        $data = $this->buildMatrixData($request);
        $fileName = "sales_performance_matrix_{$data['year']}_m{$data['month']}_w{$data['activeWeek']}.csv";

        $headers = [
            'Content-type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Output UTF-8 BOM for Excel to open symbols (₦) and accents properly
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Letterhead
            fputcsv($file, [$data['companyName']]);
            fputcsv($file, ['RETAIL SALES TEAM PERFORMANCE SCORECARD MATRIX']);
            fputcsv($file, ['Period:', $data['periodLabel']]);
            fputcsv($file, ['Generated at:', now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []);

            // Tier 1 Header
            fputcsv($file, [
                'CONSULTANT PROFILE', '',
                'LOCATION(S) / EVENT(S)', '',
                'PAYMENTS / REVENUE', '', '', '',
                'ACTIVITIES', '',
                'ENGAGEMENTS (VERIFIED)', '', '', '',
                'SUMMARY REPORT', ''
            ]);

            // Tier 2 Columns
            fputcsv($file, [
                'S/N',
                'Sales Consultant',
                'Locations Visited / Canvassed',
                'Office Visits',
                'New Sales (NGN)',
                'Part / Top-ups (NGN)',
                'Expected Inflow / Pipeline',
                'Total Realized (NGN)',
                'Site Inspections',
                'Estates Inspected',
                'Calls Logged',
                'WhatsApp Chats',
                'SMS Sent',
                'Contacts Generated',
                'Phone Count',
                'Email Count',
                'Weekly Observations & Action Points',
                'Manager Feedback'
            ]);

            foreach ($data['matrixRows'] as $i => $row) {
                $user = $row['user'];
                $fieldLog = $row['field_log'];

                fputcsv($file, [
                    $i + 1,
                    $user->name . ($user->branch ? " ({$user->branch->name})" : ''),
                    $row['canvassing_locations'],
                    $row['office_visits_count'],
                    number_format($row['actual_payments_value'], 2, '.', ''),
                    number_format($row['topups_value'], 2, '.', ''),
                    // Prepend tab (\t) to phone numbers/notes so Excel does NOT format as scientific notation
                    "\t" . ($row['expected_payments_notes'] ?: ($row['expected_payments_count'] > 0 ? "{$row['expected_payments_count']} pipeline" : '')),
                    number_format($row['total_revenue'], 2, '.', ''),
                    $row['inspections_count'],
                    $row['project_locations_inspected'],
                    $row['calls_count'],
                    $row['whatsapp_count'],
                    $row['sms_count'],
                    $row['new_contacts_count'],
                    $row['contacts_phone_count'],
                    $row['contacts_email_count'],
                    $row['observations_recommendations'],
                    $fieldLog ? $fieldLog->manager_feedback : ''
                ]);
            }

            // Totals Row
            $agg = $data['aggregates'];
            fputcsv($file, []);
            fputcsv($file, [
                'TOTAL',
                'TEAM AGGREGATE',
                'ALL LOCATIONS',
                $agg['office_visits_count'],
                number_format($agg['actual_payments_value'], 2, '.', ''),
                number_format($agg['topups_value'], 2, '.', ''),
                "{$agg['expected_payments_count']} deals",
                number_format($agg['total_realized_revenue'], 2, '.', ''),
                $agg['inspections_count'],
                'ALL SITES',
                $agg['calls_count'],
                $agg['whatsapp_count'],
                $agg['sms_count'],
                $agg['new_contacts_count'],
                $agg['contacts_phone_count'],
                $agg['contacts_email_count'],
                'Verified CRM Aggregate Output'
            ]);

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Seed realistic demo test data directly from the web interface.
     */
    public function seedSampleData(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser || (!$currentUser->isCompanyAdmin() && !$currentUser->isSuperAdmin() && !in_array($currentUser->role, ['company_admin', 'super_admin', 'sales_manager']))) {
            return back()->with('error', 'Only administrative managers can seed demo performance data.');
        }

        try {
            $seeder = new \Database\Seeders\RetailPerformanceDemoSeeder();
            $seeder->run();

            return back()->with('success', 'Realistic sales team performance test data successfully populated for the current sprint!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('RetailPerformanceDemoSeeder Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to populate demo data: ' . $e->getMessage());
        }
    }
}
