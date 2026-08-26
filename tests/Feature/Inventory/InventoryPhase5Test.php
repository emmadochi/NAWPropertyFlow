<?php

namespace Tests\Feature\Inventory;

use App\Models\CompanySetting;
use App\Models\Expense;
use App\Models\Inventory\GoodsReceivedNote;
use App\Models\Inventory\InventoryChartOfAccount;
use App\Models\Inventory\InventoryJournalEntry;
use App\Models\Inventory\InventorySite;
use App\Models\Inventory\MaterialCatalogue;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\SiteStock;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\SupplierInvoice;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Tests\TestCase;

class InventoryPhase5Test extends TestCase
{
    protected User $admin;
    protected User $accountant;
    protected User $siteEngineer;
    protected Project $project;
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

        $acctRole = Role::where('slug', 'accountant')->first();
        $this->accountant = User::factory()->create([
            'role' => 'accountant',
            'role_id' => $acctRole->id,
        ]);

        $qsRole = Role::where('slug', 'quantity_surveyor')->first();
        $this->siteEngineer = User::factory()->create([
            'role' => 'quantity_surveyor',
            'role_id' => $qsRole ? $qsRole->id : $adminRole->id,
        ]);

        $this->project = Project::create([
            'name' => 'Eko Atlantic Highrise Phase 1',
            'location' => 'Eko Atlantic City, Lagos',
            'type' => 'commercial',
            'status' => 'in_progress',
        ]);

        $this->site = InventorySite::create([
            'project_id' => $this->project->id,
            'name' => 'Eko Atlantic Main Yard',
            'code' => 'EKO-SITE-01',
            'address' => 'Boulevard Central, Eko Atlantic, Lagos',
            'gps_lat' => 6.4180000,
            'gps_lng' => 3.4150000,
            'geofence_radius_meters' => 300,
            'is_active' => true,
        ]);

        $this->cement = MaterialCatalogue::create([
            'name' => 'Dangote Falcon Cement 50kg',
            'code' => 'CEM-DAN-50',
            'category' => 'cement',
            'unit_of_measure' => 'bags',
            'standard_unit_cost' => 8500.00,
            'reorder_level' => 150,
            'safety_stock_level' => 50,
        ]);

        $this->rebar = MaterialCatalogue::create([
            'name' => 'High-Yield Rebar 16mm',
            'code' => 'STL-16MM-TMT',
            'category' => 'steel',
            'unit_of_measure' => 'tonnes',
            'standard_unit_cost' => 1250000.00,
            'reorder_level' => 10,
            'safety_stock_level' => 3,
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Dangote Building Solutions Ltd',
            'code' => 'SUP-DAN-001',
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);
    }

    public function test_grn_delivery_auto_posts_balanced_asset_capitalization_journal()
    {
        // 1. Create and Approve PO for 200 bags of cement = ₦1,700,000
        $po = PurchaseOrder::create([
            'ref_number' => 'PO-2026-0901',
            'site_id' => $this->site->id,
            'supplier_id' => $this->supplier->id,
            'created_by_user_id' => $this->admin->id,
            'status' => 'approved',
            'subtotal_amount' => 1700000.00,
            'tax_amount' => 0,
            'delivery_fee' => 0,
            'total_amount' => 1700000.00,
            'approval_tier' => 'tier2',
        ]);

        $poItem = $po->items()->create([
            'material_id' => $this->cement->id,
            'qty_ordered' => 200,
            'qty_delivered_cumulative' => 0,
            'unit_price' => 8500.00,
            'total_price' => 1700000.00,
        ]);

        // 2. Receive Gate Delivery (GRN)
        $response = $this->actingAs($this->admin)->post(route('inventory.grn.store'), [
            'purchase_order_id' => $po->id,
            'delivery_date' => date('Y-m-d'),
            'delivery_time' => '10:30',
            'waybill_number' => 'WB-DAN-7788',
            'driver_name' => 'Musa Garba',
            'items' => [
                [
                    'po_item_id' => $poItem->id,
                    'material_id' => $this->cement->id,
                    'qty_received' => 200,
                    'qty_rejected' => 0,
                    'batch_number' => 'LOT-DAN-0901',
                ],
            ],
        ]);

        $grn = GoodsReceivedNote::where('waybill_number', 'WB-DAN-7788')->first();
        $this->assertNotNull($grn);

        // 3. Verify Double-Entry Journal Entry was created
        $journal = InventoryJournalEntry::where('reference_type', GoodsReceivedNote::class)
            ->where('reference_id', $grn->id)
            ->with('items')
            ->first();

        $this->assertNotNull($journal);
        $this->assertTrue($journal->is_balanced);
        $this->assertEquals(1700000.00, (float)$journal->total_debit);
        $this->assertEquals(1700000.00, (float)$journal->total_credit);

        // Debit 1300 (Asset) and Credit 2150 (GRNI)
        $debitItem = $journal->items->firstWhere('entry_type', 'debit');
        $creditItem = $journal->items->firstWhere('entry_type', 'credit');

        $this->assertEquals('1300', $debitItem->account_code);
        $this->assertEquals(1700000.00, (float)$debitItem->amount);
        $this->assertEquals('2150', $creditItem->account_code);
        $this->assertEquals(1700000.00, (float)$creditItem->amount);
    }

