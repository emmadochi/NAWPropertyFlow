<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Sale;
use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentInvoiceMail;
use Carbon\Carbon;

class SalesService
{
    protected $leadService;
    protected $paymentService;

    public function __construct(LeadService $leadService, PaymentService $paymentService)
    {
        $this->leadService = $leadService;
        $this->paymentService = $paymentService;
    }

    /**
     * Record a closed sale transaction.
     */
    public function recordSale(array $data, ?int $userId = null): Sale
    {
        return DB::transaction(function () use ($data, $userId) {
            $lead = Lead::findOrFail($data['lead_id']);
            $property = Property::findOrFail($data['property_id']);
            $currentUserId = $userId ?? Auth::id();

            // Ensure valid sales officer ID
            $salesOfficerId = $data['sales_officer_id'] ?? $lead->assigned_to ?? $currentUserId;
            if ($salesOfficerId && !\App\Models\User::where('id', $salesOfficerId)->exists()) {
                $salesOfficerId = \App\Models\User::value('id');
            }

            $dealClosedAt = !empty($data['deal_closed_at']) ? Carbon::parse($data['deal_closed_at']) : Carbon::now();
            $sendNotification = isset($data['send_notification_email']) ? filter_var($data['send_notification_email'], FILTER_VALIDATE_BOOLEAN) : true;

            // 1. Create Sale Record
            $sale = Sale::create([
                'lead_id' => $lead->id,
                'property_id' => $property->id,
                'property_unit_id' => $data['property_unit_id'] ?? null,
                'sales_officer_id' => $salesOfficerId,
                'deal_value' => $data['deal_value'],
                'units_purchased' => $data['units_purchased'] ?? 1,
                'status' => $data['status'] ?? 'Closed Won',
                'payment_receipt' => $data['payment_receipt'] ?? null,
                'deal_closed_at' => $dealClosedAt,
            ]);

            // 2. Update Lead Status to Closed Won
            $lead->status = 'Closed Won';
            $lead->save();

            // 3. Mark Property Unit sold or Decrement Property Units
            if ($sale->property_unit_id) {
                $unit = \App\Models\PropertyUnit::find($sale->property_unit_id);
                if ($unit) {
                    $unit->markSold();
                }
            } else {
                if ($property->available_units > 0) {
                    $property->decrement('available_units', $sale->units_purchased);
                }
            }

            // 4. Create payment plan (outright or installment milestones)
            $planType = $data['plan_type'] ?? 'outright';
            $initialDeposit = isset($data['initial_deposit']) ? floatval($data['initial_deposit']) : 0;
            $bankRef = $data['bank_reference'] ?? ('REF-' . strtoupper(substr(uniqid(), -6)));
            $payMethod = $data['payment_method'] ?? 'Bank Transfer';

            $milestones = [];

            if ($planType === 'outright') {
                $milestones[] = [
                    'label' => '100% Outright Full Payment',
                    'amount_due' => $sale->deal_value,
                    'due_date' => $dealClosedAt->toDateString(),
                ];
            } else {
                // Installment plan: Deposit milestone + monthly spread
                $spreadMonths = isset($data['installment_months']) ? max(1, intval($data['installment_months'])) : 6;
                $balance = max(0, $sale->deal_value - $initialDeposit);
                $monthlyAmount = $spreadMonths > 0 ? round($balance / $spreadMonths, 2) : 0;

                // Milestone 1: Initial Deposit
                $milestones[] = [
                    'label' => 'Initial Commitment Deposit',
                    'amount_due' => $initialDeposit > 0 ? $initialDeposit : $sale->deal_value,
                    'due_date' => $dealClosedAt->toDateString(),
                ];

                // Milestones 2..N: Monthly Installments
                if ($balance > 0) {
                    for ($i = 1; $i <= $spreadMonths; $i++) {
                        $isLast = ($i === $spreadMonths);
                        $installmentAmt = $isLast ? ($balance - ($monthlyAmount * ($spreadMonths - 1))) : $monthlyAmount;
                        $milestones[] = [
                            'label' => "Installment Tranche #{$i} of {$spreadMonths}",
                            'amount_due' => max(0, $installmentAmt),
                            'due_date' => (clone $dealClosedAt)->addMonths($i)->toDateString(),
                        ];
                    }
                }
            }

            $paymentPlan = $this->paymentService->createPlan($sale, [
                'plan_type' => $planType,
                'payment_plan_duration_id' => $data['payment_plan_duration_id'] ?? null,
                'duration_months' => $data['installment_months'] ?? null,
                'base_deal_value' => $data['base_deal_value'] ?? null,
                'interest_rate_pct' => $data['interest_rate_pct'] ?? 0,
                'interest_amount' => $data['interest_amount'] ?? 0,
                'total_amount' => $sale->deal_value,
                'number_of_installments' => count($milestones),
                'milestones' => $milestones,
                'notes' => $data['notes'] ?? ("Closed deal with " . ($planType === 'outright' ? 'full payment' : 'installment structure') . "."),
            ]);

            // If initial payment was made (outright full deal or initial deposit > 0)
            $firstMilestone = $paymentPlan->milestones()->first();
            if ($firstMilestone) {
                if ($planType === 'outright') {
                    $this->paymentService->recordMilestonePayment($firstMilestone, [
                        'amount_paid' => $sale->deal_value,
                        'bank_reference' => $bankRef,
                        'notes' => "Full outright payment recorded on sale closing via {$payMethod}.",
                        'payment_date' => $dealClosedAt->toDateTimeString(),
                        'send_receipt_email' => $sendNotification,
                    ], $sendNotification);
                } elseif ($initialDeposit > 0) {
                    $this->paymentService->recordMilestonePayment($firstMilestone, [
                        'amount_paid' => $initialDeposit,
                        'bank_reference' => $bankRef,
                        'notes' => "Initial commitment deposit recorded on sale closing via {$payMethod}.",
                        'payment_date' => $dealClosedAt->toDateTimeString(),
                        'send_receipt_email' => $sendNotification,
                    ], $sendNotification);
                }
            }

            // 5. Calculate commissions
            $this->paymentService->calculateCommissions($sale);

            // 6. Log activities
            $this->leadService->logActivity(
                $lead->id,
                $currentUserId,
                'Sale Closed & Receipt Issued',
                "Deal closed for '{$property->name}'. Value: ₦" . number_format($sale->deal_value, 2) . " (" . ucfirst($planType) . ($initialDeposit > 0 ? ", Initial Deposit: ₦" . number_format($initialDeposit, 2) : '') . ")"
            );

            // Create customer user account for buyer portal access
            if ($lead->email) {
                try {
                    $userExists = \App\Models\User::where('email', $lead->email)->exists();
                    if (!$userExists) {
                        \App\Models\User::create([
                            'name' => $lead->full_name,
                            'email' => $lead->email,
                            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                            'role' => 'customer',
                            'status' => 'active',
                            'phone_number' => $lead->phone_number,
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Customer buyer account creation skipped: ' . $e->getMessage());
                }
            }

            // 7. Send invoice/receipt notification email (if enabled)
            if ($sendNotification && $lead->email) {
                try {
                    Mail::to($lead->email)->send(new PaymentInvoiceMail($sale));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Payment invoice mail failed: ' . $e->getMessage());
                }
            }

            try {
                event(new \App\Events\DealWon($sale, $sendNotification));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('DealWon event error: ' . $e->getMessage());
            }

            if ($sendNotification) {
                try {
                    app(\App\Services\DripService::class)->triggerFor($lead, 'deal_won');
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Drip deal_won error: ' . $e->getMessage());
                }
            }

            return $sale;
        });
    }
}
