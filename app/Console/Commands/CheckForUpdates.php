<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use App\Models\SystemUpdate;
use Illuminate\Support\Facades\Log;
class CheckForUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-for-updates';

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
        $this->info('Checking for system updates...');
        
        try {
            // Get the current version from your app
            $currentVersion = config('app.version');
            
            // Query GitHub API for latest release
            $client = new Client();
            $response = $client->get(' https://api.github.com/repos/Darkaxis/T83-INVENTORY-MANAGEMENT_SYSTEM/releases/latest');
            $latestRelease = json_decode($response->getBody(), true);
            
            $latestVersion = ltrim($latestRelease['tag_name'], 'v');
            
            if (version_compare($latestVersion, $currentVersion, '>')) {
                // New version available
                $this->info("New version available: {$latestVersion}");
                
                // Record the update in the database
                SystemUpdate::create([
                    'version' => $latestVersion,
                    'status' => 'checking',
                    'release_notes' => $latestRelease['body'],
                    'checked_at' => now(),
                ]);
                
                // If automatic updates are enabled, start the download
                if (config('app.auto_update')) {
                    $this->call('update:download', ['version' => $latestVersion]);
                }
            } else {
                $this->info('System is up to date.');
            }
        } catch (\Exception $e) {
            $this->error('Error checking for updates: ' . $e->getMessage());
            Log::error('Update check failed: ' . $e->getMessage());
        }
    }
}
