<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_inventory_settings', function (Blueprint $table) {
            $table->id();
            // In a multi-tenant setup this is implicit, but we keep a company_id for clarity
            $table->unsignedBigInteger('company_id')->default(1);

            // PO Approval tier thresholds (in base currency units, e.g. Naira)
            $table->decimal('po_tier1_max', 15, 2)->default(500000.00)->comment('Max amount PM can approve');
            $table->decimal('po_tier2_max', 15, 2)->default(5000000.00)->comment('Max amount MD can approve; above goes to board');

            // GRN & geofencing
            $table->boolean('grn_geofence_strict')->default(true)->comment('Reject GRNs submitted outside site geofence');
            $table->time('after_hours_start')->default('18:00:00')->comment('Deliveries after this time are flagged');
            $table->time('after_hours_end')->default('07:00:00')->comment('Deliveries before this time are flagged');

            // Anomaly thresholds
            $table->decimal('waste_alert_multiplier', 5, 2)->default(1.5)->comment('Flag waste if X times above rolling average');
            $table->integer('cement_shelf_life_days')->default(90)->comment('Days before cement batch triggers expiry alert');
            $table->integer('perfect_match_consecutive_limit')->default(3)->comment('Flag if GRN matches PO exactly this many times in a row');
            $table->integer('staff_pairing_occurrences_limit')->default(5)->comment('Flag supplier-storekeeper pairing after N occurrences in 30 days');

            // Price variance
            $table->decimal('price_variance_alert_pct', 5, 2)->default(10.00)->comment('Alert if purchase price exceeds benchmark by this %');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_inventory_settings');
    }
};
