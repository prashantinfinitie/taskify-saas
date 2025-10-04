<?php

namespace App\Http\Controllers;

use Exception;
use ZipArchive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class PluginInstallerController extends Controller
{
    public function showForm()
    {
        return view('plugins.install');
    }

    public function install(Request $request)
    {
        $request->validate([
            'plugin_zip.*' => 'required|file|mimes:zip',
        ]);

        $uploadedZip = $request->file('plugin_zip')[0];
        $tmpDir = storage_path('app/tmp_plugin_' . uniqid());

        File::ensureDirectoryExists($tmpDir);

        $zip = new ZipArchive;
        if ($zip->open($uploadedZip->getRealPath()) !== true) {
            return response()->json(['error' => true, 'message' => 'Unable to open zip file.']);
        }

        $zip->extractTo($tmpDir);
        $zip->close();

        $pluginFolder = collect(File::directories($tmpDir))->first();
        if (!$pluginFolder || !File::exists($pluginFolder . '/plugin.json')) {
            File::deleteDirectory($tmpDir);
            return response()->json(['error' => true, 'message' => 'plugin.json not found in the uploaded plugin.']);
        }

        $pluginData = json_decode(File::get($pluginFolder . '/plugin.json'), true);
        $pluginSlug = $pluginData['slug'] ?? basename($pluginFolder);
        $pluginNamespace = str_replace('-', '', ucwords($pluginSlug, '-'));
        $destination = base_path('plugins/' . $pluginNamespace);

        try {
            // Don't use a global transaction since migrations will handle their own
            File::ensureDirectoryExists(base_path('plugins'));

            if (File::exists($destination)) {
                $existingJson = json_decode(File::get($destination . '/plugin.json'), true);
                if (version_compare($pluginData['version'], $existingJson['version'], '<=')) {
                    File::deleteDirectory($tmpDir);
                    return response()->json([
                        'error' => true,
                        'message' => 'Plugin already installed with the same or higher version.'
                    ]);
                }

                $backupDir = storage_path('app/plugin_backups/' . $pluginNamespace . '_' . now()->format('YmdHis'));
                File::ensureDirectoryExists(dirname($backupDir));
                File::moveDirectory($destination, $backupDir);
                Log::info("🔄 Plugin backed up to: {$backupDir}");
            }

            if (!File::moveDirectory($pluginFolder, $destination)) {
                throw new Exception("Failed to move plugin to: {$destination}");
            }
            Log::info("✅ Plugin moved to: {$destination}");

            // Register the service provider first
            $provider = $pluginData['provider'] ?? "Plugins\\{$pluginNamespace}\\Providers\\{$pluginNamespace}ServiceProvider";
            $providerPath = $destination . '/Providers/' . class_basename(str_replace('\\', '/', $provider)) . '.php';

            if (File::exists($providerPath)) {
                require_once $providerPath;

                if (class_exists($provider)) {
                    app()->register($provider);
                    Log::info("✅ Service provider registered: {$provider}");
                } else {
                    throw new Exception("Provider class '{$provider}' not found.");
                }
            } else {
                Log::warning("⚠️ Service provider file not found: {$providerPath}");
            }

            // Run migrations with proper path handling
            $migrationPath = "plugins/{$pluginNamespace}/Database/Migrations";
            $fullMigrationPath = base_path($migrationPath);

            if (File::exists($fullMigrationPath)) {
                // Check if there are any migration files
                $migrationFiles = File::files($fullMigrationPath);

                if (!empty($migrationFiles)) {
                    Log::info("🔄 Running migrations for plugin: {$pluginNamespace}");
                    Log::info("Migration files found: " . count($migrationFiles));

                    try {
                        // Run migrations with --realpath flag to ensure proper path resolution
                        Artisan::call('migrate', [
                            '--path' => $migrationPath,
                            '--force' => true,
                            '--realpath' => false, // Use relative path
                        ]);

                        $migrationOutput = Artisan::output();
                        Log::info("Migration output: " . $migrationOutput);
                        Log::info("✅ Migrations executed for plugin: {$pluginNamespace}");
                    } catch (Exception $migrationException) {
                        Log::error("❌ Migration failed: " . $migrationException->getMessage());
                        throw new Exception("Migration failed: " . $migrationException->getMessage());
                    }
                } else {
                    Log::info("ℹ️ No migration files found in: {$fullMigrationPath}");
                }
            } else {
                Log::info("ℹ️ No migration directory found: {$fullMigrationPath}");
            }

            // Execute update script if exists
            $updateScript = base_path("plugins/{$pluginNamespace}/update.php");
            if (File::exists($updateScript)) {
                include_once $updateScript;
                Log::info("✅ update.php executed for plugin: {$pluginNamespace}");
            }

            // Publish assets if publish_tag is defined
            if (!empty($pluginData['publish_tag'])) {
                Log::info("🔄 Publishing plugin assets with tag: {$pluginData['publish_tag']}");

                try {
                    Artisan::call('vendor:publish', [
                        '--tag' => $pluginData['publish_tag'],
                        '--force' => true,
                    ]);

                    $publishOutput = Artisan::output();
                    Log::info("Publish output: " . $publishOutput);
                    Log::info("✅ Published plugin assets with tag: {$pluginData['publish_tag']}");
                } catch (Exception $publishException) {
                    Log::error("❌ Asset publishing failed: " . $publishException->getMessage());
                    // Don't throw here as this might not be critical
                    Log::warning("⚠️ Continuing installation despite asset publishing failure");
                }
            } else {
                Log::info("ℹ️ No publish_tag defined in plugin.json, skipping vendor:publish.");
            }

            // No need to commit since we're not using a global transaction
            File::deleteDirectory($tmpDir);

            Log::info("✅ Plugin '{$pluginNamespace}' installed/updated successfully.");

            return response()->json([
                'error' => false,
                'message' => 'Plugin installed/updated successfully.',
                'plugin_name' => $pluginData['name'] ?? $pluginNamespace,
                'plugin_version' => $pluginData['version'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            Log::error("❌ Plugin installation failed: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
                'plugin' => $pluginNamespace ?? 'unknown'
            ]);

            // No need to rollback since we're not using a global transaction
            File::deleteDirectory($tmpDir);

            return response()->json([
                'error' => true,
                'message' => 'Plugin installation failed: ' . $e->getMessage()
            ]);
        }
    }

    public static function generateUninstallScript(string $pluginPath)
    {
        $migrationPath = $pluginPath . '/Database/Migrations';
        $uninstallScript = $pluginPath . '/uninstall.php';
        $dropStatements = [];

        if (File::exists($migrationPath)) {
            $migrationFiles = File::files($migrationPath);
            foreach ($migrationFiles as $file) {
                $content = File::get($file);
                preg_match_all("/Schema::create\\(['\"](.*?)['\"]", $content, $matches);
                foreach ($matches[1] as $table) {
                    $dropStatements[] = "Schema::dropIfExists('{$table}');";
                }
            }
        }

        $phpContent = "<?php\\n\\nuse Illuminate\\\\Support\\\\Facades\\\\Schema;\\n\\n";
        foreach ($dropStatements as $drop) {
            $phpContent .= "$drop\\n";
        }

        File::put($uninstallScript, $phpContent);
    }
}
