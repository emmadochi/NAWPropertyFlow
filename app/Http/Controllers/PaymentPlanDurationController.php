<?php

namespace App\Http\Controllers;

use App\Models\PaymentPlanDuration;
use Illuminate\Http\Request;

class PaymentPlanDurationController extends Controller
{
    /**
     * Display listing of payment plan durations and interest settings.
     */
    public function index()
    {
        $this->ensureTableExists();

        $durations = PaymentPlanDuration::orderBy('sort_order', 'asc')
            ->orderBy('duration_months', 'asc')
            ->get();

        return view('settings.payment-plans.index', compact('durations'));
    }

    /**
     * Self-healing schema check to prevent 500 error if migration has not run yet on server.
     */
    private function ensureTableExists(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('payment_plan_durations')) {
            try {
                \Illuminate\Support\Facades\Schema::create('payment_plan_durations', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->integer('duration_months')->default(0);
                    $table->decimal('interest_rate_pct', 5, 2)->default(0.00);
                    $table->decimal('initial_deposit_pct', 5, 2)->default(30.00);
                    $table->integer('number_of_installments')->default(1);
                    $table->text('description')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->integer('sort_order')->default(0);
                    $table->timestamps();
                });

                \Illuminate\Support\Facades\DB::table('payment_plan_durations')->insert([
                    [
                        'name' => 'Outright Purchase (30 Days)',
                        'duration_months' => 0,
                        'interest_rate_pct' => 0.00,
                        'initial_deposit_pct' => 100.00,
                        'number_of_installments' => 1,
                        'description' => '100% full upfront purchase payment within 30 days. No interest surcharge.',
                        'is_active' => true,
                        'sort_order' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => '3 Months Fast-Track Spread',
                        'duration_months' => 3,
                        'interest_rate_pct' => 0.00,
                        'initial_deposit_pct' => 40.00,
                        'number_of_installments' => 3,
                        'description' => '40% initial commitment deposit with remaining balance spread over 90 days (Interest-free).',
                        'is_active' => true,
                        'sort_order' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => '6 Months Standard Spread',
                        'duration_months' => 6,
                        'interest_rate_pct' => 5.00,
                        'initial_deposit_pct' => 30.00,
                        'number_of_installments' => 6,
                        'description' => '30% initial deposit followed by 5 equal monthly milestones with a 5% structuring interest fee.',
                        'is_active' => true,
                        'sort_order' => 3,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => '9 Months Construction Milestone Plan',
                        'duration_months' => 9,
                        'interest_rate_pct' => 7.50,
                        'initial_deposit_pct' => 25.00,
                        'number_of_installments' => 9,
                        'description' => '25% foundation deposit with milestone tranches linked to building progress across 9 months (7.5% interest).',
                        'is_active' => true,
                        'sort_order' => 4,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => '12 Months Extended Flexi',
                        'duration_months' => 12,
                        'interest_rate_pct' => 10.00,
                        'initial_deposit_pct' => 20.00,
                        'number_of_installments' => 12,
                        'description' => '20% commitment deposit with 11 equal monthly installment tranches (10% annual interest).',
                        'is_active' => true,
                        'sort_order' => 5,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => '18 Months Executive Plan',
                        'duration_months' => 18,
                        'interest_rate_pct' => 15.00,
                        'initial_deposit_pct' => 20.00,
                        'number_of_installments' => 18,
                        'description' => '18-month structured installment plan with 15% interest surcharge for off-plan residential developments.',
                        'is_active' => true,
                        'sort_order' => 6,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => '24 Months Long-Term Development Plan',
                        'duration_months' => 24,
                        'interest_rate_pct' => 20.00,
                        'initial_deposit_pct' => 15.00,
                        'number_of_installments' => 24,
                        'description' => '24-month structured flexi milestone plan with 20% interest surcharge for luxury off-plan projects.',
                        'is_active' => true,
                        'sort_order' => 7,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('payment_plans')) {
            try {
                \Illuminate\Support\Facades\Schema::table('payment_plans', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('payment_plans', 'payment_plan_duration_id')) {
                        $table->unsignedBigInteger('payment_plan_duration_id')->nullable()->after('sale_id');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('payment_plans', 'duration_months')) {
                        $table->integer('duration_months')->default(0)->after('payment_plan_duration_id');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('payment_plans', 'base_deal_value')) {
                        $table->decimal('base_deal_value', 15, 2)->nullable()->after('duration_months');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('payment_plans', 'interest_rate_pct')) {
                        $table->decimal('interest_rate_pct', 5, 2)->default(0.00)->after('base_deal_value');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('payment_plans', 'interest_amount')) {
                        $table->decimal('interest_amount', 15, 2)->default(0.00)->after('interest_rate_pct');
                    }
                });
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }

    /**
     * Store a newly created payment plan duration.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration_months' => 'required|integer|min:0|max:120',
            'interest_rate_pct' => 'required|numeric|min:0|max:100',
            'initial_deposit_pct' => 'required|numeric|min:0|max:100',
            'number_of_installments' => 'required|integer|min:1|max:120',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? (PaymentPlanDuration::max('sort_order') + 1);

        PaymentPlanDuration::create($validated);

        return redirect()->route('settings.payment-plans.index')
            ->with('success', 'Payment plan duration & interest rate created successfully.');
    }

    /**
     * Update the specified payment plan duration.
     */
    public function update(Request $request, PaymentPlanDuration $paymentPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration_months' => 'required|integer|min:0|max:120',
            'interest_rate_pct' => 'required|numeric|min:0|max:100',
            'initial_deposit_pct' => 'required|numeric|min:0|max:100',
            'number_of_installments' => 'required|integer|min:1|max:120',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $paymentPlan->update($validated);

        return redirect()->route('settings.payment-plans.index')
            ->with('success', 'Payment plan duration updated successfully.');
    }

    /**
     * Toggle active status.
     */
    public function toggle(PaymentPlanDuration $paymentPlan)
    {
        $paymentPlan->update([
            'is_active' => !$paymentPlan->is_active
        ]);

        $status = $paymentPlan->is_active ? 'activated' : 'deactivated';
        return redirect()->route('settings.payment-plans.index')
            ->with('success', "Payment plan {$paymentPlan->name} {$status}.");
    }

    /**
     * Remove the specified payment plan duration.
     */
    public function destroy(PaymentPlanDuration $paymentPlan)
    {
        $paymentPlan->delete();

        return redirect()->route('settings.payment-plans.index')
            ->with('success', 'Payment plan duration deleted successfully.');
    }
}
