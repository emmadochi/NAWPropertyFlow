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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('sales_officer_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('deal_value', 15, 2);
            $table->integer('units_purchased')->default(1);
            $table->string('status')->default('Closed Won'); // Payment Processing, Closed Won
            $table->string('payment_receipt')->nullable(); // File path
            $table->dateTime('deal_closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
