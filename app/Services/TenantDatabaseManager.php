<?php
// filepath: d:\WST\inventory-management-system\app\Services\TenantDatabaseManager.php

namespace App\Services;

use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

class TenantDatabaseManager
{
    protected $mainConnection;
    
    /**
     * Create a new TenantDatabaseManager instance.
     */
    public function __construct()
    {
        // Store the main database connection name
        $this->mainConnection = config('database.default', 'mysql');
    }

    public function createTenantDatabase(Store $store)
    {
        try {
            // Prefix to avoid collisions with existing databases
            $tenantDB = 'tenant_' . $store->slug;
            
            // Create database with raw SQL for direct database creation
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$tenantDB}`");
            
            // Configure and connect to the new database
            Config::set('database.connections.tenant.database', $tenantDB);
            Config::set('database.connections.tenant.driver', 'mysql');
            
            DB::purge('tenant');
            
            // Test connection to new database
            try {
                DB::connection('tenant')->getPdo();
                Log::info("Successfully connected to tenant database: {$tenantDB}");
            } catch (\Exception $e) {
                Log::error("Failed to connect to tenant database after creation: {$e->getMessage()}");
                throw $e;
            }
            
            // Create the necessary tables in the tenant database
            $this->createTenantTables();
            
            // Seed initial data
            $this->seedTenantData();
            
            // Mark database as created in the store record
            $store->update(['database_created' => true]);
            
            Log::info("Successfully created tenant database and tables for store: {$store->name}", [
                'store_id' => $store->id,
                'database' => $tenantDB
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to create tenant database: " . $e->getMessage(), [
                'store_id' => $store->id,
                'slug' => $store->slug,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    protected function createTenantTables()
    {
        // Create users table for tenant authentication
        Schema::connection('tenant')->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('user');
            $table->rememberToken();
            $table->timestamps();
        });
        
        // Create password resets table
        Schema::connection('tenant')->create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        
        // Create roles table
        Schema::connection('tenant')->create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });
        
        // Create permissions table
        Schema::connection('tenant')->create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });
        
        // Create model_has_roles pivot table
        Schema::connection('tenant')->create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            
            $table->primary(['role_id', 'model_id', 'model_type']);
        });
        
        // Create products table
        Schema::connection('tenant')->create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique()->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('quantity')->default(0);
            $table->unsignedBigInteger('category_id')->nullable();
            $table->timestamps();
        });
        
        // Create categories table
        Schema::connection('tenant')->create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        
        Log::info("Created all required tables in tenant database");
    }
    
    protected function seedTenantData()
    {
        // Seed default roles
        DB::connection('tenant')->table('roles')->insert([
            ['name' => 'owner', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manager', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'staff', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]
        ]);
        
        // Seed default categories
        DB::connection('tenant')->table('categories')->insert([
            ['name' => 'General', 'description' => 'Default category for products', 'created_at' => now(), 'updated_at' => now()]
        ]);
        
        Log::info("Seeded initial data in tenant database");
    }
    
    public function dropDatabase($databaseName)
    {
        // Validate database name to prevent SQL injection
        if (!preg_match('/^tenant_[a-z0-9\-]+$/', $databaseName)) {
            throw new \Exception("Invalid database name: {$databaseName}");
        }
        
        try {
            DB::statement("DROP DATABASE IF EXISTS `{$databaseName}`");
            Log::info("Successfully dropped database: {$databaseName}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to drop database {$databaseName}: " . $e->getMessage());
            return false;
        }
    }
    public function switchToTenant(Store $store)
    {
        // Switch to the tenant database connection
        Config::set('database.connections.tenant.driver', 'mysql');
         
        Config::set('database.connections.tenant.database', 'tenant_' . $store->slug);
        DB::purge('tenant');
        
        // Test connection to the tenant database
        try {
            DB::connection('tenant')->getPdo();
            Log::info("Switched to tenant database: {$store->slug}");
        } catch (\Exception $e) {
            Log::error("Failed to switch to tenant database: " . $e->getMessage());
            throw $e;
        }
    }
    public function switchToMain()
    {
        // Switch back to the main database connection
        Config::set('database.connections.tenant.database', null);
        DB::purge('tenant');
        
        // Test connection to the main database
        try {
            DB::connection()->getPdo();
            Log::info("Switched back to main database");
        } catch (\Exception $e) {
            Log::error("Failed to switch back to main database: " . $e->getMessage());
            throw $e;
        }
        
    }
}
