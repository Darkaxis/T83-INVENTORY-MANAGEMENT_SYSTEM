<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller


{
    //add middleware to check if user is admin
    public function __construct()
    {
        
        $this->middleware('admin'); // Ensure only admin can access this controller
    }
    public function index()
    {

        // Get counts for the dashboard
        $storesCount = Store::count();
        $activeStoresCount = Store::where('status', 'active')->count();
        $usersCount = User::count();
        $productsCount = Product::count();
        
        // Get recent stores
        $stores = Store::withCount(['users', 'products'])
                      ->orderBy('created_at', 'desc')
                      ->limit(5)
                      ->get();
        
        // Get recent activity
        $lastWeek = Carbon::now()->subDays(7);
        $recentStoresCount = Store::where('created_at', '>=', $lastWeek)->count();
        $recentUsersCount = User::where('created_at', '>=', $lastWeek)->count();
        
        return view('admin.dashboard', compact(
            'stores',            
            'storesCount',
            'activeStoresCount',
            'usersCount',
            'productsCount',
            'recentStoresCount',
            'recentUsersCount'
        ));
    }
}