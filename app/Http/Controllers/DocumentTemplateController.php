<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentTemplateController extends Controller
{
    public function index()
    {
        $templates = DocumentTemplate::with('latestVersion', 'creator')
            ->orderBy('name')
            ->paginate(15);

        return view('document-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('document-templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'trigger_event' => 'required|in:deal_won,payment_received,inspection_completed',
            'is_active'     => 'nullable|boolean',
            'content'       => 'required|string',
        ]);

        $template = DocumentTemplate::create([
            'name'               => $validated['name'],
            'trigger_event'      => $validated['trigger_event'],
            'is_active'          => $request->has('is_active'),
            'created_by_user_id' => Auth::id() ?? 1,
        ]);

        DocumentTemplateVersion::create([
            'document_template_id' => $template->id,
            'version_number'       => 1,
            'content'              => $validated['content'],
            'created_by_user_id'   => Auth::id() ?? 1,
        ]);

        return redirect()->route('document-templates.show', $template)
            ->with('success', 'Document template created successfully.');
    }

    public function show(DocumentTemplate $documentTemplate)
    {
        $documentTemplate->load('versions.creator', 'creator');
        return view('document-templates.show', [
            'template' => $documentTemplate
        ]);
    }

    public function edit(DocumentTemplate $documentTemplate)
    {
        $documentTemplate->load('latestVersion');
        return view('document-templates.editor', [
            'template' => $documentTemplate
        ]);
    }

    public function update(Request $request, DocumentTemplate $documentTemplate)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'trigger_event' => 'required|in:deal_won,payment_received,inspection_completed',
            'is_active'     => 'nullable|boolean',
            'content'       => 'required|string',
        ]);

        $documentTemplate->update([
            'name'          => $validated['name'],
            'trigger_event' => $validated['trigger_event'],
            'is_active'     => $request->has('is_active'),
        ]);

        // Get latest version number
        $latest = $documentTemplate->latestVersion;
        $nextVersion = $latest ? $latest->version_number + 1 : 1;

        DocumentTemplateVersion::create([
            'document_template_id' => $documentTemplate->id,
            'version_number'       => $nextVersion,
            'content'              => $validated['content'],
            'created_by_user_id'   => Auth::id() ?? 1,
        ]);

        return redirect()->route('document-templates.show', $documentTemplate)
            ->with('success', 'Document template and new version saved successfully.');
    }

    public function destroy(DocumentTemplate $documentTemplate)
    {
        $documentTemplate->delete();
        return redirect()->route('document-templates.index')
            ->with('success', 'Template removed.');
    }
}
