<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ManifestController extends Controller
{
    public function index()
    {
        // Get screenshots from config and format them properly
        $screenshots = Config::get('laravelpwa.manifest.custom.screenshots', []);
        $formattedScreenshots = [];
        
        foreach ($screenshots as $screenshot) {
            if (!empty($screenshot['src'])) {
                $formattedScreenshot = [
                    'src' => '/' . ltrim($screenshot['src'], '/'),
                    'sizes' => $screenshot['sizes'] ?? '1280x720',
                    'type' => $screenshot['type'] ?? 'image/png',
                    'form_factor' => $screenshot['form_factor'] ?? 'wide'
                ];
                
                if (!empty($screenshot['label'])) {
                    $formattedScreenshot['label'] = $screenshot['label'];
                }
                
                $formattedScreenshots[] = $formattedScreenshot;
            }
        }

        // Get settings from config
        $manifest = [
            'name' => Config::get('laravelpwa.manifest.name'),
            'short_name' => Config::get('laravelpwa.manifest.short_name'),
            'start_url' => Config::get('laravelpwa.manifest.start_url', '/'),
            'background_color' => Config::get('laravelpwa.manifest.background_color'),
            'theme_color' => Config::get('laravelpwa.manifest.theme_color'),
            'display' => Config::get('laravelpwa.manifest.display', 'standalone'),
            'description' => Config::get('laravelpwa.manifest.description'),
            'icons' => Config::get('laravelpwa.manifest.icons', []),
            'screenshots' => $formattedScreenshots,
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }
}