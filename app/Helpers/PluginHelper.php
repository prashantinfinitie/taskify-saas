<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class PluginHelper
{
    /**
     * Get all plugins installed in the system.
     */
    public static function all()
    {
        $plugins = [];

        $pluginsPath = base_path('plugins');
        if (!File::exists($pluginsPath)) {
            return $plugins;
        }

        foreach (File::directories($pluginsPath) as $dir) {
            $json = $dir . '/plugin.json';
            if (File::exists($json)) {
                $data = json_decode(File::get($json), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error("❌ Invalid JSON in plugin.json: {$json}");
                    continue;
                }
                $data['slug'] = basename($dir);
                $data['path'] = $dir;
                $plugins[] = $data;
            }
        }

        return $plugins;
    }

    /**
     * Retrieve a single plugin by slug.
     */
    public static function get(string $slug): ?array
    {
        $path = base_path("plugins/{$slug}");
        $json = "{$path}/plugin.json";
        if (!File::exists($json)) {
            return null;
        }

        $data = json_decode(File::get($json), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("❌ Invalid JSON in plugin.json: {$json}");
            return null;
        }
        $data['slug'] = $slug;
        $data['path'] = $path;
        return $data;
    }

    /**
     * Update enabled/disabled status in plugin.json.
     */
    public static function updateStatus(string $slug, bool $enabled): void
    {
        $plugin = self::get($slug);
        if (!$plugin) {
            throw new Exception("Plugin not found: {$slug}");
        }

        $pluginJsonPath = "{$plugin['path']}/plugin.json";
        $plugin['enabled'] = $enabled;

        File::put($pluginJsonPath, json_encode($plugin, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        Log::info("🔄 Updated plugin status: {$slug} => " . ($enabled ? "enabled" : "disabled"));
    }

    /**
     * Delete the plugin safely without problematic transaction management.
     */
    public static function delete(string $slug): bool
    {
        $plugin = self::get($slug);
        if (!$plugin) {
            throw new \Exception("Plugin not found: {$slug}");
        }

        if (!empty($plugin['enabled'])) {
            throw new \Exception("Cannot uninstall plugin while enabled. Please disable it first.");
        }

        $pluginNamespace = str_replace('-', '', ucwords($slug, '-'));
        $migrationPath = "plugins/{$pluginNamespace}/Database/Migrations";
        $pluginPath = base_path("plugins/{$pluginNamespace}");

        try {
            Log::info("🚀 Starting uninstallation of plugin: {$pluginNamespace}");

            // Rollback plugin migrations if present
            // Note: migrate:rollback handles its own transactions
            if (File::exists(base_path($migrationPath))) {
                $migrationFiles = File::files(base_path($migrationPath));
                $migrationCount = count($migrationFiles);

                if ($migrationCount > 0) {
                    try {
                        Artisan::call('migrate:rollback', [
                            '--path' => $migrationPath,
                            // '--step' => $migrationCount,
                            '--force' => true,
                        ]);

                        $rollbackOutput = Artisan::output();
                        Log::info("Rollback output: " . $rollbackOutput);
                        Log::info("🔄 Rolled back {$migrationCount} migrations for plugin: {$pluginNamespace}");
                    } catch (\Exception $e) {
                        Log::warning("⚠️ Standard rollback failed, trying manual method: {$e->getMessage()}");
                        self::rollbackPluginMigrationsManually($migrationPath, $pluginNamespace);
                    }
                }
            } else {
                Log::info("ℹ️ No migrations found for plugin: {$pluginNamespace}");
            }

            // Clean up database entries in separate transactions if needed
            self::cleanupPluginDatabaseEntries($slug, $pluginNamespace);

            // Run uninstall.php if exists
            $uninstallScript = "{$pluginPath}/uninstall.php";
            if (File::exists($uninstallScript)) {
                include_once $uninstallScript;
                Log::info("✅ uninstall.php executed for plugin: {$pluginNamespace}");
            } else {
                Log::info("ℹ️ No uninstall.php found for plugin: {$pluginNamespace}");
            }

            // Delete the plugin directory
            $deleted = File::deleteDirectory($pluginPath);

            if ($deleted) {
                Log::info("🗑️ Plugin folder deleted: {$pluginNamespace}");
            } else {
                Log::error("❌ Failed to delete plugin folder: {$pluginNamespace}");
                throw new \Exception("Failed to delete plugin folder: {$pluginNamespace}");
            }

            Log::info("✅ Plugin uninstalled successfully: {$pluginNamespace}");
            return true;
        } catch (\Throwable $e) {
            Log::error("❌ Failed to uninstall plugin '{$pluginNamespace}': {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception("Failed to uninstall plugin: {$e->getMessage()}");
        }
    }

    /**
     * Manually rollback plugin migrations by checking migration records
     */
    private static function rollbackPluginMigrationsManually(string $migrationPath, string $pluginNamespace): void
    {
        try {
            // Get all migration files
            $migrationFiles = File::files(base_path($migrationPath));
            $migrationNames = [];

            foreach ($migrationFiles as $file) {
                $filename = $file->getFilenameWithoutExtension();
                // Extract migration name (remove timestamp prefix)
                if (preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_(.+)$/', $filename, $matches)) {
                    $migrationNames[] = $matches[1];
                }
            }

            if (!empty($migrationNames)) {
                // Use a separate transaction for database cleanup
                DB::transaction(function () use ($migrationNames, $pluginNamespace) {
                    // Get migrations that exist in the database
                    $existingMigrations = DB::table('migrations')
                        ->where(function ($query) use ($migrationNames) {
                            foreach ($migrationNames as $name) {
                                $query->orWhere('migration', 'like', "%{$name}");
                            }
                        })
                        ->orderBy('batch', 'desc')
                        ->get();

                    // Remove migration records from database
                    foreach ($existingMigrations as $migration) {
                        try {
                            DB::table('migrations')
                                ->where('migration', $migration->migration)
                                ->delete();

                            Log::info("🔄 Manually removed migration record: {$migration->migration}");
                        } catch (\Exception $e) {
                            Log::error("❌ Failed to remove migration record {$migration->migration}: {$e->getMessage()}");
                        }
                    }
                });
            }
        } catch (\Exception $e) {
            Log::error("❌ Error during manual migration cleanup: {$e->getMessage()}");
        }
    }

    /**
     * Clean up any remaining plugin-related database entries
     */
    private static function cleanupPluginDatabaseEntries(string $slug, string $pluginNamespace): void
    {
        try {
            // Use a separate transaction for database cleanup
            DB::transaction(function () use ($slug, $pluginNamespace) {
                // Remove from migrations table (in case some weren't caught by rollback)
                $deletedMigrations = DB::table('migrations')
                    ->where('migration', 'like', "%{$slug}%")
                    ->orWhere('migration', 'like', "%{$pluginNamespace}%")
                    ->delete();

                if ($deletedMigrations > 0) {
                    Log::info("🧹 Cleaned up {$deletedMigrations} migration entries from database");
                }

                // Clean up any plugin-specific settings or configurations
                // Only clean tables that actually exist and have the right columns

                // Check if settings table exists and has the right columns
                if (Schema::hasTable('settings')) {
                    $settingsColumns = Schema::getColumnListing('settings');

                    if (in_array('key', $settingsColumns)) {
                        $deletedSettings = DB::table('settings')->where('key', 'like', "%{$slug}%")->delete();
                        if ($deletedSettings > 0) {
                            Log::info("🧹 Cleaned up {$deletedSettings} settings entries");
                        }
                    } elseif (in_array('name', $settingsColumns)) {
                        $deletedSettings = DB::table('settings')->where('name', 'like', "%{$slug}%")->delete();
                        if ($deletedSettings > 0) {
                            Log::info("🧹 Cleaned up {$deletedSettings} settings entries");
                        }
                    }
                }

                // Check if permissions table exists
                if (Schema::hasTable('permissions')) {
                    $permissionsColumns = Schema::getColumnListing('permissions');

                    if (in_array('name', $permissionsColumns)) {
                        $deletedPermissions = DB::table('permissions')->where('name', 'like', "%{$slug}%")->delete();
                        if ($deletedPermissions > 0) {
                            Log::info("🧹 Cleaned up {$deletedPermissions} permission entries");
                        }
                    }
                }

                // Check if cache table exists
                if (Schema::hasTable('cache')) {
                    $cacheColumns = Schema::getColumnListing('cache');

                    if (in_array('key', $cacheColumns)) {
                        $deletedCache = DB::table('cache')->where('key', 'like', "%plugin.{$slug}%")->delete();
                        if ($deletedCache > 0) {
                            Log::info("🧹 Cleaned up {$deletedCache} cache entries");
                        }
                    }
                }
            });

            Log::info("🧹 Database cleanup completed successfully");
        } catch (\Exception $e) {
            Log::warning("⚠️ Error during database cleanup: {$e->getMessage()}");
            // Don't throw the exception, just log it and continue
        }
    }
    public static function getPluginLabels()
    {
        $pluginLabels = [];

        $pluginDirs = File::directories(base_path('plugins'));

        foreach ($pluginDirs as $pluginDir) {
            $labelFile = $pluginDir . '/Resources/lang/plugin_labels.php';

            if (File::exists($labelFile)) {
                $labels = include $labelFile;
                if (is_array($labels)) {
                    $pluginLabels = array_merge($pluginLabels, $labels);
                }
            }
        }

        // ksort($pluginLabels);

        return $pluginLabels;
    }
}
