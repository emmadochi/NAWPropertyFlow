<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Property;
use App\Models\Branch;
use App\Models\User;
use App\Models\PaymentMilestone;
use App\Models\PayrollBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Display expense dashboard with metrics, filterable ledger & Net Profit P&L analysis.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Expense::with(['user', 'approver', 'property', 'branch'])
            ->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by property / project
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        // Filter by Date Range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('expense_date', [$request->start_date, $request->end_date]);
        }

        $expenses = $query->paginate(15)->withQueryString();

        // Financial Ledger Metrics
        $totalExpensesAllTime = Expense::whereIn('status', ['approved', 'paid'])->sum('amount');
        $pendingApprovalCount = Expense::where('status', 'pending')->count();
        $pendingApprovalTotal = Expense::where('status', 'pending')->sum('amount');
        $thisMonthExpenses = Expense::whereIn('status', ['approved', 'paid'])
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        // Corporate Inflow metrics for P&L comparison
        $totalVerifiedInflow = PaymentMilestone::where('status', 'paid')->sum('amount_paid');
        $totalPayrollPaid = PayrollBatch::where('status', 'paid')->sum('total_net');
        $netOperatingProfit = $totalVerifiedInflow - ($totalExpensesAllTime + $totalPayrollPaid);

        // Expense category breakdown for analytics chart
        $expensesByCategory = Expense::whereIn('status', ['approved', 'paid'])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $properties = Property::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $categories = Expense::categories();

        return view('accounting.expenses.index', compact(
            'expenses',
            'totalExpensesAllTime',
            'pendingApprovalCount',
            'pendingApprovalTotal',
            'thisMonthExpenses',
            'totalVerifiedInflow',
            'totalPayrollPaid',
            'netOperatingProfit',
            'expensesByCategory',
            'properties',
            'branches',
            'categories'
        ));
    }

    /**
     * Store a newly logged expense.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'property_id' => 'nullable|exists:properties,id',
            'branch_id' => 'nullable|exists:branches,id',
            'vendor_name' => 'nullable|string|max:255',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt_file')) {
            $receiptPath = $request->file('receipt_file')->store('expenses', 'public');
        }

        $user = Auth::user();
        $isAccountantOrAdmin = in_array($user->role, ['super_admin', 'company_admin', 'accountant', 'finance_manager']);

        // Auto-approve if created directly by an Accountant or Super Admin
        $status = $isAccountantOrAdmin ? 'approved' : 'pending';

        $expense = Expense::create([
            'user_id' => $user->id,
            'approved_by' => $isAccountantOrAdmin ? $user->id : null,
            'property_id' => $validated['property_id'] ?? null,
            'branch_id' => $validated['branch_id'] ?? $user->branch_id ?? null,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'status' => $status,
            'payment_method' => $validated['payment_method'],
            'receipt_file' => $receiptPath,
            'vendor_name' => $validated['vendor_name'] ?? null,
            'reference_number' => 'EXP-' . strtoupper(substr(uniqid(), -6)),
            'notes' => $validated['notes'] ?? null,
            'approved_at' => $isAccountantOrAdmin ? now() : null,
        ]);

        return back()->with('success', "Expense [{$expense->reference_number}] of ₦" . number_format($expense->amount, 2) . " logged successfully.");
    }

    /**
     * Approve or Mark as Paid by Accountant / Finance Officer.
     */
    public function updateStatus(Request $request, Expense $expense)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['super_admin', 'company_admin', 'accountant', 'finance_manager'])) {
            abort(403, 'Unauthorized. Only Finance Officers or Administrators can approve expenses.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,paid',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'approved' && !$expense->approved_by) {
            $updateData['approved_by'] = $user->id;
            $updateData['approved_at'] = now();
        }

        if ($validated['status'] === 'paid') {
            $updateData['paid_at'] = now();
            if (!$expense->approved_by) {
                $updateData['approved_by'] = $user->id;
                $updateData['approved_at'] = now();
            }
        }

        $expense->update($updateData);

        return back()->with('success', "Expense #{$expense->reference_number} status updated to " . ucfirst($validated['status']) . ".");
    }

    /**
     * Delete an expense entry.
     */
    public function destroy(Expense $expense)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['super_admin', 'company_admin', 'accountant', 'finance_manager'])) {
            abort(403, 'Unauthorized.');
        }

        if ($expense->receipt_file) {
            Storage::disk('public')->delete($expense->receipt_file);
        }

        $expense->delete();

        return back()->with('success', 'Expense record deleted successfully.');
    }
}
