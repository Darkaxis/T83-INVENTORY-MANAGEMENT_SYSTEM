<!-- filepath: d:\WST\inventory-management-system\resources\views\stores\edit.blade.php -->
@extends('layouts.app')

@section('title', 'Edit Store')

@section('page-title', 'Edit Store')

@section('content')
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12">
      <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
            <h6 class="text-white text-capitalize ps-3">Edit Store: {{ $store->name }}</h6>
          </div>
        </div>
        <div class="card-body px-0 pb-2">
          <div class="container">
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
            
            @if($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif
            
            <form action="{{ route('stores.update', $store) }}" method="POST">
              @csrf
              @method('PUT')
              
              <div class="row">
                <div class="col-md-6">
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Store Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $store->name) }}" required>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Subdomain Slug</label>
                    <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug', $store->slug) }}" 
                           pattern="[a-z0-9\-]+" title="Only lowercase letters, numbers, and hyphens are allowed" required>
                    <small class="form-text text-muted d-block mt-1">
                      This will be used for the store URL: http://{{ $store->slug }}.localhost
                    </small>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $store->email) }}">
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $store->phone) }}">
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-12">
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $store->address) }}">
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-4">
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">City</label>
                    <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $store->city) }}">
                  </div>
                </div>
                
                <div class="col-md-4">
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">State</label>
                    <input type="text" class="form-control" id="state" name="state" value="{{ old('state', $store->state) }}">
                  </div>
                </div>
                
                <div class="col-md-4">
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">ZIP Code</label>
                    <input type="text" class="form-control" id="zip" name="zip" value="{{ old('zip', $store->zip) }}">
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group my-3">
                    <label for="status" class="ms-0">Status</label>
                    <select class="form-control" id="status" name="status">
                      <option value="active" {{ old('status', $store->status) == 'active' ? 'selected' : '' }}>Active</option>
                      <option value="inactive" {{ old('status', $store->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                  </div>
                </div>
                
                @hasrole('super_admin')
                <div class="col-md-6">
                  <div class="form-group my-3">
                    <label for="approved" class="ms-0">Approval Status</label>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="approved" name="approved" value="1" {{ $store->approved ? 'checked' : '' }}>
                      <label class="form-check-label" for="approved">
                        Approved for database creation
                      </label>
                    </div>
                    <small class="form-text text-muted d-block mt-1">
                      @if(!$store->approved)
                        Approving this store will create its database automatically.
                      @elseif($store->approved && !$store->database_created)
                        Store is approved, but database hasn't been created yet.
                      @else
                        Store is approved and database is created.
                      @endif
                    </small>
                  </div>
                </div>
                @endhasrole
              </div>
              
              @if(!$store->approved)
                <div class="alert alert-warning">
                  <div class="d-flex">
                    <div>
                      <i class="fas fa-exclamation-triangle me-2"></i>
                    </div>
                    <div>
                      <h4 class="alert-heading">Pending Approval</h4>
                      <p>This store needs admin approval before a database can be created.</p>
                      
                      @hasrole('super_admin')
                      <form action="{{ route('stores.approve', $store) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-warning">
                          <i class="fas fa-check me-1"></i> Approve & Create Database
                        </button>
                      </form>
                      @endhasrole
                    </div>
                  </div>
                </div>
              @elseif(!$store->database_connected)
                <div class="alert alert-warning">
                  <div class="d-flex">
                    <div>
                      <i class="fas fa-exclamation-triangle me-2"></i>
                    </div>
                    
                  </div>
                </div>
              @endif
              
              <div class="row mt-4">
                <div class="col-12">
                  <button type="submit" class="btn bg-gradient-primary">
                    <i class="fas fa-save me-1"></i> Update Store
                  </button>
                  <a href="{{ route('stores.index') }}" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                  </a>
                  
                  @if($store->database_connected)
                  <a href="http://{{ $store->slug }}.localhost/admin" target="_blank" class="btn btn-outline-info ms-2">
                    <i class="fas fa-external-link-alt me-1"></i> Access Store Admin
                  </a>
                  @endif
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Database information card -->
  <div class="row mt-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header p-3 pt-2">
          <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
            <i class="fas fa-database"></i>
          </div>
          <div class="text-end pt-1">
            <p class="text-sm mb-0 text-capitalize">Database Status</p>
            <h4 class="mb-0">
              @if(!$store->approved)
                <span class="text-warning">Pending Approval</span>
              @elseif($store->database_connected) 
                <span class="text-success">Connected</span>
              @else 
                <span class="text-danger">Disconnected</span>
              @endif
            </h4>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Details</h6>
              <ul class="list-group">
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">Database Name</h6>
                    </div>
                  </div>
                  <div class="d-flex align-items-center text-sm">
                    tenant_{{ $store->slug }}
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">Connection Status</h6>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    @if(!$store->approved)
                      <span class="badge bg-gradient-warning">Pending Approval</span>
                    @elseif($store->database_connected)
                      <span class="badge bg-gradient-success">Connected</span>
                    @else
                      <span class="badge bg-gradient-danger">Disconnected</span>
                    @endif
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">Approval Status</h6>
                    </div>
                  </div>
                  <div class="d-flex align-items-center">
                    @if($store->approved)
                      <span class="badge bg-gradient-success">Approved</span>
                    @else
                      <span class="badge bg-gradient-warning">Pending</span>
                    @endif
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="card-footer p-3">
          @if(!$store->approved)
            @hasrole('super_admin')
            <form action="{{ route('stores.approve', $store) }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-sm btn-warning w-100">
                <i class="fas fa-check me-1"></i> Approve & Create Database
              </button>
            </form>
            @else
            <button type="button" class="btn btn-sm btn-outline-secondary w-100" disabled>
              <i class="fas fa-clock me-1"></i> Awaiting Admin Approval
            </button>
            @endhasrole
         
            <button type="button" class="btn btn-sm btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#resetDatabaseModal">
              <i class="fas fa-exclamation-triangle me-1"></i> Reset Database
            </button>
            
            <!-- Reset Database Modal -->
            <div class="modal fade" id="resetDatabaseModal" tabindex="-1" aria-labelledby="resetDatabaseModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="resetDatabaseModalLabel">Confirm Database Reset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="alert alert-danger">
                      <strong>WARNING:</strong> This will delete and recreate the store's database. All data for this store will be permanently lost.
                    </div>
                    <p>Are you absolutely sure you want to reset the database for <strong>{{ $store->name }}</strong>?</p>
                    <p>Please type <strong>{{ $store->slug }}</strong> to confirm:</p>
                    <input type="text" id="confirmSlug" class="form-control">
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                 
                      @csrf
                      <button type="submit" id="confirmResetBtn" class="btn btn-danger" disabled>Reset Database</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
    
    <div class="col-md-6">
      <div class="card">
        <div class="card-header p-3 pt-2">
          <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
            <i class="fas fa-users"></i>
          </div>
          <div class="text-end pt-1">
            <p class="text-sm mb-0 text-capitalize">Staff Members</p>
            <h4 class="mb-0">{{ $store->database_connected ? ($store->users_count ?? 0) : 'N/A' }}</h4>
          </div>
        </div>
        <div class="card-body">
          <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Management Options</h6>
          @if($store->approved && $store->database_connected)
          <div class="list-group">
            <a href="{{ route('admin.stores.staff.index', $store) }}" class="list-group-item list-group-item-action">
              <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1">Manage Staff</h6>
                <i class="fas fa-chevron-right"></i>
              </div>
              <p class="mb-1 text-sm">View, add, edit, or remove staff members</p>
            </a>
            <a href="{{ route('admin.stores.products.index', $store) }}" class="list-group-item list-group-item-action">
              <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1">Browse Products</h6>
                <i class="fas fa-chevron-right"></i>
              </div>
              <p class="mb-1 text-sm">View and manage store products</p>
            </a>
            
          </div>
          @else
          <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-1"></i> Management options will be available after the store is approved and the database is created.
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Script to enable the reset database button only when the confirmation text matches
  document.addEventListener('DOMContentLoaded', function() {
    const confirmInput = document.getElementById('confirmSlug');
    const confirmBtn = document.getElementById('confirmResetBtn');
    const storeSlug = '{{ $store->slug }}';
    
    if (confirmInput && confirmBtn) {
      confirmInput.addEventListener('input', function() {
        confirmBtn.disabled = confirmInput.value !== storeSlug;
      });
    }
  });
</script>
@endpush