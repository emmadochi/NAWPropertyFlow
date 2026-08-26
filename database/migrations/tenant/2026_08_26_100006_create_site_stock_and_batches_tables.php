<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('inventory_sites')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('material_catalogue')->cascadeOnDelete();
            $table->decimal('qty_on_hand', 14, 3)->default(0);
            $table->decimal('qty_reserved', 14, 3)->default(0)->comment('Committed or in-transit stock');
            $table->decimal('qty_quarantined', 14, 3)->default(0)->comment('Failed QC or undergoing inspection');
            $table->timestamp('last_physical_count_at')->nullable();
            $table->unsignedBigInteger('last_count_by_user_id')->nullable();
            $table->foreign('last_count_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['site_id', 'material_id']);
        });

        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_stock_id')->constrained('site_stock')->cascadeOnDelete();
            $table->string('batch_number', 100);
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('qty_received', 14, 3)->default(0);
            $table->decimal('qty_remaining', 14, 3)->default(0);
            $table->unsignedBigInteger('received_on_grn_id')->nullable();
            $table->enum('qc_status', ['pending', 'pass', 'fail'])->default('pass');
            $table->text('qc_notes')->nullable();
            $table->timestamps();

            $table->index(['site_stock_id', 'batch_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
        Schema::dropIfExists('site_stock');
    }
};
