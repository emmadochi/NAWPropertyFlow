<?php

namespace Tests\Feature\Inventory;

use App\Models\CompanySetting;
use App\Models\Inventory\GoodsReceivedNote;
use App\Models\Inventory\InventoryAnomalyFlag;
use App\Models\Inventory\InventorySite;
use App\Models\Inventory\MaterialCatalogue;
use App\Models\Inventory\PriceBenchmark;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\SupplierInvoice;
use App\Models\Inventory\SupplierUser;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Tests\TestCase;

class InventoryPhase4Test extends TestCase
{
    protected User $admin;
    protected User $accountant;
    protected InventorySite $site;
    protected MaterialCatalogue $cement;
    protected Supplier $supplier;
    protected SupplierUser $supplierUser;
    protected PurchaseOrder $po;
    protected GoodsReceivedNote $grn;

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

        $acctRole = Role::where('slug', 'accountant')->first();
        $this->accountant = User::factory()->create([
            'role' => 'accountant',
            'role_id' => $acctRole->id,
        ]);

        $project = Project::create([
            'name' => 'Victoria Island Luxury Suites',
            'location' => 'Victoria Island, Lagos',
            'type' => 'residential',
            'status' => 'in_progress',
        ]);

        $this->site = InventorySite::create([
            'project_id' => $project->id,
            'name' => 'VI Site Yard',
            'code' => 'VI-SITE-01',
            'address' => 'Ahmadu Bello Way, VI, Lagos',
            'gps_lat' => 6.4281000,
            'gps_lng' => 3.4219000,
            'geofence_radius_meters' => 200,
            'is_active' => true,
        ]);

        $this->cement = MaterialCatalogue::create([
            'name' => 'BUA Cement 50kg',
            'code' => 'CEM-BUA-50',
            'category' => 'cement',
            'unit_of_measure' => 'bags',
            'standard_unit_cost' => 8600.00,
            'reorder_level' => 100,
            'safety_stock_level' => 30,
        ]);

        $this->supplier = Supplier::create([
            'name' => 'BUA Group Materials Distribution',
            'code' => 'SUP-BUA-01',
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);

        $this->supplierUser = SupplierUser::create([
            'supplier_id' => $this->supplier->id,
            'name' => 'Alhaji Rabiu Rep',
            'email' => 'rabiu.rep@buagroup.com',
            'password' => \Illuminate\Support\Facades\Hash::make('Secret123!'),
            'is_active' => true,
        ]);

        // PO for 100 bags @ ₦8,600 = ₦860,000
        $this->po = PurchaseOrder::create([
            'ref_number' => 'PO-2026-0500',
            'site_id' => $this->site->id,
            'supplier_id' => $this->supplier->id,
            'created_by_user_id' => $this->admin->id,
            'status' => 'approved',
            'subtotal_amount' => 860000.00,
            'tax_amount' => 0,
            'delivery_fee' => 0,
            'total_amount' => 860000.00,
            'approval_tier' => 'tier2',
        ]);

        $poItem = $this->po->items()->create([
            'material_id' => $this->cement->id,
            'qty_ordered' => 100,
            'qty_delivered_cumulative' => 100,
            'unit_price' => 8600.00,
            'total_price' => 860000.00,
        ]);

        // GRN for 100 bags
        $this->grn = GoodsReceivedNote::create([
            'ref_number' => 'GRN-2026-0500',
            'purchase_order_id' => $this->po->id,
            'site_id' => $this->site->id,
            'received_by_user_id' => $this->admin->id,
            'delivery_date' => date('Y-m-d'),
            'delivery_time' => '11:00',
            'geofence_check_passed' => true,
            'status' => 'complete',
        ]);

        $this->grn->items()->create([
            'po_item_id' => $poItem->id,
            'material_id' => $this->cement->id,
            'qty_ordered' => 100,
            'qty_received' => 100,
            'qty_rejected' => 0,
            'unit_price_confirmed' => 8600.00,
        ]);
    }

