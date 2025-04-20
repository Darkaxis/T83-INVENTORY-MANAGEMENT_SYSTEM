<?php

namespace Database\Seeders;

use App\Models\PricingTier;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoStoreWithProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a demo store
        $store = Store::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Demo Electronics Store',
                'email' => 'demo@example.com',
                'phone' => '555-123-4567',
                'address' => '123 Demo Street',
                'city' => 'Demo City',
                'state' => 'DS',
                'zip' => '12345',
                'status' => 'active',
                'approved' => true,
                'database_created' => true
            ]
        );
        
        // Assign a pricing tier (Professional)
        $pricingTier = PricingTier::where('name', 'Professional')->first();
        if ($pricingTier) {
            $store->update([
                'pricing_tier_id' => $pricingTier->id,
                'billing_cycle' => 'monthly',
                'subscription_start_date' => now(),
                'subscription_end_date' => now()->addMonth(),
                'auto_renew' => true
            ]);
        }
        
        $this->command->info('Demo store created: demo.inventory.test');
        
        // Create a store owner
        $user = User::firstOrCreate(
            ['email' => 'demo-owner@example.com'],
            [
                'name' => 'Demo Store Owner',
                'password' => Hash::make('password'),
                'store_id' => $store->id,
                'role' => 'owner',
                'email_verified_at' => now()
            ]
        );
        
        $this->command->info('Demo store owner created: demo-owner@example.com / password');
        
        // Create products for the demo store
        $productCount = 50; // Create 50 sample products
        $existingCount = Product::where('store_id', $store->id)->count();
        
        if ($existingCount >= $productCount) {
            $this->command->info("Demo store already has {$existingCount} products. Skipping product creation.");
            return;
        }
        
        $toCreate = $productCount - $existingCount;
        $this->command->info("Creating {$toCreate} products for demo store...");
        
        // Create specific product categories
        $categories = [
            'Laptops' => ['price_range' => [599, 2999], 'count' => 8],
            'Smartphones' => ['price_range' => [299, 1299], 'count' => 10],
            'Accessories' => ['price_range' => [9.99, 99.99], 'count' => 15],
            'Components' => ['price_range' => [29.99, 599.99], 'count' => 12],
            'Gaming' => ['price_range' => [19.99, 499.99], 'count' => 5],
        ];
        
        foreach ($categories as $category => $config) {
            for ($i = 0; $i < $config['count']; $i++) {
                $this->createProductInCategory($store->id, $category, $config['price_range']);
            }
        }
        
        $this->command->info('Demo store products created!');
    }
    
    /**
     * Create a product in a specific category
     */
    private function createProductInCategory($storeId, $category, $priceRange)
    {
        $faker = \Faker\Factory::create();
        
        $brands = [
            'Laptops' => ['Dell', 'HP', 'Lenovo', 'Apple', 'Asus', 'Acer', 'Microsoft'],
            'Smartphones' => ['Apple', 'Samsung', 'Google', 'OnePlus', 'Xiaomi', 'Sony'],
            'Accessories' => ['Logitech', 'Anker', 'Belkin', 'JBL', 'Sony', 'Samsung'],
            'Components' => ['Intel', 'AMD', 'Nvidia', 'Corsair', 'Kingston', 'Western Digital'],
            'Gaming' => ['Razer', 'Logitech', 'SteelSeries', 'Corsair', 'HyperX'],
        ];
        
        $models = [
            'Laptops' => ['XPS', 'Inspiron', 'Pavilion', 'ThinkPad', 'MacBook Pro', 'ZenBook', 'Predator'],
            'Smartphones' => ['iPhone', 'Galaxy S', 'Pixel', 'OnePlus', 'Redmi', 'Xperia'],
            'Accessories' => ['Mouse', 'Keyboard', 'Headphones', 'Charger', 'Cable', 'Stand', 'Hub'],
            'Components' => ['CPU', 'GPU', 'RAM', 'SSD', 'HDD', 'Motherboard', 'Power Supply'],
            'Gaming' => ['Mouse', 'Keyboard', 'Headset', 'Controller', 'Console'],
        ];
        
        $brand = $faker->randomElement($brands[$category]);
        $model = $faker->randomElement($models[$category]);
        $variant = $faker->bothify('###?');
        
        $name = "{$brand} {$model} {$variant}";
        $skuPrefix = substr($brand, 0, 2) . substr($model, 0, 3);
        $sku = strtoupper($skuPrefix . '-' . $faker->bothify('######'));
        
        // Only use fields from the fillable array
        Product::create([
            'name' => $name,
            'price' => $faker->randomFloat(2, $priceRange[0], $priceRange[1]),
            'sku' => $sku,
            'stock' => $faker->numberBetween(0, 100),
            'store_id' => $storeId
        ]);
    }
}