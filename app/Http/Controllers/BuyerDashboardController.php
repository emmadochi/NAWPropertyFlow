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
     * Authenticate a client instantly and securely via their 1-tap Magic Portal Token.
     */
    public function magicLogin(Request $request, string $token)
    {
        // 1. Find lead with matching 64-char token
        $lead = \App\Models\Lead::where('portal_token', $token)->first();

        if (!$lead) {
            abort(404, 'Invalid or expired Client Portal access link.');
        }

        // 2. Ensure customer user account exists for this client email
        $user = null;
        if (!empty($lead->email)) {
            $user = \App\Models\User::where('email', $lead->email)->first();
        }

        if (!$user) {
            $user = \App\Models\User::create([
                'name' => $lead->full_name,
                'email' => $lead->email ?: ('client_' . $lead->id . '@ricafltd.com'),
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                'role' => 'customer',
                'status' => 'active',
                'phone_number' => $lead->phone_number,
                'branch_id' => $lead->branch_id,
            ]);
        }

        // 3. Authenticate user into session
        Auth::login($user, true);

        // 4. Log portal access activity
        app(\App\Services\LeadService::class)->logActivity(
            $lead->id,
            $user->id,
            'Portal Login',
            "Client accessed their private buyer portal via secure 1-tap magic link."
        );

        return redirect()->route('buyer.dashboard')->with('success', 'Welcome to your RICAF Client Portal!');
    }

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
     * Submit Proof of Payment (POP) directly from Buyer Portal.
     */
    public function submitProofOfPayment(Request $request, PaymentMilestone $milestone)
    {
        $user = Auth::user();
        $lead = $milestone->paymentPlan->sale->lead;

        // Security check: ensure milestone belongs to this buyer if customer
        if ($user->role === 'customer' && strtolower($lead->email) !== strtolower($user->email)) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'amount_paid' => 'required|numeric|min:1',
            'bank_reference' => 'required|string|max:100',
            'payment_date' => 'nullable|date',
            'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
            'notes' => 'nullable|string|max:500',
        ]);

        // Upload Proof of Payment file
        $path = $request->file('proof_file')->store('proofs_of_payment', 'public');

        // Update Milestone with submitted POP data
        $milestone->update([
            'amount_paid' => $request->amount_paid,
            'bank_reference' => $request->bank_reference,
            'paid_at' => $request->payment_date ? \Carbon\Carbon::parse($request->payment_date) : now(),
            'proof_of_payment' => $path,
            'pop_submitted_at' => now(),
            'notes' => $request->notes ? ($milestone->notes . "\n[Buyer Note]: " . $request->notes) : $milestone->notes,
            'status' => 'partial', // Mark as submitted/pending audit
        ]);

        // Log Timeline Activity on CRM
        app(\App\Services\LeadService::class)->logActivity(
            $lead->id,
            Auth::id(),
            'Payment Submitted',
            "Client uploaded Proof of Payment (₦" . number_format($request->amount_paid, 2) . ") for '{$milestone->label}' [Ref: {$request->bank_reference}]. Awaiting Admin Verification."
        );

        return back()->with('success', 'Your Proof of Payment has been uploaded successfully! Our accounts desk is reviewing it.');
    }

    /**
     * Generate & stream a payment milestone receipt.
     */
    public function downloadReceipt(PaymentMilestone $milestone)
    {
        $buyerEmail = Auth::user()->email;

        // Security check: ensure milestone belongs to this buyer
        if ($milestone->paymentPlan->sale->lead->email !== $buyerEmail && Auth::user()->role === 'customer') {
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
