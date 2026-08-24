<?php

namespace App\Http\Controllers;

use App\Models\PayrollBatch;
use App\Models\PayrollDeduction;
use App\Models\Payslip;
use App\Models\SalaryStructure;
use App\Models\User;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    protected PayrollService $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Display payroll overview, metrics, and batch history.
     */
    public function index(Request $request)
    {
        $batches = PayrollBatch::with(['creator', 'approver'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(12);

        $totalPaidOut = PayrollBatch::where('status', 'paid')->sum('total_net');
        $totalCommissionsPaid = PayrollBatch::where('status', 'paid')->sum('total_commissions');
        $staffCount = User::whereNotIn('role', ['customer'])->where('status', 'active')->count();

        return view('payroll.index', compact('batches', 'totalPaidOut', 'totalCommissionsPaid', 'staffCount'));
    }

    /**
     * Generate or recalculate a monthly payroll batch.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2035',
        ]);

        $batch = $this->payrollService->generateMonthlyPayroll(
            (int) $validated['month'],
            (int) $validated['year'],
            Auth::id()
        );

        return redirect()->route('payroll.show', $batch->id)
            ->with('success', "{$batch->title} generated successfully.");
    }

    /**
     * View detailed payroll breakdown for a specific batch.
     */
    public function show(PayrollBatch $batch)
    {
        $batch->load(['payslips.user.departmentRelation', 'payslips.deductions']);
        $staffMembers = User::whereNotIn('role', ['customer'])->where('status', 'active')->get();

        return view('payroll.show', compact('batch', 'staffMembers'));
    }

    /**
     * Approve payroll batch.
     */
    public function approve(PayrollBatch $batch)
    {
        if ($batch->status !== 'draft') {
            return back()->with('error', 'Only draft payroll batches can be approved.');
        }

        $this->payrollService->approvePayrollBatch($batch, Auth::id());

        return back()->with('success', "{$batch->title} has been approved.");
    }

    /**
     * Mark payroll batch as paid and update commission statuses.
     */
    public function markPaid(PayrollBatch $batch)
    {
        if ($batch->status !== 'approved') {
            return back()->with('error', 'Batch must be approved before marking as paid.');
        }

        $this->payrollService->markPayrollPaid($batch);

        return back()->with('success', "{$batch->title} marked as paid successfully.");
    }

    /**
     * Staff Salary & Compensation Directory.
     */
    public function salaryStructures()
    {
        $staff = User::whereNotIn('role', ['customer'])
            ->where('status', 'active')
            ->with(['salaryStructure', 'departmentRelation', 'branch'])
            ->orderBy('name', 'asc')
            ->paginate(15);

        return view('payroll.salary-structures', compact('staff'));
    }

    /**
     * Update individual staff salary structure.
     */
    public function updateSalaryStructure(Request $request, User $user)
    {
        $validated = $request->validate([
            'base_salary' => 'required|numeric|min:0',
            'housing_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'other_allowances' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'pension_percent' => 'nullable|numeric|min:0|max:100',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:30',
            'account_name' => 'nullable|string|max:255',
        ]);

        SalaryStructure::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return back()->with('success', "Salary structure updated for {$user->name}.");
    }

    /**
     * Store itemized ad-hoc deduction (Loans, Fines, Advances).
     */
    public function storeDeduction(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'deduction_type' => 'required|in:loan_repayment,fine,other',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'notes' => 'nullable|string',
            'payroll_batch_id' => 'nullable|exists:payroll_batches,id',
        ]);

        $validated['created_by'] = Auth::id();

        PayrollDeduction::create($validated);

        // If added directly inside an active batch view, recalculate that batch
        if (!empty($validated['payroll_batch_id'])) {
            $batch = PayrollBatch::findOrFail($validated['payroll_batch_id']);
            $this->payrollService->generateMonthlyPayroll($batch->month, $batch->year, Auth::id());
            return back()->with('success', 'Deduction recorded and batch recalculated.');
        }

        return back()->with('success', 'Payroll deduction recorded successfully.');
    }

    /**
     * Delete an ad-hoc deduction.
     */
    public function destroyDeduction(PayrollDeduction $deduction)
    {
        $month = $deduction->month;
        $year = $deduction->year;
        $deduction->delete();

        // Recalculate batch if it exists
        $batch = PayrollBatch::where('month', $month)->where('year', $year)->first();
        if ($batch && $batch->status === 'draft') {
            $this->payrollService->generateMonthlyPayroll($month, $year, Auth::id());
        }

        return back()->with('success', 'Deduction removed.');
    }

    /**
     * View personal salary balance, deduction breakdown, and historical payslips.
     */
    public function myPayslips(Request $request)
    {
        $user = Auth::user();
        $salaryStructure = SalaryStructure::where('user_id', $user->id)->first();
        $payslips = Payslip::where('user_id', $user->id)
            ->with(['payrollBatch', 'deductions'])
            ->latest()
            ->paginate(12);

        $activeFines = PayrollDeduction::where('user_id', $user->id)
            ->where('month', (int) now()->format('n'))
            ->where('year', (int) now()->format('Y'))
            ->get();

        return view('payroll.my-payslips', compact('user', 'salaryStructure', 'payslips', 'activeFines'));
    }

    /**
     * View and download printable PDF payslip for a staff member.
     */
    public function downloadPayslip(Payslip $payslip)
    {
        $user = Auth::user();
        if ($payslip->user_id !== $user->id && !in_array($user->role, ['super_admin', 'company_admin', 'hr', 'accountant'])) {
            abort(403, 'Unauthorized access to this payslip.');
        }

        $payslip->load(['payrollBatch', 'user.departmentRelation', 'deductions']);
        $companySetting = \App\Models\CompanySetting::getCached();

        return view('pdf.payslip', compact('payslip', 'companySetting'));
    }

    /**
     * Export bank payment transfer CSV for a payroll batch.
     */
    public function exportBankCsv(PayrollBatch $batch)
    {
        $batch->load('payslips.user');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$batch->title}_Bank_Transfer.csv\"",
        ];

        $callback = function () use ($batch) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee Name', 'Bank Name', 'Account Number', 'Account Name', 'Net Amount (NGN)', 'Narration']);

            foreach ($batch->payslips as $slip) {
                fputcsv($file, [
                    $slip->user->name,
                    $slip->bank_name ?? 'N/A',
                    $slip->account_number ?? 'N/A',
                    $slip->account_name ?? $slip->user->name,
                    number_format((float) $slip->net_pay, 2, '.', ''),
                    "{$batch->title} - {$slip->user->name}",
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
