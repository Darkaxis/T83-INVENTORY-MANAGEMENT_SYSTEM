<?php
// filepath: d:\WST\inventory-management-system\app\Http\Controllers\DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use App\Services\TenantDatabaseManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TenantDashboardController extends Controller
{
    protected $databaseManager;
    
    public function __construct(TenantDatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }
    
    /**
     * Show the dashboard
     */
    public function index(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        try {
            // Get current date ranges
            // Use Carbon to get the current date and time
            
            $today = Carbon::today();
            $startOfWeek = Carbon::today()->startOfWeek();
            $startOfMonth = Carbon::today()->startOfMonth();
            
            // Get sales statistics
            $todaySales = $this->getSalesTotal($today, $today);
            $weekSales = $this->getSalesTotal($startOfWeek, $today);
            $monthSales = $this->getSalesTotal($startOfMonth, $today);
            
            // Get count of products, low stock products, and total inventory value
            $productCount = DB::connection('tenant')->table('products')->count();
            $lowStockCount = DB::connection('tenant')->table('products')
                ->where('stock', '<=', 5)
                ->where('stock', '>', 0)
                ->count();
                
            $inventoryValue = DB::connection('tenant')->table('products')
                ->selectRaw('SUM(price * stock) as total_value')
                ->first()->total_value ?? 0;
                
            // Get top 5 selling products
            $topProducts = DB::connection('tenant')->table('products')
                ->select('id', 'name', 'sold_count', 'stock', 'price')
                ->where('sold_count', '>', 0)
                ->orderByDesc('sold_count')
                ->limit(5)
                ->get();
                
            // Get recent sales
            $recentSales = DB::connection('tenant')->table('sales')
                ->join('users', 'sales.user_id', '=', 'users.id')
                ->select('sales.*', 'users.name as cashier_name')
                ->orderByDesc('sales.created_at')
                ->limit(5)
                ->get();
                
            // Get recent activities (combines sales and product updates)
            $recentActivities = $this->getRecentActivities();
            
            // Get sales data for chart (last 7 days)
            $salesChartData = $this->getSalesChartData();
            
            // Get data for category breakdown
            $categoryData = $this->getCategoryData();
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return view('dashboard.index', compact(
                'store', 
                'todaySales', 
                'weekSales', 
                'monthSales',
                'productCount',
                'lowStockCount',
                'inventoryValue',
                'topProducts',
                'recentSales',
                'recentActivities',
                'salesChartData',
                'categoryData'
            ));
            
        } catch (\Exception $e) {
            // Log the error
            Log::error("Error loading dashboard", [
                'store' => $store->slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return view('dashboard.index', [
                'store' => $store,
                'error' => 'Could not load dashboard data.'
            ]);
        }
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
     * Get total sales for a given date range with proper date formatting
     */
    private function getSalesTotal($from, $to)
    {
        // Format the dates to ensure proper SQL compatibility
        $fromDate = $from->format('Y-m-d 00:00:00');
        $toDate = $to->format('Y-m-d 23:59:59');
        
        // Log the actual query parameters for debugging
        Log::info("Getting sales total", [
            'from_date' => $fromDate,
            'to_date' => $toDate
        ]);
        
        // Try with explicit date formatting instead of Carbon objects
        $salesTotal = DB::connection('tenant')
            ->table('sales')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('total_amount');
        
        Log::info("Sales total result", ['amount' => $salesTotal]);
        
        return $salesTotal;
    }
    
    /**
     * Get recent activities combining different event types
     */
    private function getRecentActivities()
    {
        // Get sales as activities
        $sales = DB::connection('tenant')
            ->table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->select(
                'sales.id',
                'sales.created_at',
                'users.name as user_name',
                DB::raw("'sale' as type"),
                DB::raw("CONCAT('Sale #', sales.id, ' - ₱', FORMAT(sales.total_amount, 2)) as description")
            )
            ->orderByDesc('sales.created_at')
            ->limit(5);
            
        // Get product updates as activities
        $productUpdates = DB::connection('tenant')
            ->table('products')
            ->select(
                'id',
                'updated_at as created_at',
                DB::raw("'System' as user_name"),
                DB::raw("'product' as type"),
                DB::raw("CONCAT('Updated ', name) as description")
            )
            ->orderByDesc('updated_at')
            ->limit(5);
            
        // Union the queries and get most recent 10
        return $sales->union($productUpdates)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }
    
    /**
     * Get sales data for chart (last 7 days)
     */
    private function getSalesChartData()
    {
        $days = 7;
        $result = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            
            $sales = DB::connection('tenant')
                ->table('sales')
                ->whereDate('created_at', $date)
                ->sum('total_amount');
                
            $result[] = [
                'date' => $date->format('M d'),
                'amount' => $sales
            ];
        }
        
        return $result;
    }
    
    /**
     * Get data for category breakdown chart
     */
    private function getCategoryData()
    {
        return DB::connection('tenant')
            ->table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                DB::raw('IFNULL(categories.name, "Uncategorized") as category'),
                DB::raw('SUM(sale_items.stock * sale_items.unit_price) as total')
            )
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();
    }
}