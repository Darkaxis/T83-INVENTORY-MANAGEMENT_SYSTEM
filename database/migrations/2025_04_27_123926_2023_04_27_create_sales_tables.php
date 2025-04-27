<?php
// filepath: d:\WST\inventory-management-system\database\migrations\tenant\2023_04_27_create_sales_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Store;
use App\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Get all stores with database_created = true
        $stores = Store::where('database_created', true)->get();
        $databaseManager = app(TenantDatabaseManager::class);
        
        foreach ($stores as $store) {
            try {
                // Switch to tenant database
                $databaseManager->switchToTenant($store);
                
                // Check if sales table already exists to avoid errors
                if (!Schema::connection('tenant')->hasTable('sales')) {
                    // Create sales table
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
                }
                
                // Create sale_items table if it doesn't exist
                if (!Schema::connection('tenant')->hasTable('sale_items')) {
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
                }
                
                // Add sold_count column to products table if it doesn't exist
                if (Schema::connection('tenant')->hasTable('products') && 
                    !Schema::connection('tenant')->hasColumn('products', 'sold_count')) {
                    Schema::connection('tenant')->table('products', function (Blueprint $table) {
                        $table->integer('sold_count')->default(0)->after('stock');
                    });
                }
                
                // Add settings table if it doesn't exist
                if (!Schema::connection('tenant')->hasTable('settings')) {
                    Schema::connection('tenant')->create('settings', function (Blueprint $table) {
                        $table->id();
                        $table->string('store_name');
                        $table->string('accent_color')->nullable();
                        $table->binary('logo_binary')->nullable();
                        $table->string('logo_mime_type')->nullable();
                        $table->decimal('tax_rate', 5, 2)->default(10.00);
                        $table->boolean('receipt_show_logo')->default(true);
                        $table->string('receipt_footer_text')->nullable();
                        $table->timestamps();
                    });
                    
                    // Add default settings
                    DB::connection('tenant')->table('settings')->insert([
                        'store_name' => $store->name,
                        'accent_color' => '#4e73df',
                        'tax_rate' => 10.00,
                        'receipt_show_logo' => true,
                        'receipt_footer_text' => 'Thank you for your business!',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                
                // Switch back to main database
                $databaseManager->switchToMain();
                
            } catch (\Exception $e) {
                // Make sure we switch back to main db even if there's an error
                $databaseManager->switchToMain();
                
                // Log the error
                \Illuminate\Support\Facades\Log::error("Error updating tenant database schema for store: {$store->name}", [
                    'store_id' => $store->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    public function down()
    {
        // Getting all stores with database_created = true
        $stores = Store::where('database_created', true)->get();
        $databaseManager = app(TenantDatabaseManager::class);
        
        foreach ($stores as $store) {
            try {
                // Switch to tenant database
                $databaseManager->switchToTenant($store);
                
                // Drop tables in reverse order
                Schema::connection('tenant')->dropIfExists('sale_items');
                Schema::connection('tenant')->dropIfExists('sales');
                
                // Remove sold_count column from products table
                if (Schema::connection('tenant')->hasTable('products') && 
                    Schema::connection('tenant')->hasColumn('products', 'sold_count')) {
                    Schema::connection('tenant')->table('products', function (Blueprint $table) {
                        $table->dropColumn('sold_count');
                    });
                }
                
                // Switch back to main database
                $databaseManager->switchToMain();
                
            } catch (\Exception $e) {
                // Make sure we switch back to main db even if there's an error
                $databaseManager->switchToMain();
                
                // Log the error
                \Illuminate\Support\Facades\Log::error("Error rolling back tenant database schema for store: {$store->name}", [
                    'store_id' => $store->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
};