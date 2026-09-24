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
        if (!Schema::hasTable('sales_weekly_field_logs')) {
            Schema::create('sales_weekly_field_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedSmallInteger('year');
                $table->unsignedTinyInteger('month'); // 1 - 12
                $table->unsignedTinyInteger('week_number'); // 1 - 5
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                
                // Outbound canvassing & territory coverage
                $table->text('canvassing_locations')->nullable();
                $table->unsignedInteger('office_visits_count')->default(0);

                // Pipeline projections & phone contacts
                $table->unsignedInteger('expected_payments_count')->default(0);
                $table->text('expected_payments_notes')->nullable();

                // Self-reflection & Manager review
                $table->text('observations_recommendations')->nullable();
                $table->text('manager_feedback')->nullable();

                $table->timestamps();

                $table->unique(['user_id', 'year', 'month', 'week_number'], 'unique_user_week_log');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_weekly_field_logs');
    }
};
