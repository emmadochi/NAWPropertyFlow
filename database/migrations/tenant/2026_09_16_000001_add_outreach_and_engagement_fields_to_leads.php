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
                if (!Schema::hasColumn('leads', 'outreach_location')) {
                    $table->string('outreach_location')->nullable()->after('lead_source');
                }
                if (!Schema::hasColumn('leads', 'last_contacted_at')) {
                    $table->dateTime('last_contacted_at')->nullable()->after('status');
                }
                if (!Schema::hasColumn('leads', 'last_contact_channel')) {
                    $table->string('last_contact_channel')->nullable()->after('last_contacted_at'); // call, whatsapp, sms, inspection, meeting
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
                if (Schema::hasColumn('leads', 'last_contact_channel')) {
                    $table->dropColumn('last_contact_channel');
                }
                if (Schema::hasColumn('leads', 'last_contacted_at')) {
                    $table->dropColumn('last_contacted_at');
                }
                if (Schema::hasColumn('leads', 'outreach_location')) {
                    $table->dropColumn('outreach_location');
                }
            });
        }
    }
};
