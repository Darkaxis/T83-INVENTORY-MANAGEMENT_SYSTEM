<?php

namespace App\Http\Controllers;

use App\Models\PricingTier;
use App\Models\Store;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request, $subdomain)
    {
        $store = Store::where('slug', $subdomain)->firstOrFail();
        $availableTiers = PricingTier::where('is_active', true)->orderBy('sort_order')->get();
        
        return view('tenant.subscription.index', compact('store', 'availableTiers'));
    }
    
    public function upgrade(Request $request, $subdomain)
    {
        $store = Store::where('slug', $subdomain)->firstOrFail();
        
        $request->validate([
            'pricing_tier_id' => 'required|exists:pricing_tiers,id',
        ]);
        
        $newTier = PricingTier::findOrFail($request->pricing_tier_id);
        
        // Here you would implement payment processing
        // For now, just update the store with the new tier
        $store->update([
            'pricing_tier_id' => $newTier->id,
            'subscription_start_date' => now(),
            'subscription_end_date' => $store->billing_cycle === 'monthly' ? 
                now()->addMonth() : now()->addYear(),
        ]);
        
        return redirect()->route('tenant.subscription', ['subdomain' => $store->slug])
            ->with('success', 'Your subscription has been upgraded to ' . $newTier->name);
    }
}