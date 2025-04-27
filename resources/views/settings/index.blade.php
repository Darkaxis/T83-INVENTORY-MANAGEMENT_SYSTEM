<!-- filepath: d:\WST\inventory-management-system\resources\views\settings\index.blade.php -->
@extends('layouts.app')

@section('page-title', 'Store Settings')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="display-5 fw-bold text-primary">
                <i class="fas fa-cogs me-2"></i>Store Settings
            </h1>
            <p class="text-muted">Customize your store appearance and information</p>
        </div>
    </div>
    
    @if(session('error'))
        <div class="alert alert-danger shadow-sm border-start border-danger border-4">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                </div>
                <div>{{ session('error') }}</div>
            </div>
        </div>
    @endif
    
    @if(session('success'))
        <div class="alert alert-success shadow-sm border-start border-success border-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x text-success me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold">Store Information</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.update', ['subdomain' => $store->slug]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label fw-bold">Store Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-store"></i></span>
                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                               value="{{ old('name', $store->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted">This is the name displayed to your customers and staff</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Store URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-link"></i></span>
                                        <input type="text" class="form-control bg-light" 
                                               value="{{ $store->slug }}.inventory.test" disabled readonly>
                                    </div>
                                    <small class="form-text text-muted">Your store's unique URL cannot be changed</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="accent_color" class="form-label fw-bold">Accent Color</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-palette"></i></span>
                                        <input type="color" id="color_picker" class="form-control form-control-color" 
                                               value="{{ old('accent_color', $store->accent_color_hex ?? '#4e73df') }}" 
                                               title="Choose your accent color">
                                        <input type="text" id="color_hex" class="form-control" 
                                               value="{{ old('accent_color', $store->accent_color_hex ?? '#4e73df') }}" 
                                               pattern="^#[0-9A-Fa-f]{6}$" placeholder="#4e73df">
                                        <input type="hidden" name="accent_color" id="accent_color" 
                                               value="{{ old('accent_color', $store->accent_color_hex ?? '#4e73df') }}">
                                    </div>
                                    @error('accent_color')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">This color will be used for buttons, links, and highlights</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-bold">Color Preview</label>
                                    <div class="p-2">
                                        <div id="color-preview" class="d-flex flex-wrap gap-2">
                                            <span class="badge bg-primary px-3 py-2">Primary</span>
                                            <button class="btn btn-primary btn-sm">Button</button>
                                            <a href="#" class="btn btn-link text-primary">Link</a>
                                            <div class="color-sample color-sample-primary"></div>
                                            <div class="color-sample color-sample-secondary"></div>
                                            <div class="color-sample color-sample-tertiary"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="logo" class="form-label fw-bold">Store Logo</label>
                                    <input type="file" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                                    @error('logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Recommended size: 200x200 pixels (Max 2MB)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-bold">Current Logo</label>
                                    <div class="p-2 text-center">
                                                                   
                                        <img src="{{ route('store.logo', $store) }}" alt="{{ $store->name }} logo">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary shadow-sm px-4">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold">Store Information</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Subscription Plan</span>
                            <span class="badge bg-info rounded-pill">{{ $store->pricingTier->name ?? 'Free' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Store Created</span>
                            <span>{{ $store->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>User Limit</span>
                            <span>{{ $store->pricingTier->user_limit ?? 'Unlimited' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Product Limit</span>
                            <span>{{ $store->pricingTier->product_limit ?? 'Unlimited' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        border-color: #bac8f3;
    }
    
    .input-group-text {
        background-color: #f8f9fa;
    }
    
    .card {
        transition: all 0.2s ease-in-out;
    }
    
    .btn {
        font-weight: 500;
    }
    
    .btn-primary {
        background-color: var(--primary, #4e73df);
        border-color: var(--primary, #4e73df);
    }
    
    .btn-primary:hover {
        background-color: var(--secondary, #2e59d9);
        border-color: var(--tertiary, #2653d4);
    }
    
    .form-check-input:checked {
        background-color: var(--primary, #4e73df);
        border-color: var(--primary, #4e73df);
    }
    
    .text-primary {
        color: var(--primary, #4e73df) !important;
    }
    
    .bg-primary {
        background-color: var(--primary, #4e73df) !important;
    }
    
    /* Color picker styles */
    .form-control-color {
        width: 4rem;
        padding: 0.375rem;
        height: 38px;
    }
    
    #color_hex {
        font-family: monospace;
        text-transform: uppercase;
    }
    
    /* Enhanced preview area */
    #color-preview {
        border: 1px solid #dee2e6;
        padding: 15px;
        border-radius: 6px;
        background-color: white;
    }
    
    /* Add more preview elements */
    .color-sample {
        width: 40px;
        height: 40px;
        border-radius: 4px;
        display: inline-block;
        margin-right: 10px;
        border: 1px solid #dee2e6;
    }
    
    .color-sample-primary {
        background-color: var(--primary, #4e73df);
    }
    
    .color-sample-secondary {
        background-color: var(--secondary, #2e59d9);
    }
    
    .color-sample-tertiary {
        background-color: var(--tertiary, #2653d4);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Color picker functionality
        const colorPicker = document.getElementById('color_picker');
        const colorHexInput = document.getElementById('color_hex');
        const accentColorInput = document.getElementById('accent_color');
        const colorPreview = document.getElementById('color-preview');
        
        // Function to update all related color elements
        function updateColors(hex) {
            // Update all inputs
            colorPicker.value = hex;
            colorHexInput.value = hex;
            accentColorInput.value = hex;
            
            // Get RGB values from hex
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            
            // Create color variations
            const primary = hex;
            const secondary = shadeColor(hex, -10); // 10% darker
            const tertiary = shadeColor(hex, -15);  // 15% darker
            const highlight = `rgba(${r}, ${g}, ${b}, 0.25)`;
            
            // Set CSS variables
            document.documentElement.style.setProperty('--primary', primary);
            document.documentElement.style.setProperty('--secondary', secondary);
            document.documentElement.style.setProperty('--tertiary', tertiary);
            document.documentElement.style.setProperty('--highlight', highlight);
        }
        
        // Function to darken or lighten a color
        function shadeColor(color, percent) {
            let R = parseInt(color.substring(1,3),16);
            let G = parseInt(color.substring(3,5),16);
            let B = parseInt(color.substring(5,7),16);

            R = parseInt(R * (100 + percent) / 100);
            G = parseInt(G * (100 + percent) / 100);
            B = parseInt(B * (100 + percent) / 100);

            R = (R < 255) ? R : 255;  
            G = (G < 255) ? G : 255;  
            B = (B < 255) ? B : 255;  

            R = Math.max(0, R);
            G = Math.max(0, G);
            B = Math.max(0, B);

            const RR = ((R.toString(16).length === 1) ? "0" + R.toString(16) : R.toString(16));
            const GG = ((G.toString(16).length === 1) ? "0" + G.toString(16) : G.toString(16));
            const BB = ((B.toString(16).length === 1) ? "0" + B.toString(16) : B.toString(16));

            return "#" + RR + GG + BB;
        }
        
        // Event listeners for color picker and hex input
        colorPicker.addEventListener('input', function() {
            updateColors(this.value);
        });
        
        colorHexInput.addEventListener('input', function() {
            // Validate hex input
            if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                updateColors(this.value);
            }
        });
        
        colorHexInput.addEventListener('blur', function() {
            // Force valid hex format on blur
            if (!/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                this.value = accentColorInput.value;
            } else {
                updateColors(this.value);
            }
        });
        
        // Initial update
        updateColors(accentColorInput.value);
        
        // Logo preview
        const logoInput = document.getElementById('logo');
        logoInput?.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.querySelector('.col-md-6:nth-child(2) img');
                    img.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    });
</script>
@endpush