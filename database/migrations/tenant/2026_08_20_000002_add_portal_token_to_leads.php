<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (!Schema::hasColumn('leads', 'portal_token')) {
                    $table->string('portal_token', 64)->nullable()->unique()->after('status');
                }
                if (!Schema::hasColumn('leads', 'portal_token_created_at')) {
                    $table->dateTime('portal_token_created_at')->nullable()->after('portal_token');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (Schema::hasColumn('leads', 'portal_token_created_at')) {
                    $table->dropColumn('portal_token_created_at');
                }
                if (Schema::hasColumn('leads', 'portal_token')) {
                    $table->dropColumn('portal_token');
                }
            });
        }
    }
};
