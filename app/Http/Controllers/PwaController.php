<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PwaController extends Controller
{
    /**
     * Dynamically serve tenant-specific or central PWA manifest.
     */
    public function manifest(Request $request)
    {
        $setting = rescue(fn() => CompanySetting::getCached(), null);
        $tenant = function_exists('tenant') ? tenant() : null;

        $name = $setting?->company_name 
            ?? ($tenant ? ($tenant->name ?? 'Buckcrest Havens Limited') : config('app.name', 'NAW PropertyFlow CRM'));

        $shortName = Str::limit($name, 16, '');
        $description = "Property Management and Sales CRM for " . $name;

        // Dynamic icon endpoints
        $icon192 = url('/pwa-icon/192');
        $icon512 = url('/pwa-icon/512');

        $manifest = [
            'name'             => $name,
            'short_name'       => $shortName,
            'description'      => $description,
            'id'               => '/',
            'start_url'        => '/',
            'scope'            => '/',
            'display'          => 'standalone',
            'background_color' => '#FFFFFF',
            'theme_color'      => '#0B2545',
            'orientation'      => 'portrait-primary',
            'icons'            => [
                [
                    'src'     => $icon192,
                    'sizes'   => '192x192',
                    'type'    => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src'     => $icon192,
                    'sizes'   => '192x192',
                    'type'    => 'image/png',
                    'purpose' => 'maskable',
                ],
                [
                    'src'     => $icon512,
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src'     => $icon512,
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
            'shortcuts'        => [
                [
                    'name'        => 'Leads',
                    'short_name'  => 'Leads',
                    'description' => 'View and manage CRM leads',
                    'url'         => '/leads',
                    'icons'       => [['src' => $icon192, 'sizes' => '192x192']],
                ],
                [
                    'name'        => 'Properties',
                    'short_name'  => 'Properties',
                    'description' => 'View property listings and units',
                    'url'         => '/properties',
                    'icons'       => [['src' => $icon192, 'sizes' => '192x192']],
                ],
                [
                    'name'        => 'Sales Pipeline',
                    'short_name'  => 'Sales',
                    'description' => 'Track closed deals and payment milestones',
                    'url'         => '/sales',
                    'icons'       => [['src' => $icon192, 'sizes' => '192x192']],
                ],
            ],
        ];

        return response()->json($manifest, 200, [
            'Content-Type'  => 'application/manifest+json; charset=utf-8',
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Stream the appropriate logo/icon for the PWA.
     */
    public function icon(Request $request, $size = 192)
    {
        $setting = rescue(fn() => CompanySetting::getCached(), null);

        if ($setting && $setting->logo_path) {
            $logoFullPath = public_path('storage/' . $setting->logo_path);
            if (file_exists($logoFullPath)) {
                $mimeType = mime_content_type($logoFullPath) ?: 'image/png';
                return response()->file($logoFullPath, [
                    'Content-Type'  => $mimeType,
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
        }

        // Fallback to neutral default icon
        $targetFile = ((int)$size >= 512) ? 'icon-512x512.png' : 'icon-192x192.png';
        $fallback = public_path('icons/' . $targetFile);

        if (file_exists($fallback)) {
            return response()->file($fallback, [
                'Content-Type'  => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        return response()->noContent(404);
    }
}
