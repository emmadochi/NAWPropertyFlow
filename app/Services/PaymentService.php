<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\PaymentPlan;
use App\Models\PaymentMilestone;
use App\Models\Commission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Mail\PaymentReminderMail;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PaymentService
{
    protected $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Create a payment plan with milestones.
     */
    public function createPlan(Sale $sale, array $data): PaymentPlan
    {
        return DB::transaction(function () use ($sale, $data) {
            // Delete existing plan if any
            if ($sale->paymentPlan) {
                $sale->paymentPlan->delete();
            }

            $baseAmount = isset($data['base_deal_value']) ? (float)$data['base_deal_value'] : (float)$sale->deal_value;
            $interestRate = isset($data['interest_rate_pct']) ? (float)$data['interest_rate_pct'] : 0.00;
            $interestAmount = isset($data['interest_amount']) ? (float)$data['interest_amount'] : round(($baseAmount * $interestRate) / 100, 2);
            $vatRate = isset($data['vat_rate_pct']) ? (float)$data['vat_rate_pct'] : 0.00;
            $vatAmount = isset($data['vat_amount']) ? (float)$data['vat_amount'] : 0.00;
            $taxAmount = isset($data['tax_amount']) ? (float)$data['tax_amount'] : 0.00;
            $totalAmount = isset($data['total_amount']) ? (float)$data['total_amount'] : ($baseAmount + $interestAmount + $vatAmount);

            // Determine which optional columns exist (safe for databases that haven't run the VAT migration)
            $hasVat = Schema::hasColumn('payment_plans', 'vat_rate_pct');
            $hasTax = Schema::hasColumn('payment_plans', 'tax_amount');

            $planData = [
                'sale_id'                  => $sale->id,
                'payment_plan_duration_id' => $data['payment_plan_duration_id'] ?? null,
                'duration_months'          => $data['duration_months'] ?? null,
                'plan_type'                => $data['plan_type'] ?? 'installment',
                'base_deal_value'          => $baseAmount,
                'interest_rate_pct'        => $interestRate,
                'interest_amount'          => $interestAmount,
                'total_amount'             => $totalAmount,
                'amount_paid'              => 0,
                'balance'                  => $totalAmount,
                'number_of_installments'   => $data['number_of_installments'] ?? (isset($data['milestones']) ? count($data['milestones']) : 1),
                'notes'                    => $data['notes'] ?? null,
                'status'                   => 'active',
            ];

            if ($hasVat) {
                $planData['vat_rate_pct'] = $vatRate;
                $planData['vat_amount']   = $vatAmount;
            }

            if ($hasTax) {
                $planData['tax_amount'] = $taxAmount;
            }

            $plan = PaymentPlan::create($planData);

            $milestonesData = $data['milestones'] ?? [];
            if (empty($milestonesData)) {
                // Default: single milestone for full payment
                $milestonesData[] = [
                    'label' => 'Outright Payment',
                    'amount_due' => $sale->deal_value,
                    'due_date' => Carbon::now()->addDays(7)->toDateString(),
                ];
            }

            foreach ($milestonesData as $m) {
                PaymentMilestone::create([
                    'payment_plan_id' => $plan->id,
                    'label' => $m['label'],
                    'amount_due' => $m['amount_due'],
                    'due_date' => $m['due_date'],
                    'amount_paid' => 0,
                    'status' => 'pending',
                ]);
            }

            return $plan;
        });
    }

    /**
     * Record a milestone payment manually.
     */
    public function recordMilestonePayment(PaymentMilestone $milestone, array $data, ?bool $sendReceiptEmail = null): PaymentMilestone
    {
        return DB::transaction(function () use ($milestone, $data, $sendReceiptEmail) {
            $paymentPlan = $milestone->paymentPlan;
            $sale = $paymentPlan->sale;
            $lead = $sale->lead;
            $currentUserId = Auth::id() ?? 1;

            $amountPaid = $data['amount_paid'];
            $paymentDate = !empty($data['payment_date']) ? Carbon::parse($data['payment_date']) : Carbon::now();
            $sendNotification = $sendReceiptEmail ?? (isset($data['send_receipt_email']) ? filter_var($data['send_receipt_email'], FILTER_VALIDATE_BOOLEAN) : true);
            
            // Update milestone
            $milestone->amount_paid += $amountPaid;
            $milestone->bank_reference = $data['bank_reference'] ?? $milestone->bank_reference;
            $milestone->paid_at = $paymentDate;
            if (isset($data['notes'])) {
                $milestone->notes = $data['notes'];
            }

            if ($milestone->amount_paid >= $milestone->amount_due) {
                $milestone->status = 'paid';
            } else {
                $milestone->status = 'partial';
            }
            $milestone->save();

            // Update plan total paid & balance
            $paymentPlan->amount_paid += $amountPaid;
            $paymentPlan->balance = max(0, $paymentPlan->total_amount - $paymentPlan->amount_paid);

            if ($paymentPlan->balance <= 0) {
                $paymentPlan->status = 'completed';
            }
            $paymentPlan->save();

            // Log activity
            $this->leadService->logActivity(
                $lead->id,
                $currentUserId,
                'Payment Received',
                "Payment of ₦" . number_format($amountPaid, 2) . " received for milestone: '{$milestone->label}' on " . $paymentDate->format('d M Y') . ". Reference: " . ($data['bank_reference'] ?? 'N/A')
            );

            // Generate receipt PDF for records
            try {
                $pdfPath = $this->generateReceiptPdf($milestone);
                $milestone->receipt_path = $pdfPath;
                $milestone->save();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Receipt PDF generation error: ' . $e->getMessage());
            }

            // Fire event with notification flag
            try {
                event(new \App\Events\PaymentReceived($milestone, $sendNotification));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('PaymentReceived event error: ' . $e->getMessage());
            }

            // If notification email enabled, send email
            if ($sendNotification && $lead->email) {
                try {
                    Mail::to($lead->email)->send(new \App\Mail\PaymentInvoiceMail($sale));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Payment milestone mail error: ' . $e->getMessage());
                }
            }

            return $milestone;
        });
    }

    /**
     * Generate receipt PDF for a milestone and return the storage path.
     */
    public function generateReceiptPdf(PaymentMilestone $milestone): string
    {
        $paymentPlan = $milestone->paymentPlan;
        $sale = $paymentPlan->sale;
        $lead = $sale->lead;
        $property = $sale->property;

        try {
            $pdf = Pdf::loadView('pdf.receipt', compact('milestone', 'paymentPlan', 'sale', 'lead', 'property'));
            $filename = 'receipts/receipt_' . $milestone->id . '_' . time() . '.pdf';
            Storage::disk('public')->put($filename, $pdf->output());
            return $filename;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed generating PDF receipt: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Mark a milestone payment as Verified by an Admin (super_admin / company_admin).
     * Automatically calculates & approves the Marketer's commission queued for the monthly payroll run.
     */
    public function verifyMilestonePayment(PaymentMilestone $milestone, int $adminId): PaymentMilestone
    {
        return DB::transaction(function () use ($milestone, $adminId) {
            $milestone->verified_at = Carbon::now();
            $milestone->verified_by = $adminId;
            $milestone->save();

            $sale = $milestone->paymentPlan->sale;
            $lead = $sale->lead;

            // Recalculate & approve commissions for this sale based on verified paid amount
            $this->calculateAndApproveCommissions($sale, $adminId);

            // Log activity
            $this->leadService->logActivity(
                $lead->id,
                $adminId,
                'Payment Verified & Commission Queued',
                "Admin verified payment of ₦" . number_format($milestone->amount_paid, 2) . " for '{$milestone->label}'. Marketer commission approved for monthly payroll."
            );

            return $milestone;
        });
    }

    /**
     * Calculate and approve sales commissions for verified sales transactions.
     */
    public function calculateAndApproveCommissions(Sale $sale, ?int $approvedBy = null): void
    {
        $officerRate = config('commission.sales_officer_rate', 5.0);
        $managerRate = config('commission.manager_override_rate', 1.5);

        // Calculate verified amount paid across all verified milestones
        $verifiedAmountPaid = $sale->paymentPlan?->milestones()
            ->whereNotNull('verified_at')
            ->sum('amount_paid') ?? $sale->deal_value;

        // 1. Sales Officer / Marketer Commission
        if ($sale->sales_officer_id) {
            $officer = User::find($sale->sales_officer_id);
            $rate = ($officer && !is_null($officer->commission_rate)) ? $officer->commission_rate : $officerRate;
            $amount = ($verifiedAmountPaid * $rate) / 100;
            
            $commission = Commission::firstOrNew([
                'sale_id' => $sale->id,
                'user_id' => $sale->sales_officer_id,
                'commission_type' => 'sales_officer',
            ]);

            $commission->rate_percent = $rate;
            $commission->calculated_amount = $amount;
            $commission->status = 'approved';
            $commission->approved_by = $approvedBy ?? $commission->approved_by ?? Auth::id();
            $commission->save();
        }

        // 2. Sales Manager Override Commission
        $manager = User::where('role', 'sales_manager')->first();
        if ($manager) {
            $managerAmount = ($verifiedAmountPaid * $managerRate) / 100;
            
            $managerCommission = Commission::firstOrNew([
                'sale_id' => $sale->id,
                'user_id' => $manager->id,
                'commission_type' => 'manager_override',
            ]);

            $managerCommission->rate_percent = $managerRate;
            $managerCommission->calculated_amount = $managerAmount;
            $managerCommission->status = 'approved';
            $managerCommission->approved_by = $approvedBy ?? $managerCommission->approved_by ?? Auth::id();
            $managerCommission->save();
        }
    }

    /**
     * Auto-calculate and create commissions for a sale (called upon deal closing).
     */
    public function calculateCommissions(Sale $sale): void
    {
        $officerRate = config('commission.sales_officer_rate', 5.0);
        $managerRate = config('commission.manager_override_rate', 1.5);

        // 1. Sales Officer Commission
        if ($sale->sales_officer_id) {
            $officer = User::find($sale->sales_officer_id);
            $rate = ($officer && !is_null($officer->commission_rate)) ? $officer->commission_rate : $officerRate;
            $amount = ($sale->deal_value * $rate) / 100;
            
            Commission::firstOrCreate([
                'sale_id' => $sale->id,
                'user_id' => $sale->sales_officer_id,
                'commission_type' => 'sales_officer',
            ], [
                'rate_percent' => $rate,
                'calculated_amount' => $amount,
                'status' => 'pending',
            ]);
        }

        // 2. Manager Override Commission
        $manager = User::where('role', 'sales_manager')->first();
        if ($manager) {
            $managerAmount = ($sale->deal_value * $managerRate) / 100;
            
            Commission::firstOrCreate([
                'sale_id' => $sale->id,
                'user_id' => $manager->id,
                'commission_type' => 'manager_override',
            ], [
                'rate_percent' => $managerRate,
                'calculated_amount' => $managerAmount,
                'status' => 'pending',
            ]);
        }
    }
}
