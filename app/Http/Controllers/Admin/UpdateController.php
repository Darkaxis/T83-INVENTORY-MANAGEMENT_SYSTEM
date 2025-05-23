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
use App\Jobs\ProcessSystemUpdate;
use App\Jobs\RollbackSystemUpdate;

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
        $currentVersion = config('app.version', '1.0.0');
        $newVersion = $request->version;
        
        // Create update record
        $update = SystemUpdate::create([
            'version_from' => $currentVersion,
            'version_to' => $newVersion,
            'requested_by' => 1,
            'status' => 'processing',
            'notes' => 'Update initiated from admin panel'
        ]);
        
        // Dispatch the job (which will run in the background)
        ProcessSystemUpdate::dispatch($update->id);
        
        // Redirect immediately with a message
        return redirect()->route('admin.system.update')
            ->with('success', "Update process has started in the background. You can check the status on this page.");
    }
    
    /**
     * Rollback to the previous version
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rollback(Request $request)
    {
        Log::info('Starting OTA update rollback process');
        
        try {
            // Find latest backup file
            $backupDir = storage_path('app/system-backups');
            $backupFiles = glob($backupDir . "/backup-v*.zip");
            
            if (empty($backupFiles)) {
                return redirect()->route('admin.system.update')
                    ->with('error', 'No backup files found for rollback.');
            }
            
            // Sort by modification time (newest first)
            usort($backupFiles, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            $backupFile = $backupFiles[0];
            
            // Extract version from backup filename
            preg_match('/backup-v(.+?)-/', basename($backupFile), $matches);
            $previousVersion = $matches[1] ?? 'unknown';
            Log::info("Initiating rollback to version: {$previousVersion}");
            
            // Create rollback record
            $update = SystemUpdate::create([
                'version_from' => config('app.version', '1.0.0'),
                'version_to' => $previousVersion,
                'requested_by' => 1,
                'status' => 'processing',
                'notes' => 'Rollback to previous version initiated from admin panel'
            ]);
            
            // Dispatch the job to run in the background
            RollbackSystemUpdate::dispatch($update->id);
            
            return redirect()->route('admin.system.update')
                ->with('success', "Rollback process has started in the background. You can check the status on this page.")
                ->with('warning', 'Database schema changes will NOT be rolled back. If needed, restore your database manually.');
                
        } catch (\Exception $e) {
            Log::error("Rollback initiation failed: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.system.update')
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
     *
     * @param string $version The version to download
     * @return string Path to the downloaded zip file
     */
    public function downloadUpdate($version)
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
        
        try {
            // Download the update package
            Log::info("Downloading from: {$downloadUrl}");
            $response = $this->client->get($downloadUrl, [
                'sink' => $zipPath,
                'headers' => [
                    'Authorization' => $this->githubToken ? "token {$this->githubToken}" : null
                ]
            ]);
            
            if (!file_exists($zipPath)) {
                throw new \Exception("Failed to download update package");
            }
            
            Log::info("Update package downloaded successfully to {$zipPath}");
            
            return $zipPath;
        } catch (\Exception $e) {
            if (file_exists($zipPath)) {
                unlink($zipPath); // Remove failed download
            }
            throw new \Exception("Download failed: " . $e->getMessage());
        }
    }

    /**
     * Backup the current system
     *
     * @param string $version Current version being backed up
     * @return string Path to the backup file
     */
    public function backupCurrentSystem($version)
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
     * Apply the update
     *
     * @param string $zipPath Path to the update zip file
     * @param string $version New version being applied
     * @return void
     */
    public function applyUpdate($zipPath, $version)
    {
        Log::info("Applying update package for v{$version}");
        
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
     * Run post-update tasks
     *
     * @param string $version The new version
     * @return void
     */
    public function runPostUpdateTasks($version)
    {
        Log::info("Running post-update tasks for v{$version}");
        
        // Clear all caches
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            Log::info("Application caches cleared");
        } catch (\Exception $e) {
            Log::warning("Error clearing caches: " . $e->getMessage());
        }
        
        // Run migrations
        try {
            Artisan::call('migrate', ['--force' => true]);
            Log::info("Database migrations completed");
        } catch (\Exception $e) {
            Log::warning("Error running migrations: " . $e->getMessage());
            // Don't throw an exception here - update may still be usable
        }
        
        Log::info("Post-update tasks completed for v{$version}");
    }

    /**
     * Find the source directory in extracted update package
     * 
     * @param string $extractDir
     * @return string|null
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
     * Update version in config file
     * 
     * @param string $version
     * @return void
     */
    protected function updateVersionInConfig($version)
    {
        config(['app.version' => $version]);
        
        $configPath = config_path('app.php');
        $configContent = file_get_contents($configPath);
        
        // Replace the version string in the config file
        $pattern = "/'version'\s*=>\s*'.*?'/";
        $replacement = "'version' => '{$version}'";
        $configContent = preg_replace($pattern, $replacement, $configContent);
        
        file_put_contents($configPath, $configContent);
        
        Log::info("Version updated in config to: {$version}");
    }

    /**
     * Copy files from source to destination, excluding specific paths
     */
    public function copyFiles($source, $destination, $excludes = [])
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
     * Delete a directory and its contents recursively with improved empty dir handling
     *
     * @param string $dir
     * @return bool
     */
    public function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }
        
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        
        // Safe directory handling using RecursiveDirectoryIterator
        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        
        foreach ($files as $file) {
            if ($file->isDir()) {
                // For directories, try to delete them
                if (!@rmdir($file->getRealPath())) {
                    // If rmdir fails, try once more with improved permissions
                    @chmod($file->getRealPath(), 0777);
                    @rmdir($file->getRealPath());
                }
            } else {
                // For files, try to delete them
                if (!@unlink($file->getRealPath())) {
                    // If unlink fails, try once more with improved permissions
                    @chmod($file->getRealPath(), 0666);
                    @unlink($file->getRealPath());
                }
            }
        }
        
        // Try to delete the main directory
        if (!@rmdir($dir)) {
            // Last resort - try with permissions
            @chmod($dir, 0777);
            @rmdir($dir);
        }
        
        // Return success based on whether directory still exists
        return !is_dir($dir);
    }
}