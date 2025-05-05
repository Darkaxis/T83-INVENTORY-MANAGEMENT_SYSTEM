<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use App\Services\TenantDatabaseManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;


class ReportController extends Controller
{
    protected $databaseManager;
    
    public function __construct(TenantDatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }
    
    /**
     * Show the reports dashboard
     */
    public function index(Request $request)
    {
        $store = $this->getCurrentStore($request);
        return view('reports.index', compact('store'));
    }
    
    /**
     * Sales report
     */
    public function sales(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        try {
            // Get filter parameters
            $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->subDays(30);
            $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now();
            $groupBy = $request->get('group_by', 'day'); // day, week, month, product, category, cashier
            
            // Adjust the date ranges
            $startDate = $startDate->startOfDay();
            $endDate = $endDate->endOfDay();
            
            // Base query for sales data
            $salesQuery = DB::connection('tenant')->table('sales')
                ->whereBetween('created_at', [$startDate, $endDate]);
            
            // Get total metrics
            $totalSales = $salesQuery->sum('total_amount');
            $orderCount = $salesQuery->count();
            $averageOrderValue = $orderCount > 0 ? $totalSales / $orderCount : 0;
            
            // Get data based on grouping
            $reportData = $this->getSalesReportData($startDate, $endDate, $groupBy);
            
            // Get top 5 products in the date range
            $topProducts = DB::connection('tenant')->table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->select(
                    'products.id',
                    'products.name',
                    DB::raw('SUM(sale_items.stock) as total_quantity'),
                    DB::raw('SUM(sale_items.stock * sale_items.unit_price) as total_amount')
                )
                ->whereBetween('sales.created_at', [$startDate, $endDate])
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_amount')
                ->limit(5)
                ->get();
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return view('reports.sales', compact(
                'store', 
                'startDate',
                'endDate',
                'groupBy',
                'totalSales',
                'orderCount',
                'averageOrderValue',
                'reportData',
                'topProducts'
            ));
            
        } catch (\Exception $e) {
            Log::error("Error generating sales report", [
                'store' => $store->slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return redirect()->back()->with('error', 'Error generating report: ' . $e->getMessage());
        }
    }
    
    /**
     * Export sales report as PDF
     */
    public function exportSalesPdf(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        try {
            // Get filter parameters
            $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->subDays(30);
            $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now();
            $groupBy = $request->get('group_by', 'day');
            
            // Adjust the date ranges
            $startDate = $startDate->startOfDay();
            $endDate = $endDate->endOfDay();
            
            // Store branding from the main database
            $storeBranding = [
                'accent_color' => $store->accent_color ?? '#4e73df',
                'logo_binary' => $store->logo_binary ?? null,
                'logo_mime_type' => $store->logo_mime_type ?? null
            ];
            
            // Get report data
            $reportData = $this->getSalesReportData($startDate, $endDate, $groupBy);
            
            // Get detailed order list with cashier names
            $orderDetails = $this->getOrderDetailsWithNames($startDate, $endDate, 50);
            
            // Get detailed product information
            $productDetails = $this->getProductDetails($startDate, $endDate);
            
            // Calculate product metrics
            $totalProducts = $productDetails->count();
            $totalUnits = $productDetails->sum('quantity_sold');
            
            // Get totals
            $totalSales = DB::connection('tenant')
                ->table('sales')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount');
                
            $orderCount = DB::connection('tenant')
                ->table('sales')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
                
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            // Generate PDF
            $pdf = PDF::loadView('reports.pdf.sales', [
                'store' => $store,
                'storeBranding' => $storeBranding,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'groupBy' => $groupBy,
                'reportData' => $reportData,
                'totalSales' => $totalSales,
                'orderCount' => $orderCount,
                'orderDetails' => $orderDetails,
                'productDetails' => $productDetails,
                'totalProducts' => $totalProducts,
                'totalUnits' => $totalUnits
            ]);
            
            return $pdf->download('sales-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf');
            
        } catch (\Exception $e) {
            Log::error("Error exporting sales report to PDF", [
                'store' => $store->slug,
                'error' => $e->getMessage()
            ]);
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return redirect()->back()->with('error', 'Error exporting report: ' . $e->getMessage());
        }
    }
    
    /**
     * Export sales report as CSV
     */
    public function exportSalesCsv(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        try {
            // Get filter parameters
            $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->subDays(30);
            $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now();
            $groupBy = $request->get('group_by', 'day');
            
            // Get report data
            $reportData = $this->getSalesReportData($startDate, $endDate, $groupBy);
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            // Create CSV content
            $csvContent = "Date/Period,Sales,Orders\n";
            
            foreach ($reportData as $row) {
                $csvContent .= "{$row->label},{$row->total_amount},{$row->order_count}\n";
            }
            
            // Create response
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="sales-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.csv"',
            ];
            
            return Response::make($csvContent, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error("Error exporting sales report to CSV", [
                'store' => $store->slug,
                'error' => $e->getMessage()
            ]);
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return redirect()->back()->with('error', 'Error exporting report: ' . $e->getMessage());
        }
    }
    
    /**
     * Export product sales as CSV
     */
    public function exportProductsCsv(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Switch to tenant database
        $this->databaseManager->switchToTenant($store);
        
        try {
            // Get filter parameters
            $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->subDays(30);
            $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now();
            
            // Get product details
            $productDetails = $this->getProductDetails($startDate, $endDate);
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            // Create CSV content
            $csvContent = "Product,SKU,Category,Quantity Sold,Average Price,Total Amount\n";
            
            foreach ($productDetails as $product) {
                $csvContent .= "\"{$product->product_name}\",\"{$product->sku}\",\"{$product->category_name}\",{$product->quantity_sold},{$product->average_price},{$product->total_amount}\n";
            }
            
            // Create response
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="product-sales-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.csv"',
            ];
            
            return Response::make($csvContent, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error("Error exporting product sales to CSV", [
                'store' => $store->slug,
                'error' => $e->getMessage()
            ]);
            
            // Switch back to main database
            $this->databaseManager->switchToMain();
            
            return redirect()->back()->with('error', 'Error exporting report: ' . $e->getMessage());
        }
    }
    
    /**
     * Get the current store from request
     */
    protected function getCurrentStore(Request $request)
    {
        $subdomain = $request->route('subdomain');
        return Store::where('slug', $subdomain)->firstOrFail();
    }
    
    /**
     * Get sales report data based on grouping
     */
    private function getSalesReportData($startDate, $endDate, $groupBy)
    {
        switch ($groupBy) {
            case 'day':
                return $this->getSalesByDay($startDate, $endDate);
            
            case 'week':
                return $this->getSalesByWeek($startDate, $endDate);
            
            case 'month':
                return $this->getSalesByMonth($startDate, $endDate);
            
            case 'product':
                return $this->getSalesByProduct($startDate, $endDate);
            
            case 'category':
                return $this->getSalesByCategory($startDate, $endDate);
            
            case 'cashier':
                return $this->getSalesByCashier($startDate, $endDate);
            
            default:
                return $this->getSalesByDay($startDate, $endDate);
        }
    }
    
    /**
     * Get sales grouped by day
     */
    private function getSalesByDay($startDate, $endDate)
    {
        return DB::connection('tenant')->table('sales')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('COUNT(*) as order_count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->label = Carbon::parse($item->date)->format('M d, Y');
                return $item;
            });
    }
    
    /**
     * Get sales grouped by week
     */
    private function getSalesByWeek($startDate, $endDate)
    {
        return DB::connection('tenant')->table('sales')
            ->select(
                DB::raw('YEARWEEK(created_at, 1) as yearweek'),
                DB::raw('MIN(DATE(created_at)) as week_start'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('COUNT(*) as order_count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('yearweek')
            ->orderBy('yearweek')
            ->get()
            ->map(function ($item) {
                $weekStart = Carbon::parse($item->week_start);
                $weekEnd = (clone $weekStart)->addDays(6);
                $item->label = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d, Y');
                return $item;
            });
    }
    
    /**
     * Get sales grouped by month
     */
    private function getSalesByMonth($startDate, $endDate)
    {
        return DB::connection('tenant')->table('sales')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as yearmonth'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('COUNT(*) as order_count')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('yearmonth')
            ->orderBy('yearmonth')
            ->get()
            ->map(function ($item) {
                $item->label = Carbon::parse($item->yearmonth . '-01')->format('F Y');
                return $item;
            });
    }
    
    /**
     * Get sales grouped by product
     */
    private function getSalesByProduct($startDate, $endDate)
    {
        return DB::connection('tenant')->table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(sale_items.stock * sale_items.unit_price) as total_amount'),
                DB::raw('SUM(sale_items.stock) as total_quantity')
            )
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_amount')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                $item->label = $item->name;
                $item->order_count = $item->total_quantity;
                return $item;
            });
    }
    
    /**
     * Get sales grouped by category
     */
    private function getSalesByCategory($startDate, $endDate)
    {
        return DB::connection('tenant')->table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                DB::raw('IFNULL(categories.name, "Uncategorized") as category_name'),
                DB::raw('SUM(sale_items.stock * sale_items.unit_price) as total_amount'),
                DB::raw('COUNT(DISTINCT sales.id) as order_count')
            )
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->groupBy('category_name')
            ->orderByDesc('total_amount')
            ->get()
            ->map(function ($item) {
                $item->label = $item->category_name;
                return $item;
            });
    }
    
