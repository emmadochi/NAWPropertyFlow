<?php

namespace App\Services\Accounting;

use App\Models\Accounting\BankAccount;
use App\Models\Expense;
use App\Models\Inventory\InventoryChartOfAccount;
use App\Models\Inventory\InventoryJournalEntry;
use App\Models\PaymentMilestone;
use App\Models\Payroll;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class AccountingJournalService
{
    /**
     * Post a balanced journal entry.
     */
    public function postJournal(
        string $description,
        array $items, // array of ['account_code' => string, 'entry_type' => 'debit'|'credit', 'amount' => float, 'narration' => string]
        ?string $entryDate = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $siteId = null,
        ?int $projectId = null,
        ?int $postedByUserId = null
    ): InventoryJournalEntry {
        return DB::transaction(function () use (
            $description, $items, $entryDate, $referenceType, $referenceId, $siteId, $projectId, $postedByUserId
        ) {
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($items as $item) {
                if ($item['entry_type'] === 'debit') {
                    $totalDebit += (float) $item['amount'];
                } else {
                    $totalCredit += (float) $item['amount'];
                }
            }

            $isBalanced = abs($totalDebit - $totalCredit) < 0.01;
            $date = $entryDate ?: now()->toDateString();
            $entryNumber = $this->generateEntryNumber();

            $journal = InventoryJournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $date,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'site_id' => $siteId,
                'project_id' => $projectId,
                'description' => $description,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'is_balanced' => $isBalanced,
                'posted_by_user_id' => $postedByUserId ?: (auth()->id() ?: 1),
            ]);

            foreach ($items as $item) {
                $journal->items()->create([
                    'account_code' => $item['account_code'],
                    'entry_type' => $item['entry_type'],
                    'amount' => $item['amount'],
                    'narration' => $item['narration'] ?? null,
                ]);
            }

            return $journal;
        });
    }

    /**
     * Auto-Journal: Customer Property Milestone Payment
     */
    public function recordCustomerPayment(PaymentMilestone $milestone, ?BankAccount $bankAccount = null): ?InventoryJournalEntry
    {
        $amount = (float) $milestone->amount;
        if ($amount <= 0) {
            return null;
        }

        $bankGlCode = $bankAccount ? $bankAccount->gl_account_code : '1015'; // Collections Account default
        $sale = $milestone->paymentPlan?->sale;
        $customerName = $sale?->lead?->name ?? 'Customer';
        $propertyTitle = $sale?->property?->title ?? 'Property Unit';

        $journal = $this->postJournal(
            "Customer payment received: {$milestone->title} for {$propertyTitle} ({$customerName})",
            [
                [
                    'account_code' => $bankGlCode,
                    'entry_type' => 'debit',
                    'amount' => $amount,
                    'narration' => "Deposit received into {$bankGlCode} from {$customerName}",
                ],
                [
                    'account_code' => '4100', // Residential Property Sales Revenue
                    'entry_type' => 'credit',
                    'amount' => $amount,
                    'narration' => "Property revenue realized on milestone {$milestone->title}",
                ],
            ],
            $milestone->paid_at ? $milestone->paid_at->toDateString() : now()->toDateString(),
            PaymentMilestone::class,
            $milestone->id,
            null,
            $sale?->property?->project_id,
            auth()->id()
        );

        if ($bankAccount) {
            $bankAccount->increment('current_balance', $amount);
        }

        return $journal;
    }

    /**
     * Auto-Journal: Office Operational Expense Approval
     */
    public function recordExpense(Expense $expense, ?BankAccount $bankAccount = null): ?InventoryJournalEntry
    {
        $amount = (float) $expense->amount;
        if ($amount <= 0) {
            return null;
        }

        $bankGlCode = $bankAccount ? $bankAccount->gl_account_code : '1010'; // Operations default
        $expenseGlCode = '6400'; // Office Rent, Utilities & Administrative

        if (stripos($expense->category, 'market') !== false || stripos($expense->category, 'advert') !== false) {
            $expenseGlCode = '6300';
        } elseif (stripos($expense->category, 'legal') !== false || stripos($expense->category, 'consult') !== false) {
            $expenseGlCode = '6500';
        } elseif (stripos($expense->category, 'salary') !== false || stripos($expense->category, 'welfare') !== false) {
            $expenseGlCode = '6100';
        }

        $journal = $this->postJournal(
            "Operational expense approved: {$expense->title} ({$expense->category})",
            [
                [
                    'account_code' => $expenseGlCode,
                    'entry_type' => 'debit',
                    'amount' => $amount,
                    'narration' => "Expense: {$expense->title}",
                ],
                [
                    'account_code' => $bankGlCode,
                    'entry_type' => 'credit',
                    'amount' => $amount,
                    'narration' => "Disbursement via {$expense->payment_method}",
                ],
            ],
            $expense->expense_date ? $expense->expense_date->toDateString() : now()->toDateString(),
            Expense::class,
            $expense->id,
            null,
            null,
            $expense->approved_by ?: auth()->id()
        );

        if ($bankAccount) {
            $bankAccount->decrement('current_balance', $amount);
        }

        return $journal;
    }

    /**
     * Auto-Journal: Monthly Payroll Release
     */
    public function recordPayroll(Payroll $payroll, ?BankAccount $bankAccount = null): ?InventoryJournalEntry
    {
        $netPay = (float) $payroll->net_pay;
        if ($netPay <= 0) {
            return null;
        }

        $bankGlCode = $bankAccount ? $bankAccount->gl_account_code : '1010';
        $employeeName = $payroll->user?->name ?? 'Staff';

        $journal = $this->postJournal(
            "Monthly payroll disbursement for {$employeeName} ({$payroll->period})",
            [
                [
                    'account_code' => '6100', // Staff Salaries & Benefits
                    'entry_type' => 'debit',
                    'amount' => (float) $payroll->gross_pay,
                    'narration' => "Gross salary for {$employeeName}",
                ],
                [
                    'account_code' => $bankGlCode,
                    'entry_type' => 'credit',
                    'amount' => $netPay,
                    'narration' => "Net salary disbursed via EFT to {$employeeName}",
                ],
            ],
            now()->toDateString(),
            Payroll::class,
            $payroll->id,
            null,
            null,
            auth()->id()
        );

        if ($bankAccount) {
            $bankAccount->decrement('current_balance', $netPay);
        }

        return $journal;
    }

    protected function generateEntryNumber(): string
    {
        $year = now()->format('Y');
        $count = InventoryJournalEntry::whereYear('created_at', $year)->count() + 1;
        return sprintf('JE-%s-%05d', $year, $count);
    }
}
