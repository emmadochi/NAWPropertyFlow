<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\PayrollBatch;
use App\Models\PayrollDeduction;
use App\Models\Payslip;
use App\Models\SalaryStructure;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Generate or recalculate a monthly payroll batch.
     */
    public function generateMonthlyPayroll(int $month, int $year, int $createdByUserId): PayrollBatch
    {
        return DB::transaction(function () use ($month, $year, $createdByUserId) {
            $monthName = Carbon::createFromDate($year, $month, 1)->format('F');
            $title = "{$monthName} {$year} Payroll";

            $batch = PayrollBatch::updateOrCreate(
                ['month' => $month, 'year' => $year],
                [
                    'title' => $title,
                    'status' => 'draft',
                    'created_by' => $createdByUserId,
                ]
            );

            // Fetch all active non-customer staff
            $staffMembers = User::whereNotIn('role', ['customer'])
                ->where('status', 'active')
                ->with('salaryStructure')
                ->get();

            $batchTotalBase = 0;
            $batchTotalAllowances = 0;
            $batchTotalCommissions = 0;
            $batchTotalGross = 0;
            $batchTotalDeductions = 0;
            $batchTotalNet = 0;

            foreach ($staffMembers as $staff) {
                $salary = $staff->salaryStructure;

                $base = $salary ? (float) $salary->base_salary : 0;
                $housing = $salary ? (float) $salary->housing_allowance : 0;
                $transport = $salary ? (float) $salary->transport_allowance : 0;
                $otherAllowances = $salary ? (float) $salary->other_allowances : 0;
                $totalAllowances = $housing + $transport + $otherAllowances;

                // Auto-aggregate approved sales commissions in this target month & year
                $commissionAmount = (float) Commission::where('user_id', $staff->id)
                    ->where('status', 'approved')
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->sum('calculated_amount');

                $grossPay = $base + $totalAllowances + $commissionAmount;

                // Statutory Deductions (Calculated on Base + Allowances)
                $taxPercent = $salary ? (float) $salary->tax_percent : 0;
                $pensionPercent = $salary ? (float) $salary->pension_percent : 0;

                $taxDeduction = round(($grossPay * $taxPercent) / 100, 2);
                $pensionDeduction = round((($base + $housing + $transport) * $pensionPercent) / 100, 2);

                // Fetch ad-hoc deductions (Loans, Fines, Advances) for this staff in target month
                $adHocDeductions = PayrollDeduction::where('user_id', $staff->id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->get();

                $loanDeduction = (float) $adHocDeductions->where('deduction_type', 'loan_repayment')->sum('amount');
                $otherDeductions = (float) $adHocDeductions->whereIn('deduction_type', ['fine', 'other'])->sum('amount');

                $totalDeductions = $taxDeduction + $pensionDeduction + $loanDeduction + $otherDeductions;
                $netPay = max(0, $grossPay - $totalDeductions);

                // Create or update payslip record
                $payslip = Payslip::updateOrCreate(
                    [
                        'payroll_batch_id' => $batch->id,
                        'user_id' => $staff->id,
                    ],
                    [
                        'base_salary' => $base,
                        'housing_allowance' => $housing,
                        'transport_allowance' => $transport,
                        'other_allowances' => $otherAllowances,
                        'total_allowances' => $totalAllowances,
                        'commission_amount' => $commissionAmount,
                        'gross_pay' => $grossPay,
                        'tax_deduction' => $taxDeduction,
                        'pension_deduction' => $pensionDeduction,
                        'loan_deduction' => $loanDeduction,
                        'other_deductions' => $otherDeductions,
                        'total_deductions' => $totalDeductions,
                        'net_pay' => $netPay,
                        'bank_name' => $salary?->bank_name,
                        'account_number' => $salary?->account_number,
                        'account_name' => $salary?->account_name,
                        'status' => 'pending',
                    ]
                );

                // Link ad-hoc deduction items to this payslip
                foreach ($adHocDeductions as $ded) {
                    $ded->update(['payslip_id' => $payslip->id]);
                }

                // Batch accumulators
                $batchTotalBase += $base;
                $batchTotalAllowances += $totalAllowances;
                $batchTotalCommissions += $commissionAmount;
                $batchTotalGross += $grossPay;
                $batchTotalDeductions += $totalDeductions;
                $batchTotalNet += $netPay;
            }

            // Update batch totals
            $batch->update([
                'total_base' => $batchTotalBase,
                'total_allowances' => $batchTotalAllowances,
                'total_commissions' => $batchTotalCommissions,
                'total_gross' => $batchTotalGross,
                'total_deductions' => $batchTotalDeductions,
                'total_net' => $batchTotalNet,
            ]);

            return $batch->fresh('payslips.user');
        });
    }

    /**
     * Approve payroll batch and mark included commissions as paid.
     */
    public function approvePayrollBatch(PayrollBatch $batch, int $approverId): void
    {
        DB::transaction(function () use ($batch, $approverId) {
            $batch->update([
                'status' => 'approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
            ]);

            $batch->payslips()->update(['status' => 'approved']);
        });
    }

    /**
     * Mark payroll batch as paid and update commission records.
     */
    public function markPayrollPaid(PayrollBatch $batch): void
    {
        DB::transaction(function () use ($batch) {
            $batch->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $batch->payslips()->update(['status' => 'paid']);

            // Mark the underlying commission records as paid
            Commission::where('status', 'approved')
                ->whereMonth('created_at', $batch->month)
                ->whereYear('created_at', $batch->year)
                ->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
        });
    }
}
