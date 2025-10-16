<?php
namespace Plugins\AssetManagement\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Plugins\AssetManagement\Models\Asset;
use Illuminate\Console\Scheduling\Schedule;

class AssetServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'assets');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'assets');

        // Define publishable assets
        $this->publishes([
            __DIR__ . '/../public/js' => public_path('assets/js/asset-plugin'),
        ], ['asset-assets', 'public']);

        $this->publishes([
            __DIR__ . '/../public/storage/' => public_path('storage'),
        ], 'public');

        // Auto-publish assets if they don't exist
        $this->autoPublishAssets();

        // Optional: Log plugin version when loaded
        if (file_exists(__DIR__ . '/../plugin.json')) {
            $pluginJson = json_decode(file_get_contents(__DIR__ . '/../plugin.json'), true);
            Log::info("Asset Plugin Loaded - Version: " . ($pluginJson['version'] ?? 'unknown'));
        }

        // Optional: Add scheduled tasks for Asset plugin
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
        });

        // Dynamically add the `assets` relationship to User
        User::resolveRelationUsing('assets', function ($userModel) {
            return $userModel->hasMany(Asset::class, 'assigned_to');
        });

        // Merge plugin permissions with system permissions
        $this->mergePluginConfig();
    }

    public function register(): void
    {
        //
    }

    /**
     * Automatically publish assets if they don't exist
     */
    private function autoPublishAssets(): void
    {
        // Auto-publish JS
        $sourcePathJs = __DIR__ . '/../public/js';
        $destinationPathJs = public_path('assets/js/asset-plugin');

        if (File::exists($sourcePathJs)) {
            if (!File::exists($destinationPathJs) || $this->assetsNeedUpdate($sourcePathJs, $destinationPathJs)) {
                File::ensureDirectoryExists($destinationPathJs);
                File::copyDirectory($sourcePathJs, $destinationPathJs);
                Log::info(" Asset Plugin: JS assets auto-published to {$destinationPathJs}");
            }
        }

        // Auto-publish Storage files (sample, instructions, etc.)
        $sourcePathStorage = __DIR__ . '/../public/storage/';
        $destinationPathStorage = public_path('storage');

        if (File::exists($sourcePathStorage)) {
            if (!File::exists($destinationPathStorage)) {
                File::makeDirectory($destinationPathStorage, 0755, true);
            }
            File::copyDirectory($sourcePathStorage, $destinationPathStorage);
            Log::info("Asset Plugin: Storage files auto-published to {$destinationPathStorage}");
        }
    }


    /**
     * Check if assets need to be updated
     */
    private function assetsNeedUpdate(string $sourcePath, string $destinationPath): bool
    {
        if (!File::exists($destinationPath)) {
            return true;
        }

        // Compare modification times
        $sourceTime = File::lastModified($sourcePath);
        $destTime = File::lastModified($destinationPath);

        return $sourceTime > $destTime;
    }


    protected function mergePluginConfig()
    {

        $pluginPath = __DIR__ . '/../config';

        // For permissions
        $permissionFile = $pluginPath . '/permissions.php';
        if (file_exists($permissionFile)) {

            $permissions = include $permissionFile;

            if (is_array($permissions)) {
                config()->set('taskify.permissions', array_merge(config('taskify.permissions', []), $permissions));
            }
        }

        // For module

        $moduleFile = $pluginPath . '/module.php';

        if (file_exists($moduleFile)) {

            $module = include $moduleFile;

            if (is_array($module)) {
                config()->set('taskify.modules', array_merge(config('taskify.modules', []), $module));
            }
        }
    }
}
