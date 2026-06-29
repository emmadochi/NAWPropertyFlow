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
        Schema::create('property_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('unit_number');                  // e.g. "A01", "Block B Unit 3"
            $table->string('unit_type')->nullable();        // Studio, 1BR, 2BR, Terrace, etc.
            $table->integer('floor_number')->nullable();
            $table->decimal('size_sqm', 10, 2)->nullable(); // Square metres
            $table->decimal('price', 15, 2);
            $table->decimal('service_charge', 15, 2)->nullable();
            $table->enum('status', ['available', 'reserved', 'sold', 'unavailable'])->default('available');
            $table->text('description')->nullable();
            $table->json('features')->nullable();           // e.g. ["ensuite","balcony"]
            $table->json('images')->nullable();
            // Reservation tracking
            $table->foreignId('reserved_by_lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('reservation_expires_at')->nullable();
            $table->text('reservation_notes')->nullable();
            $table->timestamps();

            $table->unique(['property_id', 'unit_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_units');
    }
};
