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
        // 1. Roles table
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // e.g. "Chief Legal Officer", "Sales Executive"
                $table->string('slug')->unique(); // e.g. "chief_legal_officer", "sales_executive"
                $table->text('description')->nullable();
                $table->boolean('is_system')->default(false); // Super admin & base roles cannot be deleted
                $table->timestamps();
            });
        }

        // 2. Permissions table
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('module'); // "Sales & Leads", "Properties", "Finance", "HR", "Marketing", "System"
                $table->string('name'); // e.g. "Verify Client Payments"
                $table->string('slug')->unique(); // e.g. "finance.verify_payments"
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        // 3. Role-Permission pivot table
        if (!Schema::hasTable('role_permission')) {
            Schema::create('role_permission', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->unique(['role_id', 'permission_id']);
                $table->timestamps();
            });
        }

        // 4. Add role_id to users table safely
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            });
        }
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
