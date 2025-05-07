<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'nullable|numeric|min:0',
            'product_limit' => 'nullable|integer|min:-1',
            'user_limit' => 'nullable|integer|min:-1',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'sort_order' => 'integer|min:0',
        ]);

        // Convert features array to JSON
        $features = $request->features ?? [];
        
        PricingTier::create([
            'name' => $request->name,
            'description' => $request->description,
            'monthly_price' => $request->monthly_price,
            'annual_price' => $request->annual_price,
            'product_limit' => $request->product_limit,
            'user_limit' => $request->user_limit,
            'is_active' => $request->is_active ?? false,
            'features_json' => $features,
            'sort_order' => $request->sort_order ?? 0,
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
    public function update(Request $request, PricingTier $pricingTier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'nullable|numeric|min:0',
            'product_limit' => 'nullable|integer|min:-1',
            'user_limit' => 'nullable|integer|min:-1',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'sort_order' => 'integer|min:0',
        ]);

        // Convert features array to JSON
        $features = $request->features ?? [];
        
        $pricingTier->update([
            'name' => $request->name,
            'description' => $request->description,
            'monthly_price' => $request->monthly_price,
            'annual_price' => $request->annual_price,
            'product_limit' => $request->product_limit,
            'user_limit' => $request->user_limit,
            'is_active' => $request->is_active ?? false,
            'features_json' => $features,
            'sort_order' => $request->sort_order ?? 0,
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