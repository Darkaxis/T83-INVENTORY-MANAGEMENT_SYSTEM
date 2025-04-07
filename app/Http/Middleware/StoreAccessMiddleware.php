<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $store = $request->store;
        
        // Allow access if user is admin or belongs to this store
        if ($user->role === 'admin' || $user->store_id === $store->id) {
            return $next($request);
        }
        
        return redirect()->route('login')
            ->with('error', 'You do not have access to this store.');
    }
}