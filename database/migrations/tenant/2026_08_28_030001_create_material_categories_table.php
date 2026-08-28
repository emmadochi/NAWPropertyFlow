<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Modify category column in material_catalogue from enum to string
        Schema::table('material_catalogue', function (Blueprint $table) {
            $table->string('category', 100)->default('other')->change();
        });

        // Seed initial default categories
        $defaults = [
            ['name' => 'Cement & Binders', 'slug' => 'cement', 'description' => 'OPC, PPC, white cement, lime, mortar binders'],
            ['name' => 'Steel & Reinforcements', 'slug' => 'steel', 'description' => 'TMT rebar bars, BRC mesh, binding wire, structural steel'],
            ['name' => 'Aggregates & Sand', 'slug' => 'aggregate', 'description' => 'Granite, sharp sand, plaster sand, gravel, stone dust'],
            ['name' => 'Timber & Formwork', 'slug' => 'timber', 'description' => 'Hardwood, plywood, marine board, bamboo props, rafters'],
            ['name' => 'Blocks & Bricks', 'slug' => 'block', 'description' => '6-inch & 9-inch vibrated sandcrete blocks, clay bricks, paving stones'],
            ['name' => 'Finishing & Tiles', 'slug' => 'finishing', 'description' => 'Floor & wall tiles, POP cement, gypsum board, emulsion & gloss paint'],
            ['name' => 'Plumbing & Pipes', 'slug' => 'plumbing', 'description' => 'PVC pressure pipes, PPR pipes, fittings, valves, water tanks'],
            ['name' => 'Electrical & Conduits', 'slug' => 'electrical', 'description' => 'Copper cables, conduit pipes, distribution boards, lighting fixtures'],
            ['name' => 'Equipment Consumables (Diesel/Oil)', 'slug' => 'equipment_consumable', 'description' => 'Automotive gas oil (AGO/Diesel), engine oil, hydraulic fluid, lubricants'],
            ['name' => 'Other Materials', 'slug' => 'other', 'description' => 'Safety PPE, hardware, consumables and general site supplies'],
        ];

        $now = now();
        foreach ($defaults as $item) {
            DB::table('material_categories')->insertOrIgnore([
                'name' => $item['name'],
                'slug' => $item['slug'],
                'description' => $item['description'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('material_categories');
    }
};
