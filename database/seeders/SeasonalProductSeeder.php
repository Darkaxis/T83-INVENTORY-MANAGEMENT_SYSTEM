<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;

class SeasonalProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = Store::where('status', 'active')->get();
        
        if ($stores->isEmpty()) {
            $this->command->info('No active stores found.');
            return;
        }

        // Current season determination
        $month = now()->month;
        
        if ($month >= 9 && $month <= 11) {
            $season = 'Fall';
        } elseif ($month >= 3 && $month <= 5) {
            $season = 'Spring';
        } elseif ($month >= 6 && $month <= 8) {
            $season = 'Summer';
        } else {
            $season = 'Winter';
        }
        
        $this->command->info("Creating {$season} seasonal products...");
        
        // Seasonal products data
        $seasonalProducts = $this->getSeasonalProducts($season);
        
        foreach ($stores as $store) {
            $this->command->info("Adding seasonal products to {$store->name}...");
            
            foreach ($seasonalProducts as $product) {
                Product::create([
                    'store_id' => $store->id,
                    'name' => $product['name'],
                    'sku' => $product['sku'] . '-' . strtolower($store->slug),
                    'price' => $product['price'],
                    'stock' => $product['stock']
                ]);
            }
        }
        
        $this->command->info('Seasonal products created successfully!');
    }
    
    /**
     * Get seasonal products based on the current season
     */
    private function getSeasonalProducts(string $season): array
    {
        $products = [
            'Winter' => [
                [
                    'name' => 'Winter Edition Smart Heater',
                    'description' => 'Energy-efficient smart heater with WiFi control and scheduling.',
                    'sku' => 'WIN-HEAT-001',
                    'price' => 89.99,
                    'stock' => 50,
                ],
                [
                    'name' => 'Deluxe Hot Chocolate Maker',
                    'description' => 'Create barista-quality hot chocolate at home with this premium machine.',
                    'sku' => 'WIN-CHOC-002',
                    'price' => 59.99,
                    'stock' => 35,
                ],
                [
                    'name' => 'Winter Gaming Bundle',
                    'description' => 'Limited edition winter-themed gaming accessories set.',
                    'sku' => 'WIN-GAME-003',
                    'price' => 129.99,
                    'stock' => 25,
                ],
                [
                    'name' => 'Holiday Smart Light Kit',
                    'description' => 'Programmable LED lights with smartphone control.',
                    'sku' => 'WIN-LITE-004',
                    'price' => 49.99,
                    'stock' => 75,
                ],
            ],
            'Spring' => [
                [
                    'name' => 'Spring Smart Garden Monitor',
                    'description' => 'Track soil moisture, sunlight, and temperature for optimal plant growth.',
                    'sku' => 'SPR-GRDN-001',
                    'price' => 79.99,
                    'stock' => 45,
                ],
                [
                    'name' => 'Home Fitness Starter Kit',
                    'description' => 'Complete home workout kit with smart tracking technology.',
                    'sku' => 'SPR-FIT-002',
                    'price' => 149.99,
                    'stock' => 30,
                ],
                [
                    'name' => 'Spring Cleaning Robot',
                    'description' => 'Automated cleaning robot with special spring cleaning modes.',
                    'sku' => 'SPR-CLEN-003',
                    'price' => 199.99,
                    'stock' => 20,
                ],
                [
                    'name' => 'Outdoor Bluetooth Speaker',
                    'description' => 'Weather-resistant portable speaker perfect for outdoor activities.',
                    'sku' => 'SPR-SPKR-004',
                    'price' => 69.99,
                    'stock' => 60,
                ],
            ],
            'Summer' => [
                [
                    'name' => 'Portable Solar Charger',
                    'description' => 'High-efficiency solar power bank for all your devices.',
                    'sku' => 'SUM-CHRG-001',
                    'price' => 59.99,
                    'stock' => 80,
                ],
                [
                    'name' => 'Smart Cooling Fan',
                    'description' => 'WiFi-enabled cooling fan with air quality monitoring.',
                    'sku' => 'SUM-FAN-002',
                    'price' => 99.99,
                    'stock' => 40,
                ],
                [
                    'name' => 'Waterproof Action Camera',
                    'description' => 'Capture all your summer adventures with this 4K action camera.',
                    'sku' => 'SUM-CAM-003',
                    'price' => 179.99,
                    'stock' => 25,
                ],
                [
                    'name' => 'Outdoor Smart Grill Thermometer',
                    'description' => 'Bluetooth thermometer for perfect grilling results every time.',
                    'sku' => 'SUM-GRIL-004',
                    'price' => 49.99,
                    'stock' => 65,
                ],
            ],
            'Fall' => [
                [
                    'name' => 'Smart Thermos Mug',
                    'description' => 'Keep your drinks at the perfect temperature all day with temperature control.',
                    'sku' => 'FAL-MUG-001',
                    'price' => 39.99,
                    'stock' => 70,
                ],
                [
                    'name' => 'Fall Gaming Special Edition Console',
                    'description' => 'Limited edition gaming console with special fall-themed games bundle.',
                    'sku' => 'FAL-GAME-002',
                    'price' => 349.99,
                    'stock' => 15,
                ],
                [
                    'name' => 'Leaf Detection Smart Camera',
                    'description' => 'Outdoor camera with AI that can alert you when leaves need to be cleared.',
                    'sku' => 'FAL-CAM-003',
                    'price' => 129.99,
                    'stock' => 30,
                ],
                [
                    'name' => 'Ultra Warm Smart Blanket',
                    'description' => 'Programmable heated blanket with smartphone control and sleep tracking.',
                    'sku' => 'FAL-BLNK-004',
                    'price' => 79.99,
                    'stock' => 50,
                ],
            ],
        ];
        
        return $products[$season];
    }
}