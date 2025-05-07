<?php
// filepath: d:\WST\inventory-management-system\app\Http\Controllers\CheckoutController.php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Tenant\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class CheckoutController extends Controller
{
    protected $databaseManager;
    
    public function __construct(TenantDatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
       
    }
    
    /**
     * Get the current store from request subdomain
     */
    protected function getCurrentStore(Request $request)
    {
        $host = $request->getHost();
        $segments = explode('.', $host);
        $subdomain = $segments[0] ?? null;
        
        return Store::where('slug', $subdomain)->firstOrFail();
    }
    
    /**
     * Show the checkout page
     */
    public function index(Request $request)
    {
        $store = $this->getCurrentStore($request);
        $this->databaseManager->switchToTenant($store);
        
        try {
            // Get categories with products for the sidebar
            $categories = DB::connection('tenant')->table('categories')
                ->where('status', true)
                ->orderBy('sort_order')
                ->get();
                
            // Get popular products for quick access
            $popularProducts = DB::connection('tenant')->table('products')
                ->select('id', 'name', 'price', 'sku', 'stock')
                ->where('status', true)
                ->where('stock', '>', 0)
                ->orderBy('sold_count', 'desc')
                ->limit(8)
                ->get();
                
            $this->databaseManager->switchToMain();
            
            return view('checkout.index', compact('store', 'categories', 'popularProducts'));
        } catch (\Exception $e) {
            Log::error("Error loading checkout page: {$e->getMessage()}");
            $this->databaseManager->switchToMain();
            return redirect()->back()->with('error', 'Error loading checkout page.');
        }
    }
    
    /**
     * Search products
     */
    public function searchProducts(Request $request)
    {
        $store = $this->getCurrentStore($request);
        $this->databaseManager->switchToTenant($store);
        
        try {
            $query = $request->get('query');
            
            $products = DB::connection('tenant')->table('products')
                ->select('id', 'name', 'price', 'sku', 'stock')
                ->where('status', true)
                ->where('stock', '>', 0)
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('sku', 'like', "%{$query}%")
                      ->orWhere('barcode', 'like', "%{$query}%");
                })
                ->limit(10)
                ->get();
                
            $this->databaseManager->switchToMain();
            
            return response()->json(['products' => $products]);
        } catch (\Exception $e) {
            Log::error("Error searching products: {$e->getMessage()}");
            $this->databaseManager->switchToMain();
            return response()->json(['error' => 'Error searching products'], 500);
        }
    }
    
    /**
     * Process the checkout
     */
    public function process(Request $request)
    {
        $store = $this->getCurrentStore($request);
        $this->databaseManager->switchToTenant($store);
        
        // Validate request
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:tenant.products,id',
            'items.*.stock' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,other',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string'
        ]);
        
        DB::connection('tenant')->beginTransaction();
        
        try {
            // Calculate totals
            $subtotal = 0;
            $itemsData = [];
            
            foreach ($validated['items'] as $item) {
                $product = DB::connection('tenant')->table('products')->find($item['id']);
                
                if (!$product) {
                    throw new \Exception("Product not found: {$item['id']}");
                }
                
                if ($product->stock < $item['stock']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }
                
                $lineTotal = $product->price * $item['stock'];
                $subtotal += $lineTotal;
                
                $itemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'stock' => $item['stock'],
                    'unit_price' => $product->price,
                    'line_total' => $lineTotal,
                    'discount' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
                
                // Update product stock
                DB::connection('tenant')->table('products')
                    ->where('id', $product->id)
                    ->decrement('stock', $item['stock']);
                
                // Increment sold count
                DB::connection('tenant')->table('products')
                    ->where('id', $product->id)
                    ->increment('sold_count', $item['stock']);
            }
            
            // Apply tax (you can customize this)
            $taxRate = 0.10; // 10% tax
            $taxAmount = $subtotal * $taxRate;
            $totalAmount = $subtotal + $taxAmount;
            
            // Create sale record
            $sale = new Sale([
                'invoice_number' => Sale::generateInvoiceNumber(),
                'user_id' => session('tenant_user_id'),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'payment_status' => 'completed',
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'notes' => $request->notes,
            ]);
            
            $sale->save();
            
            // Create sale items
            foreach ($itemsData as &$item) {
                $item['sale_id'] = $sale->id;
            }
            
            DB::connection('tenant')->table('sale_items')->insert($itemsData);
            
            DB::connection('tenant')->commit();
            $this->databaseManager->switchToMain();
            
            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully!',
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'receipt_url' => route('checkout.receipt', ['subdomain' => $store->slug, 'sale_id' => $sale->id])
            ]);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error("Error processing checkout: {$e->getMessage()}");
            $this->databaseManager->switchToMain();
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing checkout: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Show receipt
     */
    public function receipt(Request $request, $subdomain, $sale_id)
    {
        $store = $this->getCurrentStore($request);
        $this->databaseManager->switchToTenant($store);
        
        try {
            $sale = DB::connection('tenant')->table('sales')
                ->where('id', $sale_id)
                ->first();
                
            if (!$sale) {
                $this->databaseManager->switchToMain();
                return redirect()->route('checkout.index', ['subdomain' => $store->slug])
                    ->with('error', 'Sale not found.');
            }
            
            $items = DB::connection('tenant')->table('sale_items')
                ->where('sale_id', $sale_id)
                ->get();
                
            $cashier = DB::connection('tenant')->table('users')
                ->where('id', $sale->user_id)
                ->first();
                
            $this->databaseManager->switchToMain();
            
            return view('checkout.receipt', compact('store', 'sale', 'items', 'cashier'));
        } catch (\Exception $e) {
            Log::error("Error showing receipt: {$e->getMessage()}");
            $this->databaseManager->switchToMain();
            return redirect()->route('checkout.index', ['subdomain' => $store->slug])
                ->with('error', 'Error showing receipt.');
        }
    }
    
    /**
     * Show sales history
     */
    public function history(Request $request)
    {
        try {
            // Get current store
            $store = $this->getCurrentStore($request);
            
            // Ensure we're connected to the right database
            $this->databaseManager->switchToTenant($store);
            
            // Debug database connection
            $dbName = DB::connection('tenant')->getDatabaseName();
            Log::info("History - Connected to database: {$dbName} for store {$store->slug}");
            
            // Check if sales table exists
            if (!Schema::connection('tenant')->hasTable('sales')) {
                Log::error("Sales table does not exist in tenant database for store {$store->slug}");
                $this->databaseManager->switchToMain();
                return redirect()->route('checkout.index', ['subdomain' => $store->slug])
                    ->with('error', 'Sales system not properly set up.');
            }
            
            // Use a sub-query approach instead of GROUP BY to avoid SQL mode issues
            $sales = DB::connection('tenant')
                ->table('sales')
                ->select('sales.*')
                ->selectSub(function ($query) {
                    $query->from('sale_items')
                        ->selectRaw('COUNT(id)')
                        ->whereColumn('sale_id', 'sales.id');
                }, 'item_count')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            
            Log::info("History - Retrieved sales data: {$sales->total()} records");
            
            // Make sure to switch back to main database
            $this->databaseManager->switchToMain();
            
            return view('checkout.history', compact('store', 'sales'));
        } catch (\Exception $e) {
            // Log detailed error information
            Log::error("Error loading sales history: {$e->getMessage()}", [
                'exception' => get_class($e),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Make sure to switch back to main database
            $this->databaseManager->switchToMain();
            
            // Display the history page with an error message instead of redirecting
            $store = $this->getCurrentStore($request);
            return view('checkout.history', [
                'store' => $store,
                'sales' => collect([]), // Empty collection
                'error' => 'Error loading sales history: ' . $e->getMessage()
            ]);
        }
    }
}