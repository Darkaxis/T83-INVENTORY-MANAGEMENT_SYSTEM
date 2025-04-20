<!-- filepath: d:\WST\inventory-management-system\resources\views\stores\index.blade.php -->
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
            <a href="{{ route('stores.create') }}" class="btn btn-sm btn-success me-3">Add Store</a>
          </div>
        </div>
        <div class="card-body px-0 pb-2">
          @if(session('success'))
            <div class="alert alert-success mx-3">
              {{ session('success') }}
            </div>
          @endif
          
          @if(session('error'))
            <div class="alert alert-danger mx-3">
              {{ session('error') }}
            </div>
          @endif
          
          @if(session('warning'))
            <div class="alert alert-warning mx-3">
              {{ session('warning') }}
            </div>
          @endif
          
          <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Store</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Location</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pricing Tier</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subdomain</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Staff</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Products</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Approval</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">DB Status</th>
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
                  <td class="align-middle text-center text-sm">
                    <div class="d-flex flex-column align-items-center">
                      @if($store->pricingTier)
                        <span class="badge badge-sm bg-gradient-info mb-1">{{ $store->pricingTier->name }}</span>
                        <small class="d-block text-xs">
                          {{ $store->billing_cycle === 'monthly' ? '₱' . $store->pricingTier->monthly_price . '/mo' : '$' . $store->pricingTier->annual_price . '/yr' }}
                        </small>
                        <button type="button" class="btn btn-xs btn-primary mt-1" data-bs-toggle="modal" data-bs-target="#changeTierModal{{ $store->id }}">
                          Change
                        </button>
                      @else
                        <span class="badge badge-sm bg-gradient-secondary">No Tier</span>
                        <button type="button" class="btn btn-xs btn-primary mt-1" data-bs-toggle="modal" data-bs-target="#changeTierModal{{ $store->id }}">
                          Assign
                        </button>
                      @endif
                    </div>
                    
                    <!-- Change Pricing Tier Modal -->
                    <div class="modal fade" id="changeTierModal{{ $store->id }}" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Change Pricing Tier for {{ $store->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <form action="{{ route('stores.updatePricingTier', $store) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                              <div class="mb-3">
                                <label class="form-label">Current Tier</label>
                                <input type="text" class="form-control" disabled value="{{ $store->pricingTier->name ?? 'None' }}">
                              </div>
                              
                              <div class="mb-3">
                                <label for="pricing_tier_id{{ $store->id }}" class="form-label">New Pricing Tier</label>
                                <select id="pricing_tier_id{{ $store->id }}" name="pricing_tier_id" class="form-select" required>
                                  <option value="">Select a pricing tier</option>
                                  @foreach(\App\Models\PricingTier::all() as $tier)
                                  <option value="{{ $tier->id }}" {{ $store->pricing_tier_id == $tier->id ? 'selected' : '' }}>
                                    {{ $tier->name }} - ₱{{ $tier->monthly_price }}/mo | ₱{{ $tier->annual_price }}/yr
                                  </option>
                                  @endforeach
                                </select>
                              </div>
                              
                              <div class="mb-3">
                                <label for="billing_cycle{{ $store->id }}" class="form-label">Billing Cycle</label>
                                <select id="billing_cycle{{ $store->id }}" name="billing_cycle" class="form-select" required>
                                  <option value="monthly" {{ $store->billing_cycle === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                  <option value="annual" {{ $store->billing_cycle === 'annual' ? 'selected' : '' }}>Annual</option>
                                </select>
                              </div>
                              
                              <div class="mb-3">
                                <div class="form-check">
                                  <input type="checkbox" id="auto_renew{{ $store->id }}" name="auto_renew" class="form-check-input" {{ $store->auto_renew ? 'checked' : '' }}>
                                  <label class="form-check-label" for="auto_renew{{ $store->id }}">Auto-renew subscription</label>
                                </div>
                              </div>
                              
                              <div class="mb-3">
                                <div class="form-check">
                                  <input type="checkbox" id="reset_dates{{ $store->id }}" name="reset_dates" class="form-check-input" checked>
                                  <label class="form-check-label" for="reset_dates{{ $store->id }}">Reset subscription dates</label>
                                </div>
                                <small class="text-muted">If checked, subscription will start today and end in 1 month/year.</small>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                              <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="align-middle text-center">
                    <a href="http://{{ $store->slug }}.inventory.test/" target="_blank" class="text-secondary font-weight-bold text-xs">
                      {{ $store->slug }}.inventory.test
                    </a>
                  </td>
                  <td class="align-middle text-center">
                    <a href="{{ route('admin.stores.staff.index', $store) }}" class="text-primary text-xs">
                      <h4 class="mb-0">{{ $store->database_connected ? $store->getTenantUserCount() : 'N/A' }}</h4>
                    </a>
                    @if($store->pricingTier && $store->pricingTier->user_limit > 0)
                      <small class="d-block text-xs text-muted">
                        Limit: {{ $store->pricingTier->user_limit }}
                      </small>
                    @endif
                  </td>
                  <td class="align-middle text-center">
                    <a href="{{ route('admin.stores.products.index', $store) }}" class="text-primary text-xs">
                      <h4 class="mb-0">{{ $store->database_connected ? $store->getTenantProductCount() : 'N/A' }}</h4>
                    </a>
                    @if($store->pricingTier && $store->pricingTier->product_limit > 0)
                      <small class="d-block text-xs text-muted">
                        Limit: {{ $store->pricingTier->product_limit }}
                      </small>
                    @endif
                  </td>
                  <td class="align-middle text-center text-sm">
                    @if($store->approved)
                      <span class="badge badge-sm bg-gradient-success">Approved</span>
                    @else
                      <span class="badge badge-sm bg-gradient-warning">Pending</span>
                      
                      <form action="{{ route('stores.approve', $store) }}" method="POST" class="mt-1">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-warning">
                          Approve
                        </button>
                      </form>
                      
                    @endif
                  </td>
                  <td class="align-middle text-center">
                    @if($store->database_connected)
                      <span class="badge badge-sm bg-gradient-success">Connected</span>
                    @else
                      <span class="badge badge-sm bg-gradient-danger">Disconnected</span>
                     
                    @endif
                  </td>
                  
                  <td class="align-middle">
                    <div class="btn-group">
                      <a href="{{ route('stores.edit', $store) }}" class="btn btn-sm btn-info me-1">
                        <i class="fas fa-edit"></i> Edit
                      </a>
                      <a href="http://{{ $store->slug }}.inventory.test" target="_blank" class="btn btn-sm btn-primary me-1">
                        <i class="fas fa-external-link-alt"></i> Access
                      </a>
                      
                      <form action="{{ route('stores.toggleStatus', $store) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $store->status === 'active' ? 'btn-warning' : 'btn-success' }} me-1">
                          <i class="fas {{ $store->status === 'active' ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                          {{ $store->status === 'active' ? 'Disable' : 'Enable' }}
                        </button>
                      </form>
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
                            <p>Are you sure you want to delete <strong>{{ $store->name }}</strong>?</p>
                            <p>This will permanently remove all store data including products, staff, and inventory records.</p>
                            <div class="alert alert-warning">
                              <strong>Important:</strong> This action will also delete the tenant database <code>tenant_{{ $store->slug }}</code>.
                            </div>
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
  
  <!-- Quick actions panel -->
  <div class="row mt-4">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header p-3 pt-2">
          <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
            <i class="fas fa-store"></i>
          </div>
          <div class="text-end pt-1">
            <p class="text-sm mb-0 text-capitalize">Total Stores</p>
            <h4 class="mb-0">{{ $stores->count() }}</h4>
          </div>
        </div>
        <hr class="dark horizontal my-0">
        <div class="card-footer p-3">
          <p class="mb-0">
            <a href="{{ route('stores.create') }}" class="text-primary text-sm font-weight-bolder">
              <i class="fas fa-plus-circle me-1"></i> Add New Store
            </a>
          </p>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="card">
        <div class="card-header p-3 pt-2">
          <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
            <i class="fas fa-database"></i>
          </div>
          <div class="text-end pt-1">
            <p class="text-sm mb-0 text-capitalize">Connected Databases</p>
            <h4 class="mb-0">{{ $stores->where('database_connected', true)->count() }}/{{ $stores->count() }}</h4>
          </div>
        </div>
        <hr class="dark horizontal my-0">
        <div class="card-footer p-3">
          <p class="mb-0">
            @if($stores->where('database_connected', false)->count() > 0)
              <span class="text-warning text-sm font-weight-bolder">
                <i class="fas fa-exclamation-triangle me-1"></i> {{ $stores->where('database_connected', false)->count() }} database(s) need attention
              </span>
            @else
              <span class="text-success text-sm font-weight-bolder">
                <i class="fas fa-check-circle me-1"></i> All databases connected
              </span>
            @endif
          </p>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="card">
        <div class="card-header p-3 pt-2">
          <div class="icon icon-lg icon-shape bg-gradient-warning shadow-warning text-center border-radius-xl mt-n4 position-absolute">
            <i class="fas fa-cogs"></i>
          </div>
          <div class="text-end pt-1">
            <p class="text-sm mb-0 text-capitalize">Active Stores</p>
            <h4 class="mb-0">{{ $stores->where('status', 'active')->count() }}/{{ $stores->count() }}</h4>
          </div>
        </div>
        <hr class="dark horizontal my-0">
        <div class="card-footer p-3">
          <p class="mb-0">
            <a href="{{ route('admin.settings.tenant') }}" class="text-info text-sm font-weight-bolder">
              <i class="fas fa-sliders-h me-1"></i> Tenant System Settings
            </a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // You could add some JavaScript to enhance the UI
    // For example, highlight rows with database issues
    const problemRows = document.querySelectorAll('.badge.bg-gradient-danger');
    problemRows.forEach(badge => {
      const row = badge.closest('tr');
      row.classList.add('table-warning');
    });
  });
</script>
@endpush