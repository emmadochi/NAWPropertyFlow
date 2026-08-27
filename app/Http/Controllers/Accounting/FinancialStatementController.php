<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\AgingAnalysisService;
use App\Services\Accounting\FinancialStatementService;
use App\Services\Accounting\TaxComplianceService;
use Illuminate\Http\Request;

class FinancialStatementController extends Controller
{
    public function __construct(
        protected FinancialStatementService $statementService,
        protected AgingAnalysisService $agingService,
        protected TaxComplianceService $taxService
    ) {}

    /**
     * Financial Intelligence & Statements Cockpit
     */
    public function index(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());
        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $balanceSheet = $this->statementService->getBalanceSheet($asOfDate);
        $pAndL = $this->statementService->getProfitAndLoss($startDate, $endDate);
        $cashFlow = $this->statementService->getCashFlowStatement($startDate, $endDate);
        $arAging = $this->agingService->getAccountsReceivableAging();
        $apAging = $this->agingService->getAccountsPayableAging();
        $whtSummary = $this->taxService->getWhtSchedule($startDate, $endDate);

        return view('accounting.dashboard', compact(
            'balanceSheet',
            'pAndL',
            'cashFlow',
            'arAging',
            'apAging',
            'whtSummary',
            'asOfDate',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Statement of Financial Position (Balance Sheet)
     */
    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());
        $balanceSheet = $this->statementService->getBalanceSheet($asOfDate);

        return view('accounting.statements.balance_sheet', compact('balanceSheet', 'asOfDate'));
    }

    /**
     * Profit & Loss / Income Statement
     */
    public function profitAndLoss(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $pAndL = $this->statementService->getProfitAndLoss($startDate, $endDate);

        return view('accounting.statements.p_and_l', compact('pAndL', 'startDate', 'endDate'));
    }

    /**
     * Cash Flow Statement
     */
    public function cashFlow(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $cashFlow = $this->statementService->getCashFlowStatement($startDate, $endDate);

        return view('accounting.statements.cash_flow', compact('cashFlow', 'startDate', 'endDate'));
    }

    /**
     * Audited Trial Balance Matrix
     */
    public function trialBalance(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->toDateString());
        $trialBalance = $this->statementService->getTrialBalance($asOfDate);

        return view('accounting.statements.trial_balance', compact('trialBalance', 'asOfDate'));
    }
}
