<!-- filepath: d:\WST\inventory-management-system\resources\views\products\index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container py-4">
    @php
        
        $canAddProducts = $store->canAddProducts();
    @endphp
    
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="display-5 fw-bold text-primary">
                <i class="fas fa-boxes me-2"></i>Products
                <span class="badge bg-{{ $canAddProducts ? 'success' : 'danger' }} fs-6 ms-2" 
                      data-bs-toggle="tooltip" 
                      title="{{ $store->pricingTier->product_limit < 0 ? 'Unlimited products allowed' : 'Your plan allows '.$store->pricingTier->product_limit.' products' }}">
                    {{ $products->total() }}
                    @if($store->pricingTier->product_limit > 0)
                        / {{ number_format($store->pricingTier->product_limit) }}
                    @endif
                </span>
            </h1>
            <p class="text-muted">Manage your inventory items</p>
        </div>
        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
            @if($canAddProducts)
                <a href="{{ route('products.create', ['subdomain' => $store->slug]) }}" 
                   class="btn btn-primary btn-lg shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i>Add New Product
                </a>
            @else
                <div>
                    <button class="btn btn-secondary btn-lg shadow-sm" disabled>
                        <i class="fas fa-plus-circle me-2"></i>Add New Product
                    </button>
                    <div class="text-danger mt-2 fw-bold">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        You've reached your product limit. 
                        <a href="/{{ $store->slug }}/subscription" class="text-danger text-decoration-underline">
                            Upgrade your plan
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger shadow-sm border-start border-danger border-4">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                </div>
                <div>
                    <h5 class="alert-heading">{{ session('error') }}</h5>
                    @if(session('limit_data'))
                        <p class="mb-0">You have <strong>{{ session('limit_data.current') }}</strong> products out of <strong>{{ session('limit_data.limit') }}</strong> allowed in your current plan.</p>
                        <a href="/{{ $store->slug }}/subscription" class="btn btn-danger mt-2">
                            <i class="fas fa-arrow-circle-up me-1"></i> Upgrade Your Plan
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
    
    @if(session('success'))
        <div class="alert alert-success shadow-sm border-start border-success border-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x text-success me-3"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="fas fa-boxes text-primary fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Product Usage</h6>
                            <p class="mb-0 text-muted small">
                                {{ $store->pricingTier->name }} Plan • 
                                {{ $store->billing_cycle == 'annual' ? 'Annual' : 'Monthly' }} billing
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-2">
                        <div class="d-flex align-items-center justify-content-center">
                            <span class="h3 mb-0 me-2">{{ $products->total() }}</span>
                            <span class="text-muted">/</span>
                            <span class="h4 mb-0 ms-2">{{ $store->pricingTier->product_limit < 0 ? '∞' : number_format($store->pricingTier->product_limit) }}</span>
                            <span class="ms-2 text-muted">products</span>
                        </div>
                        @php 
                            $percentUsed = $store->pricingTier->product_limit > 0 ? 
                                min(100, round(($products->total() / $store->pricingTier->product_limit) * 100)) : 0;
                        @endphp
                        <div class="progress mt-2" style="height: 8px">
                            <div class="progress-bar bg-{{ $percentUsed > 90 ? 'danger' : ($percentUsed > 75 ? 'warning' : 'success') }}" 
                                 role="progressbar" 
                                 style="width: {{ $percentUsed }}%" 
                                 aria-valuenow="{{ $percentUsed }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100"></div>
                        </div>
                        
                        @if($percentUsed > 75)
                            <div class="text-center mt-3">
                                <a href="{{ route('subscription.index', ['subdomain' => $store->slug]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-arrow-circle-up me-1"></i> Upgrade Plan
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-light py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Your Inventory</h5>
                </div>
                <div class="col-md-6 text-md-end">
                    <!-- Could add search/filter here -->
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4">Name</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th class="text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="px-4">
                                    <div class="fw-bold">{{ $product->name }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $product->sku }}</span>
                                </td>
                                <td>
                                    <span class="text-success fw-bold">₱{{ number_format($product->price, 2) }}</span>
                                </td>
                                <td>
                                    @php
                                        $qty = $product->stock ?? $product->quantity;
                                        $qtyClass = $qty > 10 ? 'success' : ($qty > 5 ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge bg-{{ $qtyClass }} rounded-pill px-3">
                                        {{ $qty }}
                                    </span>
                                </td>
                                <td class="text-end px-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('products.show', ['subdomain' => $store->slug, 'product_id' => $product->id]) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('products.edit', ['subdomain' => $store->slug, 'product_id' => $product->id]) }}" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('products.destroy', ['subdomain' => $store->slug, 'product_id' => $product->id]) }}" 
                                              method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this product?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center p-5">
                                    <div class="py-5">
                                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                                        <h4>No Products Found</h4>
                                        <p class="text-muted">Start adding products to your inventory</p>
                                        @if($canAddProducts)
                                            <a href="{{ route('products.create', ['subdomain' => $store->slug]) }}" 
                                               class="btn btn-primary mt-2">
                                                <i class="fas fa-plus-circle me-2"></i>Add First Product
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-center">
                {{ $products->appends(['subdomain' => $store->slug])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .pagination {
        --bs-pagination-active-bg: #4e73df;
        --bs-pagination-active-border-color: #4e73df;
    }
    
    .table > :not(caption) > * > * {
        vertical-align: middle;
    }
    
    .btn-group .btn {
        border-radius: 0;
    }
    
    .btn-group .btn:first-child {
        border-top-left-radius: 0.25rem;
        border-bottom-left-radius: 0.25rem;
    }
    
    .btn-group .btn:last-child {
        border-top-right-radius: 0.25rem;
        border-bottom-right-radius: 0.25rem;
    }
    
    .alert {
        border-radius: 0.5rem;
    }
</style>
@endpush