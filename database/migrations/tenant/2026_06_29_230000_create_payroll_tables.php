<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Staff Salary & Compensation Structures
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->decimal('housing_allowance', 15, 2)->default(0);
            $table->decimal('transport_allowance', 15, 2)->default(0);
            $table->decimal('other_allowances', 15, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0); // PAYE rate %
            $table->decimal('pension_percent', 5, 2)->default(0); // Pension rate %
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->timestamps();
        });

        // 2. Monthly Payroll Batches
        Schema::create('payroll_batches', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g. August 2026 Payroll
            $table->integer('month'); // 1 - 12
            $table->integer('year');  // e.g. 2026
            $table->decimal('total_base', 15, 2)->default(0);
            $table->decimal('total_allowances', 15, 2)->default(0);
            $table->decimal('total_commissions', 15, 2)->default(0);
            $table->decimal('total_gross', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('total_net', 15, 2)->default(0);
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['month', 'year']);
        });

        // 3. Individual Staff Payslips
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_batch_id')->constrained('payroll_batches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            // Earnings breakdown
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->decimal('housing_allowance', 15, 2)->default(0);
            $table->decimal('transport_allowance', 15, 2)->default(0);
            $table->decimal('other_allowances', 15, 2)->default(0);
            $table->decimal('total_allowances', 15, 2)->default(0);
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->decimal('gross_pay', 15, 2)->default(0);

            // Deductions breakdown
            $table->decimal('tax_deduction', 15, 2)->default(0);
            $table->decimal('pension_deduction', 15, 2)->default(0);
            $table->decimal('loan_deduction', 15, 2)->default(0);
            $table->decimal('other_deductions', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);

            // Final take-home
            $table->decimal('net_pay', 15, 2)->default(0);

            // Banking snapshot
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();

            $table->enum('status', ['pending', 'approved', 'paid'])->default('pending');
            $table->timestamps();
            $table->unique(['payroll_batch_id', 'user_id']);
        });

        // 4. Itemized Payroll Deductions (Statutory, Loans, Fines, Advances)
        Schema::create('payroll_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payslip_id')->nullable()->constrained('payslips')->cascadeOnDelete();
            $table->enum('deduction_type', ['tax', 'pension', 'loan_repayment', 'fine', 'other'])->default('other');
            $table->string('title'); // e.g. "Car Loan Deductions", "Late Attendance Penalty"
            $table->decimal('amount', 15, 2);
            $table->integer('month');
            $table->integer('year');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_deductions');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_batches');
        Schema::dropIfExists('salary_structures');
    }
};
