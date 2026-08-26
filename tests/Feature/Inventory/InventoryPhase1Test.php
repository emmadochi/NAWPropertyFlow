<?php

namespace Tests\Feature\Inventory;

use App\Models\Inventory\BomTemplate;
use App\Models\Inventory\CompanyInventorySetting;
use App\Models\Inventory\GoodsReceivedNote;
use App\Models\Inventory\GrnItem;
use App\Models\Inventory\InventoryAnomalyFlag;
use App\Models\Inventory\InventorySite;
use App\Models\Inventory\MaterialCatalogue;
use App\Models\Inventory\MaterialIssueVoucher;
use App\Models\Inventory\MaterialRequisition;
use App\Models\Inventory\MaterialRequisitionItem;
use App\Models\Inventory\MivItem;
use App\Models\Inventory\PriceBenchmark;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\PurchaseOrderItem;
use App\Models\Inventory\SiteStock;
use App\Models\Inventory\StockBatch;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\SupplierInvoice;
use App\Models\Inventory\SupplierUser;
use App\Models\Inventory\WasteLog;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryPhase1Test extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
            '--realpath' => false,
        ]);

        $this->seed(PermissionSeeder::class);
    }

    public function test_all_inventory_permissions_and_roles_are_seeded()
    {
        $this->assertDatabaseHas('permissions', ['slug' => 'inventory.view_stock']);
        $this->assertDatabaseHas('permissions', ['slug' => 'inventory.manage_catalogue']);
        $this->assertDatabaseHas('permissions', ['slug' => 'inventory.set_bom']);
        $this->assertDatabaseHas('permissions', ['slug' => 'inventory.raise_mrf']);
        $this->assertDatabaseHas('permissions', ['slug' => 'inventory.approve_mrf']);
        $this->assertDatabaseHas('permissions', ['slug' => 'inventory.create_po']);
        $this->assertDatabaseHas('permissions', ['slug' => 'inventory.receive_grn']);
        $this->assertDatabaseHas('permissions', ['slug' => 'inventory.issue_material']);
        $this->assertDatabaseHas('permissions', ['slug' => 'inventory.match_invoice']);
        $this->assertDatabaseHas('permissions', ['slug' => 'inventory.view_anomalies']);

        $storeKeeper = Role::where('slug', 'store_keeper')->first();
        $this->assertNotNull($storeKeeper);
        $this->assertTrue($storeKeeper->hasPermission('inventory.receive_grn'));
        $this->assertTrue($storeKeeper->hasPermission('inventory.issue_material'));
        $this->assertFalse($storeKeeper->hasPermission('inventory.create_po'));

        $qs = Role::where('slug', 'quantity_surveyor')->first();
        $this->assertNotNull($qs);
        $this->assertTrue($qs->hasPermission('inventory.set_bom'));
        $this->assertTrue($qs->hasPermission('inventory.manage_catalogue'));
    }

    public function test_company_inventory_settings_creation()
    {
        $settings = CompanyInventorySetting::current();
        $this->assertNotNull($settings);
        $this->assertEquals(500000.00, $settings->po_tier1_max);
        $this->assertEquals(5000000.00, $settings->po_tier2_max);
        $this->assertTrue($settings->grn_geofence_strict);
    }

    public function test_site_and_material_catalogue_and_stock_lifecycle()
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Lekki Pearl Residence',
            'location' => 'Lekki Phase 1',
            'type' => 'residential',
            'status' => 'in_progress',
        ]);

        $site = InventorySite::create([
            'project_id' => $project->id,
            'name' => 'Lekki Main Yard',
            'code' => 'LPR-SITE-01',
            'address' => 'Plot 4, Admiralty Way, Lekki',
            'gps_lat' => 6.4474000,
            'gps_lng' => 3.4849000,
            'geofence_radius_meters' => 250,
            'created_by' => $user->id,
        ]);

        $cement = MaterialCatalogue::create([
            'name' => 'Dangote Falcon Cement 50kg',
            'code' => 'CEM-DNG-50',
            'category' => 'cement',
            'unit_of_measure' => 'bags',
            'standard_unit_cost' => 8500.00,
            'reorder_level' => 100,
            'safety_stock_level' => 30,
            'shelf_life_days' => 90,
            'is_trackable_by_batch' => true,
        ]);

        $stock = SiteStock::create([
            'site_id' => $site->id,
            'material_id' => $cement->id,
            'qty_on_hand' => 500,
            'qty_reserved' => 50,
        ]);

        $batch = StockBatch::create([
            'site_stock_id' => $stock->id,
            'batch_number' => 'BATCH-2026-AUG-01',
            'manufacture_date' => now()->subDays(10),
            'expiry_date' => now()->addDays(80),
            'qty_received' => 500,
            'qty_remaining' => 500,
            'qc_status' => 'pass',
        ]);

        $this->assertEquals(1, $site->stock()->count());
        $this->assertEquals(1, $stock->batches()->count());
        $this->assertEquals('LPR-SITE-01', $stock->site->code);
    }

    public function test_full_procurement_data_relationships()
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Abuja Commercial Plaza',
            'location' => 'Central Business District, Abuja',
            'type' => 'commercial',
            'status' => 'planning',
        ]);

        $site = InventorySite::create([
            'project_id' => $project->id,
            'name' => 'Abuja Site Gate 1',
            'code' => 'ACP-GATE-1',
            'created_by' => $user->id,
        ]);

        $supplier = Supplier::create([
            'name' => 'Julius Berger Aggregates Ltd',
            'code' => 'SUP-JB-001',
            'contact_person' => 'Alhaji Musa',
            'phone' => '08031234567',
            'email' => 'sales@jb-aggregates.ng',
        ]);

        $supplierUser = SupplierUser::create([
            'supplier_id' => $supplier->id,
            'name' => 'Musa Portal Admin',
            'email' => 'musa@jb-aggregates.ng',
            'password' => 'secret123',
        ]);

        $ironRod = MaterialCatalogue::create([
            'name' => '16mm High Tensile Rebar',
            'code' => 'STL-16MM-HT',
            'category' => 'steel',
            'unit_of_measure' => 'tonnes',
            'standard_unit_cost' => 1200000.00,
        ]);

        // 1. MRF
        $mrf = MaterialRequisition::create([
            'ref_number' => 'MRF-2026-0001',
            'site_id' => $site->id,
            'project_id' => $project->id,
            'requested_by_user_id' => $user->id,
            'activity_name' => 'Floor 1 Slab Reinforcement',
            'required_date' => now()->addDays(5),
            'status' => 'approved',
            'approved_by_user_id' => $user->id,
            'approved_at' => now(),
        ]);

        MaterialRequisitionItem::create([
            'requisition_id' => $mrf->id,
            'material_id' => $ironRod->id,
            'qty_requested' => 10,
            'qty_approved' => 10,
            'bom_expected_qty' => 9.5,
        ]);

        // 2. PO
        $po = PurchaseOrder::create([
            'ref_number' => 'PO-2026-0001',
            'requisition_id' => $mrf->id,
            'site_id' => $site->id,
            'supplier_id' => $supplier->id,
            'created_by_user_id' => $user->id,
            'status' => 'approved',
            'subtotal_amount' => 12000000.00,
            'total_amount' => 12000000.00,
            'approval_tier' => 'tier3',
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'material_id' => $ironRod->id,
            'qty_ordered' => 10,
            'unit_price' => 1200000.00,
            'total_price' => 12000000.00,
        ]);

        // 3. GRN
        $grn = GoodsReceivedNote::create([
            'ref_number' => 'GRN-2026-0001',
            'purchase_order_id' => $po->id,
            'site_id' => $site->id,
            'received_by_user_id' => $user->id,
            'delivery_date' => now()->toDateString(),
            'delivery_time' => '10:30:00',
            'driver_name' => 'Sunday Okafor',
            'vehicle_plate' => 'ABC-123-XY',
            'geofence_check_passed' => true,
            'status' => 'complete',
        ]);

        GrnItem::create([
            'grn_id' => $grn->id,
            'po_item_id' => $poItem->id,
            'material_id' => $ironRod->id,
            'qty_ordered' => 10,
            'qty_received' => 10,
        ]);

        // 4. MIV
        $miv = MaterialIssueVoucher::create([
            'ref_number' => 'MIV-2026-0001',
            'site_id' => $site->id,
            'issued_by_user_id' => $user->id,
            'received_by_user_id' => $user->id,
            'activity_name' => 'Floor 1 Slab Reinforcement',
            'work_quantity' => 150,
            'work_unit' => 'm2',
            'status' => 'issued',
        ]);

        MivItem::create([
            'miv_id' => $miv->id,
            'material_id' => $ironRod->id,
            'qty_requested' => 5,
            'qty_issued' => 5,
        ]);

        // 5. Waste Log
        $waste = WasteLog::create([
            'site_id' => $site->id,
            'material_id' => $ironRod->id,
            'miv_id' => $miv->id,
            'qty' => 0.2,
            'waste_type' => 'unavoidable',
            'activity_name' => 'Floor 1 Slab Reinforcement',
            'description' => 'Joint trimming offcuts',
            'logged_by_user_id' => $user->id,
        ]);

        // 6. 3-Way Match Invoice
        $invoice = SupplierInvoice::create([
            'purchase_order_id' => $po->id,
            'goods_received_note_id' => $grn->id,
            'supplier_id' => $supplier->id,
            'invoice_number' => 'INV-JB-9921',
            'invoice_date' => now()->toDateString(),
            'subtotal_amount' => 12000000.00,
            'total_amount' => 12000000.00,
            'payment_status' => 'matched',
            'matched_by_user_id' => $user->id,
            'matched_at' => now(),
        ]);

        // 7. Price Benchmark
        $benchmark = PriceBenchmark::create([
            'material_id' => $ironRod->id,
            'city' => 'abuja',
            'unit_price' => 1180000.00,
            'recorded_date' => now()->toDateString(),
            'entered_by_user_id' => $user->id,
            'source_market_name' => 'Dei-Dei Building Material Market',
        ]);

        // 8. Anomaly Flag
        $anomaly = InventoryAnomalyFlag::create([
            'flag_type' => 'perfect_match',
            'flaggable_type' => PurchaseOrder::class,
            'flaggable_id' => $po->id,
            'site_id' => $site->id,
            'title' => 'Suspicious Consecutive Exact Matches',
            'description' => 'Supplier deliveries matched PO exactly 3 times consecutively.',
            'severity' => 'low',
            'status' => 'open',
        ]);

        $this->assertEquals(1, $po->items()->count());
        $this->assertEquals(1, $po->goodsReceivedNotes()->count());
        $this->assertEquals($po->id, $invoice->purchaseOrder->id);
        $this->assertEquals('matched', $invoice->payment_status);
        $this->assertEquals(1, $supplier->users()->count());
        $this->assertEquals($ironRod->id, $benchmark->material->id);
        $this->assertEquals('perfect_match', $anomaly->flag_type);
    }
}
