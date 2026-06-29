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

            $plan = PaymentPlan::create([
                'sale_id' => $sale->id,
                'plan_type' => $data['plan_type'],
                'total_amount' => $sale->deal_value,
                'amount_paid' => 0,
                'balance' => $sale->deal_value,
                'number_of_installments' => $data['number_of_installments'] ?? 1,
                'notes' => $data['notes'] ?? null,
                'status' => 'active',
            ]);

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
    public function recordMilestonePayment(PaymentMilestone $milestone, array $data): PaymentMilestone
    {
        return DB::transaction(function () use ($milestone, $data) {
            $paymentPlan = $milestone->paymentPlan;
            $sale = $paymentPlan->sale;
            $lead = $sale->lead;
            $currentUserId = Auth::id() ?? 1;

            $amountPaid = $data['amount_paid'];
            
            // Update milestone
            $milestone->amount_paid += $amountPaid;
            $milestone->bank_reference = $data['bank_reference'] ?? $milestone->bank_reference;
            $milestone->paid_at = Carbon::now();
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
                "Payment of ₦" . number_format($amountPaid, 2) . " received for milestone: '{$milestone->label}'. Reference: " . ($data['bank_reference'] ?? 'N/A')
            );

            // Generate receipt PDF
            $pdfPath = $this->generateReceiptPdf($milestone);
            $milestone->receipt_path = $pdfPath;
            $milestone->save();

            event(new \App\Events\PaymentReceived($milestone));

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

        $pdf = Pdf::loadView('pdf.receipt', compact('milestone', 'paymentPlan', 'sale', 'lead', 'property'));
        
        $filename = 'receipts/receipt_' . $milestone->id . '_' . time() . '.pdf';
        
        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }

    /**
     * Auto-calculate and create commissions for a sale.
     */
    public function calculateCommissions(Sale $sale): void
    {
        // Get rates from config
        $officerRate = config('commission.sales_officer_rate', 5.0);
        $managerRate = config('commission.manager_override_rate', 1.5);

        // 1. Sales Officer Commission
        if ($sale->sales_officer_id) {
            $officer = User::find($sale->sales_officer_id);
            $rate = ($officer && !is_null($officer->commission_rate)) ? $officer->commission_rate : $officerRate;
            $amount = ($sale->deal_value * $rate) / 100;
            
            // Avoid duplicate commissions for the same sale
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
