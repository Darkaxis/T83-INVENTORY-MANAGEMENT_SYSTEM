<?php
// filepath: d:\WST\inventory-management-system\app\Console\Commands\TenantMigrateRollback.php

namespace App\Console\Commands;

use App\Models\Store;
use App\Services\TenantDatabaseManager;
use Illuminate\Console\Command;

class TenantMigrateRollback extends Command
{
    protected $signature = 'tenant:migrate:rollback {--store= : The store slug to rollback} {--step=1 : The number of migrations to rollback} {--force : Force the operation to run}';
    protected $description = 'Rollback migrations for tenant databases';

    protected $databaseManager;

    public function __construct(TenantDatabaseManager $databaseManager)
    {
        parent::__construct();
        $this->databaseManager = $databaseManager;
    }

    public function handle()
    {
        $storeSlug = $this->option('store');
        $step = $this->option('step');
        $force = $this->option('force');
        
        if ($storeSlug) {
            // Rollback specific store
            $store = Store::where('slug', $storeSlug)->first();
            
            if (!$store) {
                $this->error("Store with slug '{$storeSlug}' not found");
                return 1;
            }
            
            $this->rollbackStore($store, $step, $force);
        } else {
            // Rollback all stores
            $stores = Store::all();
            
            if ($stores->isEmpty()) {
                $this->info("No stores found to rollback");
                return 0;
            }
            
            $this->info("Rolling back {$stores->count()} stores...");
            
            foreach ($stores as $store) {
                $this->info("\nRolling back store: {$store->name} ({$store->slug})");
                $this->rollbackStore($store, $step, $force);
            }
        }
        
        return 0;
    }
    
    protected function rollbackStore(Store $store, int $step, bool $force): bool
    {
        try {
            $this->databaseManager->switchToTenant($store);
            
            // Get migrator
            $migrator = app('migrator');
            $migrator->setConnection('tenant_' . $store->slug);
            
            // Run rollback
            $this->info("Rolling back migrations for {$store->slug}...");
            $migrator->rollback([
                'pretend' => !$force,
                'step' => $step
            ]);
            
            $this->info("Rollback completed for {$store->slug}");
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return true;
        } catch (\Exception $e) {
            $this->error("Failed to rollback {$store->slug}: " . $e->getMessage());
            
            // Ensure we switch back to the main database
            $this->databaseManager->switchToMain();
            
            return false;
        }
    }
}