    /**
     * Get sales grouped by cashier
     */
    private function getSalesByCashier($startDate, $endDate)
    {
        return DB::connection('tenant')->table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                DB::raw('SUM(sales.total_amount) as total_amount'),
                DB::raw('COUNT(*) as order_count')
            )
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_amount')
            ->get()
            ->map(function ($item) {
                $item->label = $item->name;
                return $item;
            });
    }
    
    /**
     * Get detailed order list
     */
    private function getOrderDetails($startDate, $endDate, $limit)
    {
        return DB::connection('tenant')->table('sales')
            ->select(
                'id',
                'created_at',
                'total_amount',
                'user_id'
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get detailed order list with cashier names
     */
    private function getOrderDetailsWithNames($startDate, $endDate, $limit)
    {
        return DB::connection('tenant')->table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->select(
                'sales.id as order_id',
                'sales.created_at as order_date',
                'sales.total_amount',
                'users.name as cashier_name',
                DB::raw('(SELECT COUNT(*) FROM sale_items WHERE sale_items.sale_id = sales.id) as item_count')
            )
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->orderByDesc('sales.created_at')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $item->order_date_formatted = Carbon::parse($item->order_date)->format('M d, Y h:i A');
                return $item;
            });
    }
    
    /**
     * Get detailed product sales data for the report
     */
    private function getProductDetails($startDate, $endDate)
    {
        return DB::connection('tenant')->table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku',
                DB::raw('IFNULL(categories.name, "Uncategorized") as category_name'),
                DB::raw('SUM(sale_items.stock) as quantity_sold'),
                DB::raw('AVG(sale_items.unit_price) as average_price'),
                DB::raw('SUM(sale_items.stock * sale_items.unit_price) as total_amount'),
                DB::raw('MIN(sale_items.unit_price) as min_price'),
                DB::raw('MAX(sale_items.unit_price) as max_price')
            )
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->groupBy('products.id', 'products.name', 'products.sku', 'category_name')
            ->orderByDesc('total_amount')
            ->get();
    }
}