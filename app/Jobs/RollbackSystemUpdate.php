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
use ZipArchive;

class RollbackSystemUpdate implements ShouldQueue
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
            Log::info("Starting system rollback from {$update->version_from} to {$update->version_to}");
            
            // Find latest backup file
            $backupDir = storage_path('app/system-backups');
            $backupFiles = glob($backupDir . "/backup-v*.zip");
            
            if (empty($backupFiles)) {
                throw new \Exception('No backup files found for rollback.');
            }
            
            // Sort by modification time (newest first)
            usort($backupFiles, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            $backupFile = $backupFiles[0];
            Log::info("Using backup file for rollback: {$backupFile}");
            
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
            $this->updateVersionInConfig($update->version_to);
            
            // Clear caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            
            $update->update([
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => "Rollback to version {$update->version_to} completed successfully."
            ]);
            
            Log::info("Rollback to version {$update->version_to} completed successfully");
            
        } catch (\Exception $e) {
            Log::error("Rollback failed: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            $update->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'notes' => "Rollback failed. Error: " . $e->getMessage()
            ]);
        }
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
     * Delete a directory and its contents recursively with improved empty dir handling
     */
    protected function deleteDirectory($dir)
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

    /**
     * Update version in config file
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
}