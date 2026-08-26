<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\GoodsReceivedNote;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\SupplierInvoice;
use App\Services\Inventory\ThreeWayMatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierInvoiceController extends Controller
{
    public function __construct(
        protected ThreeWayMatchService $matchService
    ) {}

    public function index(Request $request)
    {
        $query = SupplierInvoice::with(['supplier', 'purchaseOrder', 'goodsReceivedNote', 'paymentApprover']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $invoices = $query->latest()->paginate(15);

        return view('inventory.invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::whereIn('status', ['approved', 'delivered', 'partially_delivered'])
            ->with(['supplier', 'goodsReceivedNotes'])
            ->latest()
            ->get();

        $selectedPo = $request->filled('purchase_order_id')
            ? PurchaseOrder::with(['supplier', 'goodsReceivedNotes'])->find($request->purchase_order_id)
            : null;

        return view('inventory.invoices.create', compact('suppliers', 'purchaseOrders', 'selectedPo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'grn_id' => 'nullable|exists:goods_received_notes,id',
            'invoice_number' => 'required|string|max:100',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'billed_amount' => 'required|numeric|min:0.01',
            'tax_amount' => 'nullable|numeric|min:0',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('invoice_file')) {
            $filePath = $request->file('invoice_file')->store('inventory/invoices', 'public');
        }

        $tax = (float)($validated['tax_amount'] ?? 0);
        $total = (float)$validated['billed_amount'];
        $subtotal = $total - $tax;

        $invoice = SupplierInvoice::create([
            'supplier_id' => $validated['supplier_id'],
            'purchase_order_id' => $validated['purchase_order_id'],
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

        // Run automated 3-way match
        $this->matchService->matchInvoice($invoice);

        return redirect()->route('inventory.invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} recorded. 3-Way Match executed automatically.");
    }

    public function show(SupplierInvoice $invoice)
    {
        $invoice->load([
            'supplier',
            'purchaseOrder.items.material',
            'goodsReceivedNote.items.material',
            'paymentApprover',
        ]);

        return view('inventory.invoices.show', compact('invoice'));
    }

    public function runMatch(SupplierInvoice $invoice)
    {
        $this->matchService->matchInvoice($invoice);

        return redirect()->route('inventory.invoices.show', $invoice)
            ->with('success', "3-Way Match re-run completed: Status is {$invoice->fresh()->payment_status}.");
    }

    public function approvePayment(Request $request, SupplierInvoice $invoice)
    {
        $invoice->update([
            'payment_status' => 'paid',
            'payment_approved_by_user_id' => Auth::id(),
            'payment_approved_at' => now(),
        ]);

        return redirect()->route('inventory.invoices.show', $invoice)
            ->with('success', "Payment voucher approved and disbursed for Invoice {$invoice->invoice_number}.");
    }
}
