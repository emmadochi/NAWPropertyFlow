<?php

namespace Tests\Feature\Inventory;

use App\Models\CompanySetting;
use App\Models\Inventory\BomTemplate;
use App\Models\Inventory\CompanyInventorySetting;
use App\Models\Inventory\GoodsReceivedNote;
use App\Models\Inventory\InventorySite;
use App\Models\Inventory\MaterialCatalogue;
use App\Models\Inventory\MaterialIssueVoucher;
use App\Models\Inventory\MaterialRequisition;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\SiteStock;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\WasteLog;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Tests\TestCase;

class InventoryPhase3Test extends TestCase
{
    protected User $admin;
    protected User $siteEngineer;
    protected User $storeKeeper;
    protected User $procurement;
    protected InventorySite $site;
    protected MaterialCatalogue $cement;
    protected MaterialCatalogue $rebar;
    protected Supplier $supplier;

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

        $engRole = Role::where('slug', 'site_engineer')->first();
        $this->siteEngineer = User::factory()->create([
            'role' => 'site_engineer',
            'role_id' => $engRole->id,
        ]);

        $storeRole = Role::where('slug', 'store_keeper')->first();
        $this->storeKeeper = User::factory()->create([
            'role' => 'store_keeper',
            'role_id' => $storeRole->id,
        ]);

        $procRole = Role::where('slug', 'procurement_officer')->first();
        $this->procurement = User::factory()->create([
            'role' => 'procurement_officer',
            'role_id' => $procRole->id,
        ]);

        $project = Project::create([
            'name' => 'Ikoyi Horizon Towers',
            'location' => 'Ikoyi, Lagos',
            'type' => 'residential',
            'status' => 'in_progress',
        ]);

        $this->site = InventorySite::create([
            'project_id' => $project->id,
            'name' => 'Ikoyi Tower Central Store',
            'code' => 'IKY-STORE-01',
            'address' => 'Glover Road, Ikoyi, Lagos',
            'gps_lat' => 6.4531000,
            'gps_lng' => 3.4358000,
            'geofence_radius_meters' => 300,
            'is_active' => true,
        ]);

        $this->cement = MaterialCatalogue::create([
            'name' => 'Dangote 3X Cement 50kg',
            'code' => 'CEM-DNG-50',
            'category' => 'cement',
            'unit_of_measure' => 'bags',
            'standard_unit_cost' => 8700.00,
            'reorder_level' => 100,
            'safety_stock_level' => 30,
        ]);

        $this->rebar = MaterialCatalogue::create([
            'name' => '16mm High Yield Steel Rebar',
            'code' => 'STL-16MM',
            'category' => 'steel',
            'unit_of_measure' => 'tonnes',
            'standard_unit_cost' => 1150000.00,
            'reorder_level' => 10,
            'safety_stock_level' => 3,
        ]);

        // Define BOM for 1:2:4 Concrete
        BomTemplate::create([
            'activity_name' => '1:2:4 Grade 25 Concrete Pour',
            'material_id' => $this->cement->id,
            'qty_per_unit' => 6.0000,
            'unit_of_work' => 'm3',
            'allowable_variance_pct' => 10.0,
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Dangote Building Materials Direct',
            'code' => 'SUP-DNG-01',
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);
    }

    public function test_site_engineer_raises_mrf_with_bom_consumption_check()
    {
        // 10m³ pour -> expected 60 bags. Engineer requests 80 bags (33% over -> triggers variance flag)
        $response = $this->actingAs($this->siteEngineer)->post(route('inventory.requisitions.store'), [
            'site_id' => $this->site->id,
            'activity_name' => '1:2:4 Grade 25 Concrete Pour',
            'work_quantity' => 10,
            'required_date' => date('Y-m-d', strtotime('+2 days')),
            'notes' => 'Urgent for deck slab',
            'items' => [
                [
                    'material_id' => $this->cement->id,
                    'qty_requested' => 80,
                ]
            ]
        ]);

        $mrf = MaterialRequisition::where('site_id', $this->site->id)->first();
        $this->assertNotNull($mrf);
        $response->assertRedirect(route('inventory.requisitions.show', $mrf));

        $item = $mrf->items->first();
        $this->assertEquals(60.0, (float)$item->bom_expected_qty);
        $this->assertTrue((bool)$item->variance_flag);

        // PM Approves with adjusted quantity
        $approveResponse = $this->actingAs($this->admin)->post(route('inventory.requisitions.approve', $mrf), [
            'approved_items' => [
                $item->id => 65.0, // adjusted down
            ]
        ]);

        $approveResponse->assertRedirect();
        $mrf->refresh();
        $this->assertEquals('approved', $mrf->status);
        $this->assertEquals(65.0, (float)$mrf->items->first()->qty_approved);
    }

