<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price','sku' ,'stock', 'store_id'];

    /**
     * Get the store that owns the product.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}