<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankTransaction;
use App\Models\Inventory\InventoryChartOfAccount;
use App\Services\Accounting\BankReconciliationService;
use Illuminate\Http\Request;

class BankTreasuryController extends Controller
{
    public function __construct(
        protected BankReconciliationService $reconService
    ) {}

    public function index()
    {
        $bankAccounts = BankAccount::with('glAccount')->get();
        $unreconciledTransactions = BankTransaction::with('bankAccount')
            ->where('reconciled', false)
            ->orderBy('transaction_date', 'desc')
            ->take(50)
            ->get();

        $recentReconciled = BankTransaction::with(['bankAccount', 'reconciledBy'])
            ->where('reconciled', true)
            ->orderBy('reconciled_at', 'desc')
            ->take(20)
            ->get();

        $glAccounts = InventoryChartOfAccount::where('account_type', 'asset')
            ->whereIn('account_code', ['1010', '1015', '1020'])
            ->get();

        return view('accounting.treasury.index', compact(
            'bankAccounts',
            'unreconciledTransactions',
            'recentReconciled',
            'glAccounts'
        ));
    }

    public function storeAccount(Request $request)
    {
        $data = $request->validate([
            'account_name' => 'required|string|max:100',
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:30',
            'currency' => 'required|string|max:10',
            'opening_balance' => 'required|numeric|min:0',
            'gl_account_code' => 'required|string|exists:inventory_chart_of_accounts,account_code',
            'notes' => 'nullable|string',
        ]);

        $data['current_balance'] = $data['opening_balance'];

        BankAccount::create($data);

        return redirect()->route('accounting.treasury.index')->with('success', 'Corporate bank account registered successfully.');
    }

    public function importStatement(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'statement_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $bankAccount = BankAccount::findOrFail($request->bank_account_id);
        $result = $this->reconService->importCsvStatement($bankAccount, $request->file('statement_file'));

        return redirect()->route('accounting.treasury.index')->with('success', "Imported {$result['imported']} transactions successfully ({$result['duplicates']} duplicates skipped).");
    }

    public function manualMatch(Request $request, BankTransaction $transaction)
    {
        $request->validate([
            'entity_type' => 'required|string',
            'entity_id' => 'required|integer',
        ]);

        $this->reconService->manualMatch($transaction, $request->entity_type, $request->entity_id);

        return redirect()->route('accounting.treasury.index')->with('success', 'Bank transaction reconciled successfully.');
    }
}
