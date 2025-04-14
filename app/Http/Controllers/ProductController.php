<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Services\TenantDatabaseManager;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    protected $databaseManager;

    public function __construct(TenantDatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    public function index(Request $request)
    {
        $store = $this->getCurrentStore($request);
        $products = Product::where('store_id', $store->id)->get();

        return view('products.index', compact('products', 'store'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(Request $request)
    {
        $store = $this->getCurrentStore($request);

        return view('products.create', compact('store'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $store = $this->getCurrentStore($request);

        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        Product::create([
            'name' => $request->name,
            'sku' => $request->sku,
            'price' => $request->price,
            'stock' => $request->stock,
            'store_id' => $store->id,
        ]);

        return redirect()->route('products.index', ['subdomain' => $store->slug])
        ->with('success', 'Product created successfully.');
    }

    public function edit(Request $request, $subdomain, $product_id)
    {
        // Get the current store
        $store = \App\Models\Store::where('slug', $subdomain)->firstOrFail();
        
        // Switch to tenant database if using multiple databases
        if (method_exists($this, 'switchToTenant')) {
            $this->databaseManager->switchToTenant($store);
        }
        
        // Manually fetch the product by ID
        $product = Product::findOrFail($product_id);
        
        // Switch back to main database if needed
        if (method_exists($this, 'switchToMain')) {
            $this->databaseManager->switchToMain();
        }
        
        return view('products.edit', compact('product', 'store'));
    }

    /**
     * Update the specified product in storage.
     */
   

/**
 * Display the specified product.
 */
public function show(Request $request, $subdomain, $product_id)
{
    $store = \App\Models\Store::where('slug', $subdomain)->firstOrFail();
    
    if (method_exists($this, 'switchToTenant')) {
        $this->databaseManager->switchToTenant($store);
    }
    
    $product = Product::findOrFail($product_id);
    
    if (method_exists($this, 'switchToMain')) {
        $this->databaseManager->switchToMain();
    }
    
    return view('products.show', compact('product', 'store', 'product_id'));
}

/**
 * Update the specified product in storage.
 */
public function update(Request $request, $subdomain, $product_id)
{
    $store = \App\Models\Store::where('slug', $subdomain)->firstOrFail();
    
    if (method_exists($this, 'switchToTenant')) {
        $this->databaseManager->switchToTenant($store);
    }
    
    $product = Product::findOrFail($product_id);
    
    $request->validate([
        'name' => 'required|string|max:255',
        'sku' => 'required|string|max:100',
        'price' => 'required|numeric',
        'stock' => 'required|integer',
    ]);
    
    $product->update($request->all());
    
    if (method_exists($this, 'switchToMain')) {
        $this->databaseManager->switchToMain();
    }
    
    return redirect()->route('products.index', ['subdomain' => $store->slug])
        ->with('success', 'Product updated successfully.');
}

/**
 * Remove the specified product from storage.
 */
public function destroy(Request $request, $subdomain, $product_id)
{
    $store = \App\Models\Store::where('slug', $subdomain)->firstOrFail();
    
    if (method_exists($this, 'switchToTenant')) {
        $this->databaseManager->switchToTenant($store);
    }
    
    $product = Product::findOrFail($product_id);
    $product->delete();
    
    if (method_exists($this, 'switchToMain')) {
        $this->databaseManager->switchToMain();
    }
    
    return redirect()->route('products.index', ['subdomain' => $store->slug])
        ->with('success', 'Product deleted successfully.');
}

    /**
     * Get the current store based on the subdomain.
     */
    private function getCurrentStore(Request $request)
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];

        return Store::where('slug', $subdomain)->firstOrFail();
    }
}