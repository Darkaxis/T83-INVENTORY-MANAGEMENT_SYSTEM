<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DeploymentRing;
use App\Models\Store;
use App\Models\SystemUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DeploymentRingController extends Controller
{
    public function index()
    {
        $rings = DeploymentRing::orderBy('order')->get();
        $updates = SystemUpdate::orderByDesc('created_at')->get();
        
        return view('admin.deployment-rings.index', compact('rings', 'updates'));
    }
    
    public function moveStore(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'ring_id' => 'required|exists:deployment_rings,id',
        ]);
        
        $store = Store::findOrFail($request->store_id);
        $store->deployment_ring_id = $request->ring_id;
        $store->save();
        
        return redirect()->back()->with('success', 'Store moved to new deployment ring');
    }
    
    public function deployUpdate(Request $request, $id)
    {
        $request->validate([
            'ring_id' => 'required|exists:deployment_rings,id',
        ]);
        
        $update = SystemUpdate::findOrFail($id);
        $ring = DeploymentRing::findOrFail($request->ring_id);
        
        // Update ring version
        $ring->version = $update->version;
        $ring->save();
        
        // Update all stores in this ring
        foreach ($ring->stores as $store) {
            // Queue update jobs for each store
            Artisan::queue('store:update', [
                'store' => $store->id,
                'version' => $update->version
            ]);
        }
        
        return redirect()->back()->with('success', 'Update deployment initiated for ring: ' . $ring->name);
    }
    
    /**
     * Store a new deployment ring
     */
    public function storeRing(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:1',
            'auto_update' => 'boolean',
        ]);
        
        // Set current version as default
        $validated['version'] = config('app.version');
        
        // Convert checkbox to boolean
        $validated['auto_update'] = isset($validated['auto_update']) ? true : false;
        
        DeploymentRing::create($validated);
        
        return redirect()->route('admin.deployment.rings')
            ->with('success', 'Deployment ring created successfully.');
    }
    
    /**
     * Update an existing deployment ring
     */
    public function updateRing(Request $request, $id)
    {
        $ring = DeploymentRing::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:1',
            'auto_update' => 'boolean',
        ]);
        
        // Convert checkbox to boolean
        $validated['auto_update'] = isset($validated['auto_update']) ? true : false;
        
        $ring->update($validated);
        
        return redirect()->route('admin.deployment.rings')
            ->with('success', 'Deployment ring updated successfully.');
    }
    
    /**
     * Add stores to a deployment ring
     */
    public function addStores(Request $request, $id)
    {
        $ring = DeploymentRing::findOrFail($id);
        
        $request->validate([
            'store_ids' => 'required|array',
            'store_ids.*' => 'exists:stores,id'
        ]);
        
        $storeCount = 0;
        foreach ($request->store_ids as $storeId) {
            $store = \App\Models\Store::findOrFail($storeId);
            
            // Only update if store isn't already in a ring
            if ($store->deployment_ring_id === null) {
                $store->deployment_ring_id = $ring->id;
                $store->save();
                $storeCount++;
            }
        }
        
        return redirect()->route('admin.deployment.rings')
            ->with('success', $storeCount . ' stores added to ' . $ring->name . ' deployment ring');
    }
}