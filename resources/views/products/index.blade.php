<!-- filepath: d:\WST\inventory-management-system\resources\views\products\index.blade.php -->

@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="display-5 fw-bold text-primary">
                <i class="fas fa-boxes me-2"></i>Products
            </h1>
            <p class="text-muted">Manage your inventory</p>
        </div>
        <div class="col-auto align-self-center">
            <a href="{{ route('products.create', ['subdomain' => $store->slug]) }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Add Product
            </a>
        </div>
    </div>
    
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
    
    <!-- Search Component -->
    @include('products.partials.search')
    
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        @if($products->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                @if(request()->has('search'))
                                    <p class="mb-0 text-muted">No products found matching "{{ request()->get('search') }}"</p>
                                @else
                                    <i class="fas fa-box-open fa-2x text-muted mb-3"></i>
                                    <p class="mb-0 text-muted">No products yet</p>
                                    <p class="text-muted">Get started by adding your first product</p>
                                @endif
                            </td>
                        </tr>
                        @else
                            @foreach($products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="ms-2">
                                            <h6 class="mb-0 fw-semibold">{{ $product->name }}</h6>
                                            @if($product->barcode)
                                            <span class="text-xs text-muted">{{ $product->barcode }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $product->sku }}</td>
                                <td>₱{{ number_format($product->price, 2) }}</td>
                                <td>
                                    @if($product->stock > 10)
                                        <span class="badge bg-success">{{ $product->stock }}</span>
                                    @elseif($product->stock > 0)
                                        <span class="badge bg-warning">{{ $product->stock }}</span>
                                    @else
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->status)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('products.show', ['subdomain' => $store->slug, 'product_id' => $product->id]) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('products.edit', ['subdomain' => $store->slug, 'product_id' => $product->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('productSearch');
    const searchButton = document.getElementById('searchButton');
    const productsTableBody = document.getElementById('productsTableBody');
    
    // Function to handle search
    function performSearch() {
        const searchTerm = searchInput.value.trim();
        if (searchTerm.length < 1) return;
        
        // Redirect to the current page with search parameter
        window.location.href = '{{ route("products.index", ["subdomain" => $store->slug]) }}?search=' + encodeURIComponent(searchTerm);
    }
    
    // Handle button click
    searchButton.addEventListener('click', performSearch);
    
    // Handle Enter key
    searchInput.addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            performSearch();
        }
    });
    
    // Handle real-time search with debounce (optional)
    let debounceTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            if (searchInput.value.trim().length >= 2) {
                // Make AJAX call for dynamic results
                fetch('{{ route("products.search", ["subdomain" => $store->slug]) }}?q=' + encodeURIComponent(searchInput.value.trim()))
                    .then(response => response.json())
                    .then(data => {
                        if (data.products && data.products.length > 0) {
                            // Display results dynamically (optional implementation)
                        }
                    })
                    .catch(error => console.error('Error searching products:', error));
            }
        }, 500);
    });
});
</script>
@endpush