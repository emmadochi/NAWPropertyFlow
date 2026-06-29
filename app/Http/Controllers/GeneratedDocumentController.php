<?php

namespace App\Http\Controllers;

use App\Models\GeneratedDocument;
use App\Models\Lead;
use App\Models\DocumentTemplate;
use App\Services\DocumentTemplateService;
use App\Mail\GeneratedDocumentMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class GeneratedDocumentController extends Controller
{
    protected $templateService;

    public function __construct(DocumentTemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    public function index()
    {
        $documents = GeneratedDocument::with('lead', 'template', 'generator')
            ->latest()
            ->paginate(15);

        return view('generated-documents.index', compact('documents'));
    }

    public function show(GeneratedDocument $document)
    {
        return view('generated-documents.show', compact('document'));
    }

    public function download(GeneratedDocument $document)
    {
        if (!$document->pdf_path || !Storage::disk('public')->exists($document->pdf_path)) {
            return back()->with('error', 'PDF file not found on disk.');
        }

        return Storage::disk('public')->download($document->pdf_path, basename($document->pdf_path));
    }

    public function email(GeneratedDocument $document)
    {
        $lead = $document->lead;
        if (!$lead->email) {
            return back()->with('error', 'Lead email address is missing.');
        }

        try {
            Mail::to($lead->email)->send(new GeneratedDocumentMail($document));
            return back()->with('success', "Document mailed to {$lead->name} successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Mail server error: ' . $e->getMessage());
        }
    }

    /**
     * Manually trigger document generation for a lead.
     */
    public function generate(Request $request)
    {
        // Only super admins and company admins can manually compile documents
        if (!in_array(Auth::user()->role, ['super_admin', 'company_admin'])) {
            abort(403, 'Only administrators can generate documents.');
        }

        $validated = $request->validate([
            'lead_id'              => 'required|exists:leads,id',
            'document_template_id' => 'required|exists:document_templates,id',
        ]);

        $lead = Lead::findOrFail($validated['lead_id']);
        $template = DocumentTemplate::findOrFail($validated['document_template_id']);

        // Check if lead has active sales to link
        $sale = $lead->sales()->latest()->first();

        $doc = $this->templateService->compileAndGenerate($template, $lead, $sale);

        return redirect()->route('generated-documents.show', $doc)
            ->with('success', 'Document compiled and generated successfully.');
    }
}
