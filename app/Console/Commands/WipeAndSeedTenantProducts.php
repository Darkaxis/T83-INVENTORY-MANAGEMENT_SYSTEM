<?php
// filepath: d:\WST\inventory-management-system\app\Console\Commands\WipeAndSeedTenantProducts.php

namespace App\Console\Commands;

use App\Models\Store;
use App\Services\TenantDatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WipeAndSeedTenantProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:wipe-and-seed {--store=all : The store slug to process} 
                           {--count=20 : Number of products to seed per store}
                           {--force : Skip confirmation prompts}
                           {--wipe-only : Only wipe products without seeding new ones}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wipe existing products and seed fresh products into tenant databases';

    /**
     * The tenant database manager instance.
     *
     * @var \App\Services\TenantDatabaseManager
     */
    protected $databaseManager;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\TenantDatabaseManager  $databaseManager
     * @return void
     */
    public function __construct(TenantDatabaseManager $databaseManager)
    {
        parent::__construct();
        $this->databaseManager = $databaseManager;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $storeOption = $this->option('store');
        $count = (int)$this->option('count');
        $force = $this->option('force');
        $wipeOnly = $this->option('wipe-only');
        
        // Get stores to process
        if ($storeOption === 'all') {
            $stores = Store::where('status', 'active')->where('approved', true)->get();
            $this->info("Processing {$stores->count()} active stores...");
            
            // Safety check for wiping multiple stores
            if (!$force && !$this->confirm("⚠️ WARNING: You are about to wipe products from {$stores->count()} stores. Are you sure you want to continue?", false)) {
                $this->warn("Operation cancelled.");
                return 1;
            }
        } else {
            $store = Store::where('slug', $storeOption)->first();
            if (!$store) {
                $this->error("Store with slug '{$storeOption}' not found.");
                return 1;
            }
            $stores = collect([$store]);
            $this->info("Processing store: {$store->name} ({$store->slug})");
            
            // Safety check for single store
            if (!$force && !$this->confirm("⚠️ WARNING: You are about to wipe all products from {$store->name}. Are you sure?", false)) {
                $this->warn("Operation cancelled.");
                return 1;
            }
        }

        foreach ($stores as $store) {
            try {
                $this->info("\n🔄 Processing store: {$store->name} ({$store->slug})");
                
                // Connect to tenant database
                if (!$this->connectToTenantDatabase($store)) {
                    continue;
                }
                
                // Wipe products
                $this->wipeProductsForStore($store);
                
                // Seed products if not wipe-only
                if (!$wipeOnly) {
                    $this->info("Now seeding fresh products...");
                    $this->call('tenant:seed-products', [
                        '--store' => $store->slug,
                        '--count' => $count,
                        '--only-products' => true
                    ]);
                }
                
                $this->info("✅ Completed processing store: {$store->name}");
            } catch (\Exception $e) {
                $this->error("Failed to process store {$store->slug}: {$e->getMessage()}");
                Log::error("Tenant wipe/seed error", [
                    'store' => $store->slug,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            } finally {
                // Always switch back to main database when done with a store
                $this->databaseManager->switchToMain();
            }
        }

        return 0;
    }
    
    /**
     * Connect to the tenant database
     *
     * @param  \App\Models\Store  $store
     * @return bool
     */
    protected function connectToTenantDatabase(Store $store): bool
    {
        $this->info("Connecting to tenant database for {$store->name}...");
        
        try {
            $this->databaseManager->switchToTenant($store);
            
            // Verify connection
            $connectedDb = DB::connection('tenant')->getDatabaseName();
            $expectedDb = 'tenant_' . $store->slug;
            
            if ($connectedDb !== $expectedDb) {
                $this->error("Connected to wrong database: {$connectedDb} (expected {$expectedDb})");
                return false;
            }
            
            $this->info("Successfully connected to tenant database: {$connectedDb}");
            return true;
        } catch (\Exception $e) {
            $this->error("Failed to connect to tenant database: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Wipe all products for a specific store.
     *
     * @param  \App\Models\Store  $store
     * @return void
     */
    protected function wipeProductsForStore(Store $store)
    {
        // Verify connection
        $connectedDb = DB::connection('tenant')->getDatabaseName();
        $expectedDb = 'tenant_' . $store->slug;
        
        if ($connectedDb !== $expectedDb) {
            $this->error("Wrong database for wiping: {$connectedDb}, reconnecting...");
            $this->databaseManager->switchToTenant($store);
            $connectedDb = DB::connection('tenant')->getDatabaseName();
            
            if ($connectedDb !== $expectedDb) {
                $this->error("Still wrong database after reconnect: {$connectedDb}");
                return;
            }
        }
        
        // Count products before wiping
        $productCount = DB::connection('tenant')->table('products')->count();
        $this->info("Found {$productCount} products to wipe in {$store->name}.");
        
        // Check if there are sale records that reference products
        $salesExist = false;
        
        // Use Schema facade instead of schema() method
        if (Schema::connection('tenant')->hasTable('sale_items')) {
            $salesCount = DB::connection('tenant')->table('sale_items')->count();
            if ($salesCount > 0) {
                $salesExist = true;
                $this->warn("⚠️ Found {$salesCount} sale items that reference products.");
                
                // Ask for confirmation unless force is true
                if (!$this->option('force') && !$this->confirm("Wiping products will break the link with existing sales records. Continue?", false)) {
                    $this->warn("Skipping product wipe for {$store->name}.");
                    return;
                }
            }
        }
        
        $this->info("Wiping all products from database: {$connectedDb}");
        
        try {
            // Disable foreign key checks without transaction first
            DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Just truncate directly - no need for a transaction for a single table truncate
            DB::connection('tenant')->table('products')->truncate();
            
            // Enable foreign key checks again
            DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->info("✅ Successfully wiped all products from {$store->name}.");
        } catch (\Exception $e) {
            // Make sure foreign key checks are re-enabled even on error
            try {
                DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Exception $innerEx) {
                // Ignore any errors when trying to re-enable foreign key checks
                $this->warn("Could not re-enable foreign key checks: " . $innerEx->getMessage());
            }
            
            $this->error("Error wiping products: " . $e->getMessage());
            throw $e;
        }
    }
}