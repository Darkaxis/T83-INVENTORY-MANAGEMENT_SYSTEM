<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\Validator;

class StoreSettingsController extends Controller
{
    protected $databaseManager;
    
    public function __construct(TenantDatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
        $this->middleware('tenant');
        $this->middleware('tenant.manager');
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
     * Show the settings page
     */
    public function index(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Define available accent colors with their labels
        $accentColors = [
            '#4e73df' => 'Blue (Default)',
            '#6610f2' => 'Indigo',
            '#6f42c1' => 'Purple',
            '#e83e8c' => 'Pink',
            '#e74a3b' => 'Red',
            '#fd7e14' => 'Orange', 
            '#f6c23e' => 'Yellow',
            '#1cc88a' => 'Green',
            '#20c9a6' => 'Teal',
            '#36b9cc' => 'Cyan',
        ];
        
        
        return view('settings.index', compact('store', 'accentColors'));
    }

    /**
     * Update the store settings
     */
    public function update(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'accent_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/', // Validate hex color
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $updateData = [
            'name' => $request->name,
            'accent_color' => $request->accent_color,
        ];
        
        // Process logo upload if present
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $file = $request->file('logo');
            // Read file contents as binary data
            $updateData['logo_binary'] = file_get_contents($file->getRealPath());
            $updateData['logo_mime_type'] = $file->getMimeType();
        }
        
        try {
            // Update store details
            $store->update($updateData);
            
            return redirect()->back()->with('success', 'Store settings updated successfully!');
        } catch (\Exception $e) {
            Log::error("Error updating store settings: {$e->getMessage()}");
            return redirect()->back()->with('error', 'There was a problem updating store settings.');
        }
    }

    /**
     * Check for system updates
     */
    public function checkUpdates(Request $request)
    {
        $store = $this->getCurrentStore($request);
        
        // Only allow Pro and above users to access this feature
        if (!$store->subscription || !in_array($store->subscription->pricing_tier->name, ['Professional', 'Unlimited'])) {
            return redirect()->route('settings.index', ['subdomain' => $store->slug])
                ->with('error', 'System updates are available only for Professional and Unlimited plans.');
        }
        
        try {
            // Run update check
            Artisan::call('app:check-for-updates');
            
            return redirect()->route('settings.index', ['subdomain' => $store->slug])
                ->with('success', 'System update check completed.');
        } catch (\Exception $e) {
            Log::error('Update check failed: ' . $e->getMessage());
            
            return redirect()->route('settings.index', ['subdomain' => $store->slug])
                ->with('error', 'Error checking for updates: ' . $e->getMessage());
        }
    }

    /**
     * Download an update
     */
    public function downloadUpdate(Request $request, $id)
    {
        $store = $this->getCurrentStore($request);
        
        // Only allow Pro and above users to access this feature
        if (!$store->subscription || !in_array($store->subscription->pricing_tier->name, ['Professional', 'Unlimited'])) {
            return redirect()->route('settings.index', ['subdomain' => $store->slug])
                ->with('error', 'System updates are available only for Professional and Unlimited plans.');
        }
        
        try {
            $update = \App\Models\SystemUpdate::findOrFail($id);
            
            // Start update download
            Artisan::call('update:download', [
                'version' => $update->version
            ]);
            
            return redirect()->route('settings.index', ['subdomain' => $store->slug])
                ->with('success', 'Update download initiated. This may take a few minutes.');
        } catch (\Exception $e) {
            Log::error('Update download failed: ' . $e->getMessage());
            
            return redirect()->route('settings.index', ['subdomain' => $store->slug])
                ->with('error', 'Error downloading update: ' . $e->getMessage());
        }
    }

    /**
     * Install an update
     */
    public function installUpdate(Request $request, $id)
    {
        $store = $this->getCurrentStore($request);
        
        // Only allow Pro and above users to access this feature
        if (!$store->subscription || !in_array($store->subscription->pricing_tier->name, ['Professional', 'Unlimited'])) {
            return redirect()->route('settings.index', ['subdomain' => $store->slug])
                ->with('error', 'System updates are available only for Professional and Unlimited plans.');
        }
        
        try {
            $update = \App\Models\SystemUpdate::findOrFail($id);
            
            // Start update installation
            Artisan::call('update:install', [
                'version' => $update->version
            ]);
            
            return redirect()->route('settings.index', ['subdomain' => $store->slug])
                ->with('success', 'Update installation initiated. The system will be updated shortly.');
        } catch (\Exception $e) {
            Log::error('Update installation failed: ' . $e->getMessage());
            
            return redirect()->route('settings.index', ['subdomain' => $store->slug])
                ->with('error', 'Error installing update: ' . $e->getMessage());
        }
    }
}