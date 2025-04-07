<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $store->name }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="#">{{ $store->name }}</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="/admin">Store Admin</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-5">
    <div class="row">
      <div class="col-12 text-center mb-5">
        <h1>Welcome to {{ $store->name }}</h1>
        <p class="lead">{{ $store->description ?? 'Your store description here' }}</p>
      </div>
    </div>
    
    <div class="row">
      <div class="col-md-6 offset-md-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Store Information</h5>
            <p><strong>Location:</strong> {{ $store->city }}, {{ $store->state }}</p>
            <p><strong>Address:</strong> {{ $store->address }}</p>
            <p><strong>Contact:</strong> {{ $store->email }}</p>
            <p><strong>Phone:</strong> {{ $store->phone }}</p>
          </div>
        </div>
      </div>
    </div>
    
    <div class="row mt-5">
      <div class="col-12 text-center">
        <p class="text-muted">This is the public-facing website for {{ $store->name }}. <a href="/admin">Login</a> to manage your store.</p>
      </div>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>