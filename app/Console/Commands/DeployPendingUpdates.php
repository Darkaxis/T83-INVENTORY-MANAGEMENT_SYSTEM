<?php
// filepath: d:\WST\inventory-management-system\app\Console\Commands\DeployPendingUpdates.php

namespace App\Console\Commands;

use App\Models\DeploymentRing;
use App\Models\SystemUpdate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DeployPendingUpdates extends Command
{
    protected $signature = 'deploy:pending-updates';
    protected $description = 'Deploy pending updates to rings with auto-update enabled';

    public function handle()
    {
        $rings = DeploymentRing::where('auto_update', true)->get();
        $latestUpdate = SystemUpdate::where('status', 'completed')->latest()->first();
        
        if (!$latestUpdate) {
            $this->info('No completed updates available for deployment.');
            return self::SUCCESS;
        }
        
        $this->info("Latest update: v{$latestUpdate->version}");
        $ringCount = 0;
        $storeCount = 0;
        
        foreach ($rings as $ring) {
            if (version_compare($latestUpdate->version, $ring->version, '>')) {
                $this->info("Updating {$ring->name} from v{$ring->version} to v{$latestUpdate->version}");
                
                // Update ring version
                $ring->version = $latestUpdate->version;
                $ring->save();
                $ringCount++;
                
                // Queue updates for all stores in this ring
                foreach ($ring->stores as $store) {
                    Artisan::queue('store:update', [
                        'store' => $store->id,
                        'version' => $latestUpdate->version
                    ]);
                    $storeCount++;
                }
            }
        }
        
        $this->info("Auto-updated {$ringCount} rings with {$storeCount} stores.");
        return self::SUCCESS;
    }
}