    public function test_three_way_match_passes_when_billed_amount_matches_grn_accepted_goods()
    {
        $response = $this->actingAs($this->accountant)->post(route('inventory.invoices.store'), [
            'supplier_id' => $this->supplier->id,
            'purchase_order_id' => $this->po->id,
            'grn_id' => $this->grn->id,
            'invoice_number' => 'INV-BUA-2026-001',
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'billed_amount' => 860000.00,
        ]);

        $invoice = SupplierInvoice::where('invoice_number', 'INV-BUA-2026-001')->first();
        $this->assertNotNull($invoice);
        $response->assertRedirect(route('inventory.invoices.show', $invoice));

        $this->assertEquals('approved_for_payment', $invoice->payment_status);
        $this->assertNotNull($invoice->matched_at);
    }

    public function test_three_way_match_flags_overbilling_and_creates_anomaly()
    {
        // Vendor bills ₦950,000 instead of ₦860,000 (>10% variance)
        $response = $this->actingAs($this->accountant)->post(route('inventory.invoices.store'), [
            'supplier_id' => $this->supplier->id,
            'purchase_order_id' => $this->po->id,
            'grn_id' => $this->grn->id,
            'invoice_number' => 'INV-BUA-OVERBILL',
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'billed_amount' => 950000.00,
        ]);

        $invoice = SupplierInvoice::where('invoice_number', 'INV-BUA-OVERBILL')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('disputed', $invoice->payment_status);
        $this->assertStringContainsString('Price Discrepancy', $invoice->discrepancy_notes);

        // Verify anomaly flag was generated
        $anomaly = InventoryAnomalyFlag::where('flag_type', 'price_spike')->first();
        $this->assertNotNull($anomaly);
        $this->assertEquals('open', $anomaly->status);
    }

    public function test_anomaly_radar_investigation_and_resolution()
    {
        $anomaly = InventoryAnomalyFlag::create([
            'site_id' => $this->site->id,
            'flag_type' => 'ghost_delivery',
            'title' => 'Geofence Breach',
            'description' => 'Delivery recorded outside geofence boundary.',
            'severity' => 'critical',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->admin)->post(route('inventory.anomalies.update-status', $anomaly), [
            'status' => 'resolved',
            'resolution_notes' => 'Driver offloaded at secondary gate 50m outside primary GPS pin due to road construction.',
        ]);

        $response->assertRedirect(route('inventory.anomalies.show', $anomaly));
        $anomaly->refresh();
        $this->assertEquals('resolved', $anomaly->status);
        $this->assertEquals($this->admin->id, $anomaly->resolved_by_user_id);
    }

    public function test_supplier_portal_auth_and_online_invoice_submission()
    {
        // 1. Supplier Login
        $loginResponse = $this->post(route('supplier.login.submit'), [
            'email' => 'rabiu.rep@buagroup.com',
            'password' => 'Secret123!',
        ]);

        $loginResponse->assertRedirect(route('supplier.dashboard'));
        $this->assertAuthenticatedAs($this->supplierUser, 'supplier');

        // 2. Access Portal Dashboard
        $dashResponse = $this->actingAs($this->supplierUser, 'supplier')->get(route('supplier.dashboard'));
        $dashResponse->assertOk()
            ->assertSee('BUA Group Materials Distribution');

        // 3. Submit Invoice via Supplier Portal
        $invResponse = $this->actingAs($this->supplierUser, 'supplier')->post(route('supplier.invoices.store'), [
            'purchase_order_id' => $this->po->id,
            'grn_id' => $this->grn->id,
            'invoice_number' => 'INV-PORTAL-001',
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'billed_amount' => 860000.00,
        ]);

        $invResponse->assertRedirect(route('supplier.invoices.index'));
        $this->assertDatabaseHas('supplier_invoices', [
            'invoice_number' => 'INV-PORTAL-001',
            'supplier_id' => $this->supplier->id,
            'payment_status' => 'approved_for_payment',
        ]);
    }
}
