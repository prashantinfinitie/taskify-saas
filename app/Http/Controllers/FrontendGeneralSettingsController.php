<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class FrontendGeneralSettingsController extends Controller
{
    public function index()
    {
        return view('settings.frontend_general_settings');
    }

    public function store_frontend_general_settings(Request $request)
    {
        $request->validate([
            'company_title' => ['required'],
            'team_collab_image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'info_card1_icon_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'info_card2_icon_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'info_card3_icon_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        $settings = [];
        $fetched_data = Setting::where('variable', 'frontend_general_settings')->first();
        if ($fetched_data != null) {
            $settings = json_decode($fetched_data->value, true);
        }

        // Collect all non-file inputs except reserved keys
        $form_val = $request->except(['_token', '_method', 'redirect_url', 'team_collab_image_file', 'info_card1_icon_file', 'info_card2_icon_file', 'info_card3_icon_file', 'attachments']);

        // File handling (same logic as before)
        $form_val['team_collab_image'] = $this->handleFileUpload($request, 'team_collab_image_file', $settings['team_collab_image'] ?? '', 'frontend/images');
        $form_val['info_card1_icon'] = $this->handleFileUpload($request, 'info_card1_icon_file', $settings['info_card1_icon'] ?? '', 'frontend/icons');
        $form_val['info_card2_icon'] = $this->handleFileUpload($request, 'info_card2_icon_file', $settings['info_card2_icon'] ?? '', 'frontend/icons');
        $form_val['info_card3_icon'] = $this->handleFileUpload($request, 'info_card3_icon_file', $settings['info_card3_icon'] ?? '', 'frontend/icons');

        // Hero logos (multiple attachments)
        $form_val['hero_logos'] = $this->handleMultipleFileUploads($request, 'attachments', $settings['hero_logos'] ?? [], 'frontend/hero-logos');

        $data = [
            'variable' => 'frontend_general_settings',
            'value' => json_encode($form_val),
        ];

        if ($fetched_data == null) {
            Setting::create($data);
        } else {
            Setting::where('variable', 'frontend_general_settings')->update($data);
        }

        Session::flash('message', 'Frontend settings saved successfully.');
        return response()->json(['error' => false]);
    }
    private function handleFileUpload(Request $request, $field, $oldFile, $path)
    {
        if ($request->hasFile($field)) {
            if ($oldFile) Storage::disk('public')->delete($oldFile);
            return $request->file($field)->store($path, 'public');
        }
        return $oldFile;
    }

    private function handleMultipleFileUploads(Request $request, $field, $oldFiles, $path)
    {
        if ($request->hasFile($field)) {
            foreach ($oldFiles as $old) {
                Storage::disk('public')->delete($old);
            }
            $newFiles = [];
            foreach ($request->file($field) as $file) {
                $newFiles[] = $file->store($path, 'public');
            }
            return $newFiles;
        }
        return $oldFiles;
    }

    public function store_frontend_about_us_general_settings(Request $request)
    {
        // Validation rules
        $request->validate([
            'header_subtitle' => ['required', 'string', 'max:255'],
            'header_title' => ['required', 'string', 'max:255'],
            'header_description' => ['nullable', 'string'],
            'info_card1_title' => ['nullable', 'string', 'max:255'],
            'info_card1_description' => ['nullable', 'string'],
            'info_card2_title' => ['nullable', 'string', 'max:255'],
            'info_card2_description' => ['nullable', 'string'],
            'info_card3_title' => ['nullable', 'string', 'max:255'],
            'info_card3_description' => ['nullable', 'string'],
            'project_management_subtitle' => ['nullable', 'string', 'max:255'],
            'project_management_title' => ['nullable', 'string', 'max:255'],
            'project_management_description' => ['nullable', 'string'],
            'project_management_feature1' => ['nullable', 'string', 'max:255'],
            'project_management_feature2' => ['nullable', 'string', 'max:255'],
            'project_management_feature3' => ['nullable', 'string', 'max:255'],
            'project_management_feature4' => ['nullable', 'string', 'max:255'],
            'project_management_image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'task_management_subtitle' => ['nullable', 'string', 'max:255'],
            'task_management_title' => ['nullable', 'string', 'max:255'],
            'task_management_description' => ['nullable', 'string'],
            'task_management_feature1' => ['nullable', 'string', 'max:255'],
            'task_management_feature2' => ['nullable', 'string', 'max:255'],
            'task_management_feature3' => ['nullable', 'string', 'max:255'],
            'task_management_feature4' => ['nullable', 'string', 'max:255'],
            'task_management_image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'team_collaboration_subtitle' => ['nullable', 'string', 'max:255'],
            'team_collaboration_title' => ['nullable', 'string', 'max:255'],
            'team_collaboration_description' => ['nullable', 'string'],
            'team_collaboration_feature1' => ['nullable', 'string', 'max:255'],
            'team_collaboration_feature2' => ['nullable', 'string', 'max:255'],
            'team_collaboration_feature3' => ['nullable', 'string', 'max:255'],
            'team_collaboration_feature4' => ['nullable', 'string', 'max:255'],
            'team_collaboration_image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'increased_productivity_subtitle' => ['nullable', 'string', 'max:255'],
            'increased_productivity_title' => ['nullable', 'string', 'max:255'],
            'increased_productivity_description' => ['nullable', 'string'],
            'increased_productivity_feature1' => ['nullable', 'string', 'max:255'],
            'increased_productivity_feature2' => ['nullable', 'string', 'max:255'],
            'increased_productivity_feature3' => ['nullable', 'string', 'max:255'],
            'increased_productivity_feature4' => ['nullable', 'string', 'max:255'],
            'increased_productivity_image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'system_overview' => ['nullable', 'string', 'max:255'],
            'discover_our_system' => ['nullable', 'string', 'max:255'],
            'carousel_image1_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'], // max 2 MB
            'carousel_image2_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
            'carousel_image3_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
            'carousel_image4_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg', 'max:2048'],
        ]);

        // Fetch existing settings
        $fetched_data = Setting::where('variable', 'frontend_about_us_settings')->first();
        $settings = $fetched_data ? json_decode($fetched_data->value, true) : [];
        
        // Collect all non-file inputs
        $form_val = $request->except([
            '_token',
            '_method',
            'redirect_url',
            'project_management_image_file',
            'task_management_image_file',
            'team_collaboration_image_file',
            'increased_productivity_image_file',
            'carousel_image1_file',
            'carousel_image2_file',
            'carousel_image3_file',
            'carousel_image4_file',
        ]);

        // Handle project_management_image
        $old_project_management_image = isset($settings['project_management_image']) && !empty($settings['project_management_image']) ? $settings['project_management_image'] : '';
        if ($request->hasFile('project_management_image_file')) {
            Storage::disk('public')->delete($old_project_management_image);
            $form_val['project_management_image'] = $request->file('project_management_image_file')->store('frontend/images', 'public');
        } else {
            $form_val['project_management_image'] = $old_project_management_image;
        }

        // Handle task_management_image
        $old_task_management_image = isset($settings['task_management_image']) && !empty($settings['task_management_image']) ? $settings['task_management_image'] : '';
        if ($request->hasFile('task_management_image_file')) {
            Storage::disk('public')->delete($old_task_management_image);
            $form_val['task_management_image'] = $request->file('task_management_image_file')->store('frontend/images', 'public');
        } else {
            $form_val['task_management_image'] = $old_task_management_image;
        }

        // Handle team_collaboration_image
        $old_team_collaboration_image = isset($settings['team_collaboration_image']) && !empty($settings['team_collaboration_image']) ? $settings['team_collaboration_image'] : '';
        if ($request->hasFile('team_collaboration_image_file')) {
            Storage::disk('public')->delete($old_team_collaboration_image);
            $form_val['team_collaboration_image'] = $request->file('team_collaboration_image_file')->store('frontend/images', 'public');
        } else {
            $form_val['team_collaboration_image'] = $old_team_collaboration_image;
        }

        // Handle increased_productivity_image
        $old_increased_productivity_image = isset($settings['increased_productivity_image']) && !empty($settings['increased_productivity_image']) ? $settings['increased_productivity_image'] : '';
        if ($request->hasFile('increased_productivity_image_file')) {
            Storage::disk('public')->delete($old_increased_productivity_image);
            $form_val['increased_productivity_image'] = $request->file('increased_productivity_image_file')->store('frontend/images', 'public');
        } else {
            $form_val['increased_productivity_image'] = $old_increased_productivity_image;
        }

        // Handle carousel_image1
        $old_carousel_image1 = isset($settings['carousel_image1_file']) && !empty($settings['carousel_image1_file']) ? $settings['carousel_image1_file'] : '';
        if ($request->hasFile('carousel_image1_file')) {
            Storage::disk('public')->delete($old_carousel_image1);
            $form_val['carousel_image1_file'] = $request->file('carousel_image1_file')->store('frontend/images', 'public');
        } else {
            $form_val['carousel_image1_file'] = $old_carousel_image1;
        }

        // Handle carousel_image2
        $old_carousel_image2 = isset($settings['carousel_image2_file']) && !empty($settings['carousel_image2_file']) ? $settings['carousel_image2_file'] : '';
        if ($request->hasFile('carousel_image2_file')) {
            Storage::disk('public')->delete($old_carousel_image2);
            $form_val['carousel_image2_file'] = $request->file('carousel_image2_file')->store('frontend/images', 'public');
        } else {
            $form_val['carousel_image2_file'] = $old_carousel_image2;
        }

        // Handle carousel_image3
        $old_carousel_image3 = isset($settings['carousel_image3_file']) && !empty($settings['carousel_image3_file']) ? $settings['carousel_image3_file'] : '';
        if ($request->hasFile('carousel_image3_file')) {
            Storage::disk('public')->delete($old_carousel_image3);
            $form_val['carousel_image3_file'] = $request->file('carousel_image3_file')->store('frontend/images', 'public');
        } else {
            $form_val['carousel_image3_file'] = $old_carousel_image3;
        }

        // Handle carousel_image4
        $old_carousel_image4 = isset($settings['carousel_image4_file']) && !empty($settings['carousel_image4_file']) ? $settings['carousel_image4_file'] : '';
        if ($request->hasFile('carousel_image4_file')) {
            Storage::disk('public')->delete($old_carousel_image4);
            $form_val['carousel_image4_file'] = $request->file('carousel_image4_file')->store('frontend/images', 'public');
        } else {
            $form_val['carousel_image4_file'] = $old_carousel_image4;
        }

        // Prepare data for storage
        $data = [
            'variable' => 'frontend_about_us_settings',
            'value' => json_encode($form_val),
        ];

        // Create or update settings
        if ($fetched_data == null) {
            Setting::create($data);
        } else {
            Setting::where('variable', 'frontend_about_us_settings')->update($data);
        }

        // Flash success message
        Session::flash('message', 'Frontend About us settings saved successfully.');
        return response()->json(['error' => false]);
    }

    public function store_features_settings(Request $request)
    {
        // Validate the features array
        $request->validate([
            'features' => ['required', 'array', 'min:1'],
            'features.*.title' => ['required', 'string', 'max:255'],
            'features.*.description' => ['required', 'string'],
            'features.*.icon' => ['nullable', 'image', 'mimes:svg', 'max:2048'],
        ]);

        $settings = [];
        $fetched_data = Setting::where('variable', 'frontend_features_settings')->first();
        if ($fetched_data != null) {
            $settings = json_decode($fetched_data->value, true);
        }

        // Process features array
        $processed_features = [];
        $existing_features = $settings['features'] ?? [];

        foreach ($request->features as $index => $feature) {
            $processed_feature = [
                'title' => $feature['title'],
                'description' => $feature['description'],
            ];

            // Handle feature icon upload
            $file_key = "features.{$index}.icon";
            $existing_icon = $existing_features[$index]['icon'] ?? '';

            if ($request->hasFile($file_key)) {
                if ($existing_icon) {
                    Storage::disk('public')->delete($existing_icon);
                }
                $processed_feature['icon'] = $request->file($file_key)->store('frontend/features', 'public');
            } else {
                // Keep existing icon if no new file uploaded
                $processed_feature['icon'] = $existing_icon;
            }

            $processed_features[] = $processed_feature;
        }

        $data = [
            'variable' => 'frontend_features_settings',
            'value' => json_encode(['features' => $processed_features]),
        ];

        if ($fetched_data == null) {
            Setting::create($data);
        } else {
            Setting::where('variable', 'frontend_features_settings')->update($data);
        }

        Session::flash('message', 'Features settings saved successfully.');
        return response()->json(['error' => false]);
    }
}
