<?php

namespace App\Helpers;

class QrCodeHelper
{
    public static function generate(string $data, int $size = 120): string
    {
        try {
            $url = 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size
                . '&data=' . rawurlencode($data) . '&format=png&margin=4';
            $ctx = stream_context_create([
                'http' => ['timeout' => 4, 'ignore_errors' => true, 'user_agent' => 'NAWPropertyFlowCRM'],
                'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);
            $bytes = @file_get_contents($url, false, $ctx);
            if ($bytes && strlen($bytes) > 200) {
                return 'data:image/png;base64,' . base64_encode($bytes);
            }
        } catch (\Throwable $e) {}
        return self::svgFallback($size);
    }

    private static function svgFallback(int $size): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 120 120">'
            . '<rect width="120" height="120" fill="#f8fafc" stroke="#e2e8f0" stroke-width="2" rx="4"/>'
            . '<rect x="10" y="10" width="30" height="30" fill="none" stroke="#1e293b" stroke-width="3"/>'
            . '<rect x="16" y="16" width="12" height="12" fill="#1e293b"/>'
            . '<rect x="80" y="10" width="30" height="30" fill="none" stroke="#1e293b" stroke-width="3"/>'
            . '<rect x="86" y="16" width="12" height="12" fill="#1e293b"/>'
            . '<rect x="10" y="80" width="30" height="30" fill="none" stroke="#1e293b" stroke-width="3"/>'
            . '<rect x="16" y="86" width="12" height="12" fill="#1e293b"/>'
            . '<text x="60" y="112" text-anchor="middle" font-size="7" fill="#94a3b8" font-family="sans-serif">Verify Receipt</text>'
            . '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
