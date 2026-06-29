<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    protected $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Store uploaded lead document.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:KYC,Contract,Agreement,Payment Receipt',
            'document_file' => 'required|file|mimes:pdf,jpg,png,jpeg,doc,docx|max:10240', // max 10MB
        ]);

        $lead = Lead::findOrFail($request->lead_id);

        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            // Store under storage/app/public/documents
            $path = $file->store('documents', 'public');

            $document = Document::create([
                'lead_id' => $lead->id,
                'name' => $request->name,
                'file_path' => $path,
                'category' => $request->category,
                'uploaded_by' => Auth::id(),
            ]);

            // Log Activity on Lead
            $this->leadService->logActivity(
                $lead->id,
                Auth::id(),
                'Document Uploaded',
                "Uploaded a '{$document->category}' document named: {$document->name}."
            );

            return back()->with('success', 'Document uploaded successfully.');
        }

        return back()->withErrors(['document_file' => 'File upload failed. Please try again.']);
    }

    /**
     * Download lead document.
     */
    public function download(Document $document)
    {
        $user = Auth::user();

        // Security check: Sales Executives can only download documents for leads assigned to them
        if ($user->role === 'sales_executive' && $document->lead->assigned_to !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found on storage.');
        }

        return Storage::disk('public')->download($document->file_path, $document->name . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    /**
     * Delete document.
     */
    public function destroy(Document $document)
    {
        $user = Auth::user();

        // Only managers or admin can delete files
        if (Auth::user()->role === 'sales_executive') {
            abort(403, 'Unauthorized.');
        }

        $lead = $document->lead;

        // Delete from storage
        Storage::disk('public')->delete($document->file_path);
        
        $document->delete();

        // Log Activity on Lead
        $this->leadService->logActivity(
            $lead->id,
            Auth::id(),
            'Updated',
            "Deleted document: {$document->name}."
        );

        return back()->with('success', 'Document deleted successfully.');
    }
}
