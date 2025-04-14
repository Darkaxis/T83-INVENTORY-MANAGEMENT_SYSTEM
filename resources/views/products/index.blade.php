<!-- filepath: d:\WST\inventory-management-system\resources\views\products\index.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Products for {{ $store->name }}</h1>
    <a href="{{ route('products.create', ['subdomain' => $store->slug]) }}" class="btn btn-primary mb-3">Add Product</a>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->price }}</td>
                    <td>{{ $product->stock }}</td>
                    <td>
                        <a href="{{ route('products.show', ['subdomain' => $store->slug, 'product' => $product->id]) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('products.edit', ['subdomain' => $store->slug, 'product' => $product->id]) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('products.destroy', ['subdomain' => $store->slug, 'product' => $product->id]) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection