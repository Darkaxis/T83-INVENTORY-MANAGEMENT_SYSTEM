@extends('layouts.app')

@section('title', $store->name . ' - Products')

@section('page-title', 'Store Products')

@section('content')
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12">
      <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between">
            <h6 class="text-white text-capitalize ps-3">{{ $store->name }} - Products</h6>
            <a href="{{ route('stores.show', $store) }}" class="btn btn-sm btn-info me-3">
              Back to Store
            </a>
          </div>
        </div>
        <div class="card-body px-0 pb-2">
          <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Product</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">SKU</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Price</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Stock</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                  <th class="text-secondary opacity-7"></th>
                </tr>
              </thead>
              <tbody>
                @forelse($products as $product)
                <tr>
                  <td>
                    <div class="d-flex px-2 py-1">
                      @if($product->image)
                        <div class="me-3">
                          <img src="{{ asset('storage/' . $product->image) }}" class="avatar avatar-sm border-radius-lg" alt="{{ $product->name }}">
                        </div>
                      @endif
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm">{{ $product->name }}</h6>
                        <p class="text-xs text-secondary mb-0">
                          @if($product->category)
                            {{ $product->category->name }}
                          @else
                            No Category
                          @endif
                        </p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="text-xs font-weight-bold mb-0">{{ $product->sku }}</p>
                    @if($product->barcode)
                      <p class="text-xs text-secondary mb-0">Barcode: {{ $product->barcode }}</p>
                    @endif
                  </td>
                  <td class="align-middle text-center">
                    <span class="text-secondary text-xs font-weight-bold">${{ number_format($product->price, 2) }}</span>
                    @if($product->sale_price)
                      <br><span class="text-danger text-xs font-weight-bold">${{ number_format($product->sale_price, 2) }}</span>
                    @endif
                  </td>
                  <td class="align-middle text-center">
                    <span class="badge badge-sm bg-gradient-{{ $product->stock > $product->min_stock ? 'success' : 'danger' }}">
                      {{ $product->stock }}
                    </span>
                  </td>
                  <td class="align-middle text-center text-sm">
                    <span class="badge badge-sm bg-gradient-{{ $product->status === 'active' ? 'success' : 'secondary' }}">
                      {{ $product->status }}
                    </span>
                  </td>
                  <td class="align-middle">
                    <a href="http://{{ $store->slug }}.localhost/admin/products/{{ $product->id }}/edit" target="_blank" class="text-secondary font-weight-bold text-xs">
                      Edit in Store
                    </a>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="text-center py-4">
                    <p class="text-sm mb-0">No products found</p>
                    <a href="http://{{ $store->slug }}.localhost/admin/products/create" target="_blank" class="btn btn-sm btn-success mt-3">
                      Add Products in Store Admin
                    </a>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          
          <div class="d-flex justify-content-center mt-3">
            {{ $products->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="row mt-4">
    <div class="col-12">
      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="mb-0">Want to manage products directly?</p>
            <p class="text-sm text-muted">You can add, edit, and manage products from the store admin panel.</p>
          </div>
          <a href="http://{{ $store->slug }}.localhost/admin/products" target="_blank" class="btn btn-primary">
            Go to Store Admin
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection