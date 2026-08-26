<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('ref_number', 50)->unique()->comment('e.g. MRF-2026-0001');
            $table->foreignId('site_id')->constrained('inventory_sites')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('activity_name')->comment('Work activity targeted, e.g. Foundation Cast');
            $table->date('required_date');
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'partially_fulfilled', 'fulfilled'])
                  ->default('pending');
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->foreign('approved_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('material_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisition_id')->constrained('material_requisitions')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('material_catalogue')->cascadeOnDelete();
            $table->decimal('qty_requested', 14, 3);
            $table->decimal('qty_approved', 14, 3)->nullable();
            $table->decimal('bom_expected_qty', 14, 3)->nullable();
            $table->boolean('variance_flag')->default(false);
            $table->string('variance_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_requisition_items');
        Schema::dropIfExists('material_requisitions');
    }
};
