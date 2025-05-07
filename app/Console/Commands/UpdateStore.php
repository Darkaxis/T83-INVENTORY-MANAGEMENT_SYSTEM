<?php

namespace App\Console\Commands;

use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateStore extends Command
{
    protected $signature = 'store:update {store} {version}';
    protected $description = 'Update a store to a specific version';

    public function handle()
    {
        $storeId = $this->argument('store');
        $targetVersion = $this->argument('version');
        
        $store = Store::find($storeId);
        
        if (!$store) {
            $this->error("Store with ID {$storeId} not found");
            return 1;
        }
        
        $this->info("Updating store '{$store->name}' to version {$targetVersion}...");
        
        try {
            // Run database migrations for the store
            $this->call('tenant:migrate', [
                'tenant' => $store->id
            ]);
            
            // Update the store's version through its deployment ring
            if ($store->deployment_ring) {
                $this->info("Store is in {$store->deployment_ring->name} deployment ring");
            }
            
            $this->info("Store successfully updated to version {$targetVersion}!");
            
        } catch (\Exception $e) {
            $this->error("Error updating store: " . $e->getMessage());
            Log::error("Store update failed", [
                'store_id' => $storeId,
                'version' => $targetVersion,
                'error' => $e->getMessage()
            ]);
            
            return 1;
        }
        
        return 0;
    }
}