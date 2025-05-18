<!-- filepath: d:\WST\inventory-management-system\resources\views\layouts\partials\sidebar.blade.php -->
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2" id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand px-4 py-3 m-0" >
      
      @if(isset($store))
      <img src="{{ route('store.logo', $store) }}" alt="{{ $store->name }} logo">
    
   @endif

      <span class="ms-1 text-sm text-dark">Inventory System</span>
    </a>
  </div>
  <hr class="horizontal dark mt-0 mb-2">
  <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      
      @php
        // Check if we're on a subdomain or main domain
        $host = request()->getHost();
        $subdomain = null;
        $isSubdomain = false;
        $segments = explode('.', $host);
        if (count($segments) === 3 && $segments[1] === 'inventory' && $segments[0]) {
            $subdomain = $segments[0]; // Get the subdomain part
            
            $isSubdomain = true;
           
        }
        
        // Get current store if on subdomain
        $currentStore = null;
        if($isSubdomain) {
      
            $currentStore = \App\Models\Store::where('slug', $subdomain)->first();
        }
      @endphp
      
      @if(!$isSubdomain)
        <!-- Admin menu items (main domain) -->
        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold {{ request()->routeIs('dashboard') ? 'active bg-light' : '' }}" href="{{ route('dashboard') }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-tachometer-alt text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold {{ request()->routeIs('stores.*') ? 'active bg-light' : '' }}" href="{{ route('stores.index') }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-store text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Stores</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold {{ request()->routeIs('admmin.system*') ? 'active bg-light' : '' }}" href="{{ route('admin.system.update') }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-sync text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Update</span>
          </a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold" href="{{ route('admin.support.index') }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-life-ring text-dark"></i>
            </div>
            
              <span>Support Tickets</span>
             
          </a>
      </li>
      @else

        <!-- Tenant menu items (subdomain) -->
        @php
            // Get subscription tier of the current store
            $tierLevel = 'free'; // Default to free
            if ($currentStore && $currentStore->pricingTier) {
                $tierName = strtolower($currentStore->pricingTier->name ?? '');
                if (str_contains($tierName, 'pro') || str_contains($tierName, 'premium') || str_contains($tierName, 'business')) {
                    $tierLevel = 'pro';
                } elseif (str_contains($tierName, 'starter') || str_contains($tierName, 'basic')) {
                    $tierLevel = 'starter';
                }
            }
        @endphp

        <!-- Products - Available to all tiers -->
        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold {{ request()->routeIs('products.*') ? 'active bg-light' : '' }}" 
             href="{{ route('products.index', ['subdomain' => $subdomain]) }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-box text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Products</span>
          </a>
        </li>

        <!-- Checkout - Available to all tiers -->
        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold {{ request()->routeIs('checkout.*') && !request()->routeIs('checkout.history') ? 'active bg-light' : '' }}" 
             href="{{ route('checkout.index', ['subdomain' => $subdomain]) }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-cash-register text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Checkout</span>
          </a>
        </li>

       

        <!-- History - Available to Starter & Pro tiers -->
        @if($tierLevel == 'starter' || $tierLevel == 'pro')
        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold {{ request()->routeIs('checkout.history') ? 'active bg-light' : '' }}" 
             href="{{ route('checkout.history', ['subdomain' => $subdomain]) }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-receipt text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Sales History</span>
          </a>
        </li>
        @endif

        <!-- Reports - Available to Pro tier only -->
        @if($tierLevel == 'pro')
        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold {{ request()->routeIs('reports.*') ? 'active bg-light' : '' }}" 
             href="{{ route('reports.index', ['subdomain' => $subdomain]) }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-chart-bar text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Reports</span>
            <span class="badge rounded-pill bg-primary ms-auto">Pro</span>
          </a>
        </li>
       
        @endif

        @php
            // Check if current user is a manager
            $isManager = session('tenant_user_role') === 'manager';
        @endphp

        <!-- User management - Only for managers -->
        @if($isManager)
         
         <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold" 
             href="{{ route('tenant.dashboard', ['subdomain' => $subdomain]) }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-tachometer-alt text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold {{ request()->routeIs('staff.*') ? 'active bg-light' : '' }}"
             href="{{ route('staff.index', ['subdomain' => $subdomain]) }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-users text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Users</span>
          </a>
        </li>

        <!-- Settings - Only for managers and if tier is Starter or Pro -->
        @if($tierLevel == 'starter' || $tierLevel == 'pro' || $tierLevel == 'unlimited')
        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold {{ request()->routeIs('settings.*') ? 'active bg-light' : '' }}"
             href="{{ route('settings.index', ['subdomain' => $subdomain]) }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-cogs text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Settings</span>
          </a>
        </li>
        @endif
        @endif

        

        <!-- User profile -->
        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold {{ request()->routeIs('profile.*') ? 'active bg-light' : '' }}" 
             href="{{ route('profile.password', ['subdomain' => $subdomain]) }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-user text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Profile</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold {{ request()->routeIs('tenant.support.*') ? 'active bg-light' : '' }}" 
             href="{{ route('tenant.support.index') }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-life-ring text-dark"></i>
            </div>
              <span>Support</span>
          </a>
      </li>
        
      @endif

      <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold" href="{{ route('logout') }}"
             onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-sign-out-alt text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Logout</span>
          </a>
          <form id="logout-form" action="{{ route('logout') }}" method="GET" style="display: none;">
              @csrf
          </form>
      </li>
    </ul>
  </div>
</aside>