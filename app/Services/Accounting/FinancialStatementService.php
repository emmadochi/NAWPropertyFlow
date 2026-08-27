<?php

namespace App\Services\Accounting;

use App\Models\Inventory\InventoryChartOfAccount;
use App\Models\Inventory\InventoryJournalEntry;
use App\Models\Inventory\InventoryJournalItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialStatementService
{
    /**
     * Statement of Financial Position (Balance Sheet)
     * Assets = Liabilities + Equity
     */
    public function getBalanceSheet(?string $asOfDate = null): array
    {
        $date = $asOfDate ? Carbon::parse($asOfDate)->endOfDay() : now()->endOfDay();

        // 1. Get all Assets, Liabilities, and Equity account balances up to the specified date
        $accountBalances = $this->getAccountBalancesAsOf($date);

        $currentAssets = [];
        $nonCurrentAssets = [];
        $totalCurrentAssets = 0;
        $totalNonCurrentAssets = 0;

        $currentLiabilities = [];
        $longTermLiabilities = [];
        $totalCurrentLiabilities = 0;
        $totalLongTermLiabilities = 0;

        $equityAccounts = [];
        $totalEquityBase = 0;

        foreach ($accountBalances as $code => $data) {
            $type = $data['account_type'];
            $balance = $data['balance'];

            if ($type === 'asset') {
                if (in_array($code, ['1010', '1015', '1020', '1200', '1300', '1400'])) {
                    $currentAssets[] = [
                        'code' => $code,
                        'name' => $data['account_name'],
                        'balance' => $balance,
                    ];
                    $totalCurrentAssets += $balance;
                } else {
                    $nonCurrentAssets[] = [
                        'code' => $code,
                        'name' => $data['account_name'],
                        'balance' => $balance,
                    ];
                    $totalNonCurrentAssets += $balance;
                }
            } elseif ($type === 'liability') {
                if (in_array($code, ['2100', '2120', '2130', '2150', '2200', '2300'])) {
                    $currentLiabilities[] = [
                        'code' => $code,
                        'name' => $data['account_name'],
                        'balance' => $balance,
                    ];
                    $totalCurrentLiabilities += $balance;
                } else {
                    $longTermLiabilities[] = [
                        'code' => $code,
                        'name' => $data['account_name'],
                        'balance' => $balance,
                    ];
                    $totalLongTermLiabilities += $balance;
                }
            } elseif ($type === 'equity') {
                $equityAccounts[] = [
                    'code' => $code,
                    'name' => $data['account_name'],
                    'balance' => $balance,
                ];
                $totalEquityBase += $balance;
            }
        }

        $totalAssets = $totalCurrentAssets + $totalNonCurrentAssets;
        $totalLiabilities = $totalCurrentLiabilities + $totalLongTermLiabilities;

        // 2. Real-time Retained Earnings & Net Income for the period
        // Net Income = Cumulative Revenue - Cumulative Expenses
        $cumulativeIncome = $this->getCumulativeNetIncomeAsOf($date);
        $totalEquity = $totalEquityBase + $cumulativeIncome;
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;
        $isBalanced = abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01;

        return [
            'as_of_date' => $date->toDateString(),
            'current_assets' => $currentAssets,
            'total_current_assets' => $totalCurrentAssets,
            'non_current_assets' => $nonCurrentAssets,
            'total_non_current_assets' => $totalNonCurrentAssets,
            'total_assets' => $totalAssets,
            'current_liabilities' => $currentLiabilities,
            'total_current_liabilities' => $totalCurrentLiabilities,
            'long_term_liabilities' => $longTermLiabilities,
            'total_long_term_liabilities' => $totalLongTermLiabilities,
            'total_liabilities' => $totalLiabilities,
            'equity_accounts' => $equityAccounts,
            'total_equity_base' => $totalEquityBase,
            'current_period_net_income' => $cumulativeIncome,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
            'is_balanced' => $isBalanced,
            'variance' => round($totalAssets - $totalLiabilitiesAndEquity, 2),
        ];
    }

    /**
     * Profit & Loss / Income Statement
     */
    public function getProfitAndLoss(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfYear();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        $journalItems = DB::table('inventory_journal_items as ji')
            ->join('inventory_journal_entries as je', 'ji.journal_entry_id', '=', 'je.id')
            ->join('inventory_chart_of_accounts as coa', 'ji.account_code', '=', 'coa.account_code')
            ->whereBetween('je.entry_date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('coa.account_type', ['revenue', 'expense'])
            ->select(
                'ji.account_code',
                'coa.account_name',
                'coa.account_type',
                'ji.entry_type',
                DB::raw('SUM(ji.amount) as total_amount')
            )
            ->groupBy('ji.account_code', 'coa.account_name', 'coa.account_type', 'ji.entry_type')
            ->get();

        $revenues = [];
        $totalRevenue = 0;

        $cogs = [];
        $totalCogs = 0;

        $opex = [];
        $totalOpex = 0;

        // Group by account code
        $grouped = [];
        foreach ($journalItems as $item) {
            if (!isset($grouped[$item->account_code])) {
                $grouped[$item->account_code] = [
                    'code' => $item->account_code,
                    'name' => $item->account_name,
                    'type' => $item->account_type,
                    'debit' => 0,
                    'credit' => 0,
                ];
            }
            if ($item->entry_type === 'debit') {
                $grouped[$item->account_code]['debit'] += (float) $item->total_amount;
            } else {
                $grouped[$item->account_code]['credit'] += (float) $item->total_amount;
            }
        }

        foreach ($grouped as $code => $data) {
            if ($data['type'] === 'revenue') {
                $amount = $data['credit'] - $data['debit'];
                $revenues[] = [
                    'code' => $code,
                    'name' => $data['name'],
                    'amount' => $amount,
                ];
                $totalRevenue += $amount;
            } elseif ($data['type'] === 'expense') {
                $amount = $data['debit'] - $data['credit'];
                if (in_array($code, ['5100', '5190', '5200', '5300'])) {
                    $cogs[] = [
                        'code' => $code,
                        'name' => $data['name'],
                        'amount' => $amount,
                    ];
                    $totalCogs += $amount;
                } else {
                    $opex[] = [
                        'code' => $code,
                        'name' => $data['name'],
                        'amount' => $amount,
                    ];
                    $totalOpex += $amount;
                }
            }
        }

        $grossProfit = $totalRevenue - $totalCogs;
        $netProfit = $grossProfit - $totalOpex;
        $grossMarginPct = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0;
        $netMarginPct = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'revenues' => $revenues,
            'total_revenue' => $totalRevenue,
            'cogs' => $cogs,
            'total_cogs' => $totalCogs,
            'gross_profit' => $grossProfit,
            'gross_margin_pct' => $grossMarginPct,
            'operating_expenses' => $opex,
            'total_operating_expenses' => $totalOpex,
            'net_profit' => $netProfit,
            'net_margin_pct' => $netMarginPct,
        ];
    }

    /**
     * Cash Flow Statement (Direct Method)
     */
    public function getCashFlowStatement(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfYear();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        // Bank Accounts (1010, 1015, 1020)
        $bankCodes = ['1010', '1015', '1020'];

        $operatingInflows = DB::table('inventory_journal_items as ji')
            ->join('inventory_journal_entries as je', 'ji.journal_entry_id', '=', 'je.id')
            ->whereBetween('je.entry_date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('ji.account_code', $bankCodes)
            ->where('ji.entry_type', 'debit')
            ->sum('ji.amount');

        $operatingOutflows = DB::table('inventory_journal_items as ji')
            ->join('inventory_journal_entries as je', 'ji.journal_entry_id', '=', 'je.id')
            ->whereBetween('je.entry_date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('ji.account_code', $bankCodes)
            ->where('ji.entry_type', 'credit')
            ->sum('ji.amount');

        $netOperatingCashFlow = (float) $operatingInflows - (float) $operatingOutflows;

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'cash_inflows' => (float) $operatingInflows,
            'cash_outflows' => (float) $operatingOutflows,
            'net_cash_flow' => $netOperatingCashFlow,
        ];
    }

    /**
     * Full Audited Trial Balance Matrix
     */
    public function getTrialBalance(?string $asOfDate = null): array
    {
        $date = $asOfDate ? Carbon::parse($asOfDate)->endOfDay() : now()->endOfDay();

        $accounts = DB::table('inventory_chart_of_accounts')
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        $trialBalance = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $acc) {
            $debits = (float) DB::table('inventory_journal_items as ji')
                ->join('inventory_journal_entries as je', 'ji.journal_entry_id', '=', 'je.id')
                ->where('ji.account_code', $acc->account_code)
                ->where('ji.entry_type', 'debit')
                ->where('je.entry_date', '<=', $date->toDateString())
                ->sum('ji.amount');

            $credits = (float) DB::table('inventory_journal_items as ji')
                ->join('inventory_journal_entries as je', 'ji.journal_entry_id', '=', 'je.id')
                ->where('ji.account_code', $acc->account_code)
                ->where('ji.entry_type', 'credit')
                ->where('je.entry_date', '<=', $date->toDateString())
                ->sum('ji.amount');

            if ($acc->account_type === 'asset' || $acc->account_type === 'expense') {
                $netDebit = max(0, $debits - $credits);
                $netCredit = max(0, $credits - $debits);
            } else {
                $netCredit = max(0, $credits - $debits);
                $netDebit = max(0, $debits - $credits);
            }

            if ($debits > 0 || $credits > 0) {
                $trialBalance[] = [
                    'code' => $acc->account_code,
                    'name' => $acc->account_name,
                    'type' => $acc->account_type,
                    'debit' => $netDebit,
                    'credit' => $netCredit,
                ];
                $totalDebit += $netDebit;
                $totalCredit += $netCredit;
            }
        }

        return [
            'as_of_date' => $date->toDateString(),
            'rows' => $trialBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    protected function getAccountBalancesAsOf(Carbon $date): array
    {
        $accounts = DB::table('inventory_chart_of_accounts')
            ->where('is_active', true)
            ->get();

        $balances = [];

        foreach ($accounts as $acc) {
            $debits = (float) DB::table('inventory_journal_items as ji')
                ->join('inventory_journal_entries as je', 'ji.journal_entry_id', '=', 'je.id')
                ->where('ji.account_code', $acc->account_code)
                ->where('ji.entry_type', 'debit')
                ->where('je.entry_date', '<=', $date->toDateString())
                ->sum('ji.amount');

            $credits = (float) DB::table('inventory_journal_items as ji')
                ->join('inventory_journal_entries as je', 'ji.journal_entry_id', '=', 'je.id')
                ->where('ji.account_code', $acc->account_code)
                ->where('ji.entry_type', 'credit')
                ->where('je.entry_date', '<=', $date->toDateString())
                ->sum('ji.amount');

            if ($acc->account_type === 'asset') {
                $net = $debits - $credits;
            } elseif ($acc->account_type === 'liability' || $acc->account_type === 'equity') {
                $net = $credits - $debits;
            } else {
                $net = 0;
            }

            if (abs($net) > 0.001) {
                $balances[$acc->account_code] = [
                    'account_name' => $acc->account_name,
                    'account_type' => $acc->account_type,
                    'balance' => $net,
                ];
            }
        }

        return $balances;
    }

    protected function getCumulativeNetIncomeAsOf(Carbon $date): float
    {
        $revenue = (float) DB::table('inventory_journal_items as ji')
            ->join('inventory_journal_entries as je', 'ji.journal_entry_id', '=', 'je.id')
            ->join('inventory_chart_of_accounts as coa', 'ji.account_code', '=', 'coa.account_code')
            ->where('coa.account_type', 'revenue')
            ->where('je.entry_date', '<=', $date->toDateString())
            ->selectRaw('SUM(CASE WHEN ji.entry_type = "credit" THEN ji.amount ELSE -ji.amount END) as net_rev')
            ->value('net_rev') ?: 0;

        $expense = (float) DB::table('inventory_journal_items as ji')
            ->join('inventory_journal_entries as je', 'ji.journal_entry_id', '=', 'je.id')
            ->join('inventory_chart_of_accounts as coa', 'ji.account_code', '=', 'coa.account_code')
            ->where('coa.account_type', 'expense')
            ->where('je.entry_date', '<=', $date->toDateString())
            ->selectRaw('SUM(CASE WHEN ji.entry_type = "debit" THEN ji.amount ELSE -ji.amount END) as net_exp')
            ->value('net_exp') ?: 0;

        return $revenue - $expense;
    }
}
