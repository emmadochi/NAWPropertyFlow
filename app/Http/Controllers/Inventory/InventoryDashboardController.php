<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\GoodsReceivedNote;
use App\Models\Inventory\InventoryAnomalyFlag;
use App\Models\Inventory\InventoryChartOfAccount;
use App\Models\Inventory\InventoryJournalEntry;
use App\Models\Inventory\InventorySite;
use App\Models\Inventory\MaterialCatalogue;
use App\Models\Inventory\MaterialIssueVoucher;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\SiteStock;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\SupplierInvoice;
use App\Models\Inventory\WasteLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Total Live Material Stock Valuation (FIFO Sum = qty_on_hand * standard_unit_cost)
        $stocks = SiteStock::with('material')->where('qty_on_hand', '>', 0)->get();
        $totalStockValuation = $stocks->sum(function ($s) {
            return (float)$s->qty_on_hand * ($s->material ? (float)$s->material->standard_unit_cost : 0);
        });

        // 2. Project Work-in-Progress (WIP) Cumulative Material Cost Issued
        $issuedStocks = DB::table('miv_items')
            ->join('material_catalogue', 'miv_items.material_id', '=', 'material_catalogue.id')
            ->selectRaw('SUM(miv_items.qty_issued * material_catalogue.standard_unit_cost) as total_wip')
            ->value('total_wip') ?? 0;

        // 3. Unbilled Goods Received Accrual (GRNI) Value
        $unbilledGrnCount = GoodsReceivedNote::whereDoesntHave('supplierInvoice')->count();

        // 4. Open Anomaly Flags / Fraud Risk Index
        $openAnomaliesCount = InventoryAnomalyFlag::where('status', 'open')->count();
        $criticalAnomaliesCount = InventoryAnomalyFlag::where('status', 'open')->where('severity', 'critical')->count();

        // 5. Stock Valuation by Site Breakdown
        $sitesBreakdown = InventorySite::where('is_active', true)
            ->with(['project', 'stocks.material'])
            ->get()
            ->map(function ($site) {
                $val = $site->stocks->sum(function ($s) {
                    return (float)$s->qty_on_hand * ($s->material ? (float)$s->material->standard_unit_cost : 0);
                });
                return [
                    'site_id' => $site->id,
                    'name' => $site->name,
                    'project' => $site->project?->name ?? 'Direct Site',
                    'stock_value' => $val,
                    'total_skus' => $site->stocks->where('qty_on_hand', '>', 0)->count(),
                ];
            })
            ->sortByDesc('stock_value')
            ->values();

        // 6. Material Categories Valuation Breakdown
        $categoryNames = \App\Models\Inventory\MaterialCategory::getActiveList();
        $categoriesBreakdown = MaterialCatalogue::with('siteStocks')
            ->get()
            ->groupBy('category')
            ->map(function ($items, $cat) use ($categoryNames) {
                $catVal = $items->sum(function ($m) {
                    return $m->siteStocks->sum('qty_on_hand') * (float)$m->standard_unit_cost;
                });
                return [
                    'category' => $categoryNames[$cat] ?? ucwords(str_replace('_', ' ', $cat)),
                    'value' => $catVal,
                ];
            })
            ->filter(fn($c) => $c['value'] > 0)
            ->sortByDesc('value')
            ->values();

        // 7. Low Stock / Reorder Warnings (< Safety Stock Level)
        $lowStockWarnings = SiteStock::with(['material', 'site'])
            ->whereHas('material', function ($q) {
                $q->whereColumn('site_stock.qty_on_hand', '<=', 'material_catalogue.safety_stock_level');
            })
            ->take(5)
            ->get();

        // 8. Recent Journal Postings
        $recentJournals = InventoryJournalEntry::with(['site', 'project', 'items'])
            ->latest('id')
            ->take(5)
            ->get();

        return view('inventory.dashboard', compact(
            'totalStockValuation',
            'issuedStocks',
            'unbilledGrnCount',
            'openAnomaliesCount',
            'criticalAnomaliesCount',
            'sitesBreakdown',
            'categoriesBreakdown',
            'lowStockWarnings',
            'recentJournals'
        ));
    }

    public function generalLedger(Request $request)
    {
        $accounts = InventoryChartOfAccount::with(['journalItems' => function ($q) {
            $q->latest()->take(10);
        }])->get();

        $query = InventoryJournalEntry::with(['site.project', 'items.account', 'poster']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        $journals = $query->latest('id')->paginate(20);
        $sites = InventorySite::where('is_active', true)->orderBy('name')->get();

        return view('inventory.general-ledger.index', compact('accounts', 'journals', 'sites'));
    }
}
