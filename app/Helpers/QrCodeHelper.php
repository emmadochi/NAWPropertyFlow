<?php

namespace App\Helpers;

/**
 * Pure-PHP QR-style code generator.
 * Produces a visually unique, hash-seeded SVG for each receipt — no external dependencies.
 * Has 3 fixed finder squares (like real QR codes) + a unique data module grid per receipt.
 */
class QrCodeHelper
{
    /**
     * Try to fetch a real QR PNG from QR Server API.
     * Falls back to a hash-seeded unique SVG if the network is unavailable.
     */
    public static function generate(string $data, int $size = 130): string
    {
        // Attempt: real QR via API
        try {
            $url = 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size
                 . '&data=' . rawurlencode($data) . '&format=png&margin=3&color=0f172a&bgcolor=ffffff';
            $ctx = stream_context_create([
                'http' => ['timeout' => 5, 'ignore_errors' => true, 'user_agent' => 'NAWPropertyFlowCRM/1.0'],
                'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);
            $bytes = @file_get_contents($url, false, $ctx);
            if ($bytes && strlen($bytes) > 500) {
                return 'data:image/png;base64,' . base64_encode($bytes);
            }
        } catch (\Throwable $e) {}

        // Fallback: generate unique hash-based QR-style SVG
        return self::generateSvgQr($data, $size);
    }

    /**
     * Generate a unique QR-style SVG using the hash of the data as the seed.
     * Has authentic QR finder patterns + a pseudo-random unique data area.
     */
    private static function generateSvgQr(string $data, int $size): string
    {
        $COLS  = 21;           // 21x21 grid (QR version 1 size)
        $CELL  = floor($size / $COLS);
        $PAD   = ($size - $COLS * $CELL) / 2;
        $DARK  = '#0f172a';
        $LIGHT = '#ffffff';
        $BG    = '#f8fafc';

        // Seed random data modules from hash of input
        $hash   = md5($data);
        $bits   = '';
        for ($i = 0; $i < strlen($hash); $i++) {
            $bits .= str_pad(decbin(hexdec($hash[$i])), 4, '0', STR_PAD_LEFT);
        }
        // bits is 128 chars long; we'll tile it if needed
        $bits = str_repeat($bits, 10);

        // Build the module grid
        $grid = [];
        for ($r = 0; $r < $COLS; $r++) {
            for ($c = 0; $c < $COLS; $c++) {
                $grid[$r][$c] = false; // default light
            }
        }

        // Draw the 3 QR finder patterns (top-left, top-right, bottom-left)
        $finders = [[0,0],[0,14],[14,0]];
        foreach ($finders as [$fr, $fc]) {
            for ($dr = 0; $dr < 7; $dr++) {
                for ($dc = 0; $dc < 7; $dc++) {
                    $dark = ($dr === 0 || $dr === 6 || $dc === 0 || $dc === 6 ||
                             ($dr >= 2 && $dr <= 4 && $dc >= 2 && $dc <= 4));
                    $grid[$fr + $dr][$fc + $dc] = $dark;
                }
            }
        }

        // Mark the separator zones around finders as light (reserved)
        $reserved = [];
        foreach ([[0,0],[0,14],[14,0]] as [$fr,$fc]) {
            for ($dr = -1; $dr <= 7; $dr++) {
                for ($dc = -1; $dc <= 7; $dc++) {
                    $rr = $fr + $dr; $cc = $fc + $dc;
                    if ($rr >= 0 && $rr < $COLS && $cc >= 0 && $cc < $COLS) {
                        $reserved["$rr,$cc"] = true;
                    }
                }
            }
        }

        // Timing patterns (row 6, col 6)
        for ($i = 8; $i < 13; $i++) {
            $grid[6][$i] = ($i % 2 === 0);
            $grid[$i][6] = ($i % 2 === 0);
            $reserved["6,$i"] = true;
            $reserved["$i,6"] = true;
        }

        // Fill data area with hash bits
        $bi = 0;
        for ($r = 0; $r < $COLS; $r++) {
            for ($c = 0; $c < $COLS; $c++) {
                if (!isset($reserved["$r,$c"]) && !self::isFinderArea($r, $c)) {
                    $grid[$r][$c] = ($bits[$bi % strlen($bits)] === '1');
                    $bi++;
                }
            }
        }

        // Build SVG
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">';
        $svg .= '<rect width="' . $size . '" height="' . $size . '" fill="' . $BG . '" rx="4"/>';

        for ($r = 0; $r < $COLS; $r++) {
            for ($c = 0; $c < $COLS; $c++) {
                if ($grid[$r][$c]) {
                    $x = round($PAD + $c * $CELL, 1);
                    $y = round($PAD + $r * $CELL, 1);
                    $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $CELL . '" height="' . $CELL . '" fill="' . $DARK . '"/>';
                }
            }
        }

        $svg .= '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private static function isFinderArea(int $r, int $c): bool
    {
        // top-left 7x7
        if ($r <= 7 && $c <= 7) return true;
        // top-right 7x7
        if ($r <= 7 && $c >= 13) return true;
        // bottom-left 7x7
        if ($r >= 13 && $c <= 7) return true;
        return false;
    }
}
