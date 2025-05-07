<!-- resources/views/stores/create.blade.php -->
@extends('layouts.app')

@section('title', 'Create Store')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">Create New Store</h1>
            <p class="mb-4">Add a new store to your inventory management system</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('stores.store') }}">
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
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}">
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
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <p class="font-weight-bold mb-2">Status</p>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="status_active" value="active" checked>
                                    <label class="form-check-label" for="status_active">Active</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="status_inactive" value="inactive">
                                    <label class="form-check-label" for="status_inactive">Inactive</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12 text-end">
                        <a href="{{ route('stores.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn bg-gradient-primary">Create Store</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header p-3">
                    <h5 class="mb-0">Help</h5>
                </div>
                <div class="card-body">
                    <p>Creating a store allows you to:</p>
                    <ul>
                        <li>Manage inventory for specific locations</li>
                        <li>Assign staff to specific stores</li>
                        <li>Track sales and performance by location</li>
                    </ul>
                    <p class="mb-0">Each store will have its own dedicated storefront that customers can visit.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
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
@endpush
@endsection