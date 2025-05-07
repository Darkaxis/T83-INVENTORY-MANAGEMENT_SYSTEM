<?php

namespace App\Console\Commands;

use App\Services\GitHubReleaseService;
use Illuminate\Console\Command;

class TestGitHubFetch extends Command
{
    protected $signature = 'test:github-fetch';
    protected $description = 'Test fetching updates from GitHub';

    public function handle(GitHubReleaseService $githubService)
    {
        $this->info('Testing GitHub release fetch...');
        
        try {
            // Test getting the latest release
            $latest = $githubService->fetchLatestRelease();
            
            if ($latest) {
                $this->info('✓ Latest release found!');
                $this->info('   Tag: ' . $latest['tag_name']);
                $this->info('   Published: ' . $latest['published_at']);
                $this->info('');
            } else {
                $this->error('✗ No latest release found.');
                $this->info('');
            }
            
            // Test checking for updates
            $this->info('Looking for new updates...');
            $newUpdates = $githubService->checkForUpdates();
            
            if ($newUpdates > 0) {
                $this->info("✓ Found {$newUpdates} new update(s)!");
            } else {
                $this->info("✓ No new updates found (already in database).");
            }
            
            // Show all updates in database
            $this->info('');
            $this->info('Current updates in database:');
            $updates = \App\Models\SystemUpdate::orderBy('version', 'desc')->get();
            
            $headers = ['Version', 'Status', 'Checked At'];
            $rows = [];
            
            foreach ($updates as $update) {
                $rows[] = [
                    $update->version, 
                    $update->status,
                    $update->checked_at->format('Y-m-d H:i:s')
                ];
            }
            
            $this->table($headers, $rows);
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . ":" . $e->getLine());
            return self::FAILURE;
        }
    }
}