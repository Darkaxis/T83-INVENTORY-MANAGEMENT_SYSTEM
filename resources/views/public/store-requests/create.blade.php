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
        /* Add to your existing styles */
        .pricing-card {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .pricing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .pricing-card .card-header {
            border-bottom: 2px solid #f8f9fa;
            font-weight: 600;
        }
        
        .pricing-tiers .badge {
            font-size: 0.7rem;
        }
        
        .pricing-card h3 {
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .pricing-card h3 small {
            font-weight: 400;
            opacity: 0.7;
            font-size: 1rem;
        }
        
        .billing-switch-container {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin: 0 auto 20px;
            display: inline-block;
        }
        
        .form-check-input:checked {
            background-color: #4CAF50;
            border-color: #4CAF50;
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
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header p-3">
                                <h5 class="mb-0">Select a Subscription Plan</h5>
                            </div>
                            <div class="card-body">
                                <div class="billing-switch-container text-center mb-4">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="billing_cycle" id="billing_monthly" value="monthly" checked>
                                        <label class="form-check-label" for="billing_monthly">
                                            Monthly Billing
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="billing_cycle" id="billing_annual" value="annual">
                                        <label class="form-check-label" for="billing_annual">
                                            Annual Billing <span class="badge bg-success">Save up to 17%</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="row pricing-tiers">
                                    @foreach(\App\Models\PricingTier::where('is_active', true)->orderBy('sort_order')->get() as $tier)
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100 pricing-card {{ $loop->index == 1 ? 'border border-2 border-primary' : '' }}">
                                            @if($loop->index == 1)
                                            <div class="position-absolute top-0 start-50 translate-middle">
                                                <span class="badge bg-primary">MOST POPULAR</span>
                                            </div>
                                            @endif
                                            <div class="card-header text-center {{ $loop->index == 1 ? 'bg-primary text-white' : '' }}">
                                                <h5 class="mb-0">{{ $tier->name }}</h5>
                                            </div>
                                            <div class="card-body d-flex flex-column">
                                                <div class="text-center mb-3">
                                                    <h3 class="monthly-price" {{ old('billing_cycle') == 'annual' ? 'style=display:none' : '' }}>
                                                        ₱{{ number_format($tier->monthly_price, 2) }}<small>/month</small>
                                                    </h3>
                                                    <h3 class="annual-price" {{ old('billing_cycle') != 'annual' ? 'style=display:none' : '' }}>
                                                        ₱{{ number_format($tier->annual_price/12, 2) }}<small>/month</small>
                                                        <p class="small text-muted mb-0">Billed as ₱{{ number_format($tier->annual_price, 2) }} yearly</p>
                                                    </h3>
                                                </div>
                                                
                                                <ul class="list-group list-group-flush flex-grow-1">
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>Products</span>
                                                        <span class="fw-bold">{{ $tier->product_limit < 0 ? 'Unlimited' : number_format($tier->product_limit) }}</span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>Staff Members</span>
                                                        <span class="fw-bold">{{ $tier->user_limit < 0 ? 'Unlimited' : number_format($tier->user_limit) }}</span>
                                                    </li>
                                                    
                                                    @if(is_array($tier->features_json))
                                                        @foreach($tier->features_json as $feature => $included)
                                                            @if(is_bool($included) || is_string($included))
                                                            <li class="list-group-item">
                                                                <div class="d-flex align-items-center">
                                                                    @if($included === true || $included === 'true' || $included === 'yes')
                                                                        <i class="material-icons text-success me-2">check_circle</i>
                                                                    @elseif($included === false || $included === 'false' || $included === 'no')
                                                                        <i class="material-icons text-muted me-2">remove_circle_outline</i>
                                                                    @else
                                                                        <i class="material-icons text-primary me-2">info</i>
                                                                    @endif
                                                                    <span>{{ ucfirst(str_replace('_', ' ', $feature)) }}</span>
                                                                    
                                                                    @if(is_string($included) && !in_array($included, ['true', 'false', 'yes', 'no']))
                                                                        <span class="ms-auto">{{ $included }}</span>
                                                                    @endif
                                                                </div>
                                                            </li>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </ul>
                                                
                                                <div class="mt-3 text-center">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="pricing_tier_id" 
                                                            id="pricing_tier_{{ $tier->id }}" value="{{ $tier->id }}"
                                                            {{ (old('pricing_tier_id') == $tier->id || ($loop->first && !old('pricing_tier_id'))) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="pricing_tier_{{ $tier->id }}">
                                                            Select {{ $tier->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
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

            // Handle billing cycle toggle
            const monthlyRadio = document.getElementById('billing_monthly');
            const annualRadio = document.getElementById('billing_annual');
            const monthlyPrices = document.querySelectorAll('.monthly-price');
            const annualPrices = document.querySelectorAll('.annual-price');
            
            monthlyRadio.addEventListener('change', function() {
                if (this.checked) {
                    monthlyPrices.forEach(el => el.style.display = 'block');
                    annualPrices.forEach(el => el.style.display = 'none');
                }
            });
            
            annualRadio.addEventListener('change', function() {
                if (this.checked) {
                    monthlyPrices.forEach(el => el.style.display = 'none');
                    annualPrices.forEach(el => el.style.display = 'block');
                }
            });
            
            // Initialize based on current selection
            if (annualRadio.checked) {
                monthlyPrices.forEach(el => el.style.display = 'none');
                annualPrices.forEach(el => el.style.display = 'block');
            }
        });
    </script>
</body>
</html>