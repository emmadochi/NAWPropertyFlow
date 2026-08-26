<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_issue_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('ref_number', 50)->unique()->comment('e.g. MIV-2026-0001');
            $table->foreignId('site_id')->constrained('inventory_sites')->cascadeOnDelete();
            $table->foreignId('issued_by_user_id')->constrained('users')->cascadeOnDelete()->comment('Store Keeper');
            $table->foreignId('received_by_user_id')->constrained('users')->cascadeOnDelete()->comment('Foreman or Site Engineer');
            $table->string('activity_name')->comment('Work activity consuming the material');
            $table->decimal('work_quantity', 12, 3)->nullable()->comment('Volume of work to be performed');
            $table->string('work_unit', 30)->nullable()->comment('Unit e.g. m3, m2, linear metres');
            $table->json('bom_expected_quantities')->nullable();
            $table->enum('status', ['pending', 'issued', 'returned', 'disputed'])->default('issued');
            $table->longText('foreman_signature_data')->nullable()->comment('Digital signature data / canvas URL');
            $table->longText('storekeeper_signature_data')->nullable();
            $table->timestamp('issued_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('miv_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('miv_id')->constrained('material_issue_vouchers')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('material_catalogue')->cascadeOnDelete();
            $table->foreignId('stock_batch_id')->nullable()->constrained('stock_batches')->nullOnDelete();
            $table->decimal('qty_requested', 14, 3);
            $table->decimal('qty_issued', 14, 3);
            $table->decimal('qty_returned', 14, 3)->default(0);
            $table->decimal('consumption_rate_variance_pct', 6, 2)->default(0);
            $table->boolean('variance_flagged')->default(false);
            $table->timestamps();
        });

        Schema::create('waste_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('inventory_sites')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('material_catalogue')->cascadeOnDelete();
            $table->foreignId('miv_id')->nullable()->constrained('material_issue_vouchers')->nullOnDelete();
            $table->decimal('qty', 14, 3);
            $table->enum('waste_type', ['avoidable', 'unavoidable', 'loss', 'theft_suspected'])->default('unavoidable');
            $table->string('activity_name')->nullable();
            $table->string('responsible_team')->nullable()->comment('Foreman / Mason / Subcontractor unit');
            $table->text('description');
            $table->json('photo_paths')->nullable();
            $table->string('weather_condition')->nullable();
            $table->boolean('insurance_claim_raised')->default(false);
            $table->foreignId('logged_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_logs');
        Schema::dropIfExists('miv_items');
        Schema::dropIfExists('material_issue_vouchers');
    }
};
