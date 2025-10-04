<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Status;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Language;
use App\Models\Priority;
use App\Models\CustomField;
use Faker\Extension\Helper;
use App\Models\ThemeSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use App\Services\CustomPathGenerator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PathGenerator::class, CustomPathGenerator::class);
    }

    public function boot()
    {
        set_time_limit(3600);
        Paginator::useBootstrapFive();

        try {
            DB::connection()->getPdo();
            $this->setupSettings();
            $this->setupViewComposer();

            $pwaSettings = $this->getPwaSettings();
            $active_theme = $this->getActiveTheme();

            View::share('active_theme', $active_theme);

            // Set reCAPTCHA configuration for anhskohbo/no-captcha
            $security_settings = $this->getSecuritySettings();
            Config::set('captcha', [
                'sitekey' => $security_settings['recaptcha_site_key'] ?? '',
                'secret' => $security_settings['recaptcha_secret_key'] ?? '',
                'options' => config('captcha.options', []) // Preserve other options if any
            ]);

            // Existing PWA settings configuration
            Config::set('laravelpwa.name', $pwaSettings['pwa_name']);
            Config::set('laravelpwa.manifest.name', $pwaSettings['pwa_name']);
            Config::set('laravelpwa.manifest.short_name', $pwaSettings['pwa_short_name']);
            Config::set('laravelpwa.manifest.description', $pwaSettings['pwa_description']);
            Config::set('laravelpwa.manifest.theme_color', $pwaSettings['pwa_theme_color']);
            Config::set('laravelpwa.manifest.background_color', $pwaSettings['pwa_background_color']);
        } catch (\Exception $e) {
            // Log the exception or handle it as needed
        }

        if (!empty($pwaSettings['pwa_logo'])) {
            Config::set('laravelpwa.manifest.icons', [
                [
                    'src' => '/' . $pwaSettings['pwa_logo'],
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ]
            ]);
        }

        if (!empty($pwaSettings['pwa_screenshot']) && !empty($pwaSettings['pwa_screenshot']['src'])) {
            Config::set('laravelpwa.manifest.custom.screenshots', [$pwaSettings['pwa_screenshot']]);
        } else {
            Config::set('laravelpwa.manifest.custom.screenshots', []);
        }


        $this->loadPlugins();
    }

    private function setupSettings()
    {
        $general_settings = $this->getGeneralSettings();
        $pusher_settings = $this->getPusherSettings();
        $security_settings = $this->getSecuritySettings();
        $email_settings = $this->getEmailSettings();
        $media_storage_settings = $this->getMediaStorageSettings();

        $this->updateConfigs($general_settings, $pusher_settings, $email_settings, $media_storage_settings);

        // Register php_date_format here
        $date_format = explode('|', $general_settings['date_format']);
        $php_date_format = $date_format[1];
        $this->app->instance('php_date_format', $php_date_format);
    }

    private function setupViewComposer()
    {
        View::composer('*', function ($view) {
            $languages = Language::all();
            $general_settings = $this->getGeneralSettings();
            $frontend_general_settings = $this->getFrontendGeneralSettings();
            $pusher_settings = $this->getPusherSettings();
            $email_settings = $this->getEmailSettings();
            $media_storage_settings = $this->getMediaStorageSettings();
            $security_settings = $this->getSecuritySettings();
            $ai_model_settings = $this->getAIModelSettings();
            $pwa_settings = $this->getPwaSettings();
            $active_theme = $this->getActiveTheme();

            $date_format = explode('|', $general_settings['date_format']);
            $js_date_format = $date_format[0];
            $php_date_format = $date_format[1];

            $TimeTrackerProjects = [];
            if (Session::has('workspace_id')) {
                $TimeTrackerProjects = Project::where('workspace_id', Session::get('workspace_id'))->get();
            }

            $TimeTrackerTasks = [];
            if (Session::has('workspace_id')) {
                $TimeTrackerTasks = Task::where('workspace_id', Session::get('workspace_id'))->get();
            }

            $data = compact(
                'general_settings',
                'frontend_general_settings',
                'email_settings',
                'pusher_settings',
                'pwa_settings',
                'active_theme',
                'media_storage_settings',
                'languages',
                'js_date_format',
                'php_date_format',
                'security_settings',
                'ai_model_settings',
                'TimeTrackerProjects',
                'TimeTrackerTasks'
            );

            $view->with($data);

            if (Session::has('workspace_id')) {
                $projects = Project::where('workspace_id', Session::get('workspace_id'))->get();
            }

            $taskCustomFields = CustomField::where('module', 'task')->get();
            $projectCustomFields = CustomField::where('module', 'project')->get();

            if (Auth::guard('web')->check() || Auth::guard('client')->check()) {
                $adminID = getAdminIdByUserRole();
                $admin = Admin::find($adminID);
                $statuses = Status::where('admin_id', $adminID)
                    ->orWhere(function ($query) {
                        $query->whereNull('admin_id')
                            ->where('is_default', 1);
                    })->get();
                $tags = Tag::where('admin_id', $adminID)->get();
                $priorities = Priority::where('admin_id', $adminID)->get();

                $google_calendar_settings = [
                    'calendar_id' => '',
                    'api_key' => ''
                ];

                if ($admin && !empty($admin->admin_settings)) {
                    $adminSettings = json_decode($admin->admin_settings, true);

                    if (isset($adminSettings['google_calendar_settings'])) {
                        $google_calendar_settings = $adminSettings['google_calendar_settings'];
                    }
                }

                $view->with(compact('statuses', 'tags', 'priorities', 'google_calendar_settings'));
            }

            $data = compact(
                'general_settings',
                'email_settings',
                'pusher_settings',
                'media_storage_settings',
                'languages',
                'js_date_format',
                'php_date_format',
                'security_settings',
                'ai_model_settings',
                'TimeTrackerProjects',
                'TimeTrackerTasks',
                'taskCustomFields',
                'projectCustomFields'
            );

            $view->with($data);
        });
    }

    private function getGeneralSettings()
    {
        $general_settings = get_settings('general_settings');

        $defaults = [
            'full_logo' => 'storage/logos/default_full_logo.png',
            'half_logo' => 'storage/logos/default_half_logo.png',
            'favicon' => 'storage/logos/default_favicon.png',
            'footer_logo' => 'storage/logos/footer_logo.png',
            'company_title' => 'Taskify - SaaS',
            'currency_symbol' => '₹',
            'currency_full_form' => 'Indian Rupee',
            'currency_code' => 'INR',
            'date_format' => 'DD-MM-YYYY|d-m-Y',
            'toast_time_out' => '5',
            'toast_position' => 'toast-top-right',
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($general_settings[$key]) || empty($general_settings[$key])) {
                $general_settings[$key] = $value;
            } elseif (in_array($key, ['full_logo', 'half_logo', 'favicon', 'footer_logo'])) {
                $general_settings[$key] = 'storage/' . $general_settings[$key];
            }
        }

        if (getAuthenticatedUser() && !(getAuthenticatedUser()->hasRole("superadmin") || getAuthenticatedUser()->hasRole("manager"))) {
            $adminId = getAdminIdByUserRole();
            $admin = $adminId ? Admin::find($adminId) : null;

            if ($admin && !empty($admin->admin_settings)) {
                $adminSettings = json_decode($admin->admin_settings, true);

                $general_settings['full_logo'] = empty($adminSettings['full_logo']) ? $general_settings['full_logo'] : 'storage/' . $adminSettings['full_logo'];
                $general_settings['half_logo'] = empty($adminSettings['half_logo']) ? $general_settings['half_logo'] : 'storage/' . $adminSettings['half_logo'];
                $general_settings['company_title'] = empty($adminSettings['company_title']) ? $general_settings['company_title'] : $adminSettings['company_title'];
            }
        }

        return $general_settings;
    }

    private function getFrontendGeneralSettings()
    {
        $general_settings = Setting::where('variable', 'frontend_general_settings')->first();
        $about_us_settings = Setting::where('variable', 'frontend_about_us_settings')->first();
        $features_settings = Setting::where('variable', 'frontend_features_settings')->first();

        // Define default values
        $defaults = [
            'company_title' => '',
            'company_description' => '',
            'feature1_title' => '',
            'feature1_description' => '',
            'feature2_title' => '',
            'feature2_description' => '',
            'feature3_title' => '',
            'feature3_description' => '',
            'feature4_title' => '',
            'feature4_description' => '',
            'team_collab_title' => '',
            'team_collab_description' => '',
            'about_section_title1' => '',
            'about_section_title2' => '',
            'about_section_description' => '',
            'info_card1_title' => '',
            'info_card1_description' => '',
            'info_card2_title' => '',
            'info_card2_description' => '',
            'info_card3_title' => '',
            'info_card3_description' => '',
            'team_collab_image' => '',
            'info_card1_icon' => '',
            'info_card2_icon' => '',
            'info_card3_icon' => '',
            'hero_logos' => [],
            'header_subtitle' => '',
            'header_title' => '',
            'header_description' => '',
            'info_about_us_card1_title' => '',
            'info_about_us_card1_description' => '',
            'info_about_us_card2_title' => '',
            'info_about_us_card2_description' => '',
            'info_about_us_card3_title' => '',
            'info_about_us_card3_description' => '',
            'project_management_subtitle' => '',
            'project_management_title' => '',
            'project_management_description' => '',
            'project_management_feature1' => '',
            'project_management_feature2' => '',
            'project_management_feature3' => '',
            'project_management_feature4' => '',
            'project_management_image' => '',
            'task_management_subtitle' => '',
            'task_management_title' => '',
            'task_management_description' => '',
            'task_management_feature1' => '',
            'task_management_feature2' => '',
            'task_management_feature3' => '',
            'task_management_feature4' => '',
            'task_management_image' => '',
            'team_collaboration_subtitle' => '',
            'team_collaboration_title' => '',
            'team_collaboration_description' => '',
            'team_collaboration_feature1' => '',
            'team_collaboration_feature2' => '',
            'team_collaboration_feature3' => '',
            'team_collaboration_feature4' => '',
            'team_collaboration_image' => '',
            'increased_productivity_subtitle' => '',
            'increased_productivity_title' => '',
            'increased_productivity_description' => '',
            'increased_productivity_feature1' => '',
            'increased_productivity_feature2' => '',
            'increased_productivity_feature3' => '',
            'increased_productivity_feature4' => '',
            'increased_productivity_image' => '',
            'system_overview' => '',
            'discover_our_system' => '',
            'carousel_image1_file' => '',
            'carousel_image2_file' => '',
            'carousel_image3_file' => '',
            'carousel_image4_file' => '',
            'features' => [],
        ];

        $settings = [];
        if ($general_settings) {
            $general_data = json_decode($general_settings->value, true);
            $settings = array_merge($settings, $general_data);
        }

        // Merge about us settings
        if ($about_us_settings) {
            $about_us_data = json_decode($about_us_settings->value, true);
            $settings = array_merge($settings, $about_us_data);
        }

        // Merge features settings
        if ($features_settings) {
            $features_data = json_decode($features_settings->value, true);
            $settings = array_merge($settings, $features_data);
        }

        foreach ($defaults as $key => $value) {
            $defaults[$key] = isset($settings[$key]) ? $settings[$key] : $value;
        }

        // Add full storage path for image keys
        foreach (
            [
                'team_collab_image',
                'info_card1_icon',
                'info_card2_icon',
                'info_card3_icon',
                'project_management_image',
                'task_management_image',
                'team_collaboration_image',
                'increased_productivity_image',
                'carousel_image1_file',
                'carousel_image2_file',
                'carousel_image3_file',
                'carousel_image4_file',
            ] as $imgKey
        ) {
            if (!empty($defaults[$imgKey])) {
                $defaults[$imgKey] = 'storage/' . $defaults[$imgKey];
            }
        }

        // Handle hero_logos
        if (!empty($defaults['hero_logos']) && is_array($defaults['hero_logos'])) {
            $defaults['hero_logos'] = array_map(function ($path) {
                return 'storage/' . $path;
            }, $defaults['hero_logos']);
        }

        // Handle feature icons
        if (!empty($defaults['features']) && is_array($defaults['features'])) {
            $defaults['features'] = array_map(function ($feature) {
                if (!empty($feature['icon'])) {
                    $feature['icon'] = 'storage/' . $feature['icon'];
                }
                return $feature;
            }, $defaults['features']);
        }

        return $defaults;
    }

    private function getPwaSettings()
    {
        $pwa_settings = get_settings('pwa_settings') ?: [];

        $defaults = [
            'pwa_name' => 'Taskify',
            'pwa_short_name' => 'Taskify',
            'pwa_theme_color' => '',
            'pwa_background_color' => '',
            'pwa_description' => 'Taskify SaaS is a project management and task management system for handling tasks and projects. It facilitates collaboration, task allocation, scheduling, and tracking of project progress.',
            'pwa_logo' => '',
            'pwa_screenshot' => [],
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($pwa_settings[$key])) {
                $pwa_settings[$key] = $value;
            }
            // Only set empty values for non-array defaults
            if (!is_array($value) && empty($pwa_settings[$key])) {
                $pwa_settings[$key] = $value;
            }
        }

        // Add storage path for logo if it exists
        if (!empty($pwa_settings['pwa_logo'])) {
            $pwa_settings['pwa_logo'] = 'storage/' . $pwa_settings['pwa_logo'];
        }

        // Process single screenshot to add full path
        if (!empty($pwa_settings['pwa_screenshot']) && is_array($pwa_settings['pwa_screenshot'])) {
            if (!empty($pwa_settings['pwa_screenshot']['src']) && !str_starts_with($pwa_settings['pwa_screenshot']['src'], 'storage/')) {
                $pwa_settings['pwa_screenshot']['src'] = 'storage/' . $pwa_settings['pwa_screenshot']['src'];
            }
        }

        return $pwa_settings;
    }

    private function getPusherSettings()
    {
        return array_merge([
            'pusher_app_id' => '',
            'pusher_app_key' => '',
            'pusher_app_secret' => '',
            'pusher_app_cluster' => '',
        ], get_settings('pusher_settings'));
    }

    private function getSecuritySettings()
    {
        $settings = get_settings('security_settings') ?: [];
        return array_merge([
            'max_login_attempts' => 0,
            'time_decay' => 0,
            'enable_recaptcha' => 0,
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => ''
        ], $settings);
    }

    private function getEmailSettings()
    {
        return get_settings('email_settings') ?? [
            'email' => '',
            'password' => '',
            'smtp_host' => '',
            'smtp_port' => '',
            'email_content_type' => '',
            'smtp_encryption' => '',
        ];
    }

    private function getMediaStorageSettings()
    {
        return array_merge([
            'media_storage_type' => '',
            's3_key' => '',
            's3_secret' => '',
            's3_region' => '',
            's3_bucket' => '',
        ], get_settings('media_storage_settings'));
    }

    private function updateConfigs($general_settings, $pusher_settings, $email_settings, $media_storage_settings)
    {
        Config::set([
            'app.timezone' => $general_settings['timezone'],
            'chatify.name' => $general_settings['company_title'],
            'chatify.pusher.key' => $pusher_settings['pusher_app_key'],
            'chatify.pusher.secret' => $pusher_settings['pusher_app_secret'],
            'chatify.pusher.app_id' => $pusher_settings['pusher_app_id'],
            'chatify.pusher.options.cluster' => $pusher_settings['pusher_app_cluster'],
            'mail.mailers.smtp.host' => $email_settings['smtp_host'],
            'mail.mailers.smtp.port' => $email_settings['smtp_port'],
            'mail.mailers.smtp.encryption' => $email_settings['smtp_encryption'],
            'mail.mailers.smtp.username' => $email_settings['email'],
            'mail.mailers.smtp.password' => $email_settings['password'],
            'mail.from.name' => $general_settings['company_title'],
            'mail.from.address' => $email_settings['email'],
            'filesystems.disks.s3.key' => $media_storage_settings['s3_key'],
            'filesystems.disks.s3.secret' => $media_storage_settings['s3_secret'],
            'filesystems.disks.s3.region' => $media_storage_settings['s3_region'],
            'filesystems.disks.s3.bucket' => $media_storage_settings['s3_bucket'],
        ]);

        config()->set('mail.default', 'smtp');
        config()->set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $email_settings['smtp_host'],
            'port' => $email_settings['smtp_port'],
            'encryption' => $email_settings['smtp_encryption'],
            'username' => $email_settings['email'],
            'password' => $email_settings['password'],
            'timeout' => null,
            'auth_mode' => null,
        ]);
    }

    private function getActiveTheme()
    {
        try {
            // Check if theme_settings table exists using raw SQL
            $tableExists = DB::select("SHOW TABLES LIKE 'theme_settings'");

            if (!empty($tableExists)) {
                $theme = ThemeSetting::getActiveTheme();
                return $theme ?: 'new';
            }

            // If table not found, return fallback
            return 'new';
        } catch (\Exception $e) {
            return 'new';
        }
    }

    private function getAIModelSettings()
    {
        $defaults = [
            "openrouter_endpoint" => "https://openrouter.ai/api/v1/chat/completions",
            "openrouter_system_prompt" => "You are a helpful assistant that writes concise, professional project or task descriptions.",
            "openrouter_temperature" => "0.7",
            "openrouter_max_tokens" => "1024",
            "openrouter_top_p" => "0.95",
            "gemini_endpoint" => "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent",
            "gemini_temperature" => "0.7",
            "gemini_top_k" => "40",
            "gemini_top_p" => "0.95",
            "gemini_max_output_tokens" => "1024",
            "rate_limit_per_minute" => "15",
            "rate_limit_per_day" => "1500",
            "max_retries" => "2",
            "retry_delay" => "1",
            "request_timeout" => "15",
            "max_prompt_length" => "1000",
            "enable_fallback" => "1",
            "fallback_provider" => "openrouter",
            "openrouter_api_key" => "",
            "gemini_api_key" => "",
            "is_active" => "gemini",
            "openrouter_model" => "nousresearch/deephermes-3-mistral-24b-preview:free",
            "openrouter_frequency_penalty" => "0",
            "openrouter_presence_penalty" => "0",
            "gemini_model" => "gemini-2.0-flash",
        ];

        return array_merge($defaults, get_settings('ai_model_settings') ?: []);
    }


    private function loadPlugins()
    {
        $pluginsPath = base_path('plugins');

        if (File::exists($pluginsPath)) {
            $pluginDirs = File::directories($pluginsPath);

            foreach ($pluginDirs as $pluginDir) {
                $pluginJson = $pluginDir . '/plugin.json';

                if (File::exists($pluginJson)) {
                    $pluginConfig = json_decode(File::get($pluginJson), true);

                    if (
                        !empty($pluginConfig['enabled']) &&
                        !empty($pluginConfig['provider'])
                    ) {
                        $providerClass = $pluginConfig['provider'];

                        // Manually require the provider if not autoloaded
                        $providerFile = $pluginDir . '/Providers/' . class_basename(str_replace('\\', '/', $providerClass)) . '.php';
                        if (File::exists($providerFile) && !class_exists($providerClass)) {
                            require_once $providerFile;
                        }

                        if (class_exists($providerClass)) {
                            app()->register($providerClass);
                            // Log::info("✅ Loaded plugin: " . basename($pluginDir) . " with provider {$providerClass}");
                        } else {
                            Log::warning("⚠️ Provider class {$providerClass} not found for plugin: " . basename($pluginDir));
                        }
                    }
                }
            }
        }
    }
}
