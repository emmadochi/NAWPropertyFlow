<?php

namespace Tests\Feature\Inventory;

use App\Models\CompanySetting;
use App\Models\Inventory\BomTemplate;
use App\Models\Inventory\CompanyInventorySetting;
use App\Models\Inventory\InventorySite;
use App\Models\Inventory\MaterialCatalogue;
use App\Models\Inventory\PriceBenchmark;
use App\Models\Inventory\Supplier;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Tests\TestCase;

class InventoryPhase2Test extends TestCase
{
    protected User $admin;
    protected User $qs;
    protected User $procurement;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
            '--realpath' => false,
        ]);

        $this->seed(PermissionSeeder::class);

        CompanySetting::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'RICAF Properties & Construction',
                'package_tier' => 'enterprise',
            ]
        );

        $adminRole = Role::where('slug', 'company_admin')->first();
        $this->admin = User::factory()->create([
            'role' => 'company_admin',
            'role_id' => $adminRole->id,
        ]);

        $qsRole = Role::where('slug', 'quantity_surveyor')->first();
        $this->qs = User::factory()->create([
            'role' => 'quantity_surveyor',
            'role_id' => $qsRole->id,
        ]);

        $procRole = Role::where('slug', 'procurement_officer')->first();
        $this->procurement = User::factory()->create([
            'role' => 'procurement_officer',
            'role_id' => $procRole->id,
        ]);

        $this->project = Project::create([
            'name' => 'Lekki Pearl Residence',
            'location' => 'Lekki Phase 1, Lagos',
            'type' => 'residential',
            'status' => 'in_progress',
        ]);
    }

    public function test_admin_can_create_and_view_inventory_site()
    {
        $response = $this->actingAs($this->admin)->post(route('inventory.sites.store'), [
            'project_id' => $this->project->id,
            'name' => 'Lekki Main Stock Yard',
            'code' => 'LPR-SITE-01',
            'address' => 'Plot 4, Admiralty Way, Lekki',
            'gps_lat' => 6.4474000,
            'gps_lng' => 3.4849000,
            'geofence_radius_meters' => 250,
            'is_active' => '1',
        ]);

        $site = InventorySite::where('code', 'LPR-SITE-01')->first();
        $this->assertNotNull($site);
        $response->assertRedirect(route('inventory.sites.show', $site));

        $viewResponse = $this->actingAs($this->admin)->get(route('inventory.sites.show', $site));
        $viewResponse->assertOk()
            ->assertSee('Lekki Main Stock Yard')
            ->assertSee('LPR-SITE-01');
    }

    public function test_catalogue_management_and_api_search()
    {
        $response = $this->actingAs($this->procurement)->post(route('inventory.catalogue.store'), [
            'name' => 'Dangote 3X Falcon Cement 50kg',
            'code' => 'CEM-DNG-50',
            'category' => 'cement',
            'unit_of_measure' => 'bags',
            'standard_unit_cost' => 8700.00,
            'reorder_level' => 150,
            'safety_stock_level' => 40,
            'shelf_life_days' => 90,
            'is_trackable_by_batch' => '1',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('inventory.catalogue.index'));
        $this->assertDatabaseHas('material_catalogue', ['code' => 'CEM-DNG-50']);

        // Test API live lookup
        $apiResponse = $this->actingAs($this->procurement)->getJson(route('inventory.catalogue.api.search', ['q' => 'Falcon']));
        $apiResponse->assertOk()
            ->assertJsonFragment(['code' => 'CEM-DNG-50']);
    }

    public function test_qs_can_define_bom_and_use_suggest_qty_calculator()
    {
        $cement = MaterialCatalogue::create([
            'name' => 'Elephant Supaset Cement 50kg',
            'code' => 'CEM-ELP-50',
            'category' => 'cement',
            'unit_of_measure' => 'bags',
            'standard_unit_cost' => 8500.00,
            'reorder_level' => 100,
            'safety_stock_level' => 20,
        ]);

        $response = $this->actingAs($this->qs)->post(route('inventory.bom.store'), [
            'material_id' => $cement->id,
            'activity_name' => '1:2:4 Grade 25 Concrete Pour',
            'qty_per_unit' => 6.2000,
            'unit_of_work' => 'm3',
            'allowable_variance_pct' => 10.0,
            'project_id' => null, // Global
        ]);

        $response->assertRedirect(route('inventory.bom.index'));
        $this->assertDatabaseHas('bom_templates', ['activity_name' => '1:2:4 Grade 25 Concrete Pour']);

        // Test suggest-qty endpoint for a 50m³ concrete pour
        $calcResponse = $this->actingAs($this->qs)->getJson(route('inventory.bom.suggest-qty', [
            'activity_name' => '1:2:4 Grade 25 Concrete Pour',
            'work_quantity' => 50,
        ]));

        $calcResponse->assertOk()
            ->assertJsonPath('materials.0.expected_qty', 310) // 50 * 6.2 = 310 bags
            ->assertJsonPath('materials.0.material_code', 'CEM-ELP-50');
    }

    public function test_supplier_registration_and_blacklist_toggle()
    {
        $response = $this->actingAs($this->procurement)->post(route('inventory.suppliers.store'), [
            'name' => 'Lafarge Cement Nigeria Plc',
            'code' => 'SUP-LAF-01',
            'contact_person' => 'Engr. Emeka',
            'phone' => '08022223344',
            'email' => 'sales@lafarge.com.ng',
            'payment_terms_days' => 45,
            'bank_name' => 'Access Bank Plc',
            'bank_account_number' => '0123456789',
            'bank_account_name' => 'Lafarge Holcim Direct Sales',
            'create_portal_user' => '1',
            'portal_user_name' => 'Emeka Lafarge Rep',
            'portal_user_email' => 'emeka.rep@lafarge.com.ng',
            'portal_user_password' => 'Password123!',
        ]);

        $supplier = Supplier::where('code', 'SUP-LAF-01')->first();
        $this->assertNotNull($supplier);
        $this->assertEquals(1, $supplier->users()->count());
        $response->assertRedirect(route('inventory.suppliers.show', $supplier));

        // Test Blacklist toggle
        $blResponse = $this->actingAs($this->admin)->post(route('inventory.suppliers.blacklist', $supplier), [
            'action' => 'blacklist',
            'blacklist_reason' => 'Delayed supply of 30 tonnes rebar causing site halt.',
        ]);

        $blResponse->assertRedirect();
        $supplier->refresh();
        $this->assertTrue($supplier->is_blacklisted);
        $this->assertEquals('Delayed supply of 30 tonnes rebar causing site halt.', $supplier->blacklist_reason);
    }

    public function test_admin_can_update_company_inventory_settings()
    {
        $response = $this->actingAs($this->admin)->put(route('inventory.settings.update'), [
            'po_tier1_max' => 750000.00,
            'po_tier2_max' => 6000000.00,
            'grn_geofence_strict' => '1',
            'after_hours_start' => '19:00',
            'after_hours_end' => '06:30',
            'waste_alert_multiplier' => 2.0,
            'cement_shelf_life_days' => 120,
            'perfect_match_consecutive_limit' => 4,
            'staff_pairing_occurrences_limit' => 6,
            'price_variance_alert_pct' => 15.0,
        ]);

        $response->assertRedirect(route('inventory.settings.edit'));
        $settings = CompanyInventorySetting::current();
        $this->assertEquals(750000.00, $settings->po_tier1_max);
        $this->assertEquals(6000000.00, $settings->po_tier2_max);
        $this->assertEquals(2.0, $settings->waste_alert_multiplier);
    }
}
