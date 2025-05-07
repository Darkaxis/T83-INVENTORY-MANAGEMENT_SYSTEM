<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemUpdate;
use Illuminate\Support\Str;

class TestUpdate extends Command
{
    // Change --version to --update-version to avoid conflicts
    protected $signature = 'test:update {action=create} {--update-version=} {--status=checking}';
    protected $description = 'Create a test update record for UI testing';

    public function handle()
    {
        $action = $this->argument('action');
        
        switch ($action) {
            case 'create':
                $this->createTestUpdate();
                break;
            case 'download':
                $this->simulateDownload();
                break;
            case 'clear':
                $this->clearTestUpdates();
                break;
            default:
                $this->error("Unknown action: {$action}");
                return 1;
        }
    }
    
    protected function createTestUpdate()
    {
        // Update the option name here
        $version = $this->option('update-version') ?? $this->generateVersion();
        $status = $this->option('status');
        
        SystemUpdate::create([
            'version' => $version,
            'status' => $status,
            'release_notes' => "This is a test update to version {$version}.\n\n- Added new feature X\n- Fixed bug Y\n- Improved performance of Z",
            'checked_at' => now(),
            'downloaded_at' => in_array($status, ['downloaded', 'installing', 'completed']) ? now() : null,
            'installed_at' => $status === 'completed' ? now() : null,
        ]);
        
        $this->info("Created test update v{$version} with status '{$status}'");
    }
    
    protected function simulateDownload()
    {
        // Update the option name here
        $version = $this->option('update-version');
        if (!$version) {
            $this->error('Version is required for download simulation');
            return;
        }
        
        $update = SystemUpdate::where('version', $version)->first();
        if (!$update) {
            $this->error("No update found with version {$version}");
            return;
        }
        
        $update->status = 'downloaded';
        $update->downloaded_at = now();
        $update->save();
        
        $this->info("Updated status of v{$version} to 'downloaded'");
    }
    
    protected function clearTestUpdates()
    {
        $count = SystemUpdate::where('version', 'LIKE', '9.%')->delete();
        $this->info("Cleared {$count} test updates");
    }
    
    protected function generateVersion()
    {
        // Create a version in the 9.x range (unlikely to conflict with real versions)
        return '9.' . rand(0, 9) . '.' . rand(0, 9);
    }
}