    public function test_miv_site_issuance_auto_posts_wip_job_costing_journal()
    {
        // 1. Establish on-hand stock: 100 bags of cement = ₦850,000
        SiteStock::create([
            'site_id' => $this->site->id,
            'material_id' => $this->cement->id,
            'qty_on_hand' => 100,
            'qty_reserved' => 0,
            'reorder_status' => 'healthy',
        ]);

        // 2. Issue 50 bags to 3rd Floor Slab Cast
        $response = $this->actingAs($this->admin)->post(route('inventory.miv.store'), [
            'site_id' => $this->site->id,
            'received_by_user_id' => $this->siteEngineer->id,
            'activity_name' => '3rd Floor Slab Concreting Cast',
            'work_quantity' => 120,
            'work_unit' => 'm3',
            'items' => [
                [
                    'material_id' => $this->cement->id,
                    'qty_issued' => 50,
                ],
            ],
        ]);

        // 3. Verify Double-Entry Journal Entry
        $journal = InventoryJournalEntry::where('reference_type', \App\Models\Inventory\MaterialIssueVoucher::class)
            ->with('items')
            ->latest('id')
            ->first();

        $this->assertNotNull($journal);
        $this->assertTrue($journal->is_balanced);
        $expectedCost = 50 * 8500.00; // ₦425,000
        $this->assertEquals($expectedCost, (float)$journal->total_debit);

        // Debit 5100 (Direct Materials / WIP) and Credit 1300 (Asset)
        $debitItem = $journal->items->firstWhere('entry_type', 'debit');
        $creditItem = $journal->items->firstWhere('entry_type', 'credit');

        $this->assertEquals('5100', $debitItem->account_code);
        $this->assertEquals($expectedCost, (float)$debitItem->amount);
        $this->assertEquals('1300', $creditItem->account_code);
        $this->assertEquals($expectedCost, (float)$creditItem->amount);
    }

    public function test_three_way_match_invoice_posts_accounts_payable_and_wht_journal()
    {
        $po = PurchaseOrder::create([
            'ref_number' => 'PO-2026-1001',
            'site_id' => $this->site->id,
            'supplier_id' => $this->supplier->id,
            'created_by_user_id' => $this->admin->id,
            'status' => 'approved',
            'subtotal_amount' => 1000000.00,
            'tax_amount' => 75000.00, // 7.5% VAT
            'delivery_fee' => 0,
            'total_amount' => 1075000.00,
            'approval_tier' => 'tier2',
        ]);

        $po->items()->create([
            'material_id' => $this->cement->id,
            'qty_ordered' => 100,
            'qty_delivered_cumulative' => 100,
            'unit_price' => 10000.00,
            'total_price' => 1000000.00,
        ]);

        $response = $this->actingAs($this->accountant)->post(route('inventory.invoices.store'), [
            'supplier_id' => $this->supplier->id,
            'purchase_order_id' => $po->id,
            'invoice_number' => 'INV-DAN-TAX-01',
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'billed_amount' => 1075000.00,
            'tax_amount' => 75000.00,
        ]);

        $invoice = SupplierInvoice::where('invoice_number', 'INV-DAN-TAX-01')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('approved_for_payment', $invoice->payment_status);

        // Verify Journal Entry
        $journal = InventoryJournalEntry::where('reference_type', SupplierInvoice::class)
            ->where('reference_id', $invoice->id)
            ->with('items')
            ->first();

        $this->assertNotNull($journal);
        $this->assertTrue($journal->is_balanced);
        $this->assertEquals(1075000.00, (float)$journal->total_debit);

        // Check 5% WHT on subtotal: ₦1,000,000 * 5% = ₦50,000. Net AP: ₦1,025,000.
        $whtItem = $journal->items->firstWhere('account_code', '2120');
        $this->assertNotNull($whtItem);
        $this->assertEquals(50000.00, (float)$whtItem->amount);

        $apItem = $journal->items->firstWhere('account_code', '2100');
        $this->assertNotNull($apItem);
        $this->assertEquals(1025000.00, (float)$apItem->amount);

        // Verify synced with main CRM Expenses
        $expense = Expense::where('reference_number', 'INV-DAN-TAX-01')->first();
        $this->assertNotNull($expense);
        $this->assertEquals(1075000.00, (float)$expense->amount);
    }

    public function test_executive_inventory_dashboard_renders_metrics_and_fifo_valuation()
    {
        SiteStock::create([
            'site_id' => $this->site->id,
            'material_id' => $this->cement->id,
            'qty_on_hand' => 200,
            'reorder_status' => 'healthy',
        ]);

        $response = $this->actingAs($this->admin)->get(route('inventory.dashboard'));

        $response->assertOk()
            ->assertSee('Executive Inventory &amp; Cost Cockpit', false)
            ->assertSee('Site Stock Asset (FIFO)')
            ->assertSee('₦1,700,000.00') // 200 bags * ₦8,500
            ->assertSee('Eko Atlantic Main Yard');
    }

    public function test_general_ledger_view_renders_chart_of_accounts_matrix()
    {
        $response = $this->actingAs($this->accountant)->get(route('inventory.general-ledger.index'));

        $response->assertOk()
            ->assertSee('Construction Double-Entry General Ledger')
            ->assertSee('1300')
            ->assertSee('Construction Materials Inventory Asset')
            ->assertSee('2150')
            ->assertSee('5100');
    }
}
