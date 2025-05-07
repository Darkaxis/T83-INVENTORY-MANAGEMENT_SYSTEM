<?php

namespace App\Console\Commands;

use App\Services\GitHubReleaseService;
use Illuminate\Console\Command;

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
    protected $description = 'Check GitHub for new system updates';

    /**
     * Execute the console command.
     */
    public function handle(GitHubReleaseService $githubService)
    {
        $this->info('Checking for updates...');
        
        try {
            $count = $githubService->checkForUpdates();
            
            if ($count === 0) {
                $this->info('No new updates found.');
            } else {
                $this->info("Found {$count} releases on GitHub.");
                $this->info("New updates added to system.");
            }

            if ($count > 0) {
                $this->info("Processing new updates...");
                $this->call('updates:process');
            }
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error checking for updates: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
