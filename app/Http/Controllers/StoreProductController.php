<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\Request;

class StoreProductController extends Controller
{
    /**
     * Display a listing of products for a specific store (admin view).
     */
    public function adminIndex(Store $store)
    {
        $products = $store->products()->paginate(15);
        return view('admin.stores.products.index', compact('store', 'products'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // This is for store subdomain access
        $store = request()->store;
        $products = $store->products()->paginate(15);
        return view('stores.products.index', compact('store', 'products'));
    }

    // Other resource methods...
}