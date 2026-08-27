<?php

namespace App\Services\Accounting;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankTransaction;
use App\Models\Expense;
use App\Models\Inventory\SupplierInvoice;
use App\Models\PaymentMilestone;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class BankReconciliationService
{
    /**
     * Parse CSV Bank Statement into BankTransactions
     */
    public function importCsvStatement(BankAccount $bankAccount, UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);
        $importedCount = 0;
        $duplicatesCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row) || count($row) < 4) {
                continue;
            }

            // Expected CSV format: Date, Reference, Narration, Debit, Credit (or Type + Amount)
            $dateStr = trim($row[0] ?? '');
            $reference = trim($row[1] ?? '');
            $narration = trim($row[2] ?? '');
            $debitAmount = (float) str_replace([',', ' '], '', $row[3] ?? 0);
            $creditAmount = (float) str_replace([',', ' '], '', $row[4] ?? 0);

            try {
                $txDate = Carbon::parse($dateStr)->toDateString();
            } catch (\Exception $e) {
                $txDate = now()->toDateString();
            }

            $type = $creditAmount > 0 ? 'credit' : 'debit';
            $amount = $creditAmount > 0 ? $creditAmount : $debitAmount;

            if ($amount <= 0) {
                continue;
            }

            $existing = BankTransaction::where('bank_account_id', $bankAccount->id)
                ->where('transaction_date', $txDate)
                ->where('amount', $amount)
                ->where('reference', $reference)
                ->first();

            if ($existing) {
                $duplicatesCount++;
                continue;
            }

            $tx = BankTransaction::create([
                'bank_account_id' => $bankAccount->id,
                'transaction_date' => $txDate,
                'type' => $type,
                'amount' => $amount,
                'reference' => $reference,
                'narration' => $narration,
                'reconciled' => false,
            ]);

            // Attempt auto-matching
            $this->autoMatchTransaction($tx);
            $importedCount++;
        }

        fclose($handle);

        return [
            'imported' => $importedCount,
            'duplicates' => $duplicatesCount,
        ];
    }

    /**
     * Auto-Match a bank line with system receipts or payment vouchers
     */
    public function autoMatchTransaction(BankTransaction $tx): bool
    {
        if ($tx->reconciled) {
            return true;
        }

        if ($tx->type === 'credit') {
            // Match with customer payment milestone
            $milestone = PaymentMilestone::where('amount', $tx->amount)
                ->where('status', 'paid')
                ->whereDoesntHave('bankTransactions')
                ->first();

            if ($milestone) {
                $tx->update([
                    'matched_entity_type' => PaymentMilestone::class,
                    'matched_entity_id' => $milestone->id,
                    'reconciled' => true,
                    'reconciled_at' => now(),
                    'reconciled_by_user_id' => auth()->id() ?: 1,
                ]);
                return true;
            }
        } else {
            // Match with supplier invoice or approved expense
            $invoice = SupplierInvoice::where('total_amount', $tx->amount)
                ->whereIn('payment_status', ['approved_for_payment', 'paid'])
                ->first();

            if ($invoice) {
                $tx->update([
                    'matched_entity_type' => SupplierInvoice::class,
                    'matched_entity_id' => $invoice->id,
                    'reconciled' => true,
                    'reconciled_at' => now(),
                    'reconciled_by_user_id' => auth()->id() ?: 1,
                ]);
                $invoice->update(['payment_status' => 'paid']);
                return true;
            }

            $expense = Expense::where('amount', $tx->amount)
                ->where('status', 'approved')
                ->first();

            if ($expense) {
                $tx->update([
                    'matched_entity_type' => Expense::class,
                    'matched_entity_id' => $expense->id,
                    'reconciled' => true,
                    'reconciled_at' => now(),
                    'reconciled_by_user_id' => auth()->id() ?: 1,
                ]);
                return true;
            }
        }

        return false;
    }

    /**
     * Manual 1-Click Match
     */
    public function manualMatch(BankTransaction $tx, string $entityType, int $entityId): bool
    {
        $tx->update([
            'matched_entity_type' => $entityType,
            'matched_entity_id' => $entityId,
            'reconciled' => true,
            'reconciled_at' => now(),
            'reconciled_by_user_id' => auth()->id() ?: 1,
        ]);

        if ($entityType === SupplierInvoice::class) {
            SupplierInvoice::where('id', $entityId)->update(['payment_status' => 'paid']);
        }

        return true;
    }
}
