<!-- resources/views/stores/index.blade.php -->
@extends('layouts.app')

@section('title', 'Store Management')

@section('page-title', 'Stores')

@section('content')
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12">
      <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between">
            <h6 class="text-white text-capitalize ps-3">All Stores</h6>
          </div>
        </div>
        <div class="card-body px-0 pb-2">
          @if(session('success'))
            <div class="alert alert-success mx-3">
              {{ session('success') }}
            </div>
          @endif
          
          <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Store</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Location</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subdomain</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Staff</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Products</th>
                  <th class="text-secondary opacity-7" style="min-width: 200px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($stores as $store)
                <tr>
                  <td>
                    <div class="d-flex px-2 py-1">
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm">
                          <a href="{{ route('stores.show', $store) }}">{{ $store->name }}</a>
                        </h6>
                        <p class="text-xs text-secondary mb-0">{{ $store->email }}</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="text-xs font-weight-bold mb-0">{{ $store->city }}, {{ $store->state }}</p>
                    <p class="text-xs text-secondary mb-0">{{ $store->address }}</p>
                  </td>
                  <td class="align-middle text-center text-sm">
                    <span class="badge badge-sm bg-gradient-{{ $store->status === 'active' ? 'success' : 'secondary' }}">{{ $store->status }}</span>
                  </td>
                  <td class="align-middle text-center">
                    <a href="http://{{ $store->slug }}.localhost/admin" target="_blank" class="text-secondary font-weight-bold text-xs">
                      {{ $store->slug }}.localhost
                    </a>
                  </td>
                  <td class="align-middle text-center">
                    <a href="{{ route('admin.stores.staff.index', $store) }}" class="text-primary text-xs">
                      {{ $store->users()->count() }} members
                    </a>
                  </td>
                  <td class="align-middle text-center">
                    <a href="{{ route('admin.stores.products.index', $store) }}" class="text-primary text-xs">
                      {{ $store->products()->count() }} items
                    </a>
                  </td>
                  <td class="align-middle">
                    <div class="btn-group">
                      <a href="{{ route('stores.edit', $store) }}" class="btn btn-sm btn-info me-1">
                        <i class="fas fa-edit"></i> Edit
                      </a>
                      <a href="http://{{ $store->slug }}.localhost/admin" target="_blank" class="btn btn-sm btn-primary me-1">
                        <i class="fas fa-external-link-alt"></i> Access
                      </a>
                      <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $store->id }}">
                        <i class="fas fa-trash"></i> Delete
                      </button>
                    </div>
                    
                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteModal{{ $store->id }}" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            Are you sure you want to delete <strong>{{ $store->name }}</strong>? 
                            This will permanently remove all store data including products, staff, and inventory records.
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <form action="{{ route('stores.destroy', $store) }}" method="POST" class="d-inline">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-danger">Delete Store</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="row mt-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header p-3">
          <h5 class="mb-0">Quick Actions</h5>
          <p class="text-sm mb-0">Manage your multi-tenant system</p>
        </div>
        <div class="card-body p-3">
          <div class="row">
            <div class="col-md-4">
              <div class="card card-body border">
                <h6 class="mb-3">Add New Store</h6>
                <p class="mb-3 text-sm">Create a new store with its own subdomain, staff and inventory.</p>
                <a href="{{ route('stores.create') }}" class="btn btn-sm btn-success">
                  <i class="fas fa-plus"></i> Create Store
                </a>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card card-body border">
                <h6 class="mb-3">Manage System Users</h6>
                <p class="mb-3 text-sm">Add or edit system administrators with full access.</p>
                <a href="#" class="btn btn-sm btn-info">
                  <i class="fas fa-users"></i> Manage Users
                </a>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card card-body border">
                <h6 class="mb-3">System Settings</h6>
                <p class="mb-3 text-sm">Configure global settings for your inventory system.</p>
                <a href="#" class="btn btn-sm btn-primary">
                  <i class="fas fa-cogs"></i> Settings
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Initialize Bootstrap tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })
</script>
@endpush