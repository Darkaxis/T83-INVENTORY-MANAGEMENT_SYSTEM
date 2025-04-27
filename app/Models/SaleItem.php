<?php
// filepath: d:\WST\inventory-management-system\app\Models\Tenant\SaleItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class SaleItem extends Model
{
    use HasFactory;
    
    protected $connection = 'tenant';
    
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'product_sku',
        'stock',
        'unit_price',
        'line_total',
        'discount'
    ];
    
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}