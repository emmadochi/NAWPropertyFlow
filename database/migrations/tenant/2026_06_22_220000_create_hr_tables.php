<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sales Targets per user per month
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('target_month'); // 1-12
            $table->integer('target_year');
            $table->integer('leads_target')->default(0);
            $table->integer('sales_target')->default(0);
            $table->decimal('revenue_target', 15, 2)->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'target_month', 'target_year']);
        });

        // Leave Requests
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('leave_type'); // annual, sick, unpaid, maternity, paternity, compassionate
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days_requested');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('attachment_path')->nullable(); // for medical certs etc.
            $table->timestamps();
        });

        // Staff Certifications / Training Records
        Schema::create('staff_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('issuing_body')->nullable();
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('certificate_number')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Disciplinary Records
        Schema::create('disciplinary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            $table->string('incident_type'); // warning, suspension, termination, query
            $table->date('incident_date');
            $table->text('description');
            $table->text('action_taken');
            $table->string('status')->default('open'); // open, resolved, appealed
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });

        // Performance Reviews (quarterly / annually)
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewed_by')->constrained('users')->cascadeOnDelete();
            $table->string('review_period'); // e.g. Q1-2025, Annual-2025
            $table->integer('score')->nullable(); // 0-100
            $table->string('rating')->nullable(); // excellent, good, average, poor
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('goals_next_period')->nullable();
            $table->text('manager_comments')->nullable();
            $table->text('employee_comments')->nullable();
            $table->string('status')->default('draft'); // draft, submitted, acknowledged
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('disciplinary_records');
        Schema::dropIfExists('staff_certifications');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('sales_targets');
    }
};
