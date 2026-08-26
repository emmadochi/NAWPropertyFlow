<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 30)->unique()->comment('Internal supplier code e.g. SUP-0001');
            $table->string('contact_person')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('rc_number', 50)->nullable()->comment('CAC registration number');
            $table->string('tin', 50)->nullable()->comment('Tax Identification Number');
            $table->integer('payment_terms_days')->default(30)->comment('Standard net payment days');
            $table->decimal('performance_score', 5, 2)->default(100.00)
                  ->comment('Auto-computed score based on delivery, price & quality history');
            $table->boolean('is_blacklisted')->default(false);
            $table->text('blacklist_reason')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number', 20)->nullable();
            $table->string('bank_account_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Supplier portal user accounts (separate from main CRM users)
        Schema::create('supplier_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_users');
        Schema::dropIfExists('suppliers');
    }
};
