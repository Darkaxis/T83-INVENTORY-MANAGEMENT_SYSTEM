<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Models\Product;
use App\Models\User;
use App\Services\TenantDatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class SeedTenantProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:seed-products {--store=all : The store slug to seed products for} 
                           {--count=20 : Number of products to seed per store}
                           {--users=5 : Number of users to seed per store}
                           {--with-users : Also seed users for the store}
                           {--only-users : Only seed users, not products}
                           {--only-products : Only seed products, not users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed product and user data into tenant databases';

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
        $userCount = (int)$this->option('users');
        $withUsers = $this->option('with-users') || $this->option('only-users');
        $onlyUsers = $this->option('only-users');
        $onlyProducts = $this->option('only-products');
        
        // Get stores to seed
        if ($storeOption === 'all') {
            $stores = Store::where('status', 'active')->where('approved', true)->get();
            $this->info("Processing {$stores->count()} active stores...");
        } else {
            $store = Store::where('slug', $storeOption)->first();
            if (!$store) {
                $this->error("Store with slug '{$storeOption}' not found.");
                return 1;
            }
            $stores = collect([$store]);
            $this->info("Processing store: {$store->name} ({$store->slug})");
        }

        foreach ($stores as $store) {
            try {
                // Connect to tenant database, creating if needed
                if (!$this->prepareTenantDatabase($store)) {
                    continue;
                }
                
                // Seed initial tenant data
                $this->seedTenantData();
                
                // Seed users if requested and not only products
                if ($withUsers && !$onlyProducts) {
                    $this->seedUsersForStore($store, $userCount);
                }
                
                // Seed products if not only-users or if only-products
                if (!$onlyUsers || $onlyProducts) {
                    $this->seedProductsForStore($store, $count);
                }
            } catch (\Exception $e) {
                $this->error("Failed to seed data for store {$store->slug}: {$e->getMessage()}");
                Log::error("Tenant seeding error", [
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
     * Prepare the tenant database (create if needed)
     *
     * @param  \App\Models\Store  $store
     * @return bool
     */
    protected function prepareTenantDatabase(Store $store): bool
    {
        $this->info("Processing tenant database for {$store->name}...");
        
        // Check if tenant database exists
        $tenantDbName = 'tenant_' . $store->slug;
        $databaseExists = $this->checkDatabaseExists($tenantDbName);
        
        if (!$databaseExists) {
            $this->warn("Database {$tenantDbName} doesn't exist. Creating it now...");
            
            try {
                // Use the built-in service to create the tenant database
                $result = $this->databaseManager->createTenantDatabase($store);
                
                if ($result) {
                    $this->info("Successfully created database for {$store->name}");
                } else {
                    $this->error("Failed to create database for {$store->name}");
                    return false;
                }
            } catch (\Exception $e) {
                $this->error("Error creating database: " . $e->getMessage());
                Log::error("Failed to create tenant database", [
                    'store' => $store->slug,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return false;
            }
        }
        
        // Now try to switch to tenant database
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
     * Seed users for a specific store.
     *
     * @param  \App\Models\Store  $store
     * @param  int  $count
     * @return void
     */
    protected function seedUsersForStore(Store $store, int $count)
    {
        // Verify connection
        $connectedDb = DB::connection('tenant')->getDatabaseName();
        $expectedDb = 'tenant_' . $store->slug;
        
        if ($connectedDb !== $expectedDb) {
            $this->error("Wrong database for users: {$connectedDb}, reconnecting...");
            $this->databaseManager->switchToTenant($store);
            $connectedDb = DB::connection('tenant')->getDatabaseName();
            
            if ($connectedDb !== $expectedDb) {
                $this->error("Still wrong database after reconnect: {$connectedDb}");
                return;
            }
        }
        
        $this->info("Seeding users in database: {$connectedDb}");
        
        // Check if we can add more users based on tier limits
        $maxUsers = $this->getUserLimit($store);
        $existingCount = DB::connection('tenant')->table('users')->count();
        
        $this->info("Store has {$existingCount} users. Limit is " . ($maxUsers > 0 ? $maxUsers : "unlimited") . ".");
        
        if ($maxUsers > 0 && $existingCount >= $maxUsers) {
            $this->warn("User limit reached for {$store->name}. Skipping user creation.");
            return;
        }
        
        // Calculate how many users we can add
        $toCreate = ($maxUsers > 0) ? min($count, $maxUsers - $existingCount) : $count;
        
        if ($toCreate <= 0) {
            $this->warn("Cannot add more users due to limit restrictions.");
            return;
        }
        
        $this->info("Creating {$toCreate} users for {$store->name}...");
        
        // Create users with realistic data
        $faker = \Faker\Factory::create();
        
        // Always ensure we have an owner if there are none
        $hasOwner = DB::connection('tenant')->table('users')->where('role', 'manager')->exists();
        
        if (!$hasOwner) {
            $this->info("Creating store owner account...");
            DB::connection('tenant')->table('users')->insert([
                'name' => 'Store Owner',
                'email' => 'owner@' . $store->slug . '.example.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $toCreate--;
        }
        
        // Create manager if needed
        $hasManager = DB::connection('tenant')->table('users')->where('role', 'manager')->exists();
        if (!$hasManager && $toCreate > 0) {
            $this->info("Creating store manager account...");
            DB::connection('tenant')->table('users')->insert([
                'name' => 'Store Manager',
                'email' => 'manager@' . $store->slug . '.example.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $toCreate--;
        }
        
        // Define possible roles with weighted distribution
        $roles = [
            'staff' => 60,
            'inventory' => 25,
            'manager' => 15,
        ];
        
        // Create additional users
        for ($i = 0; $i < $toCreate; $i++) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $name = $firstName . ' ' . $lastName;
            
            // Weighted random role selection
            $role = $this->getWeightedRandomRole($roles);
            
            $email = strtolower($firstName . '.' . $lastName . '@' . $store->slug . '.example.com');
            
            // Make sure email is unique
            $count = 1;
            $originalEmail = $email;
            while (DB::connection('tenant')->table('users')->where('email', $email)->exists()) {
                $email = str_replace('@', $count . '@', $originalEmail);
                $count++;
            }
            
            DB::connection('tenant')->table('users')->insert([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => $role,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            if ($i % 5 === 0 && $i > 0) {
                $this->output->write('.');
            }
        }
        
        $this->info("\nCreated " . ($count - $toCreate) . " users for {$store->name}.");
    }

    /**
     * Get a weighted random role.
     * 
     * @param array $roles
     * @return string
     */
    protected function getWeightedRandomRole(array $roles): string
    {
        $total = array_sum($roles);
        $rand = mt_rand(1, $total);
        
        $sum = 0;
        foreach ($roles as $role => $weight) {
            $sum += $weight;
            if ($rand <= $sum) {
                return $role;
            }
        }
        
        return array_key_first($roles);
    }
    
    /**
     * Seed products for a specific store.
     *
     * @param  \App\Models\Store  $store
     * @param  int  $count
     * @return void
     */
    protected function seedProductsForStore(Store $store, int $count)
    {
        // Verify connection
        $connectedDb = DB::connection('tenant')->getDatabaseName();
        $expectedDb = 'tenant_' . $store->slug;
        
        if ($connectedDb !== $expectedDb) {
            $this->error("Wrong database for products: {$connectedDb}, reconnecting...");
            $this->databaseManager->switchToTenant($store);
            $connectedDb = DB::connection('tenant')->getDatabaseName();
            
            if ($connectedDb !== $expectedDb) {
                $this->error("Still wrong database after reconnect: {$connectedDb}");
                return;
            }
        }
        
        $this->info("Seeding products in database: {$connectedDb}");
        
        // Check if we can add more products based on tier limits
        $maxProducts = $this->getProductLimit($store);
        $existingCount = DB::connection('tenant')->table('products')->count();
        
        $this->info("Store has {$existingCount} products. Limit is " . ($maxProducts > 0 ? $maxProducts : "unlimited") . ".");
        
        if ($maxProducts > 0 && $existingCount >= $maxProducts) {
            $this->warn("Product limit reached for {$store->name}. Skipping.");
            return;
        }
        
        // Calculate how many products we can add
        $toCreate = ($maxProducts > 0) ? min($count, $maxProducts - $existingCount) : $count;
        
        if ($toCreate <= 0) {
            $this->warn("Cannot add more products due to limit restrictions.");
            return;
        }
        
        $this->info("Creating {$toCreate} products for {$store->name}...");
        
        // Create products with realistic data
        $faker = \Faker\Factory::create();
        
        $categories = ['Electronics', 'Office Supplies', 'Furniture', 'Clothing', 'Tools'];
        
        // Get default category id
        $defaultCategoryId = DB::connection('tenant')->table('categories')
            ->where('name', 'General')
            ->value('id') ?? null;
            
        for ($i = 0; $i < $toCreate; $i++) {
            $category = $faker->randomElement($categories);
            $name = $this->generateProductName($category, $faker);
            $sku = $this->generateSku($category, $faker);
            
            DB::connection('tenant')->table('products')->insert([
                'name' => $name,
                'sku' => $sku,
                'description' => $faker->paragraph(),
                'price' => $faker->randomFloat(2, 10, 1000),
                'stock' => $faker->numberBetween(0, 100),
                'status' => $faker->boolean(),
                'category_id' => $defaultCategoryId,
                'sold_count' => 0,
                'barcode' => $faker->ean13(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            if ($i % 10 === 0 && $i > 0) {
                $this->output->write('.');
            }
        }
        
        $finalCount = DB::connection('tenant')->table('products')->count();
        $this->info("\nCreated {$toCreate} products for {$store->name}. Total products now: {$finalCount}");
    }
    
    /**
     * Generate a realistic product name based on category.
     *
     * @param  string  $category
     * @param  \Faker\Generator  $faker
     * @return string
     */
    protected function generateProductName(string $category, $faker): string
    {
        $brands = [
            'Electronics' => ['Apple', 'Samsung', 'Sony', 'Dell', 'LG', 'Logitech'],
            'Office Supplies' => ['Staples', 'HP', 'Canon', 'Epson', 'Sharpie', 'Post-it'],
            'Furniture' => ['IKEA', 'Ashley', 'Steelcase', 'Herman Miller', 'Sauder'],
            'Clothing' => ['Nike', 'Adidas', 'Levi\'s', 'Gap', 'H&M', 'Zara'],
            'Tools' => ['DeWalt', 'Bosch', 'Makita', 'Milwaukee', 'Stanley', 'Craftsman']
        ];
        
        $products = [
            'Electronics' => ['Laptop', 'Smartphone', 'Tablet', 'Monitor', 'Keyboard', 'Mouse', 'Headphones'],
            'Office Supplies' => ['Notebook', 'Printer', 'Paper', 'Stapler', 'Markers', 'Toner', 'Binder'],
            'Furniture' => ['Chair', 'Desk', 'Bookshelf', 'Cabinet', 'Table', 'Sofa', 'Drawer'],
            'Clothing' => ['T-Shirt', 'Jeans', 'Jacket', 'Sweater', 'Shoes', 'Socks', 'Hat'],
            'Tools' => ['Drill', 'Saw', 'Hammer', 'Screwdriver Set', 'Wrench', 'Pliers', 'Measuring Tape']
        ];
        
        $brand = $faker->randomElement($brands[$category] ?? ['Generic']);
        $product = $faker->randomElement($products[$category] ?? ['Item']);
        $model = $faker->bothify('###?');
        
        return "{$brand} {$product} {$model}";
    }
    
    /**
     * Generate a SKU based on category.
     *
     * @param  string  $category
     * @param  \Faker\Generator  $faker
     * @return string
     */
    protected function generateSku(string $category, $faker): string
    {
        $prefix = strtoupper(substr($category, 0, 3));
        return $prefix . '-' . $faker->bothify('######');
    }
    
    /**
     * Check if a database exists
     *
     * @param string $dbName
     * @return bool
     */
    private function checkDatabaseExists(string $dbName): bool
    {
        try {
            $databases = DB::select("SHOW DATABASES LIKE '{$dbName}'");
            return count($databases) > 0;
        } catch (\Exception $e) {
            $this->error("Error checking if database exists: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get product limit based on store's pricing tier.
     *
     * @param  \App\Models\Store  $store
     * @return int
     */
    protected function getProductLimit(Store $store): int
    {
        // If no pricing tier, use a default
        if (!$store->pricing_tier_id) {
            return 20;
        }
        
        // Get pricing tier from main database
        $this->databaseManager->switchToMain();
        $pricingTier = \App\Models\PricingTier::find($store->pricing_tier_id);
        $this->databaseManager->switchToTenant($store);
        
        if (!$pricingTier) {
            return 20;
        }
        
        // -1 means unlimited
        if ($pricingTier->product_limit === -1) {
            return 0; // No limit
        }
        
        return $pricingTier->product_limit;
    }
    
    /**
     * Get user limit based on store's pricing tier.
     *
     * @param  \App\Models\Store  $store
     * @return int
     */
    protected function getUserLimit(Store $store): int
    {
        // If no pricing tier, use a default
        if (!$store->pricing_tier_id) {
            return 3;
        }
        
        // Get pricing tier from main database
        $this->databaseManager->switchToMain();
        $pricingTier = \App\Models\PricingTier::find($store->pricing_tier_id);
        $this->databaseManager->switchToTenant($store);
        
        if (!$pricingTier) {
            return 3;
        }
        
        // -1 means unlimited
        if ($pricingTier->user_limit === null || $pricingTier->user_limit === -1) {
            return 0; // No limit
        }
        
        return $pricingTier->user_limit;
    }
    
    /**
     * Seed initial tenant data.
     *
     * @return void
     */
    protected function seedTenantData()
    {
        // Seed default roles
        DB::connection('tenant')->table('roles')->insert([
            ['name' => 'manager', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'staff', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]
        ]);
        
        // Seed default categories
        DB::connection('tenant')->table('categories')->insert([
            [
                'name' => 'General', 
                'description' => 'Default category for products', 
                'status' => true,
                'sort_order' => 1,
                'created_at' => now(), 
                'updated_at' => now()
            ]
        ]);
        
        // // Seed default settings
        // DB::connection('tenant')->table('settings')->insert([
        //     [
        //         'store_name' => 'My Store',
        //         'accent_color' => '#4e73df',  // Default blue
        //         'tax_rate' => 10.00,
        //         'receipt_show_logo' => true,
        //         'receipt_footer_text' => 'Thank you for your business!',
        //         'created_at' => now(),
        //         'updated_at' => now()
        //     ]
        // ]);
        
        Log::info("Seeded initial data in tenant database");
    }
}