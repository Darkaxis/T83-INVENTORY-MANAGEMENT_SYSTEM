<?php
// filepath: d:\WST\inventory-management-system\app\Console\Commands\TenantMigrate.php

namespace App\Console\Commands;

use App\Models\Store;
use App\Services\TenantDatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class TenantMigrate extends Command
{
    protected $signature = 'tenant:migrate {--store= : The store slug to migrate} {--force : Force the operation to run}';
    protected $description = 'Run migrations for tenant databases';

    protected $databaseManager;

    public function __construct(TenantDatabaseManager $databaseManager)
    {
        parent::__construct();
        $this->databaseManager = $databaseManager;
    }

    public function handle()
    {
        $storeSlug = $this->option('store');
        $force = $this->option('force');
        
        if ($storeSlug) {
            // Migrate specific store
            $store = Store::where('slug', $storeSlug)->first();
            
            if (!$store) {
                $this->error("Store with slug '{$storeSlug}' not found");
                return 1;
            }
            
            $this->migrateStore($store, $force);
        } else {
            // Migrate all stores
            $stores = Store::all();
            
            if ($stores->isEmpty()) {
                $this->info("No stores found to migrate");
                return 0;
            }
            
            $this->info("Migrating all {$stores->count()} stores...");
            
            $count = 0;
            foreach ($stores as $store) {
                $this->info("\nMigrating store: {$store->name} ({$store->slug})");
                if ($this->migrateStore($store, $force)) {
                    $count++;
                }
            }
            
            $this->info("\n{$count} of {$stores->count()} stores migrated successfully");
        }
        
        return 0;
    }
    
    protected function migrateStore(Store $store, bool $force): bool
    {
        try {
            $this->databaseManager->switchToTenant($store);
            
            // Get tenant migrations path
            $path = database_path('migrations/tenant');
            
            if (!file_exists($path)) {
                $this->error("Tenant migrations folder not found: {$path}");
                return false;
            }
            
            // Create migrations table if it doesn't exist
            $migrator = app('migrator');
            
            if (!Schema::hasTable('migrations')) {
                $this->info("Creating migrations table...");
                $migrator->getRepository()->createRepository();
            }
            
            // Run migrations
            $this->info("Running migrations for {$store->slug}...");
            $migrator->run($path, ['pretend' => !$force]);
            
            $this->info("Migrations completed for {$store->slug}");
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return true;
        } catch (\Exception $e) {
            $this->error("Failed to migrate {$store->slug}: " . $e->getMessage());
            
            // Ensure we switch back to the main database
            try {
                $this->databaseManager->switchToMain();
            } catch (\Exception $e) {
                $this->error("Additionally failed to switch back to main database: " . $e->getMessage());
            }
            
            return false;
        }
    }
}