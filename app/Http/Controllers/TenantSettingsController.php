<?php
// filepath: d:\WST\inventory-management-system\app\Http\Controllers\Admin\TenantSettingsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\Log;

class TenantSettingsController extends Controller
{
    /**
     * Display tenant system settings
     */
    public function index()
    {
        $activeStores = Store::where('status', 'active')->count();
        $totalStores = Store::count();
        $databaseIssues = Store::whereHas('database_connected', function($query) {
            $query->where('database_connected', false);
        })->count();
        
        return view('admin.settings.tenant', compact('activeStores', 'totalStores', 'databaseIssues'));
    }
    
    /**
     * Update tenant system settings
     */
    public function update(Request $request)
    {
        // Validate and save settings
        // For example, default tenant database settings
        
        return back()->with('success', 'Settings updated successfully');
    }
}