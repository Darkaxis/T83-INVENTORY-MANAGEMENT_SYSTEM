<nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
  <div class="container-fluid py-1 px-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('dashboard') }}">Home</a></li>
        @yield('breadcrumbs')
      </ol>
      <h6 class="font-weight-bolder mb-0">@yield('page-title', 'Inventory Management')</h6>
    </nav>
    <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
      <div class="ms-md-auto pe-md-3 d-flex align-items-center">
        <div class="input-group input-group-outline">
          <label class="form-label">Search...</label>
          <input type="text" class="form-control">
        </div>
      </div>
      <ul class="navbar-nav justify-content-end">
        <!-- User dropdown, notifications, etc. -->
        <li class="nav-item dropdown">
          <a href="javascript:;" class="nav-link text-body p-0" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="material-symbols-rounded me-1">account_circle</i>
            <span class="d-sm-inline d-none">{{ Auth::user()->name ?? 'User' }}</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="userDropdown">
            {{-- <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
            <li><a class="dropdown-item" href="{{ route('settings') }}">Settings</a></li> --}}
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="GET" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item">Logout</button>
              </form>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>