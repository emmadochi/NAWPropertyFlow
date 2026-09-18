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
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (!Schema::hasColumn('leads', 'unreachable_count')) {
                    $table->unsignedTinyInteger('unreachable_count')->default(0)->after('last_contact_channel');
                }
                if (!Schema::hasColumn('leads', 'is_flagged_fake')) {
                    $table->boolean('is_flagged_fake')->default(false)->after('unreachable_count');
                }
                if (!Schema::hasColumn('leads', 'flagged_reason')) {
                    $table->string('flagged_reason')->nullable()->after('is_flagged_fake');
                }
                if (!Schema::hasColumn('leads', 'flagged_at')) {
                    $table->dateTime('flagged_at')->nullable()->after('flagged_reason');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (Schema::hasColumn('leads', 'flagged_at')) {
                    $table->dropColumn('flagged_at');
                }
                if (Schema::hasColumn('leads', 'flagged_reason')) {
                    $table->dropColumn('flagged_reason');
                }
                if (Schema::hasColumn('leads', 'is_flagged_fake')) {
                    $table->dropColumn('is_flagged_fake');
                }
                if (Schema::hasColumn('leads', 'unreachable_count')) {
                    $table->dropColumn('unreachable_count');
                }
            });
        }
    }
};
