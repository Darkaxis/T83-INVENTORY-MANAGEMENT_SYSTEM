<?php

namespace App\Jobs;

use App\Models\SystemUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use GuzzleHttp\Client;
use ZipArchive;

class ProcessSystemUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $updateId;
    public $timeout = 3600; // 1 hour timeout
    public $tries = 1;

    public function __construct($updateId)
    {
        $this->updateId = $updateId;
    }

    public function handle()
    {
        // Get the update record
        $update = SystemUpdate::findOrFail($this->updateId);
        
        try {
            Log::info("Starting system update from {$update->version_from} to {$update->version_to}");
            
            // Download the update
            $zipPath = $this->downloadUpdate($update->version_to);
            
            // Backup current application
            $backupPath = $this->backupCurrentSystem($update->version_from);
            
            // Extract and apply update
            $this->applyUpdate($zipPath, $update->version_to);
            
            // Run post-update tasks
            $this->runPostUpdateTasks($update->version_to);
            
            // Update record in database
            $update->update([
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => "Update successfully completed. System backup saved at: {$backupPath}"
            ]);
            
            Log::info("System update to {$update->version_to} completed successfully");
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
        }
    }

    /**
     * Download the update package
     *
     * @param string $version The version to download
     * @return string Path to the downloaded zip file
     */
    protected function downloadUpdate($version)
    {
        Log::info("Downloading update v{$version}");
        
        $githubRepo = config('services.github.repository', 'Darkaxis/T83-INVENTORY-MANAGEMENT_SYSTEM');
        $githubToken = config('services.github.token');
        
        
        $client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WST-InventorySystem-Updater',
                'Authorization' => $githubToken ? "token {$githubToken}" : null
            ]
        ]);
        
        $updateDir = storage_path('app/system-updates');
        if (!file_exists($updateDir)) {
            mkdir($updateDir, 0755, true);
        }
        
        $zipPath = $updateDir . '/update-' . $version . '.zip';
        
        // Construct the download URL
        list($owner, $repo) = explode('/', $githubRepo);
        $downloadUrl = "https://api.github.com/repos/{$owner}/{$repo}/zipball/v{$version}";
        
        try {
            // Download the update package
            Log::info("Downloading from: {$downloadUrl}");
            $response = $client->get($downloadUrl, [
                'sink' => $zipPath,
                'headers' => [
                    'Authorization' => $githubToken ? "token {$githubToken}" : null
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
     * Apply the update
     *
     * @param string $zipPath Path to the update zip file
     * @param string $version New version being applied
     * @return void
     */
    protected function applyUpdate($zipPath, $version)
    {
        Log::info("Applying update package for v{$version}");
        
        $extractDir = storage_path("app/system-updates/extract-{$version}");
        if (file_exists($extractDir)) {
            $this->deleteDirectory($extractDir);
        }
        
        mkdir($extractDir, 0755, true);
        
        // Extract the zip file
        $zip = new ZipArchive;
        $openResult = $zip->open($zipPath);
        
        if ($openResult !== true) {
            throw new \Exception("Failed to open update package. Error code: {$openResult}");
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
    protected function runPostUpdateTasks($version)
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
     * Delete a directory and its contents recursively
     *
     * @param string $dir The directory to delete
     * @return bool True on success
     */
    protected function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }
        
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        
        // Ensure trailing slash
        $dir = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
        
        // Use a DirectoryIterator to avoid issues with open file handles
        try {
            $items = new \DirectoryIterator($dir);
            foreach ($items as $item) {
                if ($item->isDot()) {
                    continue;
                }
                
                $path = $dir . $item->getFilename();
                
                if ($item->isDir()) {
                    $this->deleteDirectory($path);
                } else {
                    // For files that can't be deleted immediately, try force-closing any handles
                    if (file_exists($path) && !@unlink($path)) {
                        // Handle Windows-specific issues with locked files
                        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                            // Try to release file lock using system call
                            @chmod($path, 0777);
                            if (file_exists($path)) {
                                @unlink($path);
                            }
                        }
                    }
                }
            }
            
            // Try to delete the directory now that it should be empty
            @rmdir($dir);
            
            // Double-check if directory still exists
            if (is_dir($dir)) {
                Log::warning("Could not fully remove directory: {$dir}");
            }
            
            return !is_dir($dir);
        } catch (\Exception $e) {
            Log::error("Error deleting directory {$dir}: " . $e->getMessage());
            return false;
        }
    }
}