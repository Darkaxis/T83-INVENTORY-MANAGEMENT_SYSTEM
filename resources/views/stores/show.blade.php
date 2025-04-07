<!-- filepath: d:\WST\inventory-management-system\resources\views\stores\show.blade.php -->
@extends('layouts.app')

@section('title', $store->name ?? 'Store Details')

@section('page-title', 'Store Details')

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
  
  @if(session('warning'))
    <div class="alert alert-warning">
      {{ session('warning') }}
    </div>
  @endif

  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between">
            <h6 class="text-white text-capitalize ps-3">{{ $store->name ?? 'Store' }}</h6>
            <div class="me-3">
              <span class="badge bg-gradient-{{ ($store->status ?? 'inactive') === 'active' ? 'success' : 'secondary' }}">{{ $store->status ?? 'inactive' }}</span>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Store Details</h6>
              <ul class="list-group">
                <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong>Name:</strong> {{ $store->name ?? 'N/A' }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>Email:</strong> {{ $store->email ?? 'N/A' }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>Phone:</strong> {{ $store->phone ?? 'N/A' }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>Address:</strong> {{ $store->address ?? 'N/A' }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>City:</strong> {{ $store->city ?? 'N/A' }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>State:</strong> {{ $store->state ?? 'N/A' }}</li>
                <li class="list-group-item border-0 ps-0 text-sm"><strong>ZIP:</strong> {{ $store->zip ?? 'N/A' }}</li>
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
                  <strong>Created:</strong> {{ $store->created_at ? $store->created_at->format('M d, Y') : 'N/A' }}
                </li>
                <li class="list-group-item border-0 ps-0 text-sm">
                  <strong>Last Updated:</strong> {{ $store->updated_at ? $store->updated_at->format('M d, Y') : 'N/A' }}
                </li>
                <li class="list-group-item border-0 ps-0 text-sm">
                  <strong>Database Status:</strong> 
                  @if(isset($dbExists) && $dbExists)
                    <span class="badge bg-gradient-success">Connected</span>
                  @else
                    <span class="badge bg-gradient-danger">Not Connected</span>
                  @endif
                </li>
              </ul>
            </div>
          </div>

          <div class="row mt-4">
            <div class="col-md-6">
              <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Store Statistics</h6>
              <div class="row">
                <div class="col-6">
                  <div class="card bg-gradient-light">
                    <div class="card-body p-3">
                      <div class="row">
                        <div class="col-8">
                          <div class="numbers">
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Staff</p>
                            <h5 class="font-weight-bolder mb-0">
                              {{ $store->users_count ?? 0 }}
                            </h5>
                          </div>
                        </div>
                        <div class="col-4 text-end">
                          <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                            <i class="fas fa-users text-lg opacity-10" aria-hidden="true"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="card bg-gradient-light">
                    <div class="card-body p-3">
                      <div class="row">
                        <div class="col-8">
                          <div class="numbers">
                            <p class="text-sm mb-0 text-capitalize font-weight-bold">Products</p>
                            <h5 class="font-weight-bolder mb-0">
                              {{ $store->products_count ?? 0 }}
                            </h5>
                          </div>
                        </div>
                        <div class="col-4 text-end">
                          <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                            <i class="fas fa-boxes text-lg opacity-10" aria-hidden="true"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Database Management</h6>
              <div class="card bg-gradient-light">
                <div class="card-body p-3">
                  <div class="d-flex align-items-center">
                    <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md me-3">
                      <i class="fas fa-database text-lg opacity-10" aria-hidden="true"></i>
                    </div>
                    <div>
                      <h6 class="mb-1">Tenant Database</h6>
                      <p class="text-sm mb-0">Database: <strong>tenant_{{ $store->slug }}</strong></p>
                    </div>
                  </div>
                  
                  @if(isset($dbExists) && !$dbExists)
                    <div class="alert alert-warning text-white mt-3 mb-0">
                      <i class="fas fa-exclamation-triangle me-1"></i>
                      Database connection issue detected. Use the Rebuild button to recreate the tenant database.
                    </div>
                    <form action="{{ route('stores.rebuild-database', $store) }}" method="POST" class="mt-3">
                      @csrf
                      <button type="submit" class="btn btn-warning w-100">
                        <i class="fas fa-sync-alt me-1"></i> Rebuild Database
                      </button>
                    </form>
                  @else
                    <form action="{{ route('stores.rebuild-database', $store) }}" method="POST" class="mt-3">
                      @csrf
                      <button type="submit" class="btn btn-outline-warning w-100" onclick="return confirm('Are you sure you want to rebuild the database? This will delete ALL store data and create a fresh database.')">
                        <i class="fas fa-sync-alt me-1"></i> Reset & Rebuild Database
                      </button>
                    </form>
                  @endif
                </div>
              </div>
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
              <i class="fas fa-users me-2"></i> Manage Staff ({{ $store->users_count ?? 0 }})
            </a>
            <a href="{{ route('admin.stores.products.index', $store) }}" class="btn btn-outline-primary">
              <i class="fas fa-boxes me-2"></i> View Products ({{ $store->products_count ?? 0 }})
            </a>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteStoreModal">
              <i class="fas fa-trash me-2"></i> Delete Store
            </button>
          </div>
        </div>
      </div>
      
      <div class="card mt-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-warning shadow-warning border-radius-lg pt-4 pb-3">
            <h6 class="text-white text-capitalize ps-3">Connection Info</h6>
          </div>
        </div>
        <div class="card-body">
          <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Tenant Database</h6>
          <ul class="list-group">
            <li class="list-group-item border-0 d-flex p-2 mb-2 bg-gray-100 border-radius-lg">
              <div class="d-flex flex-column">
                <span class="mb-1 text-xs">Database Name</span>
                <span class="text-sm font-weight-bold">tenant_{{ $store->slug }}</span>
              </div>
            </li>
            <li class="list-group-item border-0 d-flex p-2 mb-2 bg-gray-100 border-radius-lg">
              <div class="d-flex flex-column">
                <span class="mb-1 text-xs">Status</span>
                <span class="text-sm font-weight-bold">
                  @if(isset($dbExists) && $dbExists)
                    <span class="text-success">Connected</span>
                  @else
                    <span class="text-danger">Not Connected</span>
                  @endif
                </span>
              </div>
            </li>
          </ul>
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
          <p>Are you sure you want to delete <strong>{{ $store->name ?? 'this store' }}</strong>?</p>
          <p class="text-danger">This action cannot be undone and will permanently delete all store data including staff accounts, products, and inventory.</p>
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-1"></i>
            This will also permanently delete the tenant database <strong>tenant_{{ $store->slug }}</strong>.
          </div>
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
</div>
@endsection