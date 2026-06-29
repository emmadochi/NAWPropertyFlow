<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Off-plan & project link
            $table->foreignId('project_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->boolean('is_off_plan')->default(false)->after('project_id');
            // GPS coordinates
            $table->decimal('latitude', 10, 8)->nullable()->after('location');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            // Enhanced property details
            $table->string('landmark')->nullable()->after('longitude'); // Nearest landmark
            $table->json('amenities')->nullable()->after('images');     // Shared amenities
            $table->string('completion_status')->nullable()->after('amenities'); // Ready, Q3 2025, etc.
            $table->integer('total_units')->nullable()->after('available_units');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn(['project_id', 'is_off_plan', 'latitude', 'longitude',
                                'landmark', 'amenities', 'completion_status', 'total_units']);
        });
    }
};
