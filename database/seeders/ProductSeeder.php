<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Store;
use App\Models\PricingTier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = Store::where('status', 'active')->get();
        
        if ($stores->isEmpty()) {
            $this->command->info('No active stores found. Please create stores before seeding products.');
            return;
        }
        
        $this->command->info('Creating products for ' . $stores->count() . ' stores...');
        
        foreach ($stores as $store) {
            // Determine how many products to create based on pricing tier
            $maxProducts = $this->getProductLimit($store);
            $existingCount = Product::where('store_id', $store->id)->count();
            $toCreate = max(0, $maxProducts - $existingCount);
            
            if ($toCreate <= 0) {
                $this->command->info("Store '{$store->name}' has reached its product limit ({$maxProducts}). Skipping.");
                continue;
            }
            
            $this->command->info("Creating {$toCreate} products for store '{$store->name}'...");
            
            // Create products for this store
            Product::factory()->count($toCreate)
                ->make()
                ->each(function ($product) use ($store) {
                    $product->store_id = $store->id;
                    $product->save();
                });
        }
        
        $this->command->info('Product seeding completed!');
    }
    
    /**
     * Get the product limit for a store based on its pricing tier.
     */
    private function getProductLimit(Store $store): int
    {
        // Default number of products if there's no tier or if tier has no limit
        $defaultLimit = 20;
        
        if (!$store->pricing_tier_id) {
            return $defaultLimit;
        }
        
        $pricingTier = PricingTier::find($store->pricing_tier_id);
        
        if (!$pricingTier) {
            return $defaultLimit;
        }
        
        // If tier has unlimited products, return a reasonable default for seeding
        if ($pricingTier->product_limit === null || $pricingTier->product_limit === -1) {
            return match($pricingTier->name) {
                'Free' => 10,
                'Starter' => 25,
                'Professional' => 50,
                'Enterprise' => 100,
                'Unlimited' => 200,
                default => $defaultLimit,
            };
        }
        
        return $pricingTier->product_limit;
    }
}