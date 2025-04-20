<!-- filepath: d:\WST\inventory-management-system\resources\views\public\store-requests\create.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request a Store</title>
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
        .header {
            background-color: #e9ecef;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .form-container {
            background-color: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 3rem;
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

    <div class="header">
        <div class="container">
            <h1>Request Your Own Store</h1>
            <p class="lead">Fill out the form below to request a store on our platform</p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="form-container">
                    <h4 class="mb-4">Store Request Form</h4>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('public.store-requests.store') }}">
                        @csrf
                        
                        <div class="card mb-4">
                            <div class="card-header p-3">
                                <h5 class="mb-0">Store Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline my-3">
                                            <label class="form-label">Store Name</label>
                                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline my-3">
                                            <label class="form-label">Subdomain</label>
                                            <input type="text" class="form-control" name="slug" value="{{ old('slug') }}" required>
                                        </div>
                                        <div class="text-xs text-muted">Your store will be accessible at: http://<span id="subdomain-preview">yoursubdomain</span>.{{ config('app.domain', 'localhost') }}</div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline my-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="input-group input-group-outline my-3">
                                            <label class="form-label">Phone</label>
                                            <input type="text" class="form-control" name="phone" value="{{ old('phone') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="input-group input-group-outline my-3">
                                            <label class="form-label">Address</label>
                                            <input type="text" class="form-control" name="address" value="{{ old('address') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="input-group input-group-outline my-3">
                                            <label class="form-label">City</label>
                                            <input type="text" class="form-control" name="city" value="{{ old('city') }}">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="input-group input-group-outline my-3">
                                            <label class="form-label">State</label>
                                            <input type="text" class="form-control" name="state" value="{{ old('state') }}">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="input-group input-group-outline my-3">
                                            <label class="form-label">ZIP Code</label>
                                            <input type="text" class="form-control" name="zip" value="{{ old('zip') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="pricing_tier_id" class="form-label">Select a Plan</label>
                                    <select id="pricing_tier_id" name="pricing_tier_id" class="form-select">
                                        @foreach(\App\Models\PricingTier::where('is_active', true)->orderBy('sort_order')->get() as $tier)
                                        <option value="{{ $tier->id }}" {{ old('pricing_tier_id') == $tier->id ? 'selected' : '' }}>
                                            {{ $tier->name }} - ₱{{ $tier->monthly_price }}/mo
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Billing Cycle</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="billing_cycle" id="billing_monthly" value="monthly" checked>
                                        <label class="form-check-label" for="billing_monthly">
                                            Monthly
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="billing_cycle" id="billing_annual" value="annual">
                                        <label class="form-check-label" for="billing_annual">
                                            Annual (Save up to 17%)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn bg-gradient-primary">Submit Store Request</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.querySelector('input[name="name"]');
            const slugInput = document.querySelector('input[name="slug"]');
            const subdomainPreview = document.getElementById('subdomain-preview');
            
            // Generate slug from store name
            nameInput.addEventListener('blur', function() {
                if (!slugInput.value) {
                    const slug = this.value.toLowerCase()
                        .replace(/[^\w ]+/g, '')
                        .replace(/ +/g, '-');
                    slugInput.value = slug;
                    subdomainPreview.textContent = slug;
                }
            });
            
            // Update preview when slug is changed
            slugInput.addEventListener('input', function() {
                subdomainPreview.textContent = this.value;
            });

            // Set initial value for preview if slug already exists
            if (slugInput.value) {
                subdomainPreview.textContent = slugInput.value;
            }

            // Handle focus and blur for Material Design floating labels
            const inputs = document.querySelectorAll('.input-group-outline input, .input-group-outline textarea');
            inputs.forEach(input => {
                if (input.value) {
                    input.parentElement.classList.add('is-filled');
                }
                
                input.addEventListener('focus', () => {
                    input.parentElement.classList.add('is-focused');
                });
                
                input.addEventListener('blur', () => {
                    input.parentElement.classList.remove('is-focused');
                    if (input.value) {
                        input.parentElement.classList.add('is-filled');
                    } else {
                        input.parentElement.classList.remove('is-filled');
                    }
                });
            });
        });
    </script>
</body>
</html>