<?php

namespace Tests\Feature\Accounting;

use App\Models\Inventory\InventoryChartOfAccount;
use App\Models\Inventory\InventoryJournalEntry;
use App\Models\Role;
use App\Models\User;
use App\Services\Accounting\AgingAnalysisService;
use App\Services\Accounting\FinancialStatementService;
use App\Services\Accounting\TaxComplianceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnterpriseAccountingSuiteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Seed tenant demo data for tests
        $this->artisan('db:seed', ['--class' => 'InventoryDemoSeeder']);
    }

    public function test_balance_sheet_is_strictly_balanced_according_to_ifrs_equation(): void
    {
        $service = app(FinancialStatementService::class);
        $balanceSheet = $service->getBalanceSheet();

        $this->assertTrue($balanceSheet['is_balanced'], "Balance Sheet must satisfy Assets == Liabilities + Equity. Variance: {$balanceSheet['variance']}");
        $this->assertGreaterThan(0, $balanceSheet['total_assets']);
        $this->assertGreaterThan(0, $balanceSheet['total_equity']);
        $this->assertEquals($balanceSheet['total_assets'], $balanceSheet['total_liabilities_and_equity']);
    }

    public function test_profit_and_loss_calculates_cogs_and_net_margins(): void
    {
        $service = app(FinancialStatementService::class);
        $pAndL = $service->getProfitAndLoss();

        $this->assertGreaterThan(0, $pAndL['total_revenue']);
        $this->assertGreaterThan(0, $pAndL['total_cogs']);
        $this->assertEquals($pAndL['total_revenue'] - $pAndL['total_cogs'], $pAndL['gross_profit']);
        $this->assertEquals($pAndL['gross_profit'] - $pAndL['total_operating_expenses'], $pAndL['net_profit']);
    }

    public function test_trial_balance_matrix_is_audited_and_balanced(): void
    {
        $service = app(FinancialStatementService::class);
        $trialBalance = $service->getTrialBalance();

        $this->assertTrue($trialBalance['is_balanced'], "Trial Balance total debits must equal total credits.");
        $this->assertEquals($trialBalance['total_debit'], $trialBalance['total_credit']);
        $this->assertNotEmpty($trialBalance['rows']);
    }

    public function test_ar_and_ap_aging_matrices(): void
    {
        $service = app(AgingAnalysisService::class);
        $ar = $service->getAccountsReceivableAging();
        $ap = $service->getAccountsPayableAging();

        $this->assertArrayHasKey('buckets', $ar);
        $this->assertArrayHasKey('buckets', $ap);
        $this->assertArrayHasKey('current', $ap['buckets']);
        $this->assertArrayHasKey('over_90', $ap['buckets']);
    }

    public function test_tax_compliance_hub_calculates_wht_and_vat(): void
    {
        $service = app(TaxComplianceService::class);
        $wht = $service->getWhtSchedule();
        $vat = $service->getVatSummary();

        $this->assertGreaterThan(0, $wht['total_wht']);
        $this->assertArrayHasKey('rows', $wht);
        $this->assertArrayHasKey('net_vat_payable', $vat);
    }

    public function test_financial_cockpit_and_statement_routes_render_successfully(): void
    {
        $accountant = User::where('email', 'accountant@propertyflow.com')->first();
        $this->actingAs($accountant);

        $this->get(route('accounting.dashboard'))->assertOk();
        $this->get(route('accounting.statements.balance-sheet'))->assertOk();
        $this->get(route('accounting.statements.p-and-l'))->assertOk();
        $this->get(route('accounting.statements.cash-flow'))->assertOk();
        $this->get(route('accounting.statements.trial-balance'))->assertOk();
        $this->get(route('accounting.treasury.index'))->assertOk();
        $this->get(route('accounting.reports.ar-aging'))->assertOk();
        $this->get(route('accounting.reports.ap-aging'))->assertOk();
        $this->get(route('accounting.tax.index'))->assertOk();
    }
}
