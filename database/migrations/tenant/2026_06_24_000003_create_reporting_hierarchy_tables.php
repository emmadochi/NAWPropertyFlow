<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add hod_id to departments table
        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('hod_id')->nullable()->constrained('users')->onDelete('set null');
        });

        // 2. Create staff_metric_submissions table
        Schema::create('staff_metric_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_metric_id')->constrained()->onDelete('cascade');
            $table->decimal('value', 15, 2);
            $table->integer('submission_month');
            $table->integer('submission_year');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_metric_submissions');

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['hod_id']);
            $table->dropColumn('hod_id');
        });
    }
};
