<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PWAController extends Controller
{
    public function showSettings()
    {
        return view('settings.pwa_settings');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:12',
            'theme_color' => 'required|string',
            'background_color' => 'required|string',
            'description' => 'required|string',
            'logo' => 'nullable|image|mimes:png|max:2048',
            // Add screenshot validation
            'screenshot_file' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'screenshot_label' => 'nullable|string|max:255',
            'screenshot_form_factor' => 'nullable|in:wide,narrow'
        ]);

        $settings = [];
        $fetched_data = Setting::where('variable', 'pwa_settings')->first();

        if ($fetched_data) {
            $settings = json_decode($fetched_data->value, true);
        }

        // Store old logo and screenshot
        $oldLogo = $settings['pwa_logo'] ?? '';
        $oldScreenshot = $settings['pwa_screenshot'] ?? [];

        // Handle new logo upload
        if ($request->hasFile('logo')) {
            if (!empty($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $settings['pwa_logo'] = $request->file('logo')->store('pwa', 'public');
        } else {
            $settings['pwa_logo'] = $oldLogo;
        }

        // Handle screenshot removal
        if ($request->has('remove_screenshot') && $request->remove_screenshot == '1') {
            // Delete current screenshot file if exists
            if (!empty($oldScreenshot['src'])) {
                // Remove 'storage/' prefix if it exists to get the actual storage path
                $screenshotPath = str_replace('storage/', '', $oldScreenshot['src']);
                Storage::disk('public')->delete($screenshotPath);
            }
            $settings['pwa_screenshot'] = [];
        }
        // Handle new screenshot upload
        elseif ($request->hasFile('screenshot_file')) {
            // Delete old screenshot if exists
            if (!empty($oldScreenshot['src'])) {
                $screenshotPath = str_replace('storage/', '', $oldScreenshot['src']);
                Storage::disk('public')->delete($screenshotPath);
            }

            $screenshotFile = $request->file('screenshot_file');
            $screenshotPath = $screenshotFile->store('pwa/screenshots', 'public');

            // Get image dimensions for sizes
            $imagePath = storage_path('app/public/' . $screenshotPath);
            $imageSize = getimagesize($imagePath);
            $width = $imageSize[0] ?? 1280;
            $height = $imageSize[1] ?? 720;

            $settings['pwa_screenshot'] = [
                'src' => $screenshotPath,
                'label' => $request->screenshot_label ?? 'App Screenshot',
                'form_factor' => $request->screenshot_form_factor ?? 'wide',
                'sizes' => $width . 'x' . $height,
                'type' => 'image/' . $screenshotFile->getClientOriginalExtension()
            ];
        }
        // Update existing screenshot metadata only (if screenshot exists but no new file uploaded)
        elseif (!empty($oldScreenshot['src'])) {
            $settings['pwa_screenshot'] = [
                'src' => $oldScreenshot['src'],
                'label' => $request->screenshot_label ?? $oldScreenshot['label'] ?? 'App Screenshot',
                'form_factor' => $request->screenshot_form_factor ?? $oldScreenshot['form_factor'] ?? 'wide',
                'sizes' => $oldScreenshot['sizes'] ?? '1280x720',
                'type' => $oldScreenshot['type'] ?? 'image/png'
            ];
        } else {
            // Ensure pwa_screenshot is set as empty array if no screenshot
            $settings['pwa_screenshot'] = [];
        }

        // Update remaining settings
        $settings['pwa_name'] = $request->input('name');
        $settings['pwa_short_name'] = $request->input('short_name');
        $settings['pwa_theme_color'] = $request->input('theme_color');
        $settings['pwa_background_color'] = $request->input('background_color');
        $settings['pwa_description'] = $request->input('description');

        // Save updated data
        $data = [
            'variable' => 'pwa_settings',
            'value' => json_encode($settings),
        ];

        if ($fetched_data === null) {
            Setting::create($data);
        } else {
            Setting::where('variable', 'pwa_settings')->update($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'PWA settings updated successfully',
            'requires_reinstall' => true
        ]);
    }
}
