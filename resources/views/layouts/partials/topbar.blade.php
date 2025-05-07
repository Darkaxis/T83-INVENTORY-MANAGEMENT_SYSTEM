<nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
  <div class="container-fluid py-1 px-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('dashboard') }}">Home</a></li>
        @yield('breadcrumbs')
      </ol>
      <h6 class="font-weight-bolder mb-0">@yield('page-title', 'Inventory Management')</h6>
        
    </nav>
  
  </div>
</nav>