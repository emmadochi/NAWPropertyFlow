<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('estate_name')->nullable();
            $table->string('location');
            $table->string('property_type'); // Land, Duplex, Terrace, Flat, etc.
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->integer('available_units')->default(1);
            $table->json('images')->nullable(); // JSON array of image paths
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
