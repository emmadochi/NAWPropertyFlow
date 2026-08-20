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
                if (!Schema::hasColumn('payment_milestones', 'proof_of_payment')) {
                    $table->string('proof_of_payment')->nullable()->after('receipt_path');
                }
                if (!Schema::hasColumn('payment_milestones', 'pop_submitted_at')) {
                    $table->dateTime('pop_submitted_at')->nullable()->after('proof_of_payment');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_milestones')) {
            Schema::table('payment_milestones', function (Blueprint $table) {
                if (Schema::hasColumn('payment_milestones', 'pop_submitted_at')) {
                    $table->dropColumn('pop_submitted_at');
                }
                if (Schema::hasColumn('payment_milestones', 'proof_of_payment')) {
                    $table->dropColumn('proof_of_payment');
                }
            });
        }
    }
};
