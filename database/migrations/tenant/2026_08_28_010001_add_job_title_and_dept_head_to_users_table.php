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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'job_title')) {
                $table->string('job_title')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'is_department_head')) {
                $table->boolean('is_department_head')->default(false)->after('job_title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'job_title')) {
                $table->dropColumn('job_title');
            }
            if (Schema::hasColumn('users', 'is_department_head')) {
                $table->dropColumn('is_department_head');
            }
        });
    }
};
