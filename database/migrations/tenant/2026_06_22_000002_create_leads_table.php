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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone_number');
            $table->string('whatsapp_number')->nullable();
            $table->string('email')->nullable();
            $table->string('budget_range')->nullable();
            $table->foreignId('property_interest_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->string('preferred_location')->nullable();
            $table->string('lead_source')->default('Direct'); // Website, Referral, Social Media, WhatsApp, etc.
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('New'); // New, Contacted, Follow Up, Inspection Scheduled, Negotiation, Payment Processing, Closed Won, Closed Lost
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
