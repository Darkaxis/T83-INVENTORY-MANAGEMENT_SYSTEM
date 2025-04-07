@extends('layouts.app')

@section('title', $store->name . ' - Details')

@section('page-title', 'Store Details')

@section('content')
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between">
            <h6 class="text-white text-capitalize ps-3">{{ $store->name }}</h6>
            <div class="me-3">
              <span class="badge bg-gradient-{{ $store->status === 'active' ? 'success' : 'secondary' }}">{{ $store->status }}</span>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Store Details</h6>
              <ul class="list-group">
                <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong>Name:</strong> {{ $store->name }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>Email:</strong> {{ $store->email }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>Phone:</strong> {{ $store->phone }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>Address:</strong> {{ $store->address }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>City:</strong> {{ $store->city }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>State:</strong> {{ $store->state }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>ZIP:</strong> {{ $store->zip }}</li>
              </ul>
            </div>
            <div class="col-md-6">
              <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Subdomain & Access</h6>
              <ul class="list-group">
                <li class="list-group-item border-0 ps-0 pt-0 text-sm">
                  <strong>Subdomain:</strong> 
                  <a href="http://{{ $store->slug }}.localhost" target="_blank">{{ $store->slug }}.localhost</a>
                </li>
                <li class="list-group-item border-0 ps-0 text-sm">
                  <strong>Admin URL:</strong> 
                  <a href="http://{{ $store->slug }}.localhost/admin" target="_blank">{{ $store->slug }}.localhost/admin</a>
                </li>
                <li class="list-group-item border-0 ps-0 text-sm">
                  <strong>Created:</strong> {{ $store->created_at->format('M d, Y') }}
                </li>
                <li class="list-group-item border-0 ps-0 text-sm">
                  <strong>Last Updated:</strong> {{ $store->updated_at->format('M d, Y') }}
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
            <h6 class="text-white text-capitalize ps-3">Store Actions</h6>
          </div>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2">
            <a href="http://{{ $store->slug }}.localhost/admin" target="_blank" class="btn btn-primary btn-lg">
              <i class="fas fa-external-link-alt me-2"></i> Access Store Admin
            </a>
            <a href="{{ route('stores.edit', $store) }}" class="btn btn-info">
              <i class="fas fa-edit me-2"></i> Edit Store
            </a>
            <a href="{{ route('admin.stores.staff.index', $store) }}" class="btn btn-outline-primary">
              <i class="fas fa-users me-2"></i> Manage Staff
            </a>
            <a href="{{ route('admin.stores.products.index', $store) }}" class="btn btn-outline-primary">
              <i class="fas fa-boxes me-2"></i> View Products
            </a>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteStoreModal">
              <i class="fas fa-trash me-2"></i> Delete Store
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="row mt-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header pb-0">
          <div class="d-flex justify-content-between">
            <h6>Staff Members</h6>
            <a href="{{ route('admin.stores.staff.create', $store) }}" class="btn btn-sm btn-success">
              <i class="fas fa-plus"></i> Add Staff
            </a>
          </div>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Role</th>
                  <th class="text-secondary opacity-7"></th>
                </tr>
              </thead>
              <tbody>
                @forelse($store->users()->limit(5)->get() as $user)
                <tr>
                  <td>
                    <div class="d-flex px-2 py-1">
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm">{{ $user->name }}</h6>
                        <p class="text-xs text-secondary mb-0">{{ $user->email }}</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="text-xs font-weight-bold mb-0">{{ ucfirst($user->role) }}</p>
                  </td>
                  <td class="align-middle">
                    <a href="{{ route('admin.stores.staff.edit', ['store' => $store, 'id' => $user->id]) }}" class="text-secondary font-weight-bold text-xs">
                      Edit
                    </a>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="3" class="text-center py-4">
                    <p class="text-sm mb-0">No staff members yet</p>
                    <a href="{{ route('admin.stores.staff.create', $store) }}" class="btn btn-sm btn-success mt-3">
                      <i class="fas fa-plus"></i> Add Staff Member
                    </a>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
            @if($store->users()->count() > 5)
            <div class="text-center mt-3">
              <a href="{{ route('admin.stores.staff.index', $store) }}" class="text-primary text-sm font-weight-bold">
                View all {{ $store->users()->count() }} staff members
              </a>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-6">
      <div class="card">
        <div class="card-header pb-0">
          <div class="d-flex justify-content-between">
            <h6>Recent Products</h6>
            <a href="{{ route('admin.stores.products.index', $store) }}" class="text-primary text-sm font-weight-bold">
              View All
            </a>
          </div>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Product</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Price</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Stock</th>
                </tr>
              </thead>
              <tbody>
                @forelse($store->products()->latest()->limit(5)->get() as $product)
                <tr>
                  <td>
                    <div class="d-flex px-2 py-1">
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm">{{ $product->name }}</h6>
                        <p class="text-xs text-secondary mb-0">SKU: {{ $product->sku }}</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="text-xs font-weight-bold mb-0">${{ number_format($product->price, 2) }}</p>
                  </td>
                  <td class="align-middle text-center">
                    <span class="text-secondary text-xs font-weight-bold">{{ $product->stock }}</span>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="3" class="text-center py-4">
                    <p class="text-sm mb-0">No products yet</p>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Delete Store Modal -->
<div class="modal fade" id="deleteStoreModal" tabindex="-1" aria-labelledby="deleteStoreModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteStoreModalLabel">Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete <strong>{{ $store->name }}</strong>?</p>
        <p class="text-danger">This action cannot be undone and will permanently delete all store data including staff accounts, products, and inventory.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form action="{{ route('stores.destroy', $store) }}" method="POST">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Delete Store</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection