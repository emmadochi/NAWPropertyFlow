<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_received_notes', function (Blueprint $table) {
            $table->id();
            $table->string('ref_number', 50)->unique()->comment('e.g. GRN-2026-0001');
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('site_id')->constrained('inventory_sites')->cascadeOnDelete();
            $table->foreignId('received_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('delivery_date');
            $table->time('delivery_time');
            $table->string('waybill_number', 100)->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone', 30)->nullable();
            $table->string('vehicle_plate', 30)->nullable();
            $table->decimal('delivery_gps_lat', 10, 7)->nullable();
            $table->decimal('delivery_gps_lng', 10, 7)->nullable();
            $table->boolean('geofence_check_passed')->default(true);
            $table->json('photo_evidence_paths')->nullable()->comment('Paths to uploaded delivery proof images');
            $table->enum('status', ['draft', 'complete', 'disputed'])->default('complete');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grn_id')->constrained('goods_received_notes')->cascadeOnDelete();
            $table->foreignId('po_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();
            $table->foreignId('material_id')->constrained('material_catalogue')->cascadeOnDelete();
            $table->decimal('qty_ordered', 14, 3)->default(0);
            $table->decimal('qty_received', 14, 3);
            $table->decimal('qty_rejected', 14, 3)->default(0);
            $table->string('rejection_reason')->nullable();
            $table->string('batch_number', 100)->nullable();
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('unit_price_confirmed', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_items');
        Schema::dropIfExists('goods_received_notes');
    }
};
