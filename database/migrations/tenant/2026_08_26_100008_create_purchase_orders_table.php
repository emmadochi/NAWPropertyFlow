<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('ref_number', 50)->unique()->comment('e.g. PO-2026-0001');
            $table->foreignId('requisition_id')->nullable()->constrained('material_requisitions')->nullOnDelete();
            $table->foreignId('site_id')->constrained('inventory_sites')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', [
                'draft', 'pending_t1', 'pending_t2', 'pending_t3', 
                'approved', 'rejected', 'partially_delivered', 'delivered', 'cancelled', 'closed'
            ])->default('draft');
            $table->decimal('subtotal_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('delivery_fee', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('approval_tier', ['tier1', 'tier2', 'tier3'])->default('tier1');
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->foreign('approved_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->date('expiry_date')->nullable()->comment('Auto-expires if undelivered past validity');
            $table->text('terms_and_conditions')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('material_catalogue')->cascadeOnDelete();
            $table->decimal('qty_ordered', 14, 3);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->decimal('qty_delivered_cumulative', 14, 3)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
