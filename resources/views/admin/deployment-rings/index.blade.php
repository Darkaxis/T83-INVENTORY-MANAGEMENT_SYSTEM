<!-- resources/views/admin/deployment-rings/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Deployment Rings</h1>
    
    <div class="row">
        <div class="col-md-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Deployment Rings</h6>
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#newRingModal">
                        <i class="fas fa-plus fa-sm"></i> Add Ring
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Ring Name</th>
                                    <th>Stores</th>
                                    <th>Current Version</th>
                                    <th>Auto Update</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rings as $ring)
                                <tr>
                                    <td>
                                        <strong>{{ $ring->name }}</strong>
                                        <div class="small text-muted">{{ $ring->description }}</div>
                                    </td>
                                    <td>
                                        {{ $ring->stores->count() }}
                                        <button class="btn btn-sm btn-link p-0 ms-2" data-bs-toggle="modal" data-bs-target="#storesModal{{ $ring->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                    <td>v{{ $ring->version }}</td>
                                    <td>
                                        @if($ring->auto_update)
                                            <span class="badge bg-success">Enabled</span>
                                        @else
                                            <span class="badge bg-secondary">Manual</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#updateRingModal{{ $ring->id }}">
                                            <i class="fas fa-sync fa-sm"></i> Update
                                        </button>
                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addStoresToRingModal{{ $ring->id }}">
                                            <i class="fas fa-plus fa-sm"></i> Add Stores
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update History</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Version</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($updates->take(10) as $update)
                                <tr>
                                    <td>v{{ $update->version }}</td>
                                    <td>
                                        @if($update->status == 'completed')
                                            <span class="badge badge-success">Completed</span>
                                        @elseif($update->status == 'failed')
                                            <span class="badge badge-danger">Failed</span>
                                        @else
                                            <span class="badge badge-info">{{ ucfirst($update->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $update->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($update->status == 'completed')
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#deployUpdateModal{{ $update->id }}">
                                                <i class="fas fa-cloud-upload-alt fa-sm"></i> Deploy
                                            </button>
                                        @endif
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
</div>

<!-- All modals section - after the tables are closed -->
@foreach($rings as $ring)
    <!-- Store List Modal -->
    <div class="modal fade" id="storesModal{{ $ring->id }}" tabindex="-1" aria-labelledby="storesModalLabel{{ $ring->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="storesModalLabel{{ $ring->id }}">Stores in {{ $ring->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($ring->stores->isEmpty())
                        <p class="text-center text-muted">No stores in this deployment ring.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Store</th>
                                        <th>Subscription</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ring->stores as $store)
                                    <tr>
                                        <td>
                                            <strong>{{ $store->name }}</strong>
                                            <div class="small text-muted">{{ $store->slug }}</div>
                                        </td>
                                        <td>
                                            @if($store->subscription)
                                                {{ $store->subscription->pricing_tier->name }}
                                            @else
                                                Free
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.deployment.move-store') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="store_id" value="{{ $store->id }}">
                                                <div class="input-group input-group-sm">
                                                    <select name="ring_id" class="form-select form-select-sm">
                                                        @foreach($rings as $r)
                                                            @if($r->id != $ring->id)
                                                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                                        Move
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Update Ring Modal -->
    <div class="modal fade" id="updateRingModal{{ $ring->id }}" tabindex="-1" aria-labelledby="updateRingModalLabel{{ $ring->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateRingModalLabel{{ $ring->id }}">Update {{ $ring->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.deployment.update-ring', $ring->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Ring Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $ring->name }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ $ring->description }}</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Order (Lower numbers appear first)</label>
                            <input type="number" name="order" class="form-control" value="{{ $ring->order }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="autoUpdateSwitch{{ $ring->id }}" 
                                    name="auto_update" value="1" {{ $ring->auto_update ? 'checked' : '' }}>
                                <label class="form-check-label" for="autoUpdateSwitch{{ $ring->id }}">Enable Auto-Update</label>
                            </div>
                            <small class="form-text text-muted">
                                When enabled, stores in this ring will automatically receive updates.
                            </small>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Stores to Ring Modal -->
    <div class="modal fade" id="addStoresToRingModal{{ $ring->id }}" tabindex="-1" aria-labelledby="addStoresToRingModalLabel{{ $ring->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStoresToRingModalLabel{{ $ring->id }}">Add Stores to {{ $ring->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.deployment.add-stores', $ring->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Select Stores</label>
                            <div class="alert alert-info">
                                <small>Only stores that aren't already assigned to a deployment ring are shown.</small>
                            </div>
                            
                            <div style="max-height: 300px; overflow-y: auto;" class="border p-3">
                                @php
                                    $availableStores = \App\Models\Store::whereNull('deployment_ring_id')->get();
                                @endphp
                                
                                @if($availableStores->isEmpty())
                                    <p class="text-center text-muted py-3">No available stores found.</p>
                                @else
                                    @foreach($availableStores as $store)
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input" id="store{{ $store->id }}_ring{{ $ring->id }}" name="store_ids[]" value="{{ $store->id }}">
                                            <label class="form-check-label" for="store{{ $store->id }}_ring{{ $ring->id }}">
                                                <strong>{{ $store->name }}</strong>
                                                <small class="text-muted">({{ $store->slug }})</small>
                                                @if($store->subscription)
                                                    <span class="badge bg-info ms-2">{{ $store->subscription->pricing_tier->name }}</span>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success" {{ $availableStores->isEmpty() ? 'disabled' : '' }}>
                                Add Selected Stores
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- Updates modals -->
@foreach($updates->take(10) as $update)
    @if($update->status == 'completed')
    <!-- Deploy Update Modal -->
    <div class="modal fade" id="deployUpdateModal{{ $update->id }}" tabindex="-1" aria-labelledby="deployUpdateModalLabel{{ $update->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deployUpdateModalLabel{{ $update->id }}">Deploy Update v{{ $update->version }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.deployment.deploy-update', $update->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Select Deployment Ring</label>
                            <select name="ring_id" class="form-select">
                                @foreach($rings as $ring)
                                    <option value="{{ $ring->id }}">
                                        {{ $ring->name }} ({{ $ring->stores->count() }} stores)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="alert alert-warning">
                            <p class="mb-0">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                This will deploy version {{ $update->version }} to all stores in the selected ring.
                            </p>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Start Deployment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

<!-- New Ring Modal -->
<div class="modal fade" id="newRingModal" tabindex="-1" aria-labelledby="newRingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newRingModalLabel">Create New Deployment Ring</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.deployment.store-ring') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Ring Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Order (Lower numbers appear first)</label>
                        <input type="number" name="order" class="form-control" value="{{ \App\Models\DeploymentRing::max('order') + 1 }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="autoUpdateSwitch" name="auto_update" value="1">
                            <label class="form-check-label" for="autoUpdateSwitch">Enable Auto-Update</label>
                        </div>
                        <small class="form-text text-muted">
                            When enabled, stores in this ring will automatically receive updates.
                        </small>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Create Ring</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Refresh page after successful form submission
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            // Add a small delay to allow the form to submit
            setTimeout(() => {
                // Check for success message in session
                if (document.querySelector('.alert-success')) {
                    location.reload();
                }
            }, 500);
        });
    });
});
</script>
@endpush