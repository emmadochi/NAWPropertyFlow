<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_catalogue', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 30)->unique()->comment('Internal material code e.g. CEM-OPC-50');
            $table->enum('category', [
                'cement', 'steel', 'aggregate', 'timber', 'block',
                'finishing', 'plumbing', 'electrical', 'equipment_consumable', 'other'
            ])->default('other');
            $table->string('unit_of_measure', 30)->comment('e.g. bags, tonnes, m³, litres, pieces');
            $table->decimal('standard_unit_cost', 15, 2)->default(0)->comment('Last known unit cost for budget estimation');
            $table->decimal('reorder_level', 12, 3)->default(0)->comment('Stock level that triggers reorder alert');
            $table->decimal('safety_stock_level', 12, 3)->default(0)->comment('Minimum safety buffer — triggers critical alert');
            $table->integer('shelf_life_days')->nullable()->comment('NULL means no expiry. Used for cement batch alerts.');
            $table->boolean('is_trackable_by_batch')->default(false)->comment('Enables batch/lot number tracking on delivery');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_catalogue');
    }
};
