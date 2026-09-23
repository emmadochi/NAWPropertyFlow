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
                if (!Schema::hasColumn('leads', 'lead_temperature')) {
                    $table->enum('lead_temperature', ['hot', 'warm', 'cold'])
                          ->nullable()
                          ->after('status')
                          ->comment('Manual override for lead temperature. Auto-computed if null.');
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
                if (Schema::hasColumn('leads', 'lead_temperature')) {
                    $table->dropColumn('lead_temperature');
                }
            });
        }
    }
};
