<?php
// filepath: d:\WST\inventory-management-system\app\Services\TenantDatabaseManager.php

namespace App\Services;

use App\Models\Store;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class TenantDatabaseManager
{
    /**
     * Switch to a tenant's database
     *
     * @param Store $store
     * @return void
     */
    public function switchToTenant(Store $store)
    {
        // Create database config for this tenant
        $databaseName = 'tenant_' . $store->slug;
        
        // Check if connection exists, if not create it
        if (!array_key_exists($databaseName, Config::get('database.connections'))) {
            // Clone the default mysql connection
            $config = Config::get('database.connections.mysql');
            
            // Update the database name
            $config['database'] = $databaseName;
            
            // Add the new connection
            Config::set('database.connections.' . $databaseName, $config);
        }
        
        // Set as default connection
        Config::set('database.default', $databaseName);
        
        // Reconnect
        DB::reconnect($databaseName);
        
        // Update session config for this tenant
        Config::set('session.cookie', 'session_' . $store->slug);
        
        Log::info("Switched to tenant database", ['database' => $databaseName]);
    }
    
    /**
     * Switch back to the main database
     *
     * @return void
     */
    public function switchToMain()
    {
        // Set as default connection
        Config::set('database.default', 'mysql');
        
        // Reconnect
        DB::reconnect('mysql');
        
        // Reset session config
        Config::set('session.cookie', 'laravel_session');
        
        Log::info("Switched to main database");
    }
    
    /**
     * Create a new tenant database
     *
     * @param Store $store
     * @return bool
     */
        
    public function createTenantDatabase(Store $store)
    {
        try {
            Log::info("Creating tenant database", ['store_id' => $store->id, 'slug' => $store->slug]);
            
            $databaseName = 'tenant_' . $store->slug;
            
            // Check if database already exists
            $dbExists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);
            
            if (!empty($dbExists)) {
                Log::info("Database already exists", ['database' => $databaseName]);
                return true;
            }
            
            // Create database
            Log::info("Attempting to create database", ['database' => $databaseName]);
            DB::statement("CREATE DATABASE `{$databaseName}`");
            
            // Configure database connection
            $config = Config::get('database.connections.mysql');
            $config['database'] = $databaseName;
            Config::set('database.connections.' . $databaseName, $config);
            
            // Switch to new database
            DB::purge($databaseName);
            DB::reconnect($databaseName);
            
            // Run migrations
            Log::info("Running migrations on new database", ['database' => $databaseName]);
            $this->runMigrations($databaseName);
            
            // Switch back to main database
            $this->switchToMain();
            
            Log::info("Tenant database created successfully", ['database' => $databaseName]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to create tenant database: " . $e->getMessage(), [
                'store_id' => $store->id,
                'database' => 'tenant_' . $store->slug,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Make sure we're back on the main connection
            $this->switchToMain();
            
            return false;
        }
    }
    
    /**
     * Run migrations for a tenant database
     *
     * @param string $connection
     * @return void
     */
    private function runMigrations($connection)
    {
        $previousConnection = Config::get('database.default');
        
        try {
            Config::set('database.default', $connection);
            
            $migrator = app('migrator');
            $migrator->setConnection($connection);
            
            // Get tenant migration path
            $path = database_path('migrations/tenant');
            
            if (file_exists($path)) {
                Log::info("Running tenant migrations", ['connection' => $connection, 'path' => $path]);
                $migrator->run($path);
            } else {
                Log::warning("Tenant migrations folder not found", ['path' => $path]);
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to run migrations: " . $e->getMessage(), [
                'connection' => $connection,
                'trace' => $e->getTraceAsString()
            ]);
        } finally {
            Config::set('database.default', $previousConnection);
        }
    }
    
    /**
     * Drop a tenant database
     *
     * @param Store $store
     * @return bool
     */
    public function dropTenantDatabase(Store $store)
    {
        $databaseName = 'tenant_' . $store->slug;
        
        try {
            // Switch to the main connection to drop the database
            $this->switchToMain();
            
            // Drop the database
            $query = "DROP DATABASE IF EXISTS `$databaseName`";
            DB::statement($query);
            
            Log::info("Dropped tenant database", ['store_id' => $store->id, 'database' => $databaseName]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to drop tenant database: ' . $e->getMessage(), [
                'store_id' => $store->id,
                'database' => $databaseName,
                'exception' => $e
            ]);
            return false;
        }
    }
    
    /**
     * Run migrations for a tenant database
     *
     * @return void
     */
    protected function migrateDatabase()
    {
        try {
            // Get migration path for tenant-specific migrations
            $migrationPath = database_path('migrations/tenant');
            
            // Run the migrations
            Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--force' => true
            ]);
            
            Log::info("Ran tenant migrations successfully");
        } catch (\Exception $e) {
            Log::error('Failed to run tenant migrations: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e; // Re-throw so the caller can handle it
        }
    }
    
    /**
     * Seed the tenant database with initial data
     *
     * @param Store $store
     * @return void
     */
    protected function seedDatabase(Store $store)
    {
        try {
            // Create initial categories
            $categories = [
                ['name' => 'General', 'description' => 'General products', 'store_id' => $store->id, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Electronics', 'description' => 'Electronic devices', 'store_id' => $store->id, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Office Supplies', 'description' => 'Office supplies and stationery', 'store_id' => $store->id, 'created_at' => now(), 'updated_at' => now()],
            ];
            
            foreach ($categories as $category) {
                DB::table('categories')->insert($category);
            }
            
            // Create initial settings
            DB::table('store_settings')->insert([
                'store_id' => $store->id,
                'low_stock_threshold' => 5,
                'currency' => 'USD',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Log::info("Seeded tenant database", ['store_id' => $store->id]);
        } catch (\Exception $e) {
            Log::error('Failed to seed tenant database: ' . $e->getMessage(), [
                'store_id' => $store->id,
                'exception' => $e
            ]);
            // We don't re-throw here because seeding failure shouldn't prevent database creation
        }
    }
}