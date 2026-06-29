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
        Schema::create('payment_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_plan_id')->constrained('payment_plans')->onDelete('cascade');
            $table->string('label');
            $table->decimal('amount_due', 15, 2);
            $table->date('due_date');
            $table->decimal('amount_paid', 15, 2)->default(0.00);
            $table->dateTime('paid_at')->nullable();
            $table->string('bank_reference')->nullable();
            $table->string('receipt_path')->nullable();
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_milestones');
    }
};
