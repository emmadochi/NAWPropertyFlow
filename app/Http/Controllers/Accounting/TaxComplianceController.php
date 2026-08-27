<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\TaxRecord;
use App\Services\Accounting\TaxComplianceService;
use Illuminate\Http\Request;

class TaxComplianceController extends Controller
{
    public function __construct(
        protected TaxComplianceService $taxService
    ) {}

    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $whtSchedule = $this->taxService->getWhtSchedule($startDate, $endDate);
        $vatSummary = $this->taxService->getVatSummary($startDate, $endDate);

        return view('accounting.tax.index', compact('whtSchedule', 'vatSummary', 'startDate', 'endDate'));
    }
}
