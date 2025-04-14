<!-- filepath: d:\WST\inventory-management-system\resources\views\public\store-requests\thank-you.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Submitted</title>
    <!-- Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <!-- Material Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/material-dashboard.css') }}">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 500;
        }
        .success-container {
            max-width: 700px;
            margin: 5rem auto;
            background-color: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
            text-align: center;
        }
        .success-icon {
            font-size: 5rem;
            color: #4caf50;
            margin-bottom: 1.5rem;
        }
        .store-info {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1.5rem 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="material-icons">shopping_cart</i> Inventory System
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="success-container">
            <div class="success-icon">
                <i class="material-icons">check_circle</i>
            </div>
            
            <h2>Thank You!</h2>
            <p class="lead">Your store request has been successfully submitted.</p>
            
            <div class="store-info">
                <h5>Request Details</h5>
                <div class="row mt-3">
                    <div class="col-sm-6 text-sm-end"><strong>Request ID:</strong></div>
                    <div class="col-sm-6 text-sm-start">{{ $storeRequest->id }}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-sm-6 text-sm-end"><strong>Store Name:</strong></div>
                    <div class="col-sm-6 text-sm-start">{{ $storeRequest->name }}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-sm-6 text-sm-end"><strong>Requested On:</strong></div>
                    <div class="col-sm-6 text-sm-start">{{ $storeRequest->created_at->format('F j, Y, g:i a') }}</div>
                </div>
            </div>
            
            <p>Our team will review your request and get back to you at <strong>{{ $storeRequest->email }}</strong> soon.</p>
            <p class="text-muted">Please save your Request ID for future reference.</p>
            
            <div class="mt-4">
                <a href="{{ url('/') }}" class="btn bg-gradient-primary">Return to Home</a>
            </div>
        </div>
    </div>
</body>
</html>