<!-- filepath: d:\WST\inventory-management-system\resources\views\layouts\partials\sidebar.blade.php -->
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2" id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand px-4 py-3 m-0" href="{{ route('dashboard') }}">
      <img src="{{ asset('assets/img/logo-ct-dark.png') }}" class="navbar-brand-img" width="26" height="26" alt="main_logo">
      <span class="ms-1 text-sm text-dark">Inventory System</span>
    </a>
  </div>
  <hr class="horizontal dark mt-0 mb-2">
  <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      
      <!-- Admin menu items -->
     
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
  
    </ul>
  </div>
</aside>