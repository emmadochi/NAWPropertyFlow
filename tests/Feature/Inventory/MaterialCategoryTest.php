<?php

namespace Tests\Feature\Inventory;

use App\Models\Inventory\MaterialCatalogue;
use App\Models\Inventory\MaterialCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_unique_slug()
    {
        $slug = MaterialCategory::generateUniqueSlug('Roofing & Waterproofing');
        $this->assertEquals('roofing_waterproofing', $slug);
    }

    public function test_can_retrieve_active_categories_list()
    {
        MaterialCategory::create([
            'name' => 'Roofing & Waterproofing',
            'slug' => 'roofing',
            'is_active' => true,
        ]);

        $list = MaterialCategory::getActiveList();
        $this->assertArrayHasKey('roofing', $list);
        $this->assertEquals('Roofing & Waterproofing', $list['roofing']);
    }

    public function test_category_relationship_with_materials()
    {
        $category = MaterialCategory::create([
            'name' => 'Cement & Binders',
            'slug' => 'cement',
            'is_active' => true,
        ]);

        $material = MaterialCatalogue::create([
            'name' => 'Elephant Cement 50kg',
            'code' => 'CEM-ELE-50',
            'category' => 'cement',
            'unit_of_measure' => 'bags',
            'standard_unit_cost' => 8200.00,
            'reorder_level' => 50,
            'safety_stock_level' => 20,
        ]);

        $this->assertTrue($category->materials->contains($material));
        $this->assertEquals($category->name, $material->categoryRef->name);
    }
}
