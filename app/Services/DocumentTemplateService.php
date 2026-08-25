<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Property;
use App\Models\Sale;
use App\Models\PaymentMilestone;
use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use Illuminate\Support\Facades\Auth;

class DocumentTemplateService
{
    protected $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Compile document templates for a given lead, and generate/save the PDF instance.
     */
    public function compileAndGenerate(DocumentTemplate $template, Lead $lead, ?Sale $sale = null, ?PaymentMilestone $milestone = null): GeneratedDocument
    {
        $latestVersion = $template->latestVersion;
        $content = $latestVersion ? $latestVersion->content : '';

        // 1. Resolve objects
        $property = $sale ? $sale->property : $lead->propertyInterest;
        if (!$property && $milestone) {
            $property = $milestone->paymentPlan->sale->property;
        }

        // 2. Map replacement values
        $company = \App\Models\CompanySetting::first();
        $agent = $lead->assignedOfficer;
        $unit = $sale ? $sale->propertyUnit : null;
        $paymentPlan = $sale ? $sale->paymentPlan : null;

        $replacements = [
            // Lead / Client
            '{{client_name}}'         => $lead->full_name,
            '{{client_phone}}'        => $lead->phone_number,
            '{{client_email}}'        => $lead->email ?? 'N/A',
            '{{client_address}}'      => $lead->preferred_location ?? 'N/A',
            '{{client_nin}}'          => 'N/A',
            '{{client_dob}}'          => 'N/A',
            '{{client_occupation}}'   => 'N/A',
            '{{client_company}}'      => 'N/A',
            '{{client_nationality}}'  => 'N/A',
            '{{client_passport}}'     => 'N/A',

            // Property
            '{{property_name}}'       => $property ? $property->name : 'N/A',
            '{{property_type}}'       => $property ? $property->property_type : 'N/A',
            '{{property_address}}'    => $property ? $property->location : 'N/A',
            '{{property_location}}'   => $property ? ($property->estate_name ?? $property->location) : 'N/A',
            '{{property_city}}'       => $property ? ($property->city ?? $property->location) : 'N/A',
            '{{property_state}}'      => $property ? ($property->state ?? 'Federal Capital Territory (Abuja)') : 'N/A',
            '{{property_size}}'       => $unit ? $unit->size_sqm . ' sqm' : ($property && $property->total_area_sqm ? $property->total_area_sqm . ' sqm' : '500 sqm'),
            '{{property_unit_type}}'  => $unit ? $unit->unit_type : ($property ? $property->property_type : 'Plot / Unit'),
            '{{property_floor}}'      => $unit ? ($unit->floor_number ?? 'Ground Floor') : 'Ground Floor',
            '{{property_block}}'      => $unit ? ($unit->unit_number ?? 'Plot ' . rand(1, 150)) : 'Plot ' . ($property ? $property->id * 4 : '12'),
            '{{survey_plan_no}}'      => 'SURV/NG/' . date('Y') . '/' . ($property ? str_pad($property->id, 4, '0', STR_PAD_LEFT) : '0102'),
            '{{title_type}}'          => $property && !empty($property->title_type) ? $property->title_type : 'Certificate of Occupancy (C of O)',
            '{{property_price}}'      => $property ? '₦' . number_format($property->price, 2) : 'N/A',
            '{{property_description}}'=> $property ? $property->description : 'N/A',

            // Deal / Finance
            '{{deal_value}}'          => $sale ? '₦' . number_format($sale->deal_value, 2) : ($property ? '₦' . number_format($property->price, 2) : '₦0.00'),
            '{{down_payment}}'        => ($paymentPlan && $paymentPlan->amount_paid > 0) ? '₦' . number_format($paymentPlan->amount_paid, 2) : ($sale ? '₦' . number_format($sale->deal_value * 0.3, 2) : '₦0.00'),
            '{{outstanding_balance}}' => $paymentPlan ? '₦' . number_format($paymentPlan->balance, 2) : ($sale ? '₦' . number_format($sale->deal_value * 0.7, 2) : '₦0.00'),
            '{{payment_plan_duration}}'=> $paymentPlan ? $paymentPlan->number_of_installments . ' months' : '6 Months',
            '{{units_purchased}}'     => $sale ? $sale->units_purchased : '1',
            '{{transaction_ref}}'     => $sale ? 'REF-PF-' . str_pad($sale->id, 5, '0', STR_PAD_LEFT) : 'REF-PF-' . rand(10000, 99999),
            '{{commission_amount}}'   => $sale ? '₦' . number_format($sale->commissions()->sum('calculated_amount'), 2) : '₦0.00',

            // Agent / Staff
            '{{agent_name}}'          => $agent ? $agent->name : (Auth::user()?->name ?? 'Sales Officer'),
            '{{agent_phone}}'         => $agent ? ($agent->phone_number ?? '+234 800 000 0000') : '+234 800 000 0000',
            '{{agent_email}}'         => $agent ? $agent->email : (Auth::user()?->email ?? 'sales@propertyflow.com'),
            '{{agent_branch}}'        => ($agent && $agent->branch) ? $agent->branch->name : 'Head Office Branch',

            // Company
            '{{company_name}}'        => $company ? $company->company_name : 'NAW PropertyFlow CRM',
            '{{company_address}}'      => $company ? ($company->address ?? 'Plot 1024, Diplomatic Drive, Maitama, Abuja') : 'Plot 1024, Diplomatic Drive, Maitama, Abuja',
            '{{company_phone}}'        => $company ? ($company->phone ?? '+234 901 000 0000') : '+234 901 000 0000',
            '{{company_email}}'        => $company ? ($company->email ?? 'legal@nawpropertyflow.com') : 'legal@nawpropertyflow.com',
            '{{company_rc_number}}'    => 'RC 1892044',

            // Dates & Schedule
            '{{current_date}}'        => now()->format('F d, Y'),
            '{{date_of_sale}}'        => ($sale && $sale->deal_closed_at) ? $sale->deal_closed_at->format('F d, Y') : now()->format('F d, Y'),
            '{{inspection_date}}'     => $lead->inspections()->where('status', 'completed')->latest()->first()?->scheduled_at?->format('F d, Y') ?? now()->subDays(3)->format('F d, Y'),
            '{{key_handover_date}}'   => now()->addDays(30)->format('F d, Y'),
            '{{contract_date}}'       => now()->format('F d, Y'),
            '{{completion_date}}'     => now()->addDays(90)->format('F d, Y'),

            // Legal
            '{{document_ref}}'        => 'DOC-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 6)),
            '{{witness_1_name}}'      => 'Barr. Tunde Balogun (Legal Director)',
            '{{witness_2_name}}'      => 'Amina Bello (Operations Head)',
            '{{solicitor_name}}'      => 'Barrister O. A. Balogun (SAN)',
            '{{solicitor_firm}}'      => 'Balogun & Associates Legal Practitioners',
            '{{signatory_capacity}}'  => 'Vendor & Developer',
        ];

        // 3. Render milestones list if needed
        if (str_contains($content, '{{milestone_payments}}')) {
            $replacements['{{milestone_payments}}'] = $this->renderMilestonesHtml($sale);
        }

        // 4. Substitute values
        $compiledHtml = str_replace(array_keys($replacements), array_values($replacements), $content);

        // 5. Generate filename & path
        $filename = 'documents/doc_' . $template->id . '_' . $lead->id . '_' . time() . '.pdf';
        $title = $template->name . ' - ' . $lead->full_name;

        // 6. Save PDF via PdfService
        $this->pdfService->generateAndSave($compiledHtml, $filename, $title);

        // 7. Record to DB
        return GeneratedDocument::create([
            'document_template_id' => $template->id,
            'lead_id'              => $lead->id,
            'title'                => $title,
            'content'              => $compiledHtml,
            'pdf_path'             => $filename,
            'generated_by_user_id' => Auth::id() ?? 1,
        ]);
    }

    /**
     * Build an HTML Table of milestones installments for dynamic injection.
     */
    protected function renderMilestonesHtml(?Sale $sale): string
    {
        if (!$sale || !$sale->paymentPlan) {
            return '<p>No active payment plan installments configured.</p>';
        }

        $milestones = $sale->paymentPlan->milestones()->orderBy('due_date')->get();

        $html = '<table>';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Installment Stage / Milestone</th>';
        $html .= '<th>Amount Due</th>';
        $html .= '<th>Due Date</th>';
        $html .= '<th>Amount Paid</th>';
        $html .= '<th>Status</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($milestones as $m) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($m->label) . '</td>';
            $html .= '<td>₦' . number_format($m->amount_due, 2) . '</td>';
            $html .= '<td>' . $m->due_date->format('M d, Y') . '</td>';
            $html .= '<td>₦' . number_format($m->amount_paid, 2) . '</td>';
            $html .= '<td style="font-weight: bold; text-transform: uppercase;">' . $m->status . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }
}
