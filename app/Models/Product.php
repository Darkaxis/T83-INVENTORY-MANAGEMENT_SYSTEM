<?php
// filepath: d:\WST\inventory-management-system\app\Models\Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'store_id',
        'name',
        'sku',
        'description',
        'price',
        'stock',
        'min_stock',
        'status',
        'category_id'
    ];
    
    /**
     * Get the store that owns the product.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    
    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}