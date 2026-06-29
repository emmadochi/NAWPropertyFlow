<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_targets', function (Blueprint $table) {
            $table->id();
            $table->string('department');
            $table->integer('target_month');
            $table->integer('target_year');
            $table->string('metric');
            $table->decimal('target_value', 15, 2);
            $table->timestamps();

            $table->unique(['department', 'target_month', 'target_year', 'metric'], 'dept_target_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_targets');
    }
};
