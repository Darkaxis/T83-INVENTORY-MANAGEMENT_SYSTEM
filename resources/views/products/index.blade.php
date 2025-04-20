<!-- filepath: d:\WST\inventory-management-system\resources\views\products\index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Products</h1>
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
            
            @if(session('limit_data'))
                <div class="mt-2">
                    <p>You have {{ session('limit_data.current') }} products out of {{ session('limit_data.limit') }} allowed in your current plan.</p>
                    <a href="/{{ $store->slug }}/subscription" class="btn btn-warning">
                        Upgrade Your Plan
                    </a>
                </div>
            @endif
        </div>
    @endif
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    <div class="mb-3 d-flex align-items-center">
        @php
            $canAddProducts = $store->canAddProducts();
        @endphp
        
        @if($canAddProducts)
            <a href="{{ route('products.create', ['subdomain' => $store->slug]) }}" class="btn btn-primary">Add New Product</a>
        @else
            <button class="btn btn-secondary" disabled title="Product limit reached">Add New Product</button>
            <div class="text-danger ml-3">
                <span>You've reached your product limit. <a href="/{{ $store->slug }}/subscription">Upgrade your plan</a> to add more.</span>
            </div>
        @endif
        
        @if(!$products->isEmpty())
            <div class="ml-auto">
                <span class="badge {{ $canAddProducts ? 'badge-success' : 'badge-danger' }}">
                    {{ $products->total() }} <!-- Changed from count() to total() for pagination -->
                    @if(session('limit_data'))
                        / {{ session('limit_data.limit') }}
                    @endif
                    products
                </span>
            </div>
        @endif
    </div>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>${{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->stock ?? $product->quantity }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('products.show', ['subdomain' => $store->slug, 'product_id' => $product->id]) }}" 
                               class="btn btn-info btn-sm">
                                <i class="fa fa-eye"></i> View
                            </a>
                            <a href="{{ route('products.edit', ['subdomain' => $store->slug, 'product_id' => $product->id]) }}" 
                               class="btn btn-warning btn-sm">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('products.destroy', ['subdomain' => $store->slug, 'product_id' => $product->id]) }}" 
                                  method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" 
                                        onclick="return confirm('Are you sure you want to delete this product?')">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No products found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Add pagination links -->
    <div class="d-flex justify-content-center">
        {{ $products->appends(['subdomain' => $store->slug])->links() }}
    </div>
</div>
@endsection