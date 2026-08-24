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
            'payment_date' => 'nullable|date',
            'send_receipt_email' => 'nullable',
        ]);

        $validated['send_receipt_email'] = $request->has('send_receipt_email');

        $this->paymentService->recordMilestonePayment($milestone, $validated, $validated['send_receipt_email']);

        $msg = 'Payment of ₦' . number_format($validated['amount_paid'], 2) . ' successfully recorded!';
        if (!$validated['send_receipt_email']) {
            $msg .= ' (Silent / Historical Mode - No client email sent).';
        }

        return back()->with('success', $msg);
    }

    /**
     * Mark milestone payment as Verified (super_admin / company_admin only).
     */
    public function verifyPayment(Request $request, PaymentMilestone $milestone)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isCompanyAdmin()) {
            abort(403, 'Only Company Admins or Super Admins can verify client milestone payments.');
        }

        if ($milestone->amount_paid <= 0) {
            return back()->with('error', 'Cannot verify a milestone that has zero amount paid.');
        }

        $this->paymentService->verifyMilestonePayment($milestone, $user->id);

        return back()->with('success', 'Milestone payment verified! Marketer commission calculated & queued for monthly payroll.');
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
