<!-- resources/views/stores/create.blade.php -->
@extends('layouts.app')

@section('title', 'Create Store')

@section('content')
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12">
      <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
            <h6 class="text-white text-capitalize ps-3">Create New Store</h6>
          </div>
        </div>
        <div class="card-body px-0 pb-2">
          <form method="POST" action="{{ route('stores.store') }}" class="p-4">
            @csrf
            
            <div class="row">
              <div class="col-md-6">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Store Name</label>
                  <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                </div>
                @error('name')
                  <div class="text-danger text-xs">{{ $message }}</div>
                @enderror
              </div>
              
              <div class="col-md-6">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Subdomain</label>
                  <input type="text" class="form-control" name="slug" value="{{ old('slug') }}" required>
                </div>
                @error('slug')
                  <div class="text-danger text-xs">{{ $message }}</div>
                @enderror
                <div class="text-xs text-muted">Your store will be accessible at: http://<span id="subdomain-preview">yoursubdomain</span>.localhost</div>
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
              <div class="col-md-12">
                <button type="submit" class="btn bg-gradient-success">Create Store</button>
                <a href="{{ route('stores.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
              </div>
            </div>
          </form>
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
  });
</script>
@endpush
@endsection