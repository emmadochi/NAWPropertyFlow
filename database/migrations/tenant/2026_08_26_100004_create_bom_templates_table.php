<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete()
                  ->comment('NULL means global/company-wide BOM template');
            $table->foreignId('material_id')->constrained('material_catalogue')->cascadeOnDelete();
            $table->string('activity_name')->comment('e.g. Column Concreting, Block Work, Floor Tiling');
            $table->decimal('qty_per_unit', 12, 4)->comment('Standard quantity of this material per unit of work');
            $table->string('unit_of_work', 30)->comment('e.g. m³, m², LM, each, floor');
            $table->decimal('allowable_variance_pct', 5, 2)->default(10.00)
                  ->comment('% above standard before system flags over-consumption');
            $table->unsignedBigInteger('set_by_user_id')->nullable()
                  ->comment('QS or Admin who set this rate');
            $table->foreign('set_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'material_id', 'activity_name'], 'bom_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_templates');
    }
};
