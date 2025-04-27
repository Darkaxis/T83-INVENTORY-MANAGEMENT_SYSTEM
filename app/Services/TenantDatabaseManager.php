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
            $table->boolean('is_active')->default(true);
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
        
        // Create products table with additional fields for checkout
        Schema::connection('tenant')->create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->nullable()->index();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('stock')->default(0); // Changed from stock to stock
            $table->integer('sold_count')->default(0); // Track sales
          
            $table->unsignedBigInteger('category_id')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        
        // Create categories table
        Schema::connection('tenant')->create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
           
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        
        // Create sales table for checkout functionality
        Schema::connection('tenant')->create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('user_id'); // cashier who processed the sale
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method')->default('cash');
            $table->string('payment_status')->default('completed');
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        
        // Create sale_items table for checkout items
        Schema::connection('tenant')->create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_name');
            $table->string('product_sku');
            $table->integer('stock');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->timestamps();
            
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
        });
        
        // Create settings table for store settings
        Schema::connection('tenant')->create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name');
            $table->string('accent_color')->nullable();
            $table->binary('logo_binary')->nullable();
            $table->string('logo_mime_type')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(10.00); // Default 10% tax rate
            $table->boolean('receipt_show_logo')->default(true);
            $table->string('receipt_footer_text')->nullable();
            $table->timestamps();
        });
        
        Log::info("Created all required tables in tenant database");
    }
    
    protected function seedTenantData()
    {
        // Seed default roles
        DB::connection('tenant')->table('roles')->insert([
            ['name' => 'manager', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
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

    /**
     * Count records from a tenant's table
     *
     * @param \App\Models\Store $store
     * @param string $tableName
     * @return int
     */
    public function countTenantRecords(Store $store, string $tableName): int
    {
        try {
            // Switch to tenant database
            $this->switchToTenant($store);
            
            // Count records
            $count = DB::connection('tenant')->table($tableName)->count();
            
            // Switch back to main
            $this->switchToMain();
            
            return $count;
        } catch (\Exception $e) {
            // Make sure we switch back to main db even if there's an error
            $this->switchToMain();
            
            Log::error("Error counting records in tenant database", [
                'store' => $store->slug,
                'table' => $tableName,
                'error' => $e->getMessage()
            ]);
            
            return 0; // Return 0 if error
        }
    }
}
