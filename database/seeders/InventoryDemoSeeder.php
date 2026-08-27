<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Inventory\BomTemplate;
use App\Models\Inventory\CompanyInventorySetting;
use App\Models\Inventory\GoodsReceivedNote;
use App\Models\Inventory\GrnItem;
use App\Models\Inventory\InventoryAnomalyFlag;
use App\Models\Inventory\InventoryChartOfAccount;
use App\Models\Inventory\InventoryJournalEntry;
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
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InventoryDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles & Stakeholder User Personas
        $adminRole = Role::firstOrCreate(['slug' => 'company_admin'], ['name' => 'Company Admin', 'is_system' => true]);
        $acctRole = Role::firstOrCreate(['slug' => 'accountant'], ['name' => 'Accountant', 'is_system' => true]);
        $qsRole = Role::firstOrCreate(['slug' => 'quantity_surveyor'], ['name' => 'Quantity Surveyor', 'is_system' => true]);
        $storeRole = Role::firstOrCreate(['slug' => 'store_keeper'], ['name' => 'Store Keeper', 'is_system' => true]);
        $siteEngRole = Role::firstOrCreate(['slug' => 'site_engineer'], ['name' => 'Site Engineer', 'is_system' => true]);

        // A. Company Admin / Managing Director
        $admin = User::updateOrCreate(
            ['email' => 'admin@propertyflow.com'],
            [
                'name' => 'Engr. Ahmed Bello (Project Director)',
                'role' => 'company_admin',
                'role_id' => $adminRole->id,
                'department' => 'Executive Management',
                'phone_number' => '+2348031110001',
                'status' => 'active',
                'password' => Hash::make('password'),
            ]
        );

        // B. Quantity Surveyor (QS)
        $qs = User::updateOrCreate(
            ['email' => 'qs@propertyflow.com'],
            [
                'name' => 'QS Babatunde Sanusi (Lead Cost Estimator)',
                'role' => 'quantity_surveyor',
                'role_id' => $qsRole->id,
                'department' => 'Quantity Survey & Estimation',
                'phone_number' => '+2348032220002',
                'status' => 'active',
                'password' => Hash::make('password'),
            ]
        );

        // C. Site Manager / Site Engineer
        $siteManager = User::updateOrCreate(
            ['email' => 'site.manager@propertyflow.com'],
            [
                'name' => 'Engr. Emeka Nwosu (Site Project Manager)',
                'role' => 'site_engineer',
                'role_id' => $siteEngRole->id,
                'department' => 'Site Civil Operations',
                'phone_number' => '+2348033330003',
                'status' => 'active',
                'password' => Hash::make('password'),
            ]
        );

        // D. Site Storekeeper / Materials Controller
        $storekeeper = User::updateOrCreate(
            ['email' => 'storekeeper@propertyflow.com'],
            [
                'name' => 'Musa Aliyu (Site Materials Controller)',
                'role' => 'store_keeper',
                'role_id' => $storeRole->id,
                'department' => 'Store & Logistics',
                'phone_number' => '+2348034440004',
                'status' => 'active',
                'password' => Hash::make('password'),
            ]
        );

        // E. Lead Accountant / Finance Officer
        $accountant = User::updateOrCreate(
            ['email' => 'accountant@propertyflow.com'],
            [
                'name' => 'Femi Adeleke (Lead Financial Controller)',
                'role' => 'accountant',
                'role_id' => $acctRole->id,
                'department' => 'Finance & Accounts',
                'phone_number' => '+2348035550005',
                'status' => 'active',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Company Inventory Thresholds & Geofence Policy
        CompanyInventorySetting::updateOrCreate(
            ['id' => 1],
            [
                'po_tier1_max' => 5000000.00,    // <= ₦5m Site Manager
                'po_tier2_max' => 20000000.00,   // <= ₦20m Project Director
                'price_variance_alert_pct' => 5.00,
                'geofence_enforcement' => true,
                'waste_threshold_pct' => 3.00,
                'require_two_step_miv' => true,
            ]
        );

        // 3. 3 Development Projects
        $proj1 = Project::firstOrCreate(
            ['name' => 'Hutu Prestige Smart Estate'],
            ['location' => 'Katampe Extension, Abuja', 'type' => 'residential', 'status' => 'in_progress', 'description' => '45 Units 4-Bedroom Terrace Duplexes with Smart Automation.']
        );
        $proj2 = Project::firstOrCreate(
            ['name' => 'Eko Atlantic Boulevard Towers'],
            ['location' => 'Victoria Island, Lagos', 'type' => 'commercial', 'status' => 'in_progress', 'description' => '18-Floor Mixed-Use Luxury Waterfront Commercial Complex.']
        );
        $proj3 = Project::firstOrCreate(
            ['name' => 'Guzape Hills Luxury Villas'],
            ['location' => 'Guzape District, Abuja', 'type' => 'residential', 'status' => 'in_progress', 'description' => '12 Units 5-Bedroom Detached Contemporary Mansions.']
        );

        // 4. 3 Material Site Yards
        $siteHutu = InventorySite::updateOrCreate(
            ['code' => 'YARD-ABJ-HUTU'],
            [
                'project_id' => $proj1->id,
                'name' => 'Hutu Prestige Main Site Yard',
                'address' => 'Plot 402, Katampe Extension, Diplomatic Zone, Abuja',
                'gps_lat' => 9.0820000,
                'gps_lng' => 7.4810000,
                'geofence_radius_meters' => 300,
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        $siteEko = InventorySite::updateOrCreate(
            ['code' => 'YARD-LOS-EKO'],
            [
                'project_id' => $proj2->id,
                'name' => 'Eko Atlantic Central Staging Yard',
                'address' => 'Tower 2 Boulevard, Eko Atlantic City, Lagos',
                'gps_lat' => 6.4180000,
                'gps_lng' => 3.4150000,
                'geofence_radius_meters' => 400,
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        $siteGuzape = InventorySite::updateOrCreate(
            ['code' => 'YARD-ABJ-GUZ'],
            [
                'project_id' => $proj3->id,
                'name' => 'Guzape Hills Compound Store',
                'address' => 'Hilltop Crescent, Guzape Phase 2, Abuja',
                'gps_lat' => 9.0350000,
                'gps_lng' => 7.5120000,
                'geofence_radius_meters' => 250,
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        // 5. Material Catalogue Master (matches enum in 2026_08_26_100003_create_material_catalogue_table)
        $matCement = MaterialCatalogue::updateOrCreate(
            ['code' => 'CEM-DAN-50KG'],
            ['name' => 'Dangote Falcon Cement 50kg', 'category' => 'cement', 'unit_of_measure' => 'bags', 'standard_unit_cost' => 8500.00, 'reorder_level' => 300, 'safety_stock_level' => 100, 'description' => 'Grade 42.5R Portland limestone cement.', 'is_active' => true]
        );
        $matRebar = MaterialCatalogue::updateOrCreate(
            ['code' => 'STL-TMT-16MM'],
            ['name' => 'High-Yield TMT Rebar 16mm', 'category' => 'steel', 'unit_of_measure' => 'tonnes', 'standard_unit_cost' => 1350000.00, 'reorder_level' => 15, 'safety_stock_level' => 5, 'description' => 'Fe500D high-yield steel reinforcement rods.', 'is_active' => true]
        );
        $matBlocks = MaterialCatalogue::updateOrCreate(
            ['code' => 'BLK-9IN-VIB'],
            ['name' => 'Vibrated 9-Inch Solid Sandcrete Blocks', 'category' => 'block', 'unit_of_measure' => 'pieces', 'standard_unit_cost' => 480.00, 'reorder_level' => 2000, 'safety_stock_level' => 500, 'description' => 'High-density vibrated load-bearing building blocks.', 'is_active' => true]
        );
        $matSand = MaterialCatalogue::updateOrCreate(
            ['code' => 'AGG-SAND-20T'],
            ['name' => 'Sharp River Sand (20-Tonne Tipper)', 'category' => 'aggregate', 'unit_of_measure' => 'trips', 'standard_unit_cost' => 120000.00, 'reorder_level' => 10, 'safety_stock_level' => 3, 'description' => 'Washed coarse river sand for structural concrete cast.', 'is_active' => true]
        );
        $matGranite = MaterialCatalogue::updateOrCreate(
            ['code' => 'AGG-GRN-30T'],
            ['name' => '3/4 Clean Granite Aggregate (30-Tonne Tipper)', 'category' => 'aggregate', 'unit_of_measure' => 'trips', 'standard_unit_cost' => 280000.00, 'reorder_level' => 8, 'safety_stock_level' => 2, 'description' => 'Machine-crushed hard granite stone.', 'is_active' => true]
        );
        $matTiles = MaterialCatalogue::updateOrCreate(
            ['code' => 'FIN-TILE-60X60'],
            ['name' => 'Royal Porcelain Vitrified Floor Tiles (600x600mm)', 'category' => 'finishing', 'unit_of_measure' => 'cartons', 'standard_unit_cost' => 12500.00, 'reorder_level' => 150, 'safety_stock_level' => 40, 'description' => 'Polished non-slip vitrified porcelain tiles.', 'is_active' => true]
        );

        // 6. Regional Price Benchmarks
        PriceBenchmark::updateOrCreate(
            ['material_id' => $matCement->id, 'region_state' => 'Abuja (FCT)'],
            ['average_unit_price' => 8500.00, 'min_price' => 8200.00, 'max_price' => 8900.00, 'source' => 'Abuja Builders Merchant Survey Q3 2026', 'survey_date' => now()->toDateString(), 'recorded_by_user_id' => $qs->id]
        );
        PriceBenchmark::updateOrCreate(
            ['material_id' => $matRebar->id, 'region_state' => 'Lagos'],
            ['average_unit_price' => 1350000.00, 'min_price' => 1300000.00, 'max_price' => 1420000.00, 'source' => 'Lagos Steel Importers Association', 'survey_date' => now()->toDateString(), 'recorded_by_user_id' => $qs->id]
        );

        // 7. Quantity Surveyor BOM Templates
        BomTemplate::updateOrCreate(
            ['activity_name' => 'Grade 25 Concrete Slab Casting (1 m3)'],
            ['material_id' => $matCement->id, 'unit_of_work' => 'm3', 'standard_coefficient' => 7.00, 'waste_allowance_pct' => 3.00, 'specifications' => '1:2:4 Mix Ratio for 25 N/mm2 structural deck slab.', 'created_by_user_id' => $qs->id]
        );

        // 8. Registered Suppliers & Portal Logins
        $supDangote = Supplier::updateOrCreate(
            ['code' => 'SUP-DAN-001'],
            ['name' => 'Dangote Building Solutions Ltd', 'contact_person' => 'Alhaji Lawal Danjuma', 'email' => 'dangote@supplier.com', 'phone' => '+2348031122334', 'address' => 'Marble House, Kingsway Road, Ikoyi, Lagos', 'bank_name' => 'Access Bank Plc', 'bank_account_number' => '0012345678', 'bank_account_name' => 'Dangote Building Solutions Ltd', 'payment_terms_days' => 30, 'rating' => 4.9, 'is_active' => true]
        );
        SupplierUser::updateOrCreate(
            ['email' => 'dangote@supplier.com'],
            ['supplier_id' => $supDangote->id, 'name' => 'Lawal Danjuma (Dangote Rep)', 'password' => Hash::make('password123'), 'is_active' => true]
        );

        $supSteel = Supplier::updateOrCreate(
            ['code' => 'SUP-STL-002'],
            ['name' => 'African Steel & Rebar Mills Ltd', 'contact_person' => 'Engr. Victor Obi', 'email' => 'steel@supplier.com', 'phone' => '+2348039988776', 'address' => 'Kilometer 12, Ikorodu Industrial Estate, Lagos', 'bank_name' => 'Zenith Bank Plc', 'bank_account_number' => '1019876543', 'bank_account_name' => 'African Steel & Rebar Mills Ltd', 'payment_terms_days' => 45, 'rating' => 4.7, 'is_active' => true]
        );
        SupplierUser::updateOrCreate(
            ['email' => 'steel@supplier.com'],
            ['supplier_id' => $supSteel->id, 'name' => 'Victor Obi (Steel Mills Rep)', 'password' => Hash::make('password123'), 'is_active' => true]
        );

        $supBlocks = Supplier::updateOrCreate(
            ['code' => 'SUP-BLK-003'],
            ['name' => 'Apex Vibrated Concrete Products Ltd', 'contact_person' => 'Chief Emeka Nwachukwu', 'email' => 'blocks@supplier.com', 'phone' => '+2348025544332', 'address' => 'Plot 88, Idu Industrial Zone, Abuja', 'bank_name' => 'Guaranty Trust Bank (GTB)', 'bank_account_number' => '0147852369', 'bank_account_name' => 'Apex Vibrated Products Ltd', 'payment_terms_days' => 14, 'rating' => 4.8, 'is_active' => true]
        );
        SupplierUser::updateOrCreate(
            ['email' => 'blocks@supplier.com'],
            ['supplier_id' => $supBlocks->id, 'name' => 'Chief Emeka Nwachukwu (Apex Blocks Rep)', 'password' => Hash::make('password123'), 'is_active' => true]
        );

        // 9. Operational Cycle #1: Hutu Prestige Cement
        $mrf1 = MaterialRequisition::updateOrCreate(
            ['ref_number' => 'MRF-2026-0001'],
            ['site_id' => $siteHutu->id, 'requested_by_user_id' => $siteManager->id, 'approved_by_user_id' => $qs->id, 'status' => 'approved', 'priority' => 'high', 'notes' => '1,000 bags cement for Block A & B 1st Floor Slab Concreting.', 'approved_at' => now()]
        );
        MaterialRequisitionItem::updateOrCreate(
            ['requisition_id' => $mrf1->id, 'material_id' => $matCement->id],
            ['qty_requested' => 1000, 'qty_approved' => 1000, 'unit_of_measure' => 'bags', 'estimated_unit_cost' => 8500.00]
        );

        $po1 = PurchaseOrder::updateOrCreate(
            ['ref_number' => 'PO-2026-0001'],
            ['site_id' => $siteHutu->id, 'supplier_id' => $supDangote->id, 'requisition_id' => $mrf1->id, 'created_by_user_id' => $qs->id, 'approved_by_user_id' => $admin->id, 'status' => 'delivered', 'subtotal_amount' => 8500000.00, 'tax_amount' => 0.00, 'delivery_fee' => 0.00, 'total_amount' => 8500000.00, 'approval_tier' => 'tier2', 'approved_at' => now()]
        );
        PurchaseOrderItem::updateOrCreate(
            ['purchase_order_id' => $po1->id, 'material_id' => $matCement->id],
            ['qty_ordered' => 1000, 'qty_delivered_cumulative' => 1000, 'unit_price' => 8500.00, 'total_price' => 8500000.00]
        );

        $grn1 = GoodsReceivedNote::updateOrCreate(
            ['ref_number' => 'GRN-2026-0001'],
            ['purchase_order_id' => $po1->id, 'site_id' => $siteHutu->id, 'received_by_user_id' => $storekeeper->id, 'delivery_date' => now()->subDays(5)->toDateString(), 'delivery_time' => '09:15', 'waybill_number' => 'WB-DAN-77881', 'driver_name' => 'Musa Garba', 'driver_phone' => '+2348039911223', 'vehicle_plate' => 'KJA-892-XA', 'delivery_gps_lat' => 9.0821000, 'delivery_gps_lng' => 7.4810500, 'geofence_check_passed' => true, 'status' => 'accepted', 'remarks' => '1,000 bags verified dry and intact by Storekeeper.']
        );
        GrnItem::updateOrCreate(
            ['grn_id' => $grn1->id, 'material_id' => $matCement->id],
            ['qty_received' => 1000, 'qty_rejected' => 0, 'batch_number' => 'LOT-DAN-001', 'unit_price_confirmed' => 8500.00]
        );

        $stkCement = SiteStock::updateOrCreate(
            ['site_id' => $siteHutu->id, 'material_id' => $matCement->id],
            ['qty_on_hand' => 585, 'qty_reserved' => 0, 'qty_quarantined' => 0]
        );
        StockBatch::updateOrCreate(
            ['site_stock_id' => $stkCement->id, 'batch_number' => 'LOT-DAN-001'],
            [
                'qty_received' => 1000,
                'qty_remaining' => 585,
                'received_on_grn_id' => $grn1->id,
                'qc_status' => 'pass',
                'qc_notes' => 'Factory certified Grade 42.5R Portland cement.',
            ]
        );

        $je1 = InventoryJournalEntry::updateOrCreate(
            ['entry_number' => 'JE-2026-00001'],
            ['entry_date' => now()->subDays(5)->toDateString(), 'reference_type' => GoodsReceivedNote::class, 'reference_id' => $grn1->id, 'site_id' => $siteHutu->id, 'project_id' => $proj1->id, 'description' => "Capitalize physical cement inventory from delivery {$grn1->ref_number} (PO: {$po1->ref_number})", 'total_debit' => 8500000.00, 'total_credit' => 8500000.00, 'is_balanced' => true, 'posted_by_user_id' => $storekeeper->id]
        );
        $je1->items()->delete();
        $je1->items()->createMany([
            ['account_code' => '1300', 'entry_type' => 'debit', 'amount' => 8500000.00, 'narration' => '1,000 bags cement received at Hutu Prestige Site'],
            ['account_code' => '2150', 'entry_type' => 'credit', 'amount' => 8500000.00, 'narration' => 'Unbilled GRNI delivery accrual for Dangote Ltd'],
        ]);

        $miv1 = MaterialIssueVoucher::updateOrCreate(
            ['ref_number' => 'MIV-2026-0001'],
            ['site_id' => $siteHutu->id, 'issued_by_user_id' => $storekeeper->id, 'received_by_user_id' => $siteManager->id, 'activity_name' => 'Block A & B 1st Floor Slab Concreting', 'work_quantity' => 57.14, 'work_unit' => 'm3', 'status' => 'issued', 'notes' => 'Disbursed to Site Engineer for slab casting.']
        );
        MivItem::updateOrCreate(
            ['miv_id' => $miv1->id, 'material_id' => $matCement->id],
            ['qty_requested' => 400, 'qty_issued' => 400, 'qty_returned' => 0]
        );

        $je2 = InventoryJournalEntry::updateOrCreate(
            ['entry_number' => 'JE-2026-00002'],
            ['entry_date' => now()->subDays(3)->toDateString(), 'reference_type' => MaterialIssueVoucher::class, 'reference_id' => $miv1->id, 'site_id' => $siteHutu->id, 'project_id' => $proj1->id, 'description' => "Material issuance {$miv1->ref_number} for activity: {$miv1->activity_name}", 'total_debit' => 3400000.00, 'total_credit' => 3400000.00, 'is_balanced' => true, 'posted_by_user_id' => $storekeeper->id]
        );
        $je2->items()->delete();
        $je2->items()->createMany([
            ['account_code' => '5100', 'entry_type' => 'debit', 'amount' => 3400000.00, 'narration' => 'Cement cost charged to Project #1 (1st Floor Slab)'],
            ['account_code' => '1300', 'entry_type' => 'credit', 'amount' => 3400000.00, 'narration' => '400 bags cement inventory stock asset deduction'],
        ]);

        $waste1 = WasteLog::updateOrCreate(
            ['description' => '15 bags cement damaged by sudden rainstorm during site staging.'],
            [
                'site_id' => $siteHutu->id,
                'material_id' => $matCement->id,
                'qty' => 15,
                'waste_type' => 'loss',
                'activity_name' => 'Offloading & Internal Staging',
                'weather_condition' => 'Heavy Rain',
                'logged_by_user_id' => $storekeeper->id,
            ]
        );

        $je3 = InventoryJournalEntry::updateOrCreate(
            ['entry_number' => 'JE-2026-00003'],
            ['entry_date' => now()->subDays(2)->toDateString(), 'reference_type' => WasteLog::class, 'reference_id' => $waste1->id, 'site_id' => $siteHutu->id, 'project_id' => $proj1->id, 'description' => "Material loss write-off: 15 bags Dangote Cement (loss)", 'total_debit' => 127500.00, 'total_credit' => 127500.00, 'is_balanced' => true, 'posted_by_user_id' => $storekeeper->id]
        );
        $je3->items()->delete();
        $je3->items()->createMany([
            ['account_code' => '5190', 'entry_type' => 'debit', 'amount' => 127500.00, 'narration' => 'Scrap loss expensed for 15 wet cement bags'],
            ['account_code' => '1300', 'entry_type' => 'credit', 'amount' => 127500.00, 'narration' => 'Asset write-off for damaged materials'],
        ]);

        $inv1 = SupplierInvoice::updateOrCreate(
            ['invoice_number' => 'INV-DAN-8891'],
            ['supplier_id' => $supDangote->id, 'purchase_order_id' => $po1->id, 'goods_received_note_id' => $grn1->id, 'invoice_date' => now()->subDays(4)->toDateString(), 'due_date' => now()->addDays(26)->toDateString(), 'total_amount' => 8500000.00, 'tax_amount' => 0.00, 'payment_status' => 'approved_for_payment', 'matched_by_user_id' => $accountant->id, 'matched_at' => now()->subDays(4), 'discrepancy_notes' => '3-Way Match verified successfully with zero price or quantity variance.', 'payment_approved_by_user_id' => $accountant->id, 'payment_approved_at' => now()->subDays(3)]
        );

        $je4 = InventoryJournalEntry::updateOrCreate(
            ['entry_number' => 'JE-2026-00004'],
            ['entry_date' => now()->subDays(4)->toDateString(), 'reference_type' => SupplierInvoice::class, 'reference_id' => $inv1->id, 'site_id' => $siteHutu->id, 'project_id' => $proj1->id, 'description' => "Reconcile vendor invoice INV-DAN-8891 (PO: {$po1->ref_number}) with 5% WHT deduction", 'total_debit' => 8500000.00, 'total_credit' => 8500000.00, 'is_balanced' => true, 'posted_by_user_id' => $accountant->id]
        );
        $je4->items()->delete();
        $je4->items()->createMany([
            ['account_code' => '2150', 'entry_type' => 'debit', 'amount' => 8500000.00, 'narration' => 'Clear GRNI accrual on invoice INV-DAN-8891'],
            ['account_code' => '2100', 'entry_type' => 'credit', 'amount' => 8075000.00, 'narration' => 'Net trade payable liability to Dangote Ltd (95%)'],
            ['account_code' => '2120', 'entry_type' => 'credit', 'amount' => 425000.00, 'narration' => '5% Withholding Tax (WHT) statutory deduction to FIRS'],
        ]);

        Expense::updateOrCreate(
            ['reference_number' => 'INV-DAN-8891'],
            ['user_id' => $accountant->id, 'approved_by' => $accountant->id, 'title' => 'Procurement: Dangote Building Solutions Ltd (INV-DAN-8891)', 'category' => 'Construction', 'amount' => 8500000.00, 'expense_date' => now()->subDays(4)->toDateString(), 'status' => 'approved', 'payment_method' => 'Bank Transfer', 'vendor_name' => $supDangote->name, 'notes' => 'Auto-posted from 3-Way Match for PO PO-2026-0001 (Site: Hutu Prestige Main Site Yard)', 'approved_at' => now()->subDays(3)]
        );

        // 10. Operational Cycle #2: Eko Atlantic Rebar
        $po2 = PurchaseOrder::updateOrCreate(
            ['ref_number' => 'PO-2026-0002'],
            ['site_id' => $siteEko->id, 'supplier_id' => $supSteel->id, 'created_by_user_id' => $qs->id, 'approved_by_user_id' => $admin->id, 'status' => 'delivered', 'subtotal_amount' => 27000000.00, 'tax_amount' => 0.00, 'delivery_fee' => 0.00, 'total_amount' => 27000000.00, 'approval_tier' => 'tier3', 'approved_at' => now()]
        );
        PurchaseOrderItem::updateOrCreate(
            ['purchase_order_id' => $po2->id, 'material_id' => $matRebar->id],
            ['qty_ordered' => 20, 'qty_delivered_cumulative' => 20, 'unit_price' => 1350000.00, 'total_price' => 27000000.00]
        );

        $grn2 = GoodsReceivedNote::updateOrCreate(
            ['ref_number' => 'GRN-2026-0002'],
            ['purchase_order_id' => $po2->id, 'site_id' => $siteEko->id, 'received_by_user_id' => $storekeeper->id, 'delivery_date' => now()->subDays(6)->toDateString(), 'delivery_time' => '14:20', 'waybill_number' => 'WB-STL-9001', 'driver_name' => 'Chinedu Eze', 'driver_phone' => '+2348058822334', 'vehicle_plate' => 'LND-452-BB', 'delivery_gps_lat' => 6.4181000, 'delivery_gps_lng' => 3.4150800, 'geofence_check_passed' => true, 'status' => 'accepted', 'remarks' => '20 tonnes 16mm rebar bundles verified with tensile test cert.']
        );
        GrnItem::updateOrCreate(
            ['grn_id' => $grn2->id, 'material_id' => $matRebar->id],
            ['qty_received' => 20, 'qty_rejected' => 0, 'batch_number' => 'LOT-STL-002', 'unit_price_confirmed' => 1350000.00]
        );

        $stkSteel = SiteStock::updateOrCreate(
            ['site_id' => $siteEko->id, 'material_id' => $matRebar->id],
            ['qty_on_hand' => 12, 'qty_reserved' => 0, 'qty_quarantined' => 0]
        );
        StockBatch::updateOrCreate(
            ['site_stock_id' => $stkSteel->id, 'batch_number' => 'LOT-STL-002'],
            [
                'qty_received' => 20,
                'qty_remaining' => 12,
                'received_on_grn_id' => $grn2->id,
                'qc_status' => 'pass',
                'qc_notes' => 'Tensile strength test certificate verified.',
            ]
        );

        $je5 = InventoryJournalEntry::updateOrCreate(
            ['entry_number' => 'JE-2026-00005'],
            ['entry_date' => now()->subDays(6)->toDateString(), 'reference_type' => GoodsReceivedNote::class, 'reference_id' => $grn2->id, 'site_id' => $siteEko->id, 'project_id' => $proj2->id, 'description' => "Capitalize physical steel inventory from delivery {$grn2->ref_number} (PO: {$po2->ref_number})", 'total_debit' => 27000000.00, 'total_credit' => 27000000.00, 'is_balanced' => true, 'posted_by_user_id' => $storekeeper->id]
        );
        $je5->items()->delete();
        $je5->items()->createMany([
            ['account_code' => '1300', 'entry_type' => 'debit', 'amount' => 27000000.00, 'narration' => '20 tonnes 16mm rebar received at Eko Atlantic Site'],
            ['account_code' => '2150', 'entry_type' => 'credit', 'amount' => 27000000.00, 'narration' => 'Unbilled delivery accrual for African Steel Mills'],
        ]);

        $miv2 = MaterialIssueVoucher::updateOrCreate(
            ['ref_number' => 'MIV-2026-0002'],
            ['site_id' => $siteEko->id, 'issued_by_user_id' => $storekeeper->id, 'received_by_user_id' => $siteManager->id, 'activity_name' => 'Basement Retaining Wall & Raft Foundation Reinforcement', 'work_quantity' => 120, 'work_unit' => 'm3', 'status' => 'issued', 'notes' => 'Disbursed to Lead Structural Engineer.']
        );
        MivItem::updateOrCreate(
            ['miv_id' => $miv2->id, 'material_id' => $matRebar->id],
            ['qty_requested' => 8, 'qty_issued' => 8, 'qty_returned' => 0]
        );

        $je6 = InventoryJournalEntry::updateOrCreate(
            ['entry_number' => 'JE-2026-00006'],
            ['entry_date' => now()->subDays(4)->toDateString(), 'reference_type' => MaterialIssueVoucher::class, 'reference_id' => $miv2->id, 'site_id' => $siteEko->id, 'project_id' => $proj2->id, 'description' => "Material issuance {$miv2->ref_number} for activity: {$miv2->activity_name}", 'total_debit' => 10800000.00, 'total_credit' => 10800000.00, 'is_balanced' => true, 'posted_by_user_id' => $storekeeper->id]
        );
        $je6->items()->delete();
        $je6->items()->createMany([
            ['account_code' => '5100', 'entry_type' => 'debit', 'amount' => 10800000.00, 'narration' => 'Steel rebar cost charged to Project #2 (Basement Raft)'],
            ['account_code' => '1300', 'entry_type' => 'credit', 'amount' => 10800000.00, 'narration' => '8 tonnes steel rebar inventory asset deduction'],
        ]);

        $inv2 = SupplierInvoice::updateOrCreate(
            ['invoice_number' => 'INV-STL-9902'],
            ['supplier_id' => $supSteel->id, 'purchase_order_id' => $po2->id, 'goods_received_note_id' => $grn2->id, 'invoice_date' => now()->subDays(2)->toDateString(), 'due_date' => now()->addDays(43)->toDateString(), 'total_amount' => 29700000.00, 'tax_amount' => 0.00, 'payment_status' => 'disputed', 'matched_by_user_id' => $accountant->id, 'matched_at' => now()->subDays(2), 'discrepancy_notes' => 'Price Discrepancy: Invoice billed ₦29,700,000.00 vs verified PO/GRN expected ₦27,000,000.00 (Variance: ₦2,700,000.00 / 10.00%).']
        );

        InventoryAnomalyFlag::updateOrCreate(
            ['title' => "Invoice Overbilling on {$po2->ref_number}"],
            ['site_id' => $siteEko->id, 'flag_type' => 'price_spike', 'severity' => 'high', 'description' => "Supplier Invoice INV-STL-9902 on PO {$po2->ref_number} overbills verified amount by 10.00% (₦2,700,000.00).", 'flaggable_type' => SupplierInvoice::class, 'flaggable_id' => $inv2->id, 'status' => 'open']
        );

        // 11. Operational Cycle #3: Guzape Hills Blocks
        $po3 = PurchaseOrder::updateOrCreate(
            ['ref_number' => 'PO-2026-0003'],
            ['site_id' => $siteGuzape->id, 'supplier_id' => $supBlocks->id, 'created_by_user_id' => $qs->id, 'approved_by_user_id' => $admin->id, 'status' => 'delivered', 'subtotal_amount' => 2400000.00, 'tax_amount' => 0.00, 'delivery_fee' => 0.00, 'total_amount' => 2400000.00, 'approval_tier' => 'tier1', 'approved_at' => now()]
        );
        PurchaseOrderItem::updateOrCreate(
            ['purchase_order_id' => $po3->id, 'material_id' => $matBlocks->id],
            ['qty_ordered' => 5000, 'qty_delivered_cumulative' => 5000, 'unit_price' => 480.00, 'total_price' => 2400000.00]
        );

        $grn3 = GoodsReceivedNote::updateOrCreate(
            ['ref_number' => 'GRN-2026-0003'],
            ['purchase_order_id' => $po3->id, 'site_id' => $siteGuzape->id, 'received_by_user_id' => $storekeeper->id, 'delivery_date' => now()->subDays(1)->toDateString(), 'delivery_time' => '11:00', 'waybill_number' => 'WB-BLK-5002', 'driver_name' => 'Kabiru Bello', 'driver_phone' => '+2348037711445', 'vehicle_plate' => 'ABC-123-XY', 'delivery_gps_lat' => 9.0351000, 'delivery_gps_lng' => 7.5120500, 'geofence_check_passed' => true, 'status' => 'accepted', 'remarks' => '5,000 sandcrete blocks verified cured and stacked.']
        );
        GrnItem::updateOrCreate(
            ['grn_id' => $grn3->id, 'material_id' => $matBlocks->id],
            ['qty_received' => 5000, 'qty_rejected' => 0, 'batch_number' => 'LOT-BLK-003', 'unit_price_confirmed' => 480.00]
        );

        $stkBlocks = SiteStock::updateOrCreate(
            ['site_id' => $siteGuzape->id, 'material_id' => $matBlocks->id],
            ['qty_on_hand' => 5000, 'qty_reserved' => 0, 'qty_quarantined' => 0]
        );
        StockBatch::updateOrCreate(
            ['site_stock_id' => $stkBlocks->id, 'batch_number' => 'LOT-BLK-003'],
            [
                'qty_received' => 5000,
                'qty_remaining' => 5000,
                'received_on_grn_id' => $grn3->id,
                'qc_status' => 'pass',
                'qc_notes' => 'Cured and load-bearing test verified.',
            ]
        );

        $je7 = InventoryJournalEntry::updateOrCreate(
            ['entry_number' => 'JE-2026-00007'],
            ['entry_date' => now()->subDays(1)->toDateString(), 'reference_type' => GoodsReceivedNote::class, 'reference_id' => $grn3->id, 'site_id' => $siteGuzape->id, 'project_id' => $proj3->id, 'description' => "Capitalize physical blocks inventory from delivery {$grn3->ref_number} (PO: {$po3->ref_number})", 'total_debit' => 2400000.00, 'total_credit' => 2400000.00, 'is_balanced' => true, 'posted_by_user_id' => $storekeeper->id]
        );
        $je7->items()->delete();
        $je7->items()->createMany([
            ['account_code' => '1300', 'entry_type' => 'debit', 'amount' => 2400000.00, 'narration' => '5,000 blocks received at Guzape Hills Site'],
            ['account_code' => '2150', 'entry_type' => 'credit', 'amount' => 2400000.00, 'narration' => 'Unbilled GRNI delivery accrual for Apex Vibrated Products'],
        ]);

        InventoryAnomalyFlag::updateOrCreate(
            ['title' => 'Ghost Delivery Warning on GRN-2026-0003-SAMPLE'],
            ['site_id' => $siteGuzape->id, 'flag_type' => 'ghost_delivery', 'severity' => 'critical', 'description' => 'Delivery scan logged 1.8km away from Guzape Hills compound geofence perimeter.', 'flaggable_type' => GoodsReceivedNote::class, 'flaggable_id' => $grn3->id, 'status' => 'open']
        );
    }
}
