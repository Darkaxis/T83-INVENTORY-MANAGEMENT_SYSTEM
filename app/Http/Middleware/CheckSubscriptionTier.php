<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\Log;

class CheckSubscriptionTier
{
    /**
     * Handle an incoming request based on subscription tier.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $minimumTier
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $minimumTier = 'free')
    {
        // Get the current subdomain/store
        $host = $request->getHost();
        $segments = explode('.', $host);
        $subdomain = $segments[0] ?? null;
        
        if (!$subdomain || $subdomain === 'www') {
            // Not on a tenant subdomain, allow access
            return $next($request);
        }
        
        // Get the store
        $store = Store::where('slug', $subdomain)->first();
        if (!$store) {
            abort(404, 'Store not found');
        }
        Log::info('Store found', ['store' => $store->slug]);
        Log::info('Store pricing tier', ['tier' => $store->pricingTier->name ?? 'none']);
        // Determine the store's tier level
        $tierLevel = 'free'; // Default
        if ($store->pricingTier) {
            $tierName = strtolower($store->pricingTier->name ?? '');
            if (str_contains($tierName, 'pro') || str_contains($tierName, 'premium') || str_contains($tierName, 'professional')) {
                $tierLevel = 'pro';
            } elseif (str_contains($tierName, 'starter') || str_contains($tierName, 'basic')) {
                $tierLevel = 'starter';
            }
        }
        
        // Map tiers to numeric values for comparison
        $tierValues = [
            'free' => 0,
            'starter' => 1, 
            'pro' => 2
        ];
        
        $requiredTierValue = $tierValues[$minimumTier] ?? 0;
        $currentTierValue = $tierValues[$tierLevel] ?? 0;
        
        // Check if current tier is sufficient
        if ($currentTierValue >= $requiredTierValue) {
            return $next($request);
        }
        
        // Access denied - redirect with appropriate message
        $upgradeRoute = route('subscription.index', ['subdomain' => $subdomain]);
        
        // Log the access attempt
        Log::info('Tier restriction enforced', [
            'store' => $subdomain,
            'current_tier' => $tierLevel,
            'required_tier' => $minimumTier,
            'route' => $request->route()->getName()
        ]);
        
        return redirect()->route('products.index', ['subdomain' => $subdomain])
            ->with('error', "This feature requires the " . ucfirst($minimumTier) . " plan or higher. Upgrade now");
    }
}