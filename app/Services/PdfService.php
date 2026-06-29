<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    /**
     * Render HTML content to a PDF and save to disk.
     */
    public function generateAndSave(string $html, string $filename, string $title = 'Document'): string
    {
        // Wrap the HTML content in a standard print wrapper with CSS styling
        $styledHtml = view('pdf.document', [
            'content' => $html,
            'title' => $title
        ])->render();

        $pdf = Pdf::loadHTML($styledHtml);

        // Store to public storage disk
        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }

    /**
     * Stream a PDF of the HTML content directly to the browser.
     */
    public function streamPdf(string $html, string $title = 'Document')
    {
        $styledHtml = view('pdf.document', [
            'content' => $html,
            'title' => $title
        ])->render();

        $pdf = Pdf::loadHTML($styledHtml);

        return $pdf->stream($title . '.pdf');
    }
}
