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
            $currentUserId = $userId ?? Auth::id() ?? 1;

            // 1. Create Sale Record
            $sale = Sale::create([
                'lead_id' => $lead->id,
                'property_id' => $property->id,
                'property_unit_id' => $data['property_unit_id'] ?? null,
                'sales_officer_id' => $data['sales_officer_id'] ?? $lead->assigned_to ?? $currentUserId,
                'deal_value' => $data['deal_value'],
                'units_purchased' => $data['units_purchased'] ?? 1,
                'status' => $data['status'] ?? 'Closed Won',
                'payment_receipt' => $data['payment_receipt'] ?? null,
                'deal_closed_at' => Carbon::now(),
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

            // 4. Create payment plan (default or custom)
            $planType = $data['plan_type'] ?? 'outright';
            $installments = $data['number_of_installments'] ?? 1;
            $milestones = $data['milestones'] ?? [
                [
                    'label' => 'Outright Payment',
                    'amount_due' => $sale->deal_value,
                    'due_date' => Carbon::now()->addDays(7)->toDateString(),
                ]
            ];

            $this->paymentService->createPlan($sale, [
                'plan_type' => $planType,
                'number_of_installments' => $installments,
                'milestones' => $milestones,
                'notes' => $data['notes'] ?? null,
            ]);

            // 5. Calculate commissions
            $this->paymentService->calculateCommissions($sale);

            // 6. Log activities
            $this->leadService->logActivity(
                $lead->id,
                $currentUserId,
                'Sale Closed',
                "Deal closed successfully for property '{$property->name}'. Value: ₦" . number_format($sale->deal_value, 2)
            );

            // Create customer user account for portal transparency
            if ($lead->email) {
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
            }

            // 7. Send invoice email
            if ($lead->email) {
                try {
                    Mail::to($lead->email)->send(new PaymentInvoiceMail($sale));
                } catch (\Exception $e) {
                    // Ignore or log
                }
            }

            event(new \App\Events\DealWon($sale));

            try {
                app(\App\Services\DripService::class)->triggerFor($lead, 'deal_won');
            } catch (\Exception $e) {}

            return $sale;
        });
    }
}
