<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\PaymentPlan;
use App\Models\PaymentMilestone;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Show form to build a payment plan.
     */
    public function createPlan(Sale $sale)
    {
        return view('payments.plan', compact('sale'));
    }

    /**
     * Store new payment plan.
     */
    public function storePlan(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'plan_type' => 'required|in:outright,installment,mortgage',
            'number_of_installments' => 'nullable|integer|min:1',
            'milestones' => 'nullable|array',
            'milestones.*.label' => 'required|string',
            'milestones.*.amount_due' => 'required|numeric|min:0',
            'milestones.*.due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $this->paymentService->createPlan($sale, $validated);

        return redirect()->route('leads.show', $sale->lead_id)
            ->with('success', 'Payment plan and milestones configured successfully!');
    }

    /**
     * Show milestones and payments for a plan.
     */
    public function showPlan(PaymentPlan $paymentPlan)
    {
        $paymentPlan->load(['sale.lead', 'sale.property', 'milestones']);
        return view('payments.milestones', compact('paymentPlan'));
    }

    /**
     * Record milestone payment manually.
     */
    public function recordPayment(Request $request, PaymentMilestone $milestone)
    {
        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:0.01',
            'bank_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $this->paymentService->recordMilestonePayment($milestone, $validated);

        return back()->with('success', 'Payment of ₦' . number_format($validated['amount_paid'], 2) . ' successfully recorded!');
    }

    /**
     * Generate & stream receipt PDF.
     */
    public function downloadReceipt(PaymentMilestone $milestone)
    {
        $paymentPlan = $milestone->paymentPlan;
        $sale = $paymentPlan->sale;
        $lead = $sale->lead;
        $property = $sale->property;

        $pdf = Pdf::loadView('pdf.receipt', compact('milestone', 'paymentPlan', 'sale', 'lead', 'property'));

        return $pdf->stream('receipt_' . $milestone->id . '.pdf');
    }
}
