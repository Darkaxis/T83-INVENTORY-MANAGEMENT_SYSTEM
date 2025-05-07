<!DOCTYPE html>
<html lang="en">

<head>

  
  <meta charset="utf-8" />

  

  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
  @if(isset($store))
   
    <link rel="icon" href="{{ route('store.favicon', ['store' => $store->id]) }}">
  @else
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
  @endif
  <title>
    @yield('title', 'Inventory Management System')
  </title>
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <!-- Nucleo Icons -->
  <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <!-- CSS Files -->
  <link id="pagestyle" href="{{ asset('assets/css/material-dashboard.css?v=3.2.0') }}" rel="stylesheet" />
  <!-- Add this to your app.blade.php head section -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
  
  @php
      $store = null;
      $accentColors = [
          'primary' => '#4e73df', 
          'secondary' => '#2e59d9',
          'tertiary' => '#2653d4',
          'highlight' => 'rgba(78, 115, 223, 0.25)'
      ];
      
      // Check if we're on a tenant subdomain
      $host = request()->getHost();
      $segments = explode('.', $host);
      $isSubdomain = count($segments) === 3 && $segments[1] === 'inventory';
      
      if ($isSubdomain) {
        
          $subdomain = $segments[0];
          $store = \App\Models\Store::where('slug', $subdomain)->first();
          
          // Get the custom colors if store exists
          if ($store) {
              $accentColors = $store->getAccentColorCss();
          }
      }
  @endphp

  <style>
      :root {
          --primary: {{ $accentColors['primary'] }};
          --secondary: {{ $accentColors['secondary'] }};
          --tertiary: {{ $accentColors['tertiary'] }};
          --highlight: {{ $accentColors['highlight'] }};
      }
      
      .btn-primary {
          background-color: var(--primary);
          border-color: var(--primary);
      }
      
      .btn-primary:hover, .btn-primary:active, .btn-primary:focus {
          background-color: var(--secondary) !important;
          border-color: var(--tertiary) !important;
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
      
      .form-control:focus, .form-select:focus, .form-check-input:focus {
          box-shadow: 0 0 0 0.25rem var(--highlight);
          border-color: var(--tertiary);
      }
      
      .form-check-input:checked {
          background-color: var(--primary);
          border-color: var(--primary);
      }
      
      a {
          color: var(--primary);
      }
      
      a:hover {
          color: var(--secondary);
      }
  </style>

  @if(isset($store) && $store->accent_color)
  <style>
      :root {
          --bs-primary: {{ $store->accent_color }};
          --bs-primary-rgb: {{ implode(',', sscanf($store->accent_color, "#%02x%02x%02x")) }};
      }
      
      .btn-primary,
      .bg-primary {
          background-color: {{ $store->accent_color }} !important;
          border-color: {{ $store->accent_color }} !important;
      }
      
      .text-primary {
          color: {{ $store->accent_color }} !important;
      }
      
      /* Add more custom styling as needed */
  </style>
  @endif
  
  @stack('styles')
</head>

<body class="g-sidenav-show bg-gray-100">
  @include('layouts.partials.sidebar')
  
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <!-- Navbar -->
    @include('layouts.partials.topbar')
    <!-- End Navbar -->
    
    <div class="container-fluid py-2">
      @yield('content')
      
      @include('layouts.partials.footer')
    </div>
  </main>
{{--   
  @include('layouts.partials.configurator') --}}
  
  <!-- Core JS Files -->
  <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
  <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script>
  
  @include('layouts.partials.scripts')
  
  @stack('scripts')
</body>
</html>