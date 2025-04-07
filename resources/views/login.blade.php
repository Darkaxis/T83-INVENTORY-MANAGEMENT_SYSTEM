<!-- resources/views/login.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>
    @if(isset($store))
      {{ $store->name }} - Login
    @else
      Inventory Management System - Login
    @endif
  </title>
  <!-- CSS files -->
  <link href="{{ asset('assets/css/material-dashboard.css') }}" rel="stylesheet" />
</head>

<body class="bg-gray-200">
  <main class="main-content mt-0">
    <div class="page-header align-items-start min-vh-100">
      <div class="container my-auto">
        <div class="row">
          <div class="col-lg-4 col-md-8 col-12 mx-auto">
            <div class="card z-index-0 fadeIn3 fadeInBottom">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                  <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">
                    @if(isset($store))
                      {{ $store->name }}
                    @else
                      Inventory Management System
                    @endif
                  </h4>
                </div>
              </div>
              <div class="card-body">
                @if(session('error'))
                  <div class="alert alert-danger text-white">
                    {{ session('error') }}
                  </div>
                @endif
                
                <form role="form" method="POST" action="{{ route('login.submit') }}">
                  @csrf
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                  </div>
                  @error('email')
                    <div class="text-danger text-xs mt-1">{{ $message }}</div>
                  @enderror
                  
                  <div class="input-group input-group-outline mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control">
                  </div>
                  @error('password')
                    <div class="text-danger text-xs mt-1">{{ $message }}</div>
                  @enderror
                  
                  <div class="form-check form-switch d-flex align-items-center mb-3">
                    <input class="form-check-input" type="checkbox" id="rememberMe" name="remember" checked>
                    <label class="form-check-label mb-0 ms-3" for="rememberMe">Remember me</label>
                  </div>
                  
                  <div class="text-center">
                    <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">Sign in</button>
                  </div>
                  
                  @if(!isset($store))
                  <div class="separator my-3">
                    <span class="text-muted font-weight-bold">OR</span>
                  </div>
                  
                  <a href="{{ route('login.google') }}" class="btn btn-outline-danger w-100 mb-2">
                    <i class="fa fa-google me-2"></i> Sign in with Google
                  </a>
                  @endif
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</body>
</html>