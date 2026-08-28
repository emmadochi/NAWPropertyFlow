<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_plans', 'vat_rate_pct')) {
                $table->decimal('vat_rate_pct', 5, 2)->default(0.00)->after('interest_amount');
            }
            if (!Schema::hasColumn('payment_plans', 'vat_amount')) {
                $table->decimal('vat_amount', 15, 2)->default(0.00)->after('vat_rate_pct');
            }
            if (!Schema::hasColumn('payment_plans', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0.00)->after('vat_amount');
            }
        });

        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'base_deal_value')) {
                $table->decimal('base_deal_value', 15, 2)->nullable()->after('deal_value');
            }
            if (!Schema::hasColumn('sales', 'interest_amount')) {
                $table->decimal('interest_amount', 15, 2)->default(0.00)->after('base_deal_value');
            }
            if (!Schema::hasColumn('sales', 'vat_amount')) {
                $table->decimal('vat_amount', 15, 2)->default(0.00)->after('interest_amount');
            }
            if (!Schema::hasColumn('sales', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0.00)->after('vat_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_plans', function (Blueprint $table) {
            if (Schema::hasColumn('payment_plans', 'vat_rate_pct')) {
                $table->dropColumn(['vat_rate_pct', 'vat_amount', 'tax_amount']);
            }
        });

        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'base_deal_value')) {
                $table->dropColumn(['base_deal_value', 'interest_amount', 'vat_amount', 'tax_amount']);
            }
        });
    }
};
