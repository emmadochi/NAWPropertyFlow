<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('goods_received_note_id')->nullable()->constrained('goods_received_notes')->nullOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('invoice_number', 100);
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal_amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->enum('payment_status', ['unmatched', 'matched', 'disputed', 'approved_for_payment', 'paid'])
                  ->default('unmatched');
            $table->unsignedBigInteger('matched_by_user_id')->nullable();
            $table->foreign('matched_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('matched_at')->nullable();
            $table->unsignedBigInteger('payment_approved_by_user_id')->nullable();
            $table->foreign('payment_approved_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('payment_approved_at')->nullable();
            $table->string('invoice_file_path')->nullable();
            $table->text('discrepancy_notes')->nullable();
            $table->timestamps();

            $table->unique(['supplier_id', 'invoice_number']);
        });

        Schema::create('price_benchmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('material_catalogue')->cascadeOnDelete();
            $table->enum('city', ['lagos', 'abuja', 'port_harcourt', 'ibadan', 'kano', 'enugu', 'other'])->default('lagos');
            $table->string('city_name_custom', 100)->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->date('recorded_date');
            $table->unsignedBigInteger('entered_by_user_id')->nullable();
            $table->foreign('entered_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('source_market_name')->nullable()->comment('e.g. Alaba Rago, Dei-Dei, Mile 2');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['material_id', 'city', 'recorded_date']);
        });

        Schema::create('inventory_anomaly_flags', function (Blueprint $table) {
            $table->id();
            $table->enum('flag_type', [
                'ghost_delivery',
                'perfect_match',
                'after_hours',
                'staff_pairing',
                'progress_gap',
                'price_spike',
                'waste_spike'
            ]);
            $table->string('flaggable_type')->nullable();
            $table->unsignedBigInteger('flaggable_id')->nullable();
            $table->foreignId('site_id')->nullable()->constrained('inventory_sites')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'under_review', 'resolved', 'dismissed'])->default('open');
            $table->unsignedBigInteger('resolved_by_user_id')->nullable();
            $table->foreign('resolved_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['flaggable_type', 'flaggable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_anomaly_flags');
        Schema::dropIfExists('price_benchmarks');
        Schema::dropIfExists('supplier_invoices');
    }
};
