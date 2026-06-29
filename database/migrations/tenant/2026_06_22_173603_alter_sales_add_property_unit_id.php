<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('property_unit_id')
                  ->nullable()
                  ->after('property_id')
                  ->constrained('property_units')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['property_unit_id']);
            $table->dropColumn('property_unit_id');
        });
    }
};
