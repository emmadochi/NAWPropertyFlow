<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Document;
use App\Models\GeneratedDocument;
use App\Models\PaymentMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class BuyerDashboardController extends Controller
{
    /**
     * Display the Buyer Portal Dashboard.
     */
    public function index()
    {
        $buyerEmail = Auth::user()->email;

        // Fetch sales associated with buyer email
        $sales = Sale::whereHas('lead', function ($query) use ($buyerEmail) {
            $query->where('email', $buyerEmail);
        })->with([
            'property.project.milestones',
            'propertyUnit',
            'paymentPlan.milestones',
            'salesOfficer'
        ])->get();

        $leadIds = $sales->pluck('lead_id')->toArray();

        // Fetch manual documents uploaded for this buyer
        $documents = Document::whereIn('lead_id', $leadIds)->get();

        // Fetch generated documents (Allocation Letters, contract PDFs, etc.)
        $generatedDocuments = GeneratedDocument::whereIn('lead_id', $leadIds)->with('template')->get();

        // Aggregate high-level stats
        $totalInvested = $sales->sum(function($sale) {
            return $sale->paymentPlan ? (float)$sale->paymentPlan->amount_paid : 0.00;
        });

        $totalBalance = $sales->sum(function($sale) {
            return $sale->paymentPlan ? (float)$sale->paymentPlan->balance : 0.00;
        });

        $unitsCount = $sales->sum('units_purchased');

        return view('buyer.dashboard', compact(
            'sales',
            'documents',
            'generatedDocuments',
            'totalInvested',
            'totalBalance',
            'unitsCount'
        ));
    }

    /**
     * Generate & stream a payment milestone receipt.
     */
    public function downloadReceipt(PaymentMilestone $milestone)
    {
        $buyerEmail = Auth::user()->email;

        // Security check: ensure milestone belongs to this buyer
        if ($milestone->paymentPlan->sale->lead->email !== $buyerEmail) {
            abort(403, 'Unauthorized receipt download.');
        }

        $paymentPlan = $milestone->paymentPlan;
        $sale = $paymentPlan->sale;
        $lead = $sale->lead;
        $property = $sale->property;

        $pdf = Pdf::loadView('pdf.receipt', compact('milestone', 'paymentPlan', 'sale', 'lead', 'property'));

        return $pdf->stream('receipt_' . $milestone->id . '.pdf');
    }

    /**
     * Download manual uploaded documents.
     */
    public function downloadDocument(Document $document)
    {
        $buyerEmail = Auth::user()->email;

        // Security check: ensure document belongs to this buyer
        if ($document->lead->email !== $buyerEmail) {
            abort(403, 'Unauthorized document access.');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found on storage.');
        }

        return Storage::disk('public')->download(
            $document->file_path,
            $document->name . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION)
        );
    }

    /**
     * Download generated PDF templates (contracts, allocation letters).
     */
    public function downloadGeneratedDocument(GeneratedDocument $document)
    {
        $buyerEmail = Auth::user()->email;

        // Security check: ensure document belongs to this buyer
        if ($document->lead->email !== $buyerEmail) {
            abort(403, 'Unauthorized document access.');
        }

        if (!$document->pdf_path || !Storage::disk('public')->exists($document->pdf_path)) {
            abort(404, 'PDF file not found on disk.');
        }

        return Storage::disk('public')->download($document->pdf_path, basename($document->pdf_path));
    }
}
