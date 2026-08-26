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
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InventoryDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get or Create Core System Users
        $admin = User::where('role', 'company_admin')->first() ?? User::first();
        $accountant = User::where('role', 'accountant')->first() ?? $admin;

        // 2. Company Inventory Thresholds & Geofence Defaults
        CompanyInventorySetting::updateOrCreate(
            ['id' => 1],
            [
                'po_tier1_max' => 5000000.00,    // <= ₦5m Store Manager/PM
                'po_tier2_max' => 20000000.00,   // <= ₦20m MD / Executive
                // > ₦20m Board Tier 3
                'price_variance_alert_pct' => 5.00,
                'geofence_enforcement' => true,
                'waste_threshold_pct' => 3.00,
                'require_two_step_miv' => true,
            ]
        );

        // 3. Create 3 Real Estate Construction Projects
        $proj1 = Project::firstOrCreate(
            ['name' => 'Hutu Prestige Smart Estate'],
            [
                'location' => 'Katampe Extension, Abuja',
                'type' => 'residential',
                'status' => 'in_progress',
                'description' => '45 Units of 4-Bedroom Terrace Duplexes with Smart Home Automation.',
            ]
        );

        $proj2 = Project::firstOrCreate(
            ['name' => 'Eko Atlantic Boulevard Towers'],
            [
                'location' => 'Victoria Island, Lagos',
                'type' => 'commercial',
                'status' => 'in_progress',
                'description' => '18-Floor Mixed-Use Luxury Waterfront Commercial & Residential Complex.',
            ]
        );

        $proj3 = Project::firstOrCreate(
            ['name' => 'Guzape Hills Luxury Villas'],
            [
                'location' => 'Guzape District, Abuja',
                'type' => 'residential',
                'status' => 'in_progress',
                'description' => '12 Units of 5-Bedroom Fully Detached Contemporary Smart Mansions.',
            ]
        );

        // 4. Create 3 Dedicated Inventory Sites / Material Yards
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

        // 5. Material Catalogue Master Setup
        $matCement = MaterialCatalogue::updateOrCreate(
            ['code' => 'CEM-DAN-50KG'],
            [
                'name' => 'Dangote Falcon Cement 50kg',
                'category' => 'cement',
                'unit_of_measure' => 'bags',
                'standard_unit_cost' => 8500.00,
                'reorder_level' => 300,
                'safety_stock_level' => 100,
                'description' => 'Grade 42.5R high-strength Portland limestone cement.',
                'is_active' => true,
            ]
        );

        $matRebar = MaterialCatalogue::updateOrCreate(
            ['code' => 'STL-TMT-16MM'],
            [
                'name' => 'High-Yield TMT Rebar 16mm',
                'category' => 'steel',
                'unit_of_measure' => 'tonnes',
                'standard_unit_cost' => 1350000.00,
                'reorder_level' => 15,
                'safety_stock_level' => 5,
                'description' => 'Fe500D earthquake-resistant structural steel reinforcement.',
                'is_active' => true,
            ]
        );

        $matSand = MaterialCatalogue::updateOrCreate(
            ['code' => 'AGG-SAND-20T'],
            [
                'name' => 'Sharp River Sand (20-Tonne Tipper)',
                'category' => 'aggregate',
                'unit_of_measure' => 'trips',
                'standard_unit_cost' => 120000.00,
                'reorder_level' => 10,
                'safety_stock_level' => 3,
                'description' => 'Washed coarse river sand for structural concrete cast.',
                'is_active' => true,
            ]
        );

        $matGranite = MaterialCatalogue::updateOrCreate(
            ['code' => 'AGG-GRN-30T'],
            [
                'name' => '3/4 Clean Granite Aggregate (30-Tonne Tipper)',
                'category' => 'aggregate',
                'unit_of_measure' => 'trips',
                'standard_unit_cost' => 280000.00,
                'reorder_level' => 8,
                'safety_stock_level' => 2,
                'description' => 'Machine-crushed unweathered hard granite stone.',
                'is_active' => true,
            ]
        );

        $matBlocks = MaterialCatalogue::updateOrCreate(
            ['code' => 'BLK-9IN-VIB'],
            [
                'name' => 'Vibrated 9-Inch Solid Sandcrete Blocks',
                'category' => 'masonry',
                'unit_of_measure' => 'pieces',
                'standard_unit_cost' => 480.00,
                'reorder_level' => 2000,
                'safety_stock_level' => 500,
                'description' => 'High-density vibrated load-bearing building blocks.',
                'is_active' => true,
            ]
        );

        $matTiles = MaterialCatalogue::updateOrCreate(
            ['code' => 'FIN-TILE-60X60'],
            [
                'name' => 'Royal Porcelain Vitrified Floor Tiles (600x600mm)',
                'category' => 'finishing',
                'unit_of_measure' => 'cartons',
                'standard_unit_cost' => 12500.00,
                'reorder_level' => 150,
                'safety_stock_level' => 40,
                'description' => 'Polished nano-sealed non-slip vitrified porcelain tiles.',
                'is_active' => true,
            ]
        );

        // 6. Regional Market Price Benchmarks
        PriceBenchmark::updateOrCreate(
            ['material_id' => $matCement->id, 'region_state' => 'Abuja (FCT)'],
            ['average_unit_price' => 8500.00, 'min_price' => 8200.00, 'max_price' => 8900.00, 'source' => 'Abuja Builders Merchant Survey Q3 2026', 'survey_date' => now()->toDateString(), 'recorded_by_user_id' => $admin->id]
        );
        PriceBenchmark::updateOrCreate(
            ['material_id' => $matRebar->id, 'region_state' => 'Lagos'],
            ['average_unit_price' => 1350000.00, 'min_price' => 1300000.00, 'max_price' => 1420000.00, 'source' => 'Lagos Steel Importers Association', 'survey_date' => now()->toDateString(), 'recorded_by_user_id' => $admin->id]
        );

        // 7. Quantity Surveyor (QS) Bill of Materials (BOM) Templates
        BomTemplate::updateOrCreate(
            ['activity_name' => 'Grade 25 Concrete Slab Casting (1 m3)'],
            [
                'material_id' => $matCement->id,
                'unit_of_work' => 'm3',
                'standard_coefficient' => 7.00, // 7 bags cement per 1 m3
                'waste_allowance_pct' => 3.00,
                'specifications' => '1:2:4 Mix Ratio for 25 N/mm2 structural deck slab.',
                'created_by_user_id' => $admin->id,
            ]
        );

        // 8. Register Suppliers & Create Portal Users
        $supDangote = Supplier::updateOrCreate(
            ['code' => 'SUP-DAN-001'],
            [
                'name' => 'Dangote Building Solutions Ltd',
                'contact_person' => 'Alhaji Lawal Danjuma',
                'email' => 'dangote@supplier.com',
                'phone' => '+2348031122334',
                'address' => 'Marble House, Kingsway Road, Ikoyi, Lagos',
                'bank_name' => 'Access Bank Plc',
                'bank_account_number' => '0012345678',
                'bank_account_name' => 'Dangote Building Solutions Ltd',
                'payment_terms_days' => 30,
                'rating' => 4.9,
                'is_active' => true,
            ]
        );

        SupplierUser::updateOrCreate(
            ['email' => 'dangote@supplier.com'],
            [
                'supplier_id' => $supDangote->id,
                'name' => 'Lawal Danjuma (Account Rep)',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        $supSteel = Supplier::updateOrCreate(
            ['code' => 'SUP-STL-002'],
            [
                'name' => 'African Steel & Rebar Mills Ltd',
                'contact_person' => 'Engr. Victor Obi',
                'email' => 'steel@supplier.com',
                'phone' => '+2348039988776',
                'address' => 'Kilometer 12, Ikorodu Industrial Estate, Lagos',
                'bank_name' => 'Zenith Bank Plc',
                'bank_account_number' => '1019876543',
                'bank_account_name' => 'African Steel & Rebar Mills Ltd',
                'payment_terms_days' => 45,
                'rating' => 4.7,
                'is_active' => true,
            ]
        );

        SupplierUser::updateOrCreate(
            ['email' => 'steel@supplier.com'],
            [
                'supplier_id' => $supSteel->id,
                'name' => 'Victor Obi (Sales Director)',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        $supBlocks = Supplier::updateOrCreate(
            ['code' => 'SUP-BLK-003'],
            [
                'name' => 'Apex Vibrated Concrete Products Ltd',
                'contact_person' => 'Chief Emeka Nwachukwu',
                'email' => 'blocks@supplier.com',
                'phone' => '+2348025544332',
                'address' => 'Plot 88, Idu Industrial Zone, Abuja',
                'bank_name' => 'Guaranty Trust Bank (GTB)',
                'bank_account_number' => '0147852369',
                'bank_account_name' => 'Apex Vibrated Products Ltd',
                'payment_terms_days' => 14,
                'rating' => 4.8,
                'is_active' => true,
            ]
        );

        // 9. OPERATIONAL CYCLE #1: Hutu Prestige Cement Procurement
        // Step A: MRF
        $mrf1 = MaterialRequisition::updateOrCreate(
            ['ref_number' => 'MRF-2026-0001'],
            [
                'site_id' => $siteHutu->id,
                'requested_by_user_id' => $admin->id,
                'approved_by_user_id' => $admin->id,
                'status' => 'approved',
                'priority' => 'high',
                'notes' => '1,000 bags cement for Block A & B 1st Floor Slab Concreting.',
                'approved_at' => now(),
            ]
        );
        MaterialRequisitionItem::updateOrCreate(
            ['requisition_id' => $mrf1->id, 'material_id' => $matCement->id],
            ['qty_requested' => 1000, 'qty_approved' => 1000, 'unit_of_measure' => 'bags', 'estimated_unit_cost' => 8500.00]
        );

        // Step B: PO (1,000 bags @ ₦8,500 = ₦8,500,000 -> Approved Tier 2)
        $po1 = PurchaseOrder::updateOrCreate(
            ['ref_number' => 'PO-2026-0001'],
            [
                'site_id' => $siteHutu->id,
                'supplier_id' => $supDangote->id,
                'requisition_id' => $mrf1->id,
                'created_by_user_id' => $admin->id,
                'approved_by_user_id' => $admin->id,
                'status' => 'delivered',
                'subtotal_amount' => 8500000.00,
                'tax_amount' => 0.00,
                'delivery_fee' => 0.00,
                'total_amount' => 8500000.00,
                'approval_tier' => 'tier2',
                'approved_at' => now(),
            ]
        );
        PurchaseOrderItem::updateOrCreate(
            ['purchase_order_id' => $po1->id, 'material_id' => $matCement->id],
            ['qty_ordered' => 1000, 'qty_delivered_cumulative' => 1000, 'unit_price' => 8500.00, 'total_price' => 8500000.00]
        );

        // Step C: Gate Delivery GRN (Stock Credited)
        $grn1 = GoodsReceivedNote::updateOrCreate(
            ['ref_number' => 'GRN-2026-0001'],
            [
                'purchase_order_id' => $po1->id,
                'site_id' => $siteHutu->id,
                'received_by_user_id' => $admin->id,
                'delivery_date' => now()->subDays(5)->toDateString(),
                'delivery_time' => '09:15',
                'waybill_number' => 'WB-DAN-77881',
                'driver_name' => 'Musa Garba',
                'driver_phone' => '+2348039911223',
                'vehicle_plate' => 'KJA-892-XA',
                'delivery_gps_lat' => 9.0821000,
                'delivery_gps_lng' => 7.4810500,
                'geofence_check_passed' => true,
                'status' => 'accepted',
                'remarks' => '1,000 bags verified dry and intact.',
            ]
        );
        GrnItem::updateOrCreate(
            ['grn_id' => $grn1->id, 'material_id' => $matCement->id],
            ['qty_received' => 1000, 'qty_rejected' => 0, 'batch_number' => 'LOT-DAN-001', 'unit_price_confirmed' => 8500.00]
        );

        // Active Stock Batch
        StockBatch::updateOrCreate(
            ['batch_number' => 'LOT-DAN-001'],
            [
                'site_id' => $siteHutu->id,
                'material_id' => $matCement->id,
                'grn_id' => $grn1->id,
                'initial_qty' => 1000,
                'current_qty' => 585, // 1000 - 400 issued - 15 waste = 585 remaining
                'unit_cost' => 8500.00,
                'received_date' => now()->subDays(5)->toDateString(),
                'status' => 'active',
            ]
        );

        SiteStock::updateOrCreate(
            ['site_id' => $siteHutu->id, 'material_id' => $matCement->id],
            ['qty_on_hand' => 585, 'qty_reserved' => 0, 'reorder_status' => 'healthy']
        );

        // Step D: Journal #1 for GRN (Debit 1300 Asset ₦8.5m, Credit 2150 GRNI ₦8.5m)
        $je1 = InventoryJournalEntry::updateOrCreate(
            ['entry_number' => 'JE-2026-00001'],
            [
                'entry_date' => now()->subDays(5)->toDateString(),
                'reference_type' => GoodsReceivedNote::class,
                'reference_id' => $grn1->id,
                'site_id' => $siteHutu->id,
                'project_id' => $proj1->id,
                'description' => "Capitalize physical cement inventory from delivery {$grn1->ref_number} (PO: {$po1->ref_number})",
                'total_debit' => 8500000.00,
                'total_credit' => 8500000.00,
                'is_balanced' => true,
                'posted_by_user_id' => $admin->id,
            ]
        );
        $je1->items()->delete();
        $je1->items()->createMany([
            ['account_code' => '1300', 'entry_type' => 'debit', 'amount' => 8500000.00, 'narration' => '1,000 bags cement received at Hutu Prestige Site'],
            ['account_code' => '2150', 'entry_type' => 'credit', 'amount' => 8500000.00, 'narration' => 'Unbilled GRNI delivery accrual for Dangote Ltd'],
        ]);

        // Step E: Issue MIV (400 bags to 1st Floor Slab Cast = ₦3,400,000)
        $miv1 = MaterialIssueVoucher::updateOrCreate(
            ['ref_number' => 'MIV-2026-0001'],
            [
                'site_id' => $siteHutu->id,
                'issued_by_user_id' => $admin->id,
                'received_by_user_id' => $admin->id,
                'activity_name' => 'Block A & B 1st Floor Slab Concreting',
                'work_quantity' => 57.14,
                'work_unit' => 'm3',
                'status' => 'issued',
                'notes' => 'Disbursed to Site Engineer for slab casting.',
            ]
        );
        MivItem::updateOrCreate(
            ['miv_id' => $miv1->id, 'material_id' => $matCement->id],
            ['qty_requested' => 400, 'qty_issued' => 400, 'qty_returned' => 0]
        );

        // Journal #2 for MIV (Debit 5100 Direct WIP ₦3.4m, Credit 1300 Asset ₦3.4m)
        $je2 = InventoryJournalEntry::updateOrCreate(
            ['entry_number' => 'JE-2026-00002'],
            [
                'entry_date' => now()->subDays(3)->toDateString(),
                'reference_type' => MaterialIssueVoucher::class,
                'reference_id' => $miv1->id,
                'site_id' => $siteHutu->id,
                'project_id' => $proj1->id,
                'description' => "Material issuance {$miv1->ref_number} for activity: {$miv1->activity_name}",
                'total_debit' => 3400000.00,
                'total_credit' => 3400000.00,
                'is_balanced' => true,
                'posted_by_user_id' => $admin->id,
            ]
        );
        $je2->items()->delete();
        $je2->items()->createMany([
            ['account_code' => '5100', 'entry_type' => 'debit', 'amount' => 3400000.00, 'narration' => 'Cement cost charged to Project #1 (1st Floor Slab)'],
            ['account_code' => '1300', 'entry_type' => 'credit', 'amount' => 3400000.00, 'narration' => '400 bags cement inventory stock asset deduction'],
        ]);

        // Step F: Waste Log (15 bags rain damaged = ₦127,500)
        $waste1 = WasteLog::updateOrCreate(
            ['description' => '15 bags cement damaged by sudden rainstorm during site staging.'],
            [
                'site_id' => $siteHutu->id,
                'material_id' => $matCement->id,
                'qty' => 15,
                'waste_type' => 'weather_damage',
                'activity_name' => 'Offloading & Internal Staging',
                'weather_condition' => 'Heavy Rain',
                'logged_by_user_id' => $admin->id,
            ]
        );

        // Journal #3 for Waste (Debit 5190 Scrap Loss ₦127.5k, Credit 1300 Asset ₦127.5k)
        $je3 = InventoryJournalEntry::updateOrCreate(
            ['entry_number' => 'JE-2026-00003'],
            [
                'entry_date' => now()->subDays(2)->toDateString(),
                'reference_type' => WasteLog::class,
                'reference_id' => $waste1->id,
                'site_id' => $siteHutu->id,
                'project_id' => $proj1->id,
                'description' => "Material loss write-off: 15 bags Dangote Cement (weather_damage)",
                'total_debit' => 127500.00,
                'total_credit' => 127500.00,
                'is_balanced' => true,
                'posted_by_user_id' => $admin->id,
            ]
        );
        $je3->items()->delete();
        $je3->items()->createMany([
            ['account_code' => '5190', 'entry_type' => 'debit', 'amount' => 127500.00, 'narration' => 'Scrap loss expensed for 15 wet cement bags'],
            ['account_code' => '1300', 'entry_type' => 'credit', 'amount' => 127500.00, 'narration' => 'Asset write-off for damaged materials'],
        ]);

        // Step G: 3-Way Matched Supplier Invoice (Dangote billed ₦8,500,000 -> Matched 100%!)
        $inv1 = SupplierInvoice::updateOrCreate(
            ['invoice_number' => 'INV-DAN-8891'],
            [
                'supplier_id' => $supDangote->id,
                'purchase_order_id' => $po1->id,
                'goods_received_note_id' => $grn1->id,
                'invoice_date' => now()->subDays(4)->toDateString(),
                'due_date' => now()->addDays(26)->toDateString(),
                'total_amount' => 8500000.00,
                'tax_amount' => 0.00,
                'payment_status' => 'approved_for_payment',
                'matched_by_user_id' => $accountant->id,
                'matched_at' => now()->subDays(4),
                'discrepancy_notes' => '3-Way Match verified successfully with zero price or quantity variance.',
                'payment_approved_by_user_id' => $accountant->id,
                'payment_approved_at' => now()->subDays(3),
            ]
        );

        // Journal #4 for Invoice 3-Way Match (Clear GRNI ₦8.5m -> AP Net ₦8.075m + WHT 5% ₦425k)
        $je4 = InventoryJournalEntry::updateOrCreate(
            ['entry_number' => 'JE-2026-00004'],
            [
                'entry_date' => now()->subDays(4)->toDateString(),
                'reference_type' => SupplierInvoice::class,
                'reference_id' => $inv1->id,
                'site_id' => $siteHutu->id,
                'project_id' => $proj1->id,
                'description' => "Reconcile vendor invoice INV-DAN-8891 (PO: {$po1->ref_number}) with 5% WHT deduction",
                'total_debit' => 8500000.00,
                'total_credit' => 8500000.00,
                'is_balanced' => true,
                'posted_by_user_id' => $accountant->id,
            ]
        );
        $je4->items()->delete();
        $je4->items()->createMany([
            ['account_code' => '2150', 'entry_type' => 'debit', 'amount' => 8500000.00, 'narration' => 'Clear GRNI accrual on invoice INV-DAN-8891'],
            ['account_code' => '2100', 'entry_type' => 'credit', 'amount' => 8075000.00, 'narration' => 'Net trade payable liability to Dangote Ltd (95%)'],
            ['account_code' => '2120', 'entry_type' => 'credit', 'amount' => 425000.00, 'narration' => '5% Withholding Tax (WHT) statutory deduction to FIRS'],
        ]);

        Expense::updateOrCreate(
            ['reference_number' => 'INV-DAN-8891'],
            [
                'user_id' => $accountant->id,
                'approved_by' => $accountant->id,
                'title' => 'Procurement: Dangote Building Solutions Ltd (INV-DAN-8891)',
                'category' => 'Construction',
                'amount' => 8500000.00,
                'expense_date' => now()->subDays(4)->toDateString(),
                'status' => 'approved',
                'payment_method' => 'Bank Transfer',
                'vendor_name' => $supDangote->name,
                'notes' => 'Auto-posted from 3-Way Match for PO PO-2026-0001 (Site: Hutu Prestige Main Site Yard)',
                'approved_at' => now()->subDays(3),
            ]
        );

        // 10. OPERATIONAL CYCLE #2: Eko Atlantic Rebar Procurement (20 tonnes @ ₦1.35m = ₦27,000,000)
        $po2 = PurchaseOrder::updateOrCreate(
            ['ref_number' => 'PO-2026-0002'],
            [
                'site_id' => $siteEko->id,
                'supplier_id' => $supSteel->id,
                'created_by_user_id' => $admin->id,
                'approved_by_user_id' => $admin->id,
                'status' => 'delivered',
                'subtotal_amount' => 27000000.00,
                'tax_amount' => 0.00,
                'delivery_fee' => 0.00,
                'total_amount' => 27000000.00,
                'approval_tier' => 'tier3',
                'approved_at' => now(),
            ]
        );
        PurchaseOrderItem::updateOrCreate(
            ['purchase_order_id' => $po2->id, 'material_id' => $matRebar->id],
            ['qty_ordered' => 20, 'qty_delivered_cumulative' => 20, 'unit_price' => 1350000.00, 'total_price' => 27000000.00]
        );

        $grn2 = GoodsReceivedNote::updateOrCreate(
            ['ref_number' => 'GRN-2026-0002'],
            [
                'purchase_order_id' => $po2->id,
                'site_id' => $siteEko->id,
                'received_by_user_id' => $admin->id,
                'delivery_date' => now()->subDays(6)->toDateString(),
                'delivery_time' => '14:20',
                'waybill_number' => 'WB-STL-9001',
                'driver_name' => 'Chinedu Eze',
                'driver_phone' => '+2348058822334',
                'vehicle_plate' => 'LND-452-BB',
                'delivery_gps_lat' => 6.4181000,
                'delivery_gps_lng' => 3.4150800,
                'geofence_check_passed' => true,
                'status' => 'accepted',
                'remarks' => '20 tonnes 16mm rebar bundles verified with tensile test cert.',
            ]
        );
        GrnItem::updateOrCreate(
            ['grn_id' => $grn2->id, 'material_id' => $matRebar->id],
            ['qty_received' => 20, 'qty_rejected' => 0, 'batch_number' => 'LOT-STL-002', 'unit_price_confirmed' => 1350000.00]
        );

        StockBatch::updateOrCreate(
            ['batch_number' => 'LOT-STL-002'],
            [
                'site_id' => $siteEko->id,
                'material_id' => $matRebar->id,
                'grn_id' => $grn2->id,
                'initial_qty' => 20,
                'current_qty' => 12, // 20 - 8 issued = 12 remaining
                'unit_cost' => 1350000.00,
                'received_date' => now()->subDays(6)->toDateString(),
                'status' => 'active',
            ]
        );

        SiteStock::updateOrCreate(
            ['site_id' => $siteEko->id, 'material_id' => $matRebar->id],
            ['qty_on_hand' => 12, 'qty_reserved' => 0, 'reorder_status' => 'healthy']
        );

        // Journal #5 for Eko GRN (Debit 1300 Asset ₦27m, Credit 2150 GRNI ₦27m)
        $je5 = InventoryJournalEntry::updateOrCreate(
            ['entry_number' => 'JE-2026-00005'],
            [
                'entry_date' => now()->subDays(6)->toDateString(),
                'reference_type' => GoodsReceivedNote::class,
                'reference_id' => $grn2->id,
                'site_id' => $siteEko->id,
                'project_id' => $proj2->id,
                'description' => "Capitalize physical steel inventory from delivery {$grn2->ref_number} (PO: {$po2->ref_number})",
                'total_debit' => 27000000.00,
                'total_credit' => 27000000.00,
                'is_balanced' => true,
                'posted_by_user_id' => $admin->id,
            ]
        );
        $je5->items()->delete();
        $je5->items()->createMany([
            ['account_code' => '1300', 'entry_type' => 'debit', 'amount' => 27000000.00, 'narration' => '20 tonnes 16mm rebar received at Eko Atlantic Site'],
            ['account_code' => '2150', 'entry_type' => 'credit', 'amount' => 27000000.00, 'narration' => 'Unbilled delivery accrual for African Steel Mills'],
        ]);

        // Issue 8 tonnes to Basement Retaining Wall (8 * ₦1,350,000 = ₦10,800,000)
        $miv2 = MaterialIssueVoucher::updateOrCreate(
            ['ref_number' => 'MIV-2026-0002'],
            [
                'site_id' => $siteEko->id,
                'issued_by_user_id' => $admin->id,
                'received_by_user_id' => $admin->id,
                'activity_name' => 'Basement Retaining Wall & Raft Foundation Reinforcement',
                'work_quantity' => 120,
                'work_unit' => 'm3',
                'status' => 'issued',
                'notes' => 'Disbursed to Lead Structural Engineer.',
            ]
        );
        MivItem::updateOrCreate(
            ['miv_id' => $miv2->id, 'material_id' => $matRebar->id],
            ['qty_requested' => 8, 'qty_issued' => 8, 'qty_returned' => 0]
        );

        // Journal #6 for MIV #2 (Debit 5100 Direct WIP ₦10.8m, Credit 1300 Asset ₦10.8m)
        $je6 = InventoryJournalEntry::updateOrCreate(
            ['entry_number' => 'JE-2026-00006'],
            [
                'entry_date' => now()->subDays(4)->toDateString(),
                'reference_type' => MaterialIssueVoucher::class,
                'reference_id' => $miv2->id,
                'site_id' => $siteEko->id,
                'project_id' => $proj2->id,
                'description' => "Material issuance {$miv2->ref_number} for activity: {$miv2->activity_name}",
                'total_debit' => 10800000.00,
                'total_credit' => 10800000.00,
                'is_balanced' => true,
                'posted_by_user_id' => $admin->id,
            ]
        );
        $je6->items()->delete();
        $je6->items()->createMany([
            ['account_code' => '5100', 'entry_type' => 'debit', 'amount' => 10800000.00, 'narration' => 'Steel rebar cost charged to Project #2 (Basement Raft)'],
            ['account_code' => '1300', 'entry_type' => 'credit', 'amount' => 10800000.00, 'narration' => '8 tonnes steel rebar inventory asset deduction'],
        ]);

        // Overbilled Supplier Invoice for Fraud Radar Testing
        $inv2 = SupplierInvoice::updateOrCreate(
            ['invoice_number' => 'INV-STL-9902'],
            [
                'supplier_id' => $supSteel->id,
                'purchase_order_id' => $po2->id,
                'goods_received_note_id' => $grn2->id,
                'invoice_date' => now()->subDays(2)->toDateString(),
                'due_date' => now()->addDays(43)->toDateString(),
                'total_amount' => 29700000.00, // ₦2.7m Overbilling (10%)
                'tax_amount' => 0.00,
                'payment_status' => 'disputed',
                'matched_by_user_id' => $accountant->id,
                'matched_at' => now()->subDays(2),
                'discrepancy_notes' => 'Price Discrepancy: Invoice billed ₦29,700,000.00 vs verified PO/GRN expected ₦27,000,000.00 (Variance: ₦2,700,000.00 / 10.00%).',
            ]
        );

        // Flag high severity anomaly on Fraud Radar
        InventoryAnomalyFlag::updateOrCreate(
            ['title' => "Invoice Overbilling on {$po2->ref_number}"],
            [
                'site_id' => $siteEko->id,
                'flag_type' => 'price_spike',
                'severity' => 'high',
                'description' => "Supplier Invoice INV-STL-9902 on PO {$po2->ref_number} overbills verified amount by 10.00% (₦2,700,000.00).",
                'flaggable_type' => SupplierInvoice::class,
                'flaggable_id' => $inv2->id,
                'status' => 'open',
            ]
        );

        // 11. OPERATIONAL CYCLE #3: Guzape Hills Sandcrete Blocks (5,000 blocks @ ₦480 = ₦2,400,000)
        $po3 = PurchaseOrder::updateOrCreate(
            ['ref_number' => 'PO-2026-0003'],
            [
                'site_id' => $siteGuzape->id,
                'supplier_id' => $supBlocks->id,
                'created_by_user_id' => $admin->id,
                'approved_by_user_id' => $admin->id,
                'status' => 'delivered',
                'subtotal_amount' => 2400000.00,
                'tax_amount' => 0.00,
                'delivery_fee' => 0.00,
                'total_amount' => 2400000.00,
                'approval_tier' => 'tier1',
                'approved_at' => now(),
            ]
        );
        PurchaseOrderItem::updateOrCreate(
            ['purchase_order_id' => $po3->id, 'material_id' => $matBlocks->id],
            ['qty_ordered' => 5000, 'qty_delivered_cumulative' => 5000, 'unit_price' => 480.00, 'total_price' => 2400000.00]
        );

        $grn3 = GoodsReceivedNote::updateOrCreate(
            ['ref_number' => 'GRN-2026-0003'],
            [
                'purchase_order_id' => $po3->id,
                'site_id' => $siteGuzape->id,
                'received_by_user_id' => $admin->id,
                'delivery_date' => now()->subDays(1)->toDateString(),
                'delivery_time' => '11:00',
                'waybill_number' => 'WB-BLK-5002',
                'driver_name' => 'Kabiru Bello',
                'driver_phone' => '+2348037711445',
                'vehicle_plate' => 'ABC-123-XY',
                'delivery_gps_lat' => 9.0351000,
                'delivery_gps_lng' => 7.5120500,
                'geofence_check_passed' => true,
                'status' => 'accepted',
                'remarks' => '5,000 sandcrete blocks verified cured and stacked.',
            ]
        );
        GrnItem::updateOrCreate(
            ['grn_id' => $grn3->id, 'material_id' => $matBlocks->id],
            ['qty_received' => 5000, 'qty_rejected' => 0, 'batch_number' => 'LOT-BLK-003', 'unit_price_confirmed' => 480.00]
        );

        StockBatch::updateOrCreate(
            ['batch_number' => 'LOT-BLK-003'],
            [
                'site_id' => $siteGuzape->id,
                'material_id' => $matBlocks->id,
                'grn_id' => $grn3->id,
                'initial_qty' => 5000,
                'current_qty' => 5000,
                'unit_cost' => 480.00,
                'received_date' => now()->subDays(1)->toDateString(),
                'status' => 'active',
            ]
        );

        SiteStock::updateOrCreate(
            ['site_id' => $siteGuzape->id, 'material_id' => $matBlocks->id],
            ['qty_on_hand' => 5000, 'qty_reserved' => 0, 'reorder_status' => 'healthy']
        );

        // Journal #7 for Guzape GRN (Debit 1300 Asset ₦2.4m, Credit 2150 GRNI ₦2.4m)
        $je7 = InventoryJournalEntry::updateOrCreate(
            ['entry_number' => 'JE-2026-00007'],
            [
                'entry_date' => now()->subDays(1)->toDateString(),
                'reference_type' => GoodsReceivedNote::class,
                'reference_id' => $grn3->id,
                'site_id' => $siteGuzape->id,
                'project_id' => $proj3->id,
                'description' => "Capitalize physical blocks inventory from delivery {$grn3->ref_number} (PO: {$po3->ref_number})",
                'total_debit' => 2400000.00,
                'total_credit' => 2400000.00,
                'is_balanced' => true,
                'posted_by_user_id' => $admin->id,
            ]
        );
        $je7->items()->delete();
        $je7->items()->createMany([
            ['account_code' => '1300', 'entry_type' => 'debit', 'amount' => 2400000.00, 'narration' => '5,000 blocks received at Guzape Hills Site'],
            ['account_code' => '2150', 'entry_type' => 'credit', 'amount' => 2400000.00, 'narration' => 'Unbilled GRNI delivery accrual for Apex Vibrated Products'],
        ]);

        // Ghost Delivery Anomaly Flag for Testing Geofence Radar
        InventoryAnomalyFlag::updateOrCreate(
            ['title' => 'Ghost Delivery Warning on GRN-2026-0003-SAMPLE'],
            [
                'site_id' => $siteGuzape->id,
                'flag_type' => 'ghost_delivery',
                'severity' => 'critical',
                'description' => 'Delivery scan logged 1.8km away from Guzape Hills compound geofence perimeter.',
                'flaggable_type' => GoodsReceivedNote::class,
                'flaggable_id' => $grn3->id,
                'status' => 'open',
            ]
        );
    }
}
