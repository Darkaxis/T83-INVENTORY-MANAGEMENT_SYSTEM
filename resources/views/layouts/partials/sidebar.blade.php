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
            Log::info('Subdomain detected', ['host' => $host, 'subdomain' => $subdomain]);
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
          <a class="nav-link text-dark font-weight-bold" href="#">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-cogs text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Settings</span>
          </a>
        </li>
      @else

        <!-- Tenant menu items (subdomain) -->
        
        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold {{ request()->routeIs('products.*') ? 'active bg-light' : '' }}" 
             href="{{ route('products.index', ['subdomain' => $subdomain]) }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-box text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Products</span>
          </a>
        </li>
        
        @php
            // Check if current user is a manager
            $isManager = session('tenant_user_role') === 'manager';
        @endphp

        @if($isManager)
        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold {{ request()->routeIs('staff.*') ? 'active bg-light' : '' }}"
             href="{{ route('staff.index', ['subdomain' => $subdomain]) }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-users text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Users</span>
          </a>
        </li>
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
        
        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold {{ request()->routeIs('subscription.*') ? 'active bg-light' : '' }}"
             href="/{{ $subdomain}}/subscription">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-credit-card text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Subscription</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark font-weight-bold {{ request()->routeIs('profile') ? 'active bg-light' : '' }}" 
             href="{{ route('profile.password', ['subdomain' => $subdomain]) }}">
            <div class="icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-box text-dark"></i>
            </div>
            <span class="nav-link-text ms-1">Profle</span>
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