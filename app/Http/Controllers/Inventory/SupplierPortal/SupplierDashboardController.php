<?php

namespace App\Http\Controllers\Inventory\SupplierPortal;

use App\Http\Controllers\Controller;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\SupplierInvoice;
use App\Services\Inventory\ThreeWayMatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierDashboardController extends Controller
{
    public function __construct(
        protected ThreeWayMatchService $matchService
    ) {}

    protected function getSupplier()
    {
        return Auth::guard('supplier')->user()->supplier;
    }

    public function index()
    {
        $supplier = $this->getSupplier();

        $openPosCount = PurchaseOrder::where('supplier_id', $supplier->id)
            ->whereIn('status', ['approved', 'partially_delivered'])
            ->count();

        $invoicedTotal = SupplierInvoice::where('supplier_id', $supplier->id)->sum('total_amount');
        $paidTotal = SupplierInvoice::where('supplier_id', $supplier->id)->where('payment_status', 'paid')->sum('total_amount');

        $recentPos = PurchaseOrder::where('supplier_id', $supplier->id)
            ->with(['site.project', 'items.material'])
            ->latest()
            ->take(5)
            ->get();

        $recentInvoices = SupplierInvoice::where('supplier_id', $supplier->id)
            ->with('purchaseOrder')
            ->latest()
            ->take(5)
            ->get();

        return view('supplier-portal.dashboard', compact('supplier', 'openPosCount', 'invoicedTotal', 'paidTotal', 'recentPos', 'recentInvoices'));
    }

    public function purchaseOrders()
    {
        $supplier = $this->getSupplier();
        $purchaseOrders = PurchaseOrder::where('supplier_id', $supplier->id)
            ->with(['site.project', 'items.material'])
            ->latest()
            ->paginate(15);

        return view('supplier-portal.purchase-orders.index', compact('supplier', 'purchaseOrders'));
    }

    public function showPO(PurchaseOrder $purchaseOrder)
    {
        $supplier = $this->getSupplier();
        if ($purchaseOrder->supplier_id !== $supplier->id) {
            abort(403, 'Unauthorized access to purchase order.');
        }

        $purchaseOrder->load(['site.project', 'items.material', 'goodsReceivedNotes']);

        return view('supplier-portal.purchase-orders.show', compact('supplier', 'purchaseOrder'));
    }

    public function invoices()
    {
        $supplier = $this->getSupplier();
        $invoices = SupplierInvoice::where('supplier_id', $supplier->id)
            ->with('purchaseOrder')
            ->latest()
            ->paginate(15);

        return view('supplier-portal.invoices.index', compact('supplier', 'invoices'));
    }

    public function createInvoice(Request $request)
    {
        $supplier = $this->getSupplier();
        $purchaseOrders = PurchaseOrder::where('supplier_id', $supplier->id)
            ->whereIn('status', ['approved', 'delivered', 'partially_delivered'])
            ->with(['site.project', 'goodsReceivedNotes'])
            ->latest()
            ->get();

        $selectedPo = $request->filled('purchase_order_id')
            ? PurchaseOrder::where('supplier_id', $supplier->id)->with(['goodsReceivedNotes'])->find($request->purchase_order_id)
            : null;

        return view('supplier-portal.invoices.create', compact('supplier', 'purchaseOrders', 'selectedPo'));
    }

    public function storeInvoice(Request $request)
    {
        $supplier = $this->getSupplier();

        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'grn_id' => 'nullable|exists:goods_received_notes,id',
            'invoice_number' => 'required|string|max:100',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'billed_amount' => 'required|numeric|min:0.01',
            'tax_amount' => 'nullable|numeric|min:0',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $po = PurchaseOrder::findOrFail($validated['purchase_order_id']);
        if ($po->supplier_id !== $supplier->id) {
            abort(403, 'Invalid purchase order.');
        }

        $filePath = null;
        if ($request->hasFile('invoice_file')) {
            $filePath = $request->file('invoice_file')->store('inventory/invoices', 'public');
        }

        $tax = (float)($validated['tax_amount'] ?? 0);
        $total = (float)$validated['billed_amount'];
        $subtotal = $total - $tax;

        $invoice = SupplierInvoice::create([
            'supplier_id' => $supplier->id,
            'purchase_order_id' => $po->id,
            'goods_received_note_id' => $validated['grn_id'] ?? null,
            'invoice_number' => $validated['invoice_number'],
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'subtotal_amount' => $subtotal,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'payment_status' => 'unmatched',
            'invoice_file_path' => $filePath,
        ]);

        // Auto 3-Way Match
        $this->matchService->matchInvoice($invoice);

        return redirect()->route('supplier.invoices.index')
            ->with('success', "Invoice {$invoice->invoice_number} submitted successfully.");
    }
}
