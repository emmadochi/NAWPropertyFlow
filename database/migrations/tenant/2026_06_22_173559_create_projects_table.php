<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('developer')->nullable();
            $table->string('location');
            $table->enum('type', ['residential', 'commercial', 'mixed_use'])->default('residential');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expected_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->enum('status', ['planning', 'in_progress', 'completed', 'on_hold', 'cancelled'])->default('planning');
            $table->integer('total_units')->default(0);
            $table->decimal('land_size_sqm', 12, 2)->nullable();
            $table->json('amenities')->nullable();       // Pool, Gym, Security, etc.
            $table->json('images')->nullable();
            $table->string('brochure_path')->nullable();
            $table->integer('completion_percentage')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
