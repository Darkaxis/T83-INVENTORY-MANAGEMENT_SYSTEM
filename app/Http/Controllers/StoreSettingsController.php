<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            'blue' => 'Blue (Default)',
            'indigo' => 'Indigo',
            'purple' => 'Purple',
            'pink' => 'Pink',
            'red' => 'Red',
            'orange' => 'Orange',
            'yellow' => 'Yellow',
            'green' => 'Green',
            'teal' => 'Teal',
            'cyan' => 'Cyan',
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
}