<!-- resources/views/login.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @if(isset($store))
  <link rel="icon" href="{{ route('store.favicon', ['store' => $store->id]) }}">
@else
  <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
@endif
  <title>
    @if(isset($store))
      {{ $store->name }} - Login
    @else
      Inventory Management System - Login
    @endif
  </title>
  
  @if(isset($store) && $store->accent_color)
  <style>
    :root {
      --primary: {{ $store->getAccentColorCss()['primary'] ?? '#4e73df' }};
      --secondary: {{ $store->getAccentColorCss()['secondary'] ?? '#2e59d9' }};
      --tertiary: {{ $store->getAccentColorCss()['tertiary'] ?? '#2653d4' }};
      --highlight: {{ $store->getAccentColorCss()['highlight'] ?? 'rgba(78, 115, 223, 0.25)' }};
    }
    
    .bg-gradient-primary {
      background-image: linear-gradient(195deg, var(--secondary) 0%, var(--primary) 100%) !important;
    }
    
    .btn.bg-gradient-primary {
      background-image: linear-gradient(195deg, var(--secondary) 0%, var(--primary) 100%);
    }
    
    .btn.bg-gradient-primary:hover {
      background-color: var(--primary);
      border-color: var(--primary);
    }
    
    .text-primary {
      color: var(--primary) !important;
    }
    
    .bg-primary {
      background-color: var(--primary) !important;
    }
    
    .border-primary {
      border-color: var(--primary) !important;
    }
    
    .form-check-input:checked {
      background-color: var(--primary);
      border-color: var(--primary);
    }
    
    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 2px var(--highlight);
    }
    
    .input-group.focused .input-group-outline .form-label,
    .input-group.focused .input-group-outline .form-label + .form-control {
      color: var(--primary);
      border-color: var(--primary);
    }
    
    .btn-outline-danger {
      border-color: var(--primary);
      color: var(--primary);
    }
    
    .btn-outline-danger:hover {
      background-color: var(--primary);
      border-color: var(--primary);
      color: white;
    }
  </style>
  @endif

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
                  @if(isset($store) && $store->logo_binary)
                    <div class="text-center mb-2">
                      <img src="{{ route('store.logo', ['store' => $store->id]) }}" 
                           style="max-height: 60px; max-width: 80%; object-fit: contain;" 
                           alt="{{ $store->name }} logo" class="img-fluid">
                    </div>
                  @endif
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
                
                                <!-- Add this to your homepage view -->
                <div class="container mt-5">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3>Need Your Own Store?</h3>
                            <p>Join our platform and start selling today!</p>
                            <a href="{{ route('public.store-requests.create') }}" class="btn btn-lg {{ isset($store) ? 'bg-gradient-primary' : 'bg-gradient-primary' }}">
                                Request Your Store Now
                            </a>
                            <!-- Add this near the top of the card-body section -->

                        </div>
                    </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</body>
</html>