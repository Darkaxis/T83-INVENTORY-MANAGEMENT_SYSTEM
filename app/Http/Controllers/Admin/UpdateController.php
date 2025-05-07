<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SystemUpdateRequest;
use App\Models\SystemUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use GuzzleHttp\Client;
use ZipArchive;

class UpdateController extends Controller
{
    protected $client;
    protected $githubRepo;
    protected $githubToken;

    public function __construct()
    {
        $this->middleware(['admin']);
        
        $this->githubRepo = config('services.github.repository', 'Darkaxis/T83-INVENTORY-MANAGEMENT_SYSTEM');
        $this->githubToken = config('services.github.token');
        
        $this->client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WST-InventorySystem-Updater',
                'Authorization' => $this->githubToken ? "token {$this->githubToken}" : null
            ]
        ]);
    }

    /**
     * Show the update dashboard
     */
    public function index()
    {
        $currentVersion = config('app.version', '1.0.0');
        $latestRelease = Cache::remember('latest_github_release', now()->addHours(3), function () {
            return $this->checkLatestRelease();
        });
        
        $updateHistory = SystemUpdate::orderByDesc('created_at')->get();
        
        return view('admin.system.update', compact('currentVersion', 'latestRelease', 'updateHistory'));
    }
    
    /**
     * Check for the latest release on GitHub
     */
    public function checkForUpdates()
    {
        Cache::forget('latest_github_release');
        $latestRelease = $this->checkLatestRelease();
        
        return redirect()->route('admin.system.update')
            ->with('success', 'Successfully checked for updates.');
    }
    
    /**
     * Process the update
     */
    public function update(SystemUpdateRequest $request)
    {
        // Set timeout and memory limits for the update process
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        
        $currentVersion = config('app.version', '1.0.0');
        $newVersion = $request->version;
        
        // Log the start of the update process
        Log::info("Starting system update from {$currentVersion} to {$newVersion}");
        
        // Create update record
        $update = SystemUpdate::create([
            'version_from' => $currentVersion,
            'version_to' => $newVersion,
            'requested_by' => 1,
            'status' => 'processing',
            'notes' => 'Update initiated from admin panel'
        ]);
        
        try {
            // 1. Download the update
            $zipPath = $this->downloadUpdate($newVersion);
            
            // 2. Backup current application
            $backupPath = $this->backupCurrentSystem($currentVersion);
            
            // 3. Extract and apply update
            $this->applyUpdate($zipPath, $newVersion);
            
            // 4. Run post-update tasks
            $this->runPostUpdateTasks($newVersion);
            
            // Update record in database
            $update->update([
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => "Update successfully completed. System backup saved at: {$backupPath}"
            ]);
            
            Log::info("System update to {$newVersion} completed successfully");
            
            return redirect()->route('admin.system.updates')
                ->with('success', "Successfully updated to version {$newVersion}!");
                
        } catch (\Exception $e) {
            Log::error("Update failed: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            $update->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'notes' => "Update failed. Error: " . $e->getMessage()
            ]);
            
            return redirect()->route('admin.system.updates')
                ->with('error', 'Update failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Rollback to the previous version
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rollback(Request $request)
    {
        // Prevent timeout for long-running operation
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        
        Log::info('Starting OTA update rollback process');
        
        try {
            // Find latest backup file
            $backupDir = storage_path('app/system-backups');
            $backupFiles = glob($backupDir . "/backup-v*.zip");
            
            if (empty($backupFiles)) {
                return redirect()->route('admin.system.updates')
                    ->with('error', 'No backup files found for rollback.');
            }
            
            // Sort by modification time (newest first)
            usort($backupFiles, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            $backupFile = $backupFiles[0];
            Log::info("Using backup file for rollback: {$backupFile}");
            
            // Extract version from backup filename
            preg_match('/backup-v(.+?)-/', basename($backupFile), $matches);
            $previousVersion = $matches[1] ?? 'unknown';
            Log::info("Rolling back to version: {$previousVersion}");
            
            // Create rollback record
            $update = SystemUpdate::create([
                'version_from' => config('app.version', '1.0.0'),
                'version_to' => $previousVersion,
                'requested_by' => 1,
                'status' => 'processing',
                'notes' => 'Rollback to previous version initiated from admin panel'
            ]);
            
            // Extract backup to temporary directory
            $extractDir = storage_path('app/system-updates/rollback-temp');
            if (file_exists($extractDir)) {
                $this->deleteDirectory($extractDir);
            }
            mkdir($extractDir, 0755, true);
            
            $zip = new ZipArchive();
            if ($zip->open($backupFile) !== true) {
                throw new \Exception('Failed to open backup file.');
            }
            
            Log::info("Extracting backup to: {$extractDir}");
            $zip->extractTo($extractDir);
            $zip->close();
            
            // Define exclusion patterns (same as during update)
            $excludes = [
                '.env',
                'storage',
                'vendor',
                '.git',
                'node_modules',
                'storage/app/system-backups',
                'storage/app/system-updates',
                'storage/logs',
                'bootstrap/cache',
                '*.log'
            ];
            
            // Copy files from backup to application root
            $rootPath = base_path();
            Log::info("Restoring files from backup to {$rootPath}");
            $this->copyFiles($extractDir, $rootPath, $excludes);
            
            // Clean up
            $this->deleteDirectory($extractDir);
            
            // Update version in config
            $this->updateVersionInConfig($previousVersion);
            
            // Clear caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            
            $update->update([
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => "Rollback to version {$previousVersion} completed successfully."
            ]);
            
            Log::info("Rollback to version {$previousVersion} completed successfully");
            
            return redirect()->route('admin.system.updates')
                ->with('success', "System successfully rolled back to version {$previousVersion}")
                ->with('warning', 'Database schema changes were NOT rolled back. If needed, restore your database manually.');
                
        } catch (\Exception $e) {
            Log::error("Rollback failed: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.system.updates')
                ->with('error', 'Rollback failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Check the latest release from GitHub
     */
    protected function checkLatestRelease()
    {
        try {
            list($owner, $repo) = explode('/', $this->githubRepo);
            $response = $this->client->get("repos/{$owner}/{$repo}/releases/latest");
            $release = json_decode($response->getBody(), true);
            
            return [
                'version' => ltrim($release['tag_name'], 'v'),
                'name' => $release['name'],
                'published_at' => $release['published_at'],
                'body' => $release['body'],
                'download_url' => $release['zipball_url'],
                'has_update' => version_compare(ltrim($release['tag_name'], 'v'), config('app.version', '1.0.0'), '>')
            ];
        } catch (\Exception $e) {
            Log::error("Error checking latest release: " . $e->getMessage());
            return [
                'version' => null,
                'name' => 'Unknown',
                'body' => 'Failed to fetch release information',
                'has_update' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Download the update package
     */
    protected function downloadUpdate($version)
    {
        Log::info("Downloading update v{$version}");
        
        $updateDir = storage_path('app/system-updates');
        if (!file_exists($updateDir)) {
            mkdir($updateDir, 0755, true);
        }
        
        $zipPath = $updateDir . '/update-' . $version . '.zip';
        
        // Construct the download URL
        list($owner, $repo) = explode('/', $this->githubRepo);
        $downloadUrl = "https://api.github.com/repos/{$owner}/{$repo}/zipball/v{$version}";
        
        // Download the update package
        $response = $this->client->get($downloadUrl, [
            'sink' => $zipPath,
            'headers' => [
                'Authorization' => $this->githubToken ? "token {$this->githubToken}" : null
            ]
        ]);
        
        if (!file_exists($zipPath)) {
            throw new \Exception("Failed to download update package");
        }
        
        Log::info("Update package downloaded successfully");
        
        return $zipPath;
    }
    
    /**
     * Backup the current system
     */
    protected function backupCurrentSystem($version)
    {
        Log::info("Backing up current system (v{$version})");
        
        $backupDir = storage_path('app/system-backups');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d-His');
        $backupPath = $backupDir . "/backup-v{$version}-{$timestamp}.zip";
        
        // Exclude these directories/files from backup
        $excludes = [
            'node_modules',
            'vendor',
            '.git',
            'storage/app/system-backups',
            'storage/app/system-updates',
            'storage/logs',
            '.env',
            'bootstrap/cache',
            '*.log'
        ];
        
        $zip = new ZipArchive();
        if ($zip->open($backupPath, ZipArchive::CREATE) !== true) {
            throw new \Exception("Failed to create backup archive");
        }
        
        $rootPath = base_path();
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                
                // Check if file should be excluded
                $exclude = false;
                foreach ($excludes as $excludePattern) {
                    if (fnmatch($excludePattern, $relativePath)) {
                        $exclude = true;
                        break;
                    }
                }
                
                if (!$exclude) {
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }
        
        $zip->close();
        
        Log::info("System backup created at: {$backupPath}");
        
        return $backupPath;
    }
    
    /**
     * Extract and apply the update
     */
    protected function applyUpdate($zipPath, $version)
    {
        Log::info("Extracting update package");
        
        $extractDir = storage_path("app/system-updates/extract-{$version}");
        if (!file_exists($extractDir)) {
            mkdir($extractDir, 0755, true);
        }
        
        // Extract the zip file
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new \Exception("Failed to open update package");
        }
        
        $zip->extractTo($extractDir);
        $zip->close();
        
        // Find the actual source directory (GitHub adds a top-level directory)
        $sourceDir = $this->findSourceDirectory($extractDir);
        if (!$sourceDir) {
            throw new \Exception("Could not locate source directory in update package");
        }
        
        Log::info("Update package extracted, applying updates from: {$sourceDir}");
        
        // Define files/directories to exclude from update
        $excludes = [
            '.env',
            'storage',
            '.git',
            'node_modules',
            'vendor'
        ];
        
        // Copy files from extracted update to application
        $this->copyFiles($sourceDir, base_path(), $excludes);
        
        // Clean up
        $this->deleteDirectory($extractDir);
        
        // Update version in config
        $this->updateVersionInConfig($version);
        
        Log::info("Update files applied successfully");
    }
    
    /**
     * Find the source directory in extracted update package
     */
    protected function findSourceDirectory($extractDir)
    {
        $directories = glob($extractDir . '/*', GLOB_ONLYDIR);
        
        if (empty($directories)) {
            return null;
        }
        
        // GitHub usually creates a directory like "owner-repo-hash"
        return $directories[0];
    }
    
    /**
     * Copy files from source to destination, excluding specific paths
     */
    protected function copyFiles($source, $destination, $excludes = [])
    {
        $dir = opendir($source);
        
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $srcPath = $source . '/' . $file;
                $destPath = $destination . '/' . $file;
                
                // Check if the file should be excluded
                $exclude = false;
                foreach ($excludes as $excludePattern) {
                    if ($file == $excludePattern || fnmatch($excludePattern . '/*', $file)) {
                        $exclude = true;
                        break;
                    }
                }
                
                if (!$exclude) {
                    if (is_dir($srcPath)) {
                        if (!file_exists($destPath)) {
                            mkdir($destPath, 0755, true);
                        }
                        $this->copyFiles($srcPath, $destPath, $excludes);
                    } else {
                        copy($srcPath, $destPath);
                    }
                }
            }
        }
        
        closedir($dir);
    }
    
    /**
     * Delete a directory and its contents
     */
    protected function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object)) {
                    $this->deleteDirectory($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        
        rmdir($dir);
    }
    
    /**
     * Update version in config file
     */
    protected function updateVersionInConfig($version)
    {
        // This is just a basic implementation - in real world you might
        // need to update an environment variable or config file
        config(['app.version' => $version]);
        
        $configPath = config_path('app.php');
        $configContent = file_get_contents($configPath);
        
        // Replace the version string in the config file
        $pattern = "/'version'\s*=>\s*'.*?'/";
        $replacement = "'version' => '{$version}'";
        $configContent = preg_replace($pattern, $replacement, $configContent);
        
        file_put_contents($configPath, $configContent);
    }
    
    /**
     * Run post-update tasks
     */
    protected function runPostUpdateTasks($version)
    {
        Log::info("Running post-update tasks for v{$version}");
        
        // Clear all caches
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        
        // Run migrations
        try {
            Artisan::call('migrate', ['--force' => true]);
            Log::info("Database migrations completed");
        } catch (\Exception $e) {
            Log::warning("Error running migrations: " . $e->getMessage());
            // Don't throw an exception here - update may still be usable
        }
        
        Log::info("Post-update tasks completed");
    }

    
}