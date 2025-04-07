<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Product management for a specific store
    public function index(Request $request)
    {
        $store = $request->store;
        $products = $store->products()->paginate(15);
        return view('stores.products.index', compact('store', 'products'));
    }
    
    // Other resource methods...
}