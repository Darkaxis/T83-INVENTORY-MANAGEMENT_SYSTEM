<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use App\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{
    /**
     * The database manager instance.
     */
    protected $databaseManager;

    /**
     * Create a new controller instance.
     */
    public function __construct(TenantDatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        // Debug the current connection
        $currentDb = DB::connection()->getDatabaseName();
        Log::info("Current database in products index", ['database' => $currentDb]);
        
        try {
            // Add pagination with 15 items per page
            $products = DB::connection('tenant')->table('products')->paginate(15);
            
        } catch (\Exception $e) {
            Log::error("Error fetching products", ['error' => $e->getMessage()]);
            $products = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        }
        
        // Switch back to main database
        $this->databaseManager->switchToMain();
        
        // Add a null product_id since index view shows multiple products
        $product_id = null;
        
        return view('products.index', compact('products', 'store', 'product_id'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(Request $request)
    {
        $store = $this->getCurrentStore($request);
        if (!$store->canAddProducts()) {
            
            return redirect()->route('products.index', ['subdomain' => $store->slug])
                ->with('error', 'You have reached the product limit for your current plan. Please upgrade to add more products.');
        }
        return view('products.create', compact('store'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Validate inputs first
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:tenant.products,sku',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        // Get limit from pricing tier and current count in a transaction to prevent race conditions
        try {
            // Switch to tenant database
            $this->databaseManager->switchToTenant($store);
            
            // Double-check product limit in a transaction to handle race conditions
            DB::connection('tenant')->beginTransaction();
            
            // Get the current product count directly from tenant database
            $currentCount = DB::connection('tenant')->table('products')->count();
            
            // Get limit from pricing tier
            $maxProducts = $store->pricingTier ? $store->pricingTier->product_limit : 20;
            $unlimited = $maxProducts === null || $maxProducts === -1;
            
            // Abort if limit is reached
            if (!$unlimited && $currentCount >= $maxProducts) {
                DB::connection('tenant')->rollBack();
                $this->databaseManager->switchToMain();
                
                // Get upgrade URL for current plan
                $upgradePath = "/{$store->slug}/subscription";
                
                // Add flash data with additional details for frontend display
                session()->flash('limit_data', [
                    'current' => $currentCount,
                    'limit' => $maxProducts,
                    'type' => 'product',
                    'upgrade_url' => $upgradePath
                ]);
                
                return redirect()->route('products.index', ['subdomain' => $store->slug])
                    ->with('error', 'Product limit reached. Please upgrade your plan to add more products.');
            }
            
            // Safe to add the product now
            DB::connection('tenant')->table('products')->insert([
                'name' => $request->name,
                'sku' => $request->sku,
                'price' => $request->price,
                'stock' => $request->stock, 
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Commit changes
            DB::connection('tenant')->commit();
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return redirect()->route('products.index', ['subdomain' => $store->slug])
                ->with('success', 'Product created successfully.');
                
        } catch (\Exception $e) {
            // Roll back any changes
            DB::connection('tenant')->rollBack();
            $this->databaseManager->switchToMain();
            
            Log::error("Error creating product", ['error' => $e->getMessage()]);
            
            return redirect()->back()
                ->with('error', 'An error occurred while creating the product.')
                ->withInput();
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Request $request, $subdomain, $product_id)
    {
        $store = Store::where('slug', $subdomain)->firstOrFail();
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        $product = Product::findOrFail($product_id);
    
        // Switch back to main database
        $this->databaseManager->switchToMain();
        
        return view('products.show', compact('product', 'store', 'product_id'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Request $request, $subdomain, $product_id)
    {
        $store = Store::where('slug', $subdomain)->firstOrFail();
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        $product = Product::findOrFail($product_id);
        
        // Switch back to main database
        $this->databaseManager->switchToMain();
        
        return view('products.edit', compact('product', 'store', 'product_id'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, $subdomain, $product_id)
    {
        $store = Store::where('slug', $subdomain)->firstOrFail();
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        // Check if product exists
        $product = DB::connection('tenant')->table('products')->find($product_id);
        if (!$product) {
            $this->databaseManager->switchToMain();
            abort(404);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);
        
        // Update using query builder
        DB::connection('tenant')->table('products')
            ->where('id', $product_id)
            ->update([
                'name' => $request->name,
                'sku' => $request->sku,
                'price' => $request->price,
                'stock' => $request->stock,
                'updated_at' => now()
            ]);
        
        // Switch back to main database
        $this->databaseManager->switchToMain();
        
        return redirect()->route('products.index', ['subdomain' => $store->slug])
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Request $request, $subdomain, $product_id)
    {
        $store = Store::where('slug', $subdomain)->firstOrFail();
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        // Delete using query builder
        DB::connection('tenant')->table('products')->where('id', $product_id)->delete();
        
        // Switch back to main database
        $this->databaseManager->switchToMain();
        
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
