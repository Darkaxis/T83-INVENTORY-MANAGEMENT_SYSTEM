<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Models\SystemUpdate;
use App\Services\UpdateDeploymentService;
use Illuminate\Console\Command;

class StoreUpdate extends Command
{
    protected $signature = 'store:update {store} {version}';
    protected $description = 'Update a store to a specific version';

    public function handle(UpdateDeploymentService $deploymentService)
    {
        $storeId = $this->argument('store');
        $version = $this->argument('version');
        
        $store = Store::findOrFail($storeId);
        $update = SystemUpdate::where('version', $version)
            ->where('status', 'completed')
            ->firstOrFail();
            
        $this->info("Updating store {$store->name} to version {$version}...");
        
        if ($deploymentService->deployToStore($store, $update)) {
            $this->info("✓ Successfully updated store to version {$version}");
            return self::SUCCESS;
        } else {
            $this->error("✗ Failed to update store to version {$version}");
            return self::FAILURE;
        }
    }
}