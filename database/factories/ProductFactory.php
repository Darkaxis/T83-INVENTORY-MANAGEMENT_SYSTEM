<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $productTypes = ['Laptop', 'Smartphone', 'Tablet', 'Desktop', 'Monitor', 'Keyboard', 'Mouse', 
            'Printer', 'Headphones', 'Camera', 'Speaker', 'Microphone', 'Hard Drive', 'SSD', 'USB Drive'];
            
        $brands = ['Apple', 'Samsung', 'Dell', 'HP', 'Lenovo', 'Asus', 'Acer', 'Microsoft', 'LG', 'Sony',
            'Logitech', 'Razer', 'Corsair'];
        
        $brand = $this->faker->randomElement($brands);
        $type = $this->faker->randomElement($productTypes);
        $model = $this->faker->bothify('##??-###');
        $name = "{$brand} {$type} {$model}";
        
        // Generate SKU: Brand initials + Type first 3 chars + random alphanumeric
        $skuPrefix = substr($brand, 0, 2) . substr($type, 0, 3);
        $sku = strtoupper($skuPrefix . '-' . $this->faker->bothify('######'));
        
        return [
            'name' => $name,
            'sku' => $sku,
            'price' => $this->faker->randomFloat(2, 9.99, 2999.99),
            'stock' => $this->faker->numberBetween(0, 100),
            // store_id will be set when the factory is used
        ];
    }
    
    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (Product $product) {
            // Additional configurations after making the product
        })->afterCreating(function (Product $product) {
            // Additional configurations after creating the product
        });
    }
}