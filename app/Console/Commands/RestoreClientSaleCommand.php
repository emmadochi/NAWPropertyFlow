<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\Property;
use App\Models\Sale;
use App\Models\PaymentPlan;
use App\Models\PaymentMilestone;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RestoreClientSaleCommand extends Command
{
    protected $signature = 'ricaf:restore-client-sale 
                            {--email=frednando27@gmail.com : Email of the client} 
                            {--phone=08033401596 : Phone of the client} 
                            {--tenant= : Optional specific tenant ID}';

    protected $description = 'Safely restores missing property sale, payment plan, and milestones for Nnaji Frederick Emeka without affecting other companies';

    public function handle(): int
    {
        $this->info('=================================================================');
        $this->info('Restoring Missing Sale & Payment Records for Nnaji Frederick Emeka');
        $this->info('=================================================================');

        $email = $this->option('email');
        $phone = $this->option('phone');
        $tenantId = $this->option('tenant');

        try {
            $tenantQuery = Tenant::query();
            if ($tenantId) {
                $tenantQuery->where('id', $tenantId);
            }
            $tenants = $tenantQuery->get();
        } catch (\Throwable $e) {
            $tenants = collect();
        }

        $processed = false;

        if ($tenants->isNotEmpty()) {
            foreach ($tenants as $tenant) {
                $tenant->run(function () use ($email, $phone, $tenant, &$processed) {
                    $found = $this->restoreForLead($email, $phone, $tenant->id);
                    if ($found) {
                        $processed = true;
                    }
                });
            }
        } else {
            $processed = $this->restoreForLead($email, $phone, 'default');
        }

        if (!$processed) {
            $this->warn("No matching lead found with Email: [{$email}] or Phone: [{$phone}]. No changes made.");
        }

        return Command::SUCCESS;
    }

    private function restoreForLead(string $email, string $phone, string $contextName): bool
    {
        // 1. Locate Lead strictly by email or phone
        $lead = Lead::withoutGlobalScopes()
            ->where(function ($q) use ($email, $phone) {
                $q->where('email', $email)
                  ->orWhere('phone_number', $phone);
            })
            ->first();

        if (!$lead) {
            return false; // Not in this database/tenant
        }

        $this->info("--> Found Target Lead in [{$contextName}]: #{$lead->id} - {$lead->full_name} ({$lead->email})");

        // 2. Safety Check: If sale already exists, do not duplicate!
        $existingSale = Sale::withoutGlobalScopes()->where('lead_id', $lead->id)->first();
        if ($existingSale) {
            $this->warn("  ℹ️ Sale record #{$existingSale->id} already exists for this lead. Skipping creation to prevent duplicates.");
            return true;
        }

        // 3. Find Property (Hutu Prestige)
        $property = null;
        if ($lead->property_interest_id) {
            $property = Property::withoutGlobalScopes()->find($lead->property_interest_id);
        }
        if (!$property) {
            $property = Property::withoutGlobalScopes()->where('name', 'like', '%Hutu Prestige%')->first();
        }
        if (!$property) {
            $property = Property::withoutGlobalScopes()->first();
        }

        if (!$property) {
            $this->error("  ❌ Could not find a suitable Property record in [{$contextName}].");
            return true;
        }

        // 4. Determine Sales Officer (Assigned officer Lewis Egure, or Admin)
        $salesOfficerId = $lead->assigned_to;
        if (!$salesOfficerId || !User::where('id', $salesOfficerId)->exists()) {
            $adminUser = User::where('role', 'super_admin')->first() ?? User::first();
            $salesOfficerId = $adminUser ? $adminUser->id : 1;
        }

        $dealValue = 19500000.00;
        $closedAt = Carbon::parse('2026-08-24 07:36:00');

        DB::transaction(function () use ($lead, $property, $salesOfficerId, $dealValue, $closedAt) {
            // A. Create Sale Record
            $sale = Sale::create([
                'lead_id'          => $lead->id,
                'property_id'      => $property->id,
                'property_unit_id' => null,
                'sales_officer_id' => $salesOfficerId,
                'deal_value'       => $dealValue,
                'base_deal_value'  => $dealValue,
                'units_purchased'  => 1,
                'status'           => 'Closed Won',
                'deal_closed_at'   => $closedAt,
                'created_at'       => $closedAt,
                'updated_at'       => now(),
            ]);

            // B. Create Payment Plan
            $plan = PaymentPlan::create([
                'sale_id'                => $sale->id,
                'plan_type'              => 'installment',
                'total_amount'           => $dealValue,
                'amount_paid'            => 11000000.00,
                'balance'                => 8500000.00,
                'number_of_installments' => 4, // Initial deposit + 3 monthly tranches
                'notes'                  => 'Closed deal with installment structure (Initial deposit + 3 tranches).',
                'status'                 => 'active',
                'created_at'             => $closedAt,
                'updated_at'             => now(),
            ]);

            // C. Create Payment Milestones matching the exact timeline history
            // Milestone 1: Initial Deposit (₦5,000,000 paid)
            PaymentMilestone::create([
                'payment_plan_id' => $plan->id,
                'label'           => 'Initial Commitment Deposit',
                'amount_due'      => 5000000.00,
                'amount_paid'     => 5000000.00,
                'due_date'        => '2026-08-24',
                'paid_at'         => '2026-08-24 07:36:00',
                'bank_reference'  => 'UBA-EZTT1785762429624960',
                'status'          => 'paid',
                'notes'           => 'Initial commitment deposit recorded on sale closing via Bank Transfer.',
                'verified_at'     => '2026-08-24 07:36:00',
                'verified_by'     => $salesOfficerId,
                'created_at'      => $closedAt,
                'updated_at'      => now(),
            ]);

            // Milestone 2: Installment Tranche #1 of 3 (₦3,500,000 paid of ₦4,833,333.33)
            PaymentMilestone::create([
                'payment_plan_id' => $plan->id,
                'label'           => 'Installment Tranche #1 of 3',
                'amount_due'      => 4833333.33,
                'amount_paid'     => 3500000.00,
                'due_date'        => '2026-09-24',
                'paid_at'         => '2026-08-27 15:13:00',
                'bank_reference'  => 'FCMB - UBA',
                'status'          => 'partial',
                'notes'           => 'Installment payments: ₦2,000,000.00 verified on Aug 24 + ₦1,500,000.00 on Aug 27.',
                'verified_at'     => '2026-08-24 08:04:00',
                'verified_by'     => $salesOfficerId,
                'created_at'      => $closedAt,
                'updated_at'      => now(),
            ]);

            // Milestone 3: Installment Tranche #2 of 3 (₦2,500,000 paid of ₦4,833,333.33)
            PaymentMilestone::create([
                'payment_plan_id' => $plan->id,
                'label'           => 'Installment Tranche #2 of 3',
                'amount_due'      => 4833333.33,
                'amount_paid'     => 2500000.00,
                'due_date'        => '2026-10-24',
                'paid_at'         => '2026-09-14 17:10:00',
                'bank_reference'  => 'ST-20260909141128508130-3',
                'status'          => 'partial',
                'notes'           => 'Installment payments: ₦2,000,000.00 verified on Sep 09 + ₦500,000.00 on Sep 14.',
                'verified_at'     => '2026-09-09 14:27:00',
                'verified_by'     => $salesOfficerId,
                'created_at'      => $closedAt,
                'updated_at'      => now(),
            ]);

            // Milestone 4: Installment Tranche #3 of 3 (₦0 paid of ₦4,833,333.34)
            PaymentMilestone::create([
                'payment_plan_id' => $plan->id,
                'label'           => 'Installment Tranche #3 of 3',
                'amount_due'      => 4833333.34,
                'amount_paid'     => 0.00,
                'due_date'        => '2026-11-24',
                'paid_at'         => null,
                'bank_reference'  => null,
                'status'          => 'pending',
                'notes'           => 'Final balance installment tranche.',
                'created_at'      => $closedAt,
                'updated_at'      => now(),
            ]);

            // Ensure lead status is Closed Won
            $lead->status = 'Closed Won';
            $lead->save();
        });

        $this->info("  ✅ Successfully restored Sale (₦19.5M), Payment Plan, and 4 Milestones (₦11M paid, ₦8.5M balance) for {$lead->full_name}.");
        return true;
    }
}
