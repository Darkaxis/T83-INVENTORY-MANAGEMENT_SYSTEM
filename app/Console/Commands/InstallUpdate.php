<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\SystemUpdate;

class InstallUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $version = $this->argument('version');
        $this->info("Installing update v{$version}...");
        
        try {
            // Get the update record
            $update = SystemUpdate::where('version', $version)->firstOrFail();
            $update->status = 'installing';
            $update->save();
            
            // Path to the ZIP file
            $zipPath = storage_path("app/updates/update-v{$version}.zip");
            
            if (!file_exists($zipPath)) {
                throw new \Exception("Update package not found at {$zipPath}");
            }
            
            // Back up current code
            $this->backupCurrentCode();
            
            // Back up the database
            $this->backupDatabase();
            
            // Extract the update
            $zip = new \ZipArchive();
            $res = $zip->open($zipPath);
            
            if ($res !== true) {
                throw new \Exception("Could not open update package: {$res}");
            }
            
            // Extract to a temporary directory
            $extractPath = storage_path('app/updates/tmp-' . $version);
            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0755, true);
            }
            
            $zip->extractTo($extractPath);
            $zip->close();
            
            // Copy files to the application root
            $this->copyFiles($extractPath, base_path());
            
            // Run migrations
            $this->call('migrate', ['--force' => true]);
            
            // Clear caches
            $this->call('optimize:clear');
            
            // Update the app version in config
            $this->updateAppVersion($version);
            
            // Update status
            $update->status = 'completed';
            $update->installed_at = now();
            $update->save();
            
            $this->info("Update v{$version} installed successfully.");
        } catch (\Exception $e) {
            $this->error('Error installing update: ' . $e->getMessage());
            Log::error('Update installation failed: ' . $e->getMessage());
            
            // Try to restore from backup
            $this->restoreFromBackup();
            
            // Update status
            if (isset($update)) {
                $update->status = 'failed';
                $update->error_message = $e->getMessage();
                $update->save();
            }
        }
    }

    protected function backupCurrentCode()
    {
        $this->info('Backing up current code...');
        $backupDir = storage_path('app/backups/code-' . date('Y-m-d-His'));
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        // Copy important directories
        foreach (['app', 'config', 'routes', 'resources'] as $dir) {
            $this->copyFiles(base_path($dir), "{$backupDir}/{$dir}");
        }
        
        // Copy important files
        foreach (['.env', 'composer.json', 'composer.lock'] as $file) {
            if (file_exists(base_path($file))) {
                copy(base_path($file), "{$backupDir}/{$file}");
            }
        }
    }

    protected function backupDatabase()
    {
        $this->info('Backing up database...');
        $backupPath = storage_path('app/backups/db-' . date('Y-m-d-His') . '.sql');
        
        // Get database configuration
        $dbConfig = config('database.connections.' . config('database.default'));
        
        // Set up the mysqldump command
        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s > %s',
            $dbConfig['host'],
            $dbConfig['username'],
            $dbConfig['password'],
            $dbConfig['database'],
            $backupPath
        );
        
        // Execute the command
        exec($command);
    }

    protected function copyFiles($source, $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $dir = opendir($source);
        
        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            
            $sourcePath = $source . '/' . $file;
            $destPath = $destination . '/' . $file;
            
            if (is_dir($sourcePath)) {
                $this->copyFiles($sourcePath, $destPath);
            } else {
                copy($sourcePath, $destPath);
            }
        }
        
        closedir($dir);
    }

    protected function updateAppVersion($version)
    {
        $configFile = config_path('app.php');
        $config = file_get_contents($configFile);
        
        // Replace the version
        $config = preg_replace(
            "/'version' => '(.*)'/",
            "'version' => '{$version}'",
            $config
        );
        
        file_put_contents($configFile, $config);
    }

    protected function restoreFromBackup()
    {
        // Implementation for restore logic
        $this->info('Attempting to restore from backup...');
        // You'd implement code here to restore from the most recent backup
    }
}
