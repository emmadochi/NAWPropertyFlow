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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Staff / Officer who logged it
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); // Accountant / Admin who approved
            $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete(); // Project/Estate linked
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete(); // Branch linked
            
            $table->string('title'); // e.g. "Site Diesel for Hutu Prestige Generator", "FCDA Survey Filing Fee"
            $table->string('category'); // Site Operations, Marketing & Media, Legal & Title, Office OPEX, Logistics & Inspection, Construction
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->string('payment_method')->default('Bank Transfer'); // Bank Transfer, Cash, Card, Cheque
            $table->string('receipt_file')->nullable(); // Uploaded invoice / receipt screenshot or PDF
            $table->string('vendor_name')->nullable(); // e.g. "TotalEnergies Fuel", "Abuja Legal Associates"
            $table->string('reference_number')->nullable(); // e.g. "EXP-2026-008" or Bank Ref
            $table->text('notes')->nullable();
            
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