    public function test_po_tiered_authorization_and_approval()
    {
        // Generate a Tier 1 PO (e.g. 50 bags * ₦8,700 = ₦435,000 <= ₦500k limit)
        $response = $this->actingAs($this->procurement)->post(route('inventory.purchase-orders.store'), [
            'site_id' => $this->site->id,
            'supplier_id' => $this->supplier->id,
            'tax_amount' => 0,
            'delivery_fee' => 15000,
            'items' => [
                [
                    'material_id' => $this->cement->id,
                    'qty_ordered' => 50,
                    'unit_price' => 8700,
                ]
            ]
        ]);

        $po = PurchaseOrder::where('supplier_id', $this->supplier->id)->first();
        $this->assertNotNull($po);
        $this->assertEquals('tier1', $po->approval_tier);
        $this->assertEquals('pending_t1', $po->status);
        $this->assertEquals(450000.00, (float)$po->total_amount);

        // Approve PO
        $appResponse = $this->actingAs($this->admin)->post(route('inventory.purchase-orders.approve', $po));
        $appResponse->assertRedirect();
        $po->refresh();
        $this->assertEquals('approved', $po->status);
    }

    public function test_gate_delivery_grn_credits_stock_and_creates_batch()
    {
        $po = PurchaseOrder::create([
            'ref_number' => 'PO-2026-0099',
            'site_id' => $this->site->id,
            'supplier_id' => $this->supplier->id,
            'created_by_user_id' => $this->procurement->id,
            'status' => 'approved',
            'subtotal_amount' => 870000.00,
            'total_amount' => 870000.00,
            'approval_tier' => 'tier2',
        ]);

        $poItem = $po->items()->create([
            'material_id' => $this->cement->id,
            'qty_ordered' => 100,
            'unit_price' => 8700.00,
            'total_price' => 870000.00,
        ]);

        // Storekeeper logs gate delivery inside geofence
        $response = $this->actingAs($this->storeKeeper)->post(route('inventory.grn.store'), [
            'purchase_order_id' => $po->id,
            'delivery_date' => date('Y-m-d'),
            'delivery_time' => '10:30',
            'waybill_number' => 'WB-DNG-7890',
            'vehicle_plate' => 'LND-554-XA',
            'driver_name' => 'Suleiman Tanko',
            'driver_phone' => '08091112233',
            'delivery_gps_lat' => 6.4531500, // On site
            'delivery_gps_lng' => 3.4358500,
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'material_id' => $this->cement->id,
                    'qty_received' => 95,
                    'qty_rejected' => 5,
                    'rejection_reason' => '5 torn bags during transit',
                    'batch_number' => 'DNG-AUG26-01',
                    'expiry_date' => date('Y-m-d', strtotime('+90 days')),
                ]
            ]
        ]);

        $grn = GoodsReceivedNote::where('purchase_order_id', $po->id)->first();
        $this->assertNotNull($grn);
        $this->assertTrue((bool)$grn->geofence_check_passed);

        // Verify stock credited
        $stock = SiteStock::where('site_id', $this->site->id)->where('material_id', $this->cement->id)->first();
        $this->assertNotNull($stock);
        $this->assertEquals(95.0, (float)$stock->qty_on_hand);
        $this->assertEquals(1, $stock->batches()->count());
    }

    public function test_material_issue_voucher_miv_debits_stock_fifo()
    {
        // Setup initial stock of 100 bags
        $stock = SiteStock::create([
            'site_id' => $this->site->id,
            'material_id' => $this->cement->id,
            'qty_on_hand' => 100,
        ]);

        $batch = $stock->batches()->create([
            'batch_number' => 'LOT-01',
            'qty_received' => 100,
            'qty_remaining' => 100,
        ]);

        // Storekeeper issues 40 bags for casting
        $response = $this->actingAs($this->storeKeeper)->post(route('inventory.miv.store'), [
            'site_id' => $this->site->id,
            'received_by_user_id' => $this->siteEngineer->id,
            'activity_name' => '1st Floor Column Pour',
            'work_quantity' => 6,
            'items' => [
                [
                    'material_id' => $this->cement->id,
                    'stock_batch_id' => $batch->id,
                    'qty_issued' => 40,
                ]
            ]
        ]);

        $miv = MaterialIssueVoucher::where('site_id', $this->site->id)->first();
        $this->assertNotNull($miv);

        // Verify stock deducted
        $stock->refresh();
        $batch->refresh();
        $this->assertEquals(60.0, (float)$stock->qty_on_hand);
        $this->assertEquals(60.0, (float)$batch->qty_remaining);
    }

    public function test_waste_log_records_incident_and_debits_stock()
    {
        $stock = SiteStock::create([
            'site_id' => $this->site->id,
            'material_id' => $this->cement->id,
            'qty_on_hand' => 50,
        ]);

        $response = $this->actingAs($this->siteEngineer)->post(route('inventory.waste.store'), [
            'site_id' => $this->site->id,
            'material_id' => $this->cement->id,
            'qty' => 5,
            'waste_type' => 'loss',
            'activity_name' => 'Foundation Pour',
            'description' => 'Heavy sudden downpour flooded uncured bags',
            'weather_condition' => 'Torrential rain',
            'deduct_from_stock' => '1',
            'insurance_claim_raised' => '0',
        ]);

        $response->assertRedirect(route('inventory.waste.index'));
        $this->assertDatabaseHas('waste_logs', ['waste_type' => 'loss', 'qty' => 5]);

        $stock->refresh();
        $this->assertEquals(45.0, (float)$stock->qty_on_hand);
    }
}
