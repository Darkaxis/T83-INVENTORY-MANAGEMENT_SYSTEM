<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PricingTier\StorePricingTierRequest;
use App\Http\Requests\Admin\PricingTier\UpdatePricingTierRequest;
use App\Models\PricingTier;
use Illuminate\Http\Request;

class PricingTierController extends Controller
{
    /**
     * Display a listing of pricing tiers.
     */
    public function index()
    {
        $pricingTiers = PricingTier::orderBy('sort_order')->get();
        return view('admin.pricing_tiers.index', compact('pricingTiers'));
    }

    /**
     * Show the form for creating a new pricing tier.
     */
    public function create()
    {
        return view('admin.pricing_tiers.create');
    }

    /**
     * Store a newly created pricing tier in storage.
     */
    public function store(StorePricingTierRequest $request)
    {
        // Validation is automatically handled by the request class
        $validated = $request->validated();
        
        // Convert features array to JSON
        $features = $validated['features'] ?? [];
        
        PricingTier::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'monthly_price' => $validated['monthly_price'],
            'annual_price' => $validated['annual_price'],
            'product_limit' => $validated['product_limit'],
            'user_limit' => $validated['user_limit'],
            'is_active' => $validated['is_active'] ?? false,
            'features_json' => $features,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.pricing-tiers.index')
            ->with('success', 'Pricing tier created successfully.');
    }

    /**
     * Show the form for editing the specified pricing tier.
     */
    public function edit(PricingTier $pricingTier)
    {
        return view('admin.pricing_tiers.edit', compact('pricingTier'));
    }

    /**
     * Update the specified pricing tier in storage.
     */
    public function update(UpdatePricingTierRequest $request, PricingTier $pricingTier)
    {
        $validated = $request->validated();
        
        // Convert features array to JSON
        $features = $validated['features'] ?? [];
        
        $pricingTier->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'monthly_price' => $validated['monthly_price'],
            'annual_price' => $validated['annual_price'],
            'product_limit' => $validated['product_limit'],
            'user_limit' => $validated['user_limit'],
            'is_active' => $validated['is_active'] ?? false,
            'features_json' => $features,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.pricing-tiers.index')
            ->with('success', 'Pricing tier updated successfully.');
    }

    /**
     * Remove the specified pricing tier from storage.
     */
    public function destroy(PricingTier $pricingTier)
    {
        // Check if any stores are using this tier
        $storeCount = $pricingTier->stores()->count();
        
        if ($storeCount > 0) {
            return redirect()->route('admin.pricing-tiers.index')
                ->with('error', "Cannot delete. This pricing tier is currently used by {$storeCount} stores.");
        }
        
        $pricingTier->delete();
        
        return redirect()->route('admin.pricing-tiers.index')
            ->with('success', 'Pricing tier deleted successfully.');
    }
}