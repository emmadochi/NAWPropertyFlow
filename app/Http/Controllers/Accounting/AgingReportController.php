<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\AgingAnalysisService;
use Illuminate\Http\Request;

class AgingReportController extends Controller
{
    public function __construct(
        protected AgingAnalysisService $agingService
    ) {}

    public function arAging()
    {
        $report = $this->agingService->getAccountsReceivableAging();
        return view('accounting.reports.ar_aging', compact('report'));
    }

    public function apAging()
    {
        $report = $this->agingService->getAccountsPayableAging();
        return view('accounting.reports.ap_aging', compact('report'));
    }
}
