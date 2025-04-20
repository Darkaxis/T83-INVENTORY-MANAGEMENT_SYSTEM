<!-- filepath: d:\WST\inventory-management-system\resources\views\products\show.blade.php -->
@extends('layouts.app')

@section('title', $product->name ?? 'Product Details')

@section('page-title', 'Product Details')

@section('content')
<div class="container-fluid py-4">
  @if(session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif
  
  @if(session('error'))
    <div class="alert alert-danger">
      {{ session('error') }}
    </div>
  @endif
  
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between">
            <h6 class="text-white text-capitalize ps-3">{{ $product->name ?? 'Product' }}</h6>
            <div class="me-3">
              <span class="badge bg-gradient-{{ $product->stock > 0 ? 'success' : 'danger' }}">
                {{ $product->stock > 0 ? 'In Stock' : 'Out of Stock' }}
              </span>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Product Details</h6>
              <ul class="list-group">
                <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong>Name:</strong> {{ $product->name }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>Price:</strong> ${{ number_format($product->price, 2) }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>Stock:</strong> {{ $product->stock }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>Created:</strong> {{ $product->created_at->format('M d, Y') }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>Last Updated:</strong> {{ $product->updated_at->format('M d, Y') }}</li>
              </ul>
            </div>
            <div class="col-md-6">
              <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Store Details</h6>
              <ul class="list-group">
                <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong>Store Name:</strong> {{ $store->name }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>Subdomain:</strong> 
                  <a href="http://{{ $store->slug }}.localhost" target="_blank">{{ $store->slug }}.inventory.test<a>
                </li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>Admin URL:</strong> 
                  <a href="http://{{ $store->slug }}.localhost/admin" target="_blank">{{ $store->slug }}.inventory.test/admin</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="card">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-success shadow-success border-radius-lg pt-4 pb-3">
            <h6 class="text-white text-capitalize ps-3">Product Actions</h6>
          </div>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2">
            <!-- Use absolute URL instead of route helper to avoid parameter issues -->
            <a href="/products/{{ $product->id }}/edit" class="btn btn-info">
              <i class="fas fa-edit me-2"></i> Edit Product
            </a>
            <!-- Use absolute URL for form action -->
            <form action="/products/{{ $product->id }}" method="POST" style="display:inline;">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger w-100">
                <i class="fas fa-trash me-2"></i> Delete Product
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection