<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Sale;
use App\Models\Property;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display reports metrics.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchId = $request->input('branch_id');

        // Set default dates if not provided (last 30 days)
        if (!$startDate) {
            $startDate = now()->subDays(30)->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = now()->format('Y-m-d');
        }

        // Get filtered dashboard metrics/charts
        $dashboardData = $this->reportService->getDashboardData(null, $startDate, $endDate, $branchId);
        $leaderboard = $this->reportService->getLeaderboard();

        // Extra details for properties performance
        $properties = Property::withCount('sales')
            ->withSum('sales as total_revenue', 'deal_value')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // Dedicated reports
        $leadsBySource = $this->reportService->getLeadsBySourceReport($startDate, $endDate, $branchId);
        $salesByAgent = $this->reportService->getSalesByAgentReport($startDate, $endDate, $branchId);
        $followUpCompliance = $this->reportService->getFollowUpComplianceReport($startDate, $endDate, $branchId);
        $branchComparison = $this->reportService->getBranchComparisonReport($startDate, $endDate);

        $branches = \App\Models\Branch::orderBy('name', 'asc')->get();

        return view('reports.index', array_merge($dashboardData, [
            'leaderboard' => $leaderboard,
            'properties_report' => $properties,
            'leadsBySource' => $leadsBySource,
            'salesByAgent' => $salesByAgent,
            'followUpCompliance' => $followUpCompliance,
            'branchComparison' => $branchComparison,
            'branches' => $branches,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchId' => $branchId,
        ]));
    }

    /**
     * Export Leads to CSV.
     */
    public function exportLeads()
    {
        $headers = [
            'Content-type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=leads_report_' . date('Y-m-d') . '.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $columns = ['ID', 'Full Name', 'Phone', 'WhatsApp', 'Email', 'Budget', 'Property Interest', 'Location', 'Source', 'Officer', 'Status', 'Created At'];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $leads = Lead::with(['propertyInterest', 'assignedOfficer'])->orderBy('created_at', 'desc')->get();

            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->id,
                    $lead->full_name,
                    $lead->phone_number,
                    $lead->whatsapp_number ?? 'N/A',
                    $lead->email ?? 'N/A',
                    $lead->budget_range ?? 'N/A',
                    $lead->propertyInterest ? $lead->propertyInterest->name : 'N/A',
                    $lead->preferred_location ?? 'N/A',
                    $lead->lead_source,
                    $lead->assignedOfficer ? $lead->assignedOfficer->name : 'Unassigned',
                    $lead->status,
                    $lead->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Export Sales to CSV.
     */
    public function exportSales()
    {
        $headers = [
            'Content-type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=sales_report_' . date('Y-m-d') . '.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $columns = ['ID', 'Lead Name', 'Property Name', 'Sales Officer', 'Deal Value (₦)', 'Units', 'Status', 'Deal Date'];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $sales = Sale::with(['lead', 'property', 'salesOfficer'])->orderBy('deal_closed_at', 'desc')->get();

            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->id,
                    $sale->lead ? $sale->lead->full_name : 'N/A',
                    $sale->property ? $sale->property->name : 'N/A',
                    $sale->salesOfficer ? $sale->salesOfficer->name : 'N/A',
                    $sale->deal_value,
                    $sale->units_purchased,
                    $sale->status,
                    $sale->deal_closed_at ? $sale->deal_closed_at->format('Y-m-d H:i:s') : 'N/A',
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Export Leads by Source report to CSV.
     */
    public function exportLeadsBySource(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchId = $request->input('branch_id');

        $headers = [
            'Content-type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=leads_by_source_' . date('Y-m-d') . '.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $columns = ['Lead Source', 'Total Leads', 'Active Leads', 'Lost Leads', 'Won Leads', 'Conversion Rate (%)'];

        $callback = function() use ($columns, $startDate, $endDate, $branchId) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $data = $this->reportService->getLeadsBySourceReport($startDate, $endDate, $branchId);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->lead_source,
                    $row->total_leads,
                    $row->active_leads,
                    $row->lost_leads,
                    $row->won_leads,
                    $row->conversion_rate,
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Export Sales by Agent report to CSV.
     */
    public function exportSalesByAgent(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchId = $request->input('branch_id');

        $headers = [
            'Content-type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=sales_by_agent_' . date('Y-m-d') . '.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $columns = ['Agent Name', 'Role', 'Deals Closed', 'Gross Revenue (₦)', 'Average Deal Value (₦)'];

        $callback = function() use ($columns, $startDate, $endDate, $branchId) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $data = $this->reportService->getSalesByAgentReport($startDate, $endDate, $branchId);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->name,
                    str_replace('_', ' ', $row->role),
                    $row->deals_closed,
                    $row->gross_revenue ?? 0,
                    $row->avg_deal_value,
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Export Follow-up Compliance report to CSV.
     */
    public function exportFollowUpCompliance(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchId = $request->input('branch_id');

        $headers = [
            'Content-type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=followup_compliance_' . date('Y-m-d') . '.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $columns = ['Agent Name', 'Total Follow-Ups', 'Completed Follow-Ups', 'Overdue Follow-Ups', 'Compliance Rate (%)'];

        $callback = function() use ($columns, $startDate, $endDate, $branchId) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $data = $this->reportService->getFollowUpComplianceReport($startDate, $endDate, $branchId);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->agent_name,
                    $row->total,
                    $row->completed,
                    $row->overdue,
                    $row->compliance_rate,
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Export Branch Performance Comparison to CSV.
     */
    public function exportBranchComparison(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $headers = [
            'Content-type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=branch_performance_' . date('Y-m-d') . '.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $columns = ['Branch Name', 'Total Leads', 'Closed Deals', 'Conversion Rate (%)', 'Gross Revenue (₦)', 'Top Sales Executive'];

        $callback = function() use ($columns, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $data = $this->reportService->getBranchComparisonReport($startDate, $endDate);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->name,
                    $row->total_leads,
                    $row->closed_deals,
                    $row->conversion_rate,
                    $row->gross_revenue,
                    $row->top_agent,
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

}
