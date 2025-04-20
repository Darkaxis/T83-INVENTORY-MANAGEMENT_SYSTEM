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
                                    <select name="accent_color" id="accent_color" class="form-select @error('accent_color') is-invalid @enderror">
                                        @foreach($accentColors as $value => $label)
                                            <option value="{{ $value }}" {{ old('accent_color', $store->accent_color) == $value ? 'selected' : '' }}
                                                    data-color="{{ $value }}">
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('accent_color')
                                        <div class="invalid-feedback">{{ $message }}</div>
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
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Color preview functionality
        const accentColorSelect = document.getElementById('accent_color');
        const colorPreview = document.getElementById('color-preview');
        
        // Color mapping (same as in Store model)
        const colors = {
            'blue': {
                primary: '#4e73df',
                secondary: '#2e59d9',
                tertiary: '#2653d4',
                highlight: 'rgba(78, 115, 223, 0.25)'
            },
            'indigo': {
                primary: '#6610f2',
                secondary: '#520dc2',
                tertiary: '#4d0cb3',
                highlight: 'rgba(102, 16, 242, 0.25)'
            },
            'purple': {
                primary: '#6f42c1',
                secondary: '#5a359f',
                tertiary: '#533291',
                highlight: 'rgba(111, 66, 193, 0.25)'
            },
            'pink': {
                primary: '#e83e8c',
                secondary: '#d4317a',
                tertiary: '#c42e72',
                highlight: 'rgba(232, 62, 140, 0.25)'
            },
            'red': {
                primary: '#e74a3b',
                secondary: '#d13b2e',
                tertiary: '#c0372a',
                highlight: 'rgba(231, 74, 59, 0.25)'
            },
            'orange': {
                primary: '#fd7e14',
                secondary: '#e96e10',
                tertiary: '#d6630f',
                highlight: 'rgba(253, 126, 20, 0.25)'
            },
            'yellow': {
                primary: '#f6c23e',
                secondary: '#e9b32d',
                tertiary: '#e0ac29',
                highlight: 'rgba(246, 194, 62, 0.25)'
            },
            'green': {
                primary: '#1cc88a',
                secondary: '#18a97c',
                tertiary: '#169b72',
                highlight: 'rgba(28, 200, 138, 0.25)'
            },
            'teal': {
                primary: '#20c9a6',
                secondary: '#1ba393',
                tertiary: '#199688',
                highlight: 'rgba(32, 201, 166, 0.25)'
            },
            'cyan': {
                primary: '#36b9cc',
                secondary: '#2fa6b9',
                tertiary: '#2a98a9',
                highlight: 'rgba(54, 185, 204, 0.25)'
            }
        };
        
        // Update the preview colors when select changes
        function updateColorPreview() {
            const selectedColor = accentColorSelect.value;
            const colorSet = colors[selectedColor] || colors['blue'];
            
            document.documentElement.style.setProperty('--primary', colorSet.primary);
            document.documentElement.style.setProperty('--secondary', colorSet.secondary);
            document.documentElement.style.setProperty('--tertiary', colorSet.tertiary);
            document.documentElement.style.setProperty('--highlight', colorSet.highlight);
        }
        
        // Initial update and add event listener
        updateColorPreview();
        accentColorSelect.addEventListener('change', updateColorPreview);
        
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