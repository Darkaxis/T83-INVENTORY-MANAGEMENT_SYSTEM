<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemUpdate;
use Illuminate\Support\Facades\Log;

class DownloadUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-update {version}';

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
        $this->info("Downloading update v{$version}...");
        
        try {
            // Get the update record
            $update = SystemUpdate::where('version', $version)->firstOrFail();
            $update->status = 'downloading';
            $update->save();
            
            // Get the release assets from GitHub
            $client = new \GuzzleHttp\Client();
            
            $response = $client->get(" https://api.github.com/repos/Darkaxis/T83-INVENTORY-MANAGEMENT_SYSTEM/releases/tags/v{$version}");
            $release = json_decode($response->getBody(), true);
            
            // Find the asset (zip file)
            $asset = collect($release['assets'])->firstWhere('name', "update-v{$version}.zip");
            
            if (!$asset) {
                throw new \Exception("Update package not found for version {$version}");
            }
            
            // Create updates directory if it doesn't exist
            $updatesDir = storage_path('app/updates');
            if (!is_dir($updatesDir)) {
                mkdir($updatesDir, 0755, true);
            }
            
            // Download the asset
            $zipPath = "{$updatesDir}/update-v{$version}.zip";
            file_put_contents($zipPath, fopen($asset['browser_download_url'], 'r'));
            
            // Update status
            $update->status = 'downloaded';
            $update->downloaded_at = now();
            $update->save();
            
            $this->info("Update downloaded successfully.");
            
            // If automatic installation is enabled, start the installation
            if (config('app.auto_install_updates')) {
                $this->call('update:install', ['version' => $version]);
            }
        } catch (\Exception $e) {
            $this->error('Error downloading update: ' . $e->getMessage());
            Log::error('Update download failed: ' . $e->getMessage());
            
            // Update status
            if (isset($update)) {
                $update->status = 'failed';
                $update->error_message = $e->getMessage();
                $update->save();
            }
        }
    }
}
