<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_milestones')) {
            Schema::table('payment_milestones', function (Blueprint $table) {
                if (!Schema::hasColumn('payment_milestones', 'verified_at')) {
                    $table->dateTime('verified_at')->nullable()->after('receipt_path');
                }
                if (!Schema::hasColumn('payment_milestones', 'verified_by')) {
                    $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_milestones')) {
            Schema::table('payment_milestones', function (Blueprint $table) {
                if (Schema::hasColumn('payment_milestones', 'verified_by')) {
                    $table->dropForeign(['verified_by']);
                    $table->dropColumn('verified_by');
                }
                if (Schema::hasColumn('payment_milestones', 'verified_at')) {
                    $table->dropColumn('verified_at');
                }
            });
        }
    }
};
