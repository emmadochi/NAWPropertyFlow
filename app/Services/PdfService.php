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
        $html = $this->normalizeHtml($html);

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
        $html = $this->normalizeHtml($html);

        $styledHtml = view('pdf.document', [
            'content' => $html,
            'title' => $title
        ])->render();

        $pdf = Pdf::loadHTML($styledHtml);

        return $pdf->stream($title . '.pdf');
    }

    /**
     * Clean page break indicators so DomPDF executes standard page breaks cleanly.
     */
    protected function normalizeHtml(string $html): string
    {
        $html = preg_replace(
            '/<div[^>]*class=["\'][^"\']*page-break[^"\']*["\'][^>]*>.*?<\/div>/is',
            '<div style="page-break-before: always; clear: both;"></div>',
            $html
        );

        $html = str_replace(
            ['<!--pagebreak-->', '[pagebreak]', '✂️ --- PAGE BREAK (Next Page Starts Here) ---'],
            '<div style="page-break-before: always; clear: both;"></div>',
            $html
        );

        return $html;
    }
}
