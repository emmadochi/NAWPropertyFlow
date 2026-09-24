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
                $followUps = FollowUp::where('assigned_officer_id', $userId)
                    ->whereIn('type', ['call', 'phone'])
                    ->whereBetween('follow_up_date', [$startDate, $endDate])
                    ->with('lead')
                    ->latest('follow_up_date')
                    ->get();

                foreach ($followUps as $f) {
                    $items[] = [
                        'title'       => $f->lead ? $f->lead->full_name : 'Direct Call',
                        'phone'       => $f->lead ? $f->lead->phone_number : 'N/A',
                        'subtitle'    => $f->notes ?: 'Scheduled prospect phone call',
                        'badge'       => 'Call Follow-up',
                        'date'        => Carbon::parse($f->follow_up_date)->format('d M, Y h:i A'),
                        'link'        => $f->lead_id ? route('leads.show', $f->lead_id) : null,
                    ];
                }

                $activities = LeadActivity::where('user_id', $userId)
                    ->where('activity_type', 'like', '%Call%')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->with('lead')
                    ->latest()
                    ->get();

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
                $followUps = FollowUp::where('assigned_officer_id', $userId)
                    ->where('type', 'whatsapp')
                    ->whereBetween('follow_up_date', [$startDate, $endDate])
                    ->with('lead')
                    ->latest('follow_up_date')
                    ->get();

                foreach ($followUps as $f) {
                    $items[] = [
                        'title'       => $f->lead ? $f->lead->full_name : 'WhatsApp Chat',
                        'phone'       => $f->lead ? $f->lead->phone_number : 'N/A',
                        'subtitle'    => $f->notes ?: 'WhatsApp broadcast/chat engagement',
                        'badge'       => 'WhatsApp Chat',
                        'date'        => Carbon::parse($f->follow_up_date)->format('d M, Y h:i A'),
                        'link'        => $f->lead_id ? route('leads.show', $f->lead_id) : null,
                    ];
                }
                break;

            case 'inspections':
                $title = "Site Inspections Conducted by {$user->name}";
                $inspections = Inspection::where('assigned_to', $userId)
                    ->whereBetween('inspection_date', [$startDate, $endDate])
                    ->with(['lead', 'property'])
                    ->latest('inspection_date')
                    ->get();

                foreach ($inspections as $ins) {
                    $items[] = [
                        'title'       => ($ins->lead ? $ins->lead->full_name : 'Prospect') . ' → ' . ($ins->property ? ($ins->property->estate_name ?? $ins->property->name) : 'Estate Tour'),
                        'phone'       => $ins->lead ? $ins->lead->phone_number : 'N/A',
                        'subtitle'    => "Status: {$ins->status} • Location: " . ($ins->property ? $ins->property->location : 'Abuja Site'),
                        'badge'       => $ins->status,
                        'date'        => Carbon::parse($ins->inspection_date)->format('d M, Y h:i A'),
                        'link'        => $ins->lead_id ? route('leads.show', $ins->lead_id) : null,
                    ];
                }
                break;

            case 'leads':
                $title = "Contacts & Prospects Captured by {$user->name}";
                $leads = Lead::where('assigned_to', $userId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->latest()
                    ->get();

                foreach ($leads as $l) {
                    $items[] = [
                        'title'       => $l->full_name,
                        'phone'       => $l->phone_number . ($l->email ? " • {$l->email}" : ''),
                        'subtitle'    => "Source: {$l->lead_source} • Outreach Territory: " . ($l->outreach_location ?: 'General Direct'),
                        'badge'       => ucfirst($l->status),
                        'date'        => $l->created_at->format('d M, Y'),
                        'link'        => route('leads.show', $l->id),
                    ];
                }
                break;

            case 'sales':
                $title = "Closed Deals by {$user->name}";
                $sales = Sale::where('sales_officer_id', $userId)
                    ->where('status', 'Closed Won')
                    ->whereBetween('deal_closed_at', [$startDate, $endDate])
                    ->with(['lead', 'property'])
                    ->latest('deal_closed_at')
                    ->get();

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
                $milestones = PaymentMilestone::whereHas('paymentPlan.sale', function($q) use ($userId) {
                    $q->where('sales_officer_id', $userId);
                })->where('status', 'Paid')
                  ->whereBetween('paid_at', [$startDate, $endDate])
                  ->with(['paymentPlan.sale.lead', 'paymentPlan.sale.property'])
                  ->latest('paid_at')
                  ->get();

                foreach ($milestones as $m) {
                    $sale = $m->paymentPlan?->sale;
                    $items[] = [
                        'title'       => ($sale && $sale->lead ? $sale->lead->full_name : 'Client') . ' — ₦' . number_format($m->amount, 2),
                        'phone'       => $sale && $sale->lead ? $sale->lead->phone_number : 'N/A',
                        'subtitle'    => "Milestone Stage: {$m->name} • Ref: " . ($m->reference ?? 'Verified'),
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
        $isExecutive = in_array($currentUser->role, ['sales_executive', 'sales_agent', 'marketer']);

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
        } elseif ($currentUser->branch_id && !in_array($currentUser->role, ['super_admin', 'company_admin'])) {
            $consultantsQuery->where('branch_id', $currentUser->branch_id);
        }

        $consultants = $consultantsQuery->orderBy('name', 'asc')->get();

        // Load existing field logs for the period
        $fieldLogsQuery = SalesWeeklyFieldLog::where('year', $year)->where('month', $month);
        if ($activeWeek !== 'all') {
            $fieldLogsQuery->where('week_number', $activeWeek);
        }
        $fieldLogs = $fieldLogsQuery->get()->groupBy('user_id');

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
                $distinctLeadLocs = Lead::where('assigned_to', $consultant->id)
                    ->whereNotNull('outreach_location')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->pluck('outreach_location')
                    ->unique()
                    ->values()
                    ->all();
                $canvassingLocations = !empty($distinctLeadLocs) ? implode(', ', $distinctLeadLocs) : 'Territory Prospecting';
            }

            // 2. Payments & Top-ups (Verified Finance Records)
            $actualPaymentsCount = Sale::where('sales_officer_id', $consultant->id)
                ->where('status', 'Closed Won')
                ->whereBetween('deal_closed_at', [$startDate, $endDate])
                ->count();

            $actualPaymentsValue = (float) Sale::where('sales_officer_id', $consultant->id)
                ->where('status', 'Closed Won')
                ->whereBetween('deal_closed_at', [$startDate, $endDate])
                ->sum('deal_value');

            $topupsCount = PaymentMilestone::whereHas('paymentPlan.sale', function($q) use ($consultant) {
                $q->where('sales_officer_id', $consultant->id);
            })->where('status', 'Paid')
              ->whereBetween('paid_at', [$startDate, $endDate])
              ->count();

            $topupsValue = (float) PaymentMilestone::whereHas('paymentPlan.sale', function($q) use ($consultant) {
                $q->where('sales_officer_id', $consultant->id);
            })->where('status', 'Paid')
              ->whereBetween('paid_at', [$startDate, $endDate])
              ->sum('amount');

            // 3. Expected payments
            $expectedCount = $primaryLog && $primaryLog->expected_payments_count > 0
                ? $primaryLog->expected_payments_count
                : Lead::where('assigned_to', $consultant->id)
                    ->whereIn('status', ['qualified', 'proposal_sent', 'negotiating'])
                    ->whereBetween('updated_at', [$startDate, $endDate])
                    ->count();

            $expectedContacts = $primaryLog && $primaryLog->expected_payments_notes
                ? $primaryLog->expected_payments_notes
                : Lead::where('assigned_to', $consultant->id)
                    ->whereIn('status', ['qualified', 'proposal_sent', 'negotiating'])
                    ->limit(4)
                    ->pluck('phone_number')
                    ->filter()
                    ->implode(', ');

            // 4. Inspections & Estates
            $inspectionsCount = Inspection::where('assigned_to', $consultant->id)
                ->whereBetween('inspection_date', [$startDate, $endDate])
                ->count();

            $projectLocations = Inspection::where('assigned_to', $consultant->id)
                ->whereBetween('inspection_date', [$startDate, $endDate])
                ->with('property')
                ->get()
                ->map(fn($i) => $i->property ? ($i->property->estate_name ?? $i->property->location ?? $i->property->name) : null)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $projectLocationsText = !empty($projectLocations) ? implode(', ', $projectLocations) : 'Office Briefings';

            // 5. Office Visits
            $officeVisits = $primaryLog && $primaryLog->office_visits_count > 0
                ? $primaryLog->office_visits_count
                : LeadActivity::where('user_id', $consultant->id)
                    ->where(fn($q) => $q->where('activity_type', 'like', '%Office%')->orWhere('description', 'like', '%Office%'))
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

            // 6. Engagements (Calls, SMS, WhatsApp)
            $callsCount = FollowUp::where('assigned_officer_id', $consultant->id)
                ->whereIn('type', ['call', 'phone'])
                ->whereBetween('follow_up_date', [$startDate, $endDate])
                ->count()
                + LeadActivity::where('user_id', $consultant->id)
                    ->where('activity_type', 'like', '%Call%')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

            $smsCount = FollowUp::where('assigned_officer_id', $consultant->id)
                ->where('type', 'sms')
                ->whereBetween('follow_up_date', [$startDate, $endDate])
                ->count()
                + LeadActivity::where('user_id', $consultant->id)
                    ->where('activity_type', 'like', '%SMS%')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

            $whatsappCount = FollowUp::where('assigned_officer_id', $consultant->id)
                ->where('type', 'whatsapp')
                ->whereBetween('follow_up_date', [$startDate, $endDate])
                ->count()
                + LeadActivity::where('user_id', $consultant->id)
                    ->where('activity_type', 'like', '%WhatsApp%')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

            // 7. Contacts generated
            $newContactsCount = Lead::where('assigned_to', $consultant->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $contactsPhoneCount = Lead::where('assigned_to', $consultant->id)
                ->whereNotNull('phone_number')
                ->where('phone_number', '!=', '')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $contactsEmailCount = Lead::where('assigned_to', $consultant->id)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

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

        $branches = Branch::orderBy('name', 'asc')->get();

        // Tenant Branding Resolution
        $setting     = CompanySetting::getCached();
        $tenantId    = tenant('id') ?? session('tenant_id') ?? '';
        $host        = request()->getHost();
        $companyName = $setting?->company_name ?? (tenant()?->name ?? 'Buckcrest Havens Limited');
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

        $columns = [
            'Sales Consultant',
            'Branch',
            'Location(s) / Event(s) Visited',
            'Actual & Successful Payments (Count)',
            'Actual New Sales Inflow (NGN)',
            'Top-ups / Milestones (Count)',
            'Top-ups Inflow (NGN)',
            'Total Realized Revenue (NGN)',
            'Expected Payments (Count)',
            'Expected Payment Prospects',
            'Site Inspections Conducted',
            'Project Locations Inspected',
            'Office Visits Escorted',
            'Calls Made',
            'SMS Sent',
            'WhatsApp Chats',
            'New Contacts Captured',
            'Contacts with Phone',
            'Contacts with Email',
            'Observations & Recommendations',
        ];

        $callback = function() use ($columns, $data) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens special characters correctly
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($data['matrixRows'] as $row) {
                fputcsv($file, [
                    $row['user']->name,
                    $row['user']->branch ? $row['user']->branch->name : 'Head Office',
                    $row['canvassing_locations'],
                    $row['actual_payments_count'],
                    number_format($row['actual_payments_value'], 2, '.', ''),
                    $row['topups_count'],
                    number_format($row['topups_value'], 2, '.', ''),
                    number_format($row['total_revenue'], 2, '.', ''),
                    $row['expected_payments_count'],
                    // Prefix with tab or quote so Excel never corrupts phone numbers into scientific notation
                    ' ' . $row['expected_payments_notes'],
                    $row['inspections_count'],
                    $row['project_locations_inspected'],
                    $row['office_visits_count'],
                    $row['calls_count'],
                    $row['sms_count'],
                    $row['whatsapp_count'],
                    $row['new_contacts_count'],
                    $row['contacts_phone_count'],
                    $row['contacts_email_count'],
                    $row['observations_recommendations'],
                ]);
            }

            // Aggregate totals row
            $agg = $data['aggregates'];
            fputcsv($file, [
                'AGGREGATE TOTALS',
                'ALL BRANCHES',
                'ALL CANVASSING HUBS',
                $agg['actual_payments_count'],
                number_format($agg['actual_payments_value'], 2, '.', ''),
                $agg['topups_count'],
                number_format($agg['topups_value'], 2, '.', ''),
                number_format($agg['total_realized_revenue'], 2, '.', ''),
                $agg['expected_payments_count'],
                '-',
                $agg['inspections_count'],
                'ALL ESTATE SITES',
                $agg['office_visits_count'],
                $agg['calls_count'],
                $agg['sms_count'],
                $agg['whatsapp_count'],
                $agg['new_contacts_count'],
                $agg['contacts_phone_count'],
                $agg['contacts_email_count'],
                'EXECUTIVE SUMMARY AUDIT',
            ]);

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
