<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Store;

class ProFeatureMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $store = $this->getCurrentStore($request);
        
        // Get store's subscription tier
        $subscription = $store->subscription;
        
        if (!$subscription || !in_array($subscription->pricing_tier->name, ['Professional', 'Unlimited'])) {
            return redirect()->route('subscription.upgrade', ['subdomain' => $store->slug])
                ->with('error', 'This feature requires a Professional or Unlimited subscription.');
        }
        
        return $next($request);
    }

    protected function getCurrentStore($request)
    {
        $host = $request->getHost();
        $segments = explode('.', $host);
        $subdomain = $segments[0] ?? null;
        
        return Store::where('slug', $subdomain)->firstOrFail();
    }
}
