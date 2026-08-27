<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('payment_plan_durations')) {
            Schema::create('payment_plan_durations', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g. "6 Months Spread", "9 Months Milestone", "12 Months Flexi"
                $table->integer('duration_months')->default(0)->comment('Duration in months (0 = outright)');
                $table->decimal('interest_rate_pct', 5, 2)->default(0.00)->comment('Interest rate percentage e.g. 5.00%');
                $table->decimal('initial_deposit_pct', 5, 2)->default(30.00)->comment('Default deposit percentage e.g. 30.00%');
                $table->integer('number_of_installments')->default(1);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });

            // Seed default real estate industry payment plan durations
            DB::table('payment_plan_durations')->insert([
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
                    'name' => '24 Months Diaspora Long-Term Plan',
                    'duration_months' => 24,
                    'interest_rate_pct' => 20.00,
                    'initial_deposit_pct' => 15.00,
                    'number_of_installments' => 24,
                    'description' => '24-month structured spread designed for diaspora buyers with a 20% interest structure.',
                    'is_active' => true,
                    'sort_order' => 7,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // Add interest tracking fields to payment_plans table if missing
        Schema::table('payment_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_plans', 'payment_plan_duration_id')) {
                $table->unsignedBigInteger('payment_plan_duration_id')->nullable()->after('sale_id');
            }
            if (!Schema::hasColumn('payment_plans', 'duration_months')) {
                $table->integer('duration_months')->nullable()->after('plan_type');
            }
            if (!Schema::hasColumn('payment_plans', 'base_deal_value')) {
                $table->decimal('base_deal_value', 15, 2)->nullable()->after('total_amount');
            }
            if (!Schema::hasColumn('payment_plans', 'interest_rate_pct')) {
                $table->decimal('interest_rate_pct', 5, 2)->default(0.00)->after('base_deal_value');
            }
            if (!Schema::hasColumn('payment_plans', 'interest_amount')) {
                $table->decimal('interest_amount', 15, 2)->default(0.00)->after('interest_rate_pct');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_plans', function (Blueprint $table) {
            if (Schema::hasColumn('payment_plans', 'payment_plan_duration_id')) {
                $table->dropColumn('payment_plan_duration_id');
            }
            if (Schema::hasColumn('payment_plans', 'duration_months')) {
                $table->dropColumn('duration_months');
            }
            if (Schema::hasColumn('payment_plans', 'base_deal_value')) {
                $table->dropColumn('base_deal_value');
            }
            if (Schema::hasColumn('payment_plans', 'interest_rate_pct')) {
                $table->dropColumn('interest_rate_pct');
            }
            if (Schema::hasColumn('payment_plans', 'interest_amount')) {
                $table->dropColumn('interest_amount');
            }
        });

        Schema::dropIfExists('payment_plan_durations');
    }
};
