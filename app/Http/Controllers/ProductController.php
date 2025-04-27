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
        
        try {
            $query = DB::connection('tenant')->table('products');
            
            // Handle search
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('sku', 'like', "%{$searchTerm}%")
                      ->orWhere('barcode', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }
            
            // Get products with pagination
            $products = $query->orderBy('created_at', 'desc')
                            ->paginate(15)
                            ->withQueryString();
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return view('products.index', compact('store', 'products'));
        } catch (\Exception $e) {
            // Log error and handle exception
            Log::error("Error fetching products", [
                'store' => $store->slug,
                'error' => $e->getMessage()
            ]);
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return view('products.index', [
                'store' => $store,
                'products' => collect([]),
                'error' => 'There was a problem loading the products.'
            ]);
        }
    }

    /**
     * AJAX search for products
     */
    public function search(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        try {
            // Validate search term
            $searchTerm = $request->query('q', '');
            if (strlen($searchTerm) < 2) {
                return response()->json(['products' => []]);
            }
            
            // Search products
            $products = DB::connection('tenant')->table('products')
                ->select('id', 'name', 'sku', 'price', 'stock', 'barcode')
                ->where(function($query) use ($searchTerm) {
                    $query->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('sku', 'like', "%{$searchTerm}%")
                        ->orWhere('barcode', 'like', "%{$searchTerm}%");
                })
                ->limit(10)
                ->get();
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return response()->json([
                'products' => $products,
                'count' => $products->count()
            ]);
        } catch (\Exception $e) {
            // Log error
            Log::error("Error searching products", [
                'store' => $store->slug,
                'query' => $searchTerm,
                'error' => $e->getMessage()
            ]);
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return response()->json(['error' => 'Error searching products'], 500);
        }
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Check product limit before showing form
        if (!$store->canAddProducts()) {
            return redirect()->route('products.index', ['subdomain' => $store->slug])
                ->with('error', 'You have reached the product limit for your current plan. Please upgrade to add more products.');
        }
        
        // Connect to tenant database to get categories
        $this->databaseManager->switchToTenant($store);
        
        // Get categories for dropdown
        $categories = DB::connection('tenant')->table('categories')
                        ->where('status', true)
                        ->orderBy('sort_order')
                        ->get();
        
        $this->databaseManager->switchToMain();
        
        return view('products.create', compact('store', 'categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Connect to tenant database BEFORE validation
        $this->databaseManager->switchToTenant($store);
        
        try {
            
            // Validate data after connecting to tenant database
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'required|string|max:255|unique:products,sku', // No tenant. prefix needed now
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'barcode' => 'nullable|string|max:100',
                'category_id' => 'nullable', // No tenant. prefix needed now
                'status' => 'sometimes|boolean',
            ]);
            
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
                
                return redirect()->route('products.index', ['subdomain' => $store->slug])
                    ->with('error', 'Product limit reached. Please upgrade your plan to add more products.');
            }
            
           
            // Prepare product data
            $productData = [
                'name' => $validatedData['name'],
                'sku' => $validatedData['sku'],
                'price' => $validatedData['price'],
                'stock' => $validatedData['stock'],
                'description' => $validatedData['description'] ?? null,
                'barcode' => $validatedData['barcode'] ?? null,
                'category_id' => !empty($validatedData['category_id']) ? $validatedData['category_id'] : null,
                'status' => isset($validatedData['status']) ? (bool)$validatedData['status'] : true,
                'sold_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Insert the product
            $productId = DB::connection('tenant')->table('products')->insertGetId($productData);
            
            // Commit changes
            DB::connection('tenant')->commit();
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return redirect()->route('products.show', [
                    'subdomain' => $store->slug, 
                    'product_id' => $productId
                ])
                ->with('success', 'Product created successfully.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            // For validation errors, switch back to main database
            $this->databaseManager->switchToMain();
            
            // Re-throw the validation exception
            throw $e;
            
        } catch (\Exception $e) {
            // Roll back any changes
            if (DB::connection('tenant')->transactionLevel() > 0) {
                DB::connection('tenant')->rollBack();
            }
            
            $this->databaseManager->switchToMain();
            
            Log::error("Error creating product", [
                'store' => $store->slug,
                'data' => $request->except(['_token']),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'An error occurred while creating the product: ' . $e->getMessage())
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
        
        try {
            // Use query builder instead of Eloquent model to match your other methods
            $product = DB::connection('tenant')->table('products')->find($product_id);
            
            if (!$product) {
                $this->databaseManager->switchToMain();
                abort(404, 'Product not found');
            }
            
            // Convert to object for view consistency
            $product = (object) $product;
            
            // Convert string dates to Carbon instances
            if (property_exists($product, 'created_at')) {
                $product->created_at = \Carbon\Carbon::parse($product->created_at);
            }
            
            if (property_exists($product, 'updated_at')) {
                $product->updated_at = \Carbon\Carbon::parse($product->updated_at);
            }
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return view('products.show', compact('product', 'store', 'product_id'));
        } catch (\Exception $e) {
            // Log the error
            Log::error("Error finding product", [
                'store' => $store->slug, 
                'product_id' => $product_id,
                'error' => $e->getMessage()
            ]);
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            // Return with error
            return redirect()->route('products.index', ['subdomain' => $store->slug])
                ->with('error', 'There was a problem loading the product.');
        }
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Request $request, $subdomain, $product_id)
    {
        $store = Store::where('slug', $subdomain)->firstOrFail();
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        try {
            // Use query builder instead of Eloquent model to match your other methods
            $product = DB::connection('tenant')->table('products')->find($product_id);
            
            if (!$product) {
                $this->databaseManager->switchToMain();
                abort(404, 'Product not found');
            }
            
            // Convert to object for view consistency
            $product = (object) $product;
            
            // Get categories for dropdown - ADDED THIS
            $categories = DB::connection('tenant')->table('categories')
                            ->where('status', true)
                            ->orderBy('sort_order')
                            ->get();
            
            // Convert string dates to Carbon instances
            if (property_exists($product, 'created_at')) {
                $product->created_at = \Carbon\Carbon::parse($product->created_at);
            }
            
            if (property_exists($product, 'updated_at')) {
                $product->updated_at = \Carbon\Carbon::parse($product->updated_at);
            }
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return view('products.edit', compact('product', 'store', 'product_id', 'categories'));
        } catch (\Exception $e) {
            // Log the error
            Log::error("Error finding product for edit", [
                'store' => $store->slug, 
                'product_id' => $product_id,
                'error' => $e->getMessage()
            ]);
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            // Return with error
            return redirect()->route('products.index', ['subdomain' => $store->slug])
                ->with('error', 'There was a problem loading the product for editing.');
        }
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
        
        // Updated validation rules to include category_id and other fields
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'barcode' => 'required|string|max:100',
            'category_id' => 'nullable|integer|exists:tenant.categories,id', // Added validation for category
            'status' => 'sometimes|boolean',
        ]);
        
        try {
            // Prepare full product data with all fields
            $productData = [
                'name' => $validatedData['name'],
                'sku' => $validatedData['sku'],
                'price' => $validatedData['price'],
                'stock' => $validatedData['stock'],
                'description' => $validatedData['description'] ?? null,
                'barcode' => $validatedData['barcode'] ?? null,
                'category_id' => $validatedData['category_id'] ?? null, // Added category_id
                'status' => isset($validatedData['status']) ? (bool)$validatedData['status'] : true,
                'updated_at' => now()
            ];
            
            // Update using query builder
            DB::connection('tenant')->table('products')
                ->where('id', $product_id)
                ->update($productData);
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return redirect()->route('products.show', ['subdomain' => $store->slug, 'product_id' => $product_id])
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            // Log the error
            Log::error("Error updating product", [
                'store' => $store->slug,
                'product_id' => $product_id,
                'data' => $request->except(['_token']),
                'error' => $e->getMessage()
            ]);
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return redirect()->back()
                ->with('error', 'An error occurred while updating the product: ' . $e->getMessage())
                ->withInput();
        }